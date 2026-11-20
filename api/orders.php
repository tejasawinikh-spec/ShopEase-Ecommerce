<?php

session_start();

header("Content-Type: application/json");

include "../config/db.php";

// Allow only logged-in admins
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "admin") {

    http_response_code(403);

    echo json_encode([
        "status" => "error",
        "message" => "Access denied. Admin login required."
    ], JSON_PRETTY_PRINT);

    exit();
}

// Fetch all orders
$sql = "SELECT * FROM orders ORDER BY order_date DESC";

$result = $conn->query($sql);

$orders = [];

if ($result && $result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

        $order_id = $row["order_id"];

        // Fetch products belonging to this order
        $stmt = $conn->prepare(
            "SELECT product_id, product_name, price, quantity, subtotal
             FROM order_items
             WHERE order_id = ?"
        );

        $stmt->bind_param("i", $order_id);

        $stmt->execute();

        $items_result = $stmt->get_result();

        $items = [];

        while ($item = $items_result->fetch_assoc()) {

            $items[] = $item;

        }

        $stmt->close();

        // Attach purchased products to the order
        $row["items"] = $items;

        $orders[] = $row;

    }

}

// Return complete order information
echo json_encode([
    "status" => "success",
    "total_orders" => count($orders),
    "orders" => $orders
], JSON_PRETTY_PRINT);

$conn->close();

?>