<?php
session_start();

include "../config/db.php";

// Admin access protection
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "admin") {
    header("Location: ../user/login.php");
    exit();
}

// Available order statuses
$statuses = [
    "Pending",
    "Processing",
    "Shipped",
    "Delivered",
    "Cancelled"
];

$message = "";
$error = "";

// Update order status
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_status"])) {

    $order_id = filter_var($_POST["order_id"], FILTER_VALIDATE_INT);
    $new_status = $_POST["order_status"] ?? "";

    if (!$order_id || !in_array($new_status, $statuses, true)) {

        $error = "Invalid order or status.";

    } else {

        $stmt = $conn->prepare(
            "UPDATE orders SET order_status = ? WHERE order_id = ?"
        );

        $stmt->bind_param("si", $new_status, $order_id);

        if ($stmt->execute()) {

            if ($stmt->affected_rows >= 0) {
                $message = "Order status updated successfully!";
            } else {
                $error = "Unable to update order status.";
            }

        } else {

            $error = "Something went wrong while updating the status.";

        }

        $stmt->close();
    }
}

// Fetch all customer orders
$sql = "SELECT * FROM orders ORDER BY order_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Order Management - ShopEase</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f2ff;
            margin: 0;
        }

        header {
            background: #1c1f4c;
            color: white;
            padding: 20px;
            text-align: center;
        }

        header a {
            color: white;
            text-decoration: none;
        }

        .container {
            width: 90%;
            max-width: 1100px;
            margin: 30px auto;
        }

        .order-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        h2, h3 {
            color: #1c1f4c;
        }

        .customer-details {
            background: #f4f2ff;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #1c1f4c;
            color: white;
        }

        .total {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            color: #00848c;
            margin-top: 15px;
        }

        .back-link {
            color: #00848c;
            text-decoration: none;
        }

        .empty {
            background: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px;
        }

        .status-section {
            background: #f4f2ff;
            padding: 18px;
            margin-top: 20px;
            border-radius: 8px;
        }

        .status-section label {
            display: block;
            font-weight: bold;
            margin-bottom: 10px;
            color: #1c1f4c;
        }

        .status-section select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-right: 10px;
            min-width: 150px;
        }

        .status-section button {
            background: #00848c;
            color: white;
            border: none;
            padding: 11px 18px;
            border-radius: 5px;
            cursor: pointer;
        }

        .status-section button:hover {
            background: #037272;
        }

        .message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin: 15px 0;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin: 15px 0;
        }

        .current-status {
            font-weight: bold;
            color: #00848c;
        }

    </style>

</head>

<body>

<header>

    <h1>ShopEase - Order Management</h1>

    <p>
        Welcome,
        <?php echo htmlspecialchars($_SESSION["user_name"]); ?>!
    </p>

    <a href="dashboard.php">Admin Dashboard</a> |
    <a href="../user/logout.php">Logout</a>

</header>


<div class="container">

    <a href="dashboard.php" class="back-link">
        ← Back to Dashboard
    </a>

    <h2>Customer Orders</h2>

    <?php if ($message !== "") { ?>
        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php } ?>

    <?php if ($error !== "") { ?>
        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php } ?>


    <?php if ($result && $result->num_rows > 0) { ?>

        <?php while ($order = $result->fetch_assoc()) { ?>

            <div class="order-card">

                <h3>
                    Order #<?php echo (int)$order["order_id"]; ?>
                </h3>

                <p>
                    <strong>Order Date:</strong>
                    <?php echo htmlspecialchars($order["order_date"]); ?>
                </p>

                <div class="customer-details">

                    <h3>Customer Details</h3>

                    <p>
                        <strong>Name:</strong>
                        <?php echo htmlspecialchars($order["customer_name"]); ?>
                    </p>

                    <p>
                        <strong>Email:</strong>
                        <?php echo htmlspecialchars($order["email"]); ?>
                    </p>

                    <p>
                        <strong>Phone:</strong>
                        <?php echo htmlspecialchars($order["phone"]); ?>
                    </p>

                    <p>
                        <strong>Address:</strong>
                        <?php echo nl2br(htmlspecialchars($order["address"])); ?>
                    </p>

                </div>


                <h3>Purchased Products</h3>

                <table>

                    <tr>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>

                    <?php

                    $order_id = (int)$order["order_id"];

                    $item_stmt = $conn->prepare(
                        "SELECT * FROM order_items WHERE order_id = ?"
                    );

                    $item_stmt->bind_param("i", $order_id);
                    $item_stmt->execute();

                    $items = $item_stmt->get_result();

                    while ($item = $items->fetch_assoc()) {

                    ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars($item["product_name"]); ?>
                            </td>

                            <td>
                                ₹<?php echo number_format((float)$item["price"], 2); ?>
                            </td>

                            <td>
                                <?php echo (int)$item["quantity"]; ?>
                            </td>

                            <td>
                                ₹<?php echo number_format((float)$item["subtotal"], 2); ?>
                            </td>

                        </tr>

                    <?php

                    }

                    $item_stmt->close();

                    ?>

                </table>


                <div class="total">

                    Total Amount:
                    ₹<?php echo number_format((float)$order["total_amount"], 2); ?>

                </div>


                <!-- Order Status Management -->

                <div class="status-section">

                    <form method="POST" action="orders.php">

                        <input
                            type="hidden"
                            name="order_id"
                            value="<?php echo (int)$order["order_id"]; ?>"
                        >

                        <label>
                            Order Status:
                        </label>

                        <p>
                            Current Status:
                            <span class="current-status">
                                <?php echo htmlspecialchars($order["order_status"] ?? "Pending"); ?>
                            </span>
                        </p>

                        <select name="order_status" required>

                            <?php foreach ($statuses as $status) { ?>

                                <option
                                    value="<?php echo htmlspecialchars($status); ?>"
                                    <?php
                                    if (($order["order_status"] ?? "Pending") === $status) {
                                        echo "selected";
                                    }
                                    ?>
                                >
                                    <?php echo htmlspecialchars($status); ?>
                                </option>

                            <?php } ?>

                        </select>

                        <button type="submit" name="update_status">
                            Update Status
                        </button>

                    </form>

                </div>

            </div>

        <?php } ?>

    <?php } else { ?>

        <div class="empty">
            <h3>No orders found.</h3>
            <p>Customer orders will appear here after checkout.</p>
        </div>

    <?php } ?>

</div>

</body>

</html>