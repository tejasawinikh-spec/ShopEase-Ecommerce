```php
<?php

session_start();

// Connect to database
include "config/db.php";

// Allow only POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: checkout.php");
    exit();
}

// Check whether cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}


// Get customer details
$customer_name = trim($_POST['customer_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');


// Use registered email for logged-in customers
if (isset($_SESSION["user_id"])) {

    $email = $_SESSION["user_email"];

} else {

    // Allow email entry for guest checkout
    $email = trim($_POST['email'] ?? '');

}


// Validate customer details
if (
    $customer_name === '' ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    !preg_match('/^[0-9]{10}$/', $phone) ||
    $address === ''
) {
    die("Invalid customer details. Please return to checkout.");
}


// Fetch products from MySQL
$products = [];

$sql = "SELECT product_id, product_name, price FROM products";

$result = $conn->query($sql);

if (!$result) {
    die("Unable to fetch products: " . $conn->error);
}

while ($row = $result->fetch_assoc()) {

    $products[$row['product_id']] = [
        "name" => $row['product_name'],
        "price" => (float)$row['price']
    ];

}


// Calculate total using database prices
$total_amount = 0;

foreach ($_SESSION['cart'] as $product_id => $quantity) {

    $product_id = (int)$product_id;
    $quantity = (int)$quantity;

    if (
        !isset($products[$product_id]) ||
        $quantity < 1
    ) {
        die("Invalid product or quantity. Please return to your cart.");
    }

    $total_amount += $products[$product_id]['price'] * $quantity;

}


// Save order in database
$order_id = 0;

try {

    // Start database transaction
    $conn->begin_transaction();


    // Insert customer order
    $order_sql = "INSERT INTO orders
    (customer_name, email, phone, address, total_amount)
    VALUES (?, ?, ?, ?, ?)";

    $order_stmt = $conn->prepare($order_sql);

    $order_stmt->bind_param(
        "ssssd",
        $customer_name,
        $email,
        $phone,
        $address,
        $total_amount
    );

    $order_stmt->execute();


    // Get generated order ID
    $order_id = $conn->insert_id;


    // Insert individual order items
    $item_sql = "INSERT INTO order_items
    (order_id, product_id, product_name, price, quantity, subtotal)
    VALUES (?, ?, ?, ?, ?, ?)";

    $item_stmt = $conn->prepare($item_sql);


    foreach ($_SESSION['cart'] as $product_id => $quantity) {

        $product_id = (int)$product_id;
        $quantity = (int)$quantity;

        $product_name = $products[$product_id]['name'];
        $price = $products[$product_id]['price'];

        $subtotal = $price * $quantity;


        $item_stmt->bind_param(
            "iisdid",
            $order_id,
            $product_id,
            $product_name,
            $price,
            $quantity,
            $subtotal
        );

        $item_stmt->execute();

    }


    // Commit database changes
    $conn->commit();


    // Clear shopping cart after successful order
    unset($_SESSION['cart']);


} catch (Throwable $e) {

    // Roll back if something goes wrong
    $conn->rollback();

    die("Order could not be saved: " .
        htmlspecialchars($e->getMessage()));

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Order Successful - ShopEase</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f5f3ff;
            text-align: center;
            padding: 60px 20px;
        }

        .success-box {
            background: white;
            max-width: 500px;
            margin: auto;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        h1 {
            color: #28a745;
        }

        p {
            color: #444;
            line-height: 1.6;
        }

        a {
            display: inline-block;
            background: #6c45d9;
            color: white;
            padding: 12px 22px;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 15px;
        }

    </style>

</head>

<body>

    <div class="success-box">

        <h1>🎉 Order Placed Successfully!</h1>

        <p>Thank you for shopping with ShopEase.</p>

        <p>
            <strong>Order ID:</strong>
            <?php echo $order_id; ?>
        </p>

        <p>
            <strong>Total Amount:</strong>
            ₹<?php echo number_format($total_amount, 2); ?>
        </p>

        <p>Your order has been saved successfully.</p>

        <a href="index.php">
            Continue Shopping
        </a>

    </div>

</body>

</html>
```