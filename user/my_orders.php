<?php
session_start();

include "../config/db.php";

// Check whether customer is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Get logged-in customer's email
$email = $_SESSION["user_email"];

// Fetch orders belonging to this customer
$stmt = $conn->prepare(
    "SELECT * FROM orders WHERE email = ? ORDER BY order_date DESC"
);

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Orders - ShopEase</title>

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
            margin: 0 10px;
        }

        .container {
            width: 90%;
            max-width: 1000px;
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

        .details {
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

        .status {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 20px;
            background: #e0f2f1;
            color: #037272;
            font-weight: bold;
        }

        .empty {
            background: white;
            padding: 35px;
            text-align: center;
            border-radius: 10px;
        }

        .back-link {
            color: #00848c;
            text-decoration: none;
        }

    </style>

</head>

<body>

<header>

    <h1>ShopEase - My Orders</h1>

    <p>
        Welcome,
        <?php echo htmlspecialchars($_SESSION["user_name"]); ?>!
    </p>

    <a href="../index.php">Home</a>
    <a href="../cart.php">Cart</a>
    <a href="logout.php">Logout</a>

</header>


<div class="container">

    <a href="../index.php" class="back-link">
        ← Continue Shopping
    </a>

    <h2>My Order History</h2>


    <?php if ($result->num_rows > 0) { ?>

        <?php while ($order = $result->fetch_assoc()) { ?>

            <div class="order-card">

                <h3>
                    Order #<?php echo (int)$order["order_id"]; ?>
                </h3>

                <p>
                    <strong>Order Date:</strong>
                    <?php echo htmlspecialchars($order["order_date"]); ?>
                </p>

                <div class="details">

                    <p>
                        <strong>Delivery Address:</strong>
                        <?php echo nl2br(htmlspecialchars($order["address"])); ?>
                    </p>

                    <p>
                        <strong>Phone:</strong>
                        <?php echo htmlspecialchars($order["phone"]); ?>
                    </p>

                    <p>
                        <strong>Order Status:</strong>

                        <span class="status">
                            <?php
                            echo htmlspecialchars(
                                $order["order_status"] ?? "Pending"
                            );
                            ?>
                        </span>

                    </p>

                </div>


                <h3>Ordered Products</h3>

                <table>

                    <tr>
                        <th>Product</th>
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

            </div>

        <?php } ?>

    <?php } else { ?>

        <div class="empty">

            <h3>You haven't placed any orders yet!</h3>

            <p>Explore our products and start shopping.</p>

            <a href="../index.php">Shop Now</a>

        </div>

    <?php } ?>

</div>

</body>

</html>

<?php
$stmt->close();
?>