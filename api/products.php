<?php

header("Content-Type: application/json");

include "../config/db.php";

// Fetch all products
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

$products = [];

if ($result && $result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

        $products[] = $row;

    }

}

// Return products as JSON
echo json_encode([
    "status" => "success",
    "total_products" => count($products),
    "products" => $products
], JSON_PRETTY_PRINT);

$conn->close();

?>