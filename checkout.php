```php
<?php

session_start();

// Connect to database
include "config/db.php";

// Check whether cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// Fetch products from MySQL
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

if (!$result) {
    die("Error fetching products: " . $conn->error);
}

// Store database products in an array
$products = [];

while ($row = $result->fetch_assoc()) {

    $products[$row['product_id']] = [
        "name" => $row['product_name'],
        "price" => (float)$row['price']
    ];

}

// Validate cart products and quantities
foreach ($_SESSION['cart'] as $id => $quantity) {

    if (
        !isset($products[$id]) ||
        !is_numeric($quantity) ||
        (int)$quantity < 1
    ) {
        die("Invalid product or quantity in cart. Please return to your cart.");
    }

}

// Calculate total
$total = 0;

foreach ($_SESSION['cart'] as $id => $quantity) {

    $total += $products[$id]['price'] * (int)$quantity;

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Checkout - ShopEase</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <style>

        .checkout-container {
            max-width: 850px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
        }

        .checkout-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #25213b;
        }

        .checkout-form label {
            display: block;
            margin-top: 18px;
            font-weight: bold;
        }

        .checkout-form input,
        .checkout-form textarea {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
        }

        .checkout-form textarea {
            height: 100px;
            resize: vertical;
        }

        .order-summary {
            margin-top: 30px;
            padding: 20px;
            background: #f8f8fc;
            border-radius: 8px;
        }

        .order-summary h3 {
            margin-bottom: 15px;
            color: #25213b;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            gap: 10px;
        }

        .grand-total {
            border-top: 1px solid #ddd;
            padding-top: 15px;
            margin-top: 15px;
            font-size: 20px;
            color: #6c45d9;
        }

        .place-order-btn {
            width: 100%;
            padding: 14px;
            margin-top: 25px;
            background: #6c45d9;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 17px;
            cursor: pointer;
        }

        .place-order-btn:hover {
            background: #5030b0;
        }

    </style>

</head>

<body>

    <!-- Navigation Bar -->
    <header class="navbar">

        <div class="logo">ShopEase.</div>

        <nav class="nav-links">
            <a href="index.php">Home</a>
            <a href="index.php#products">Products</a>
            <a href="cart.php">Cart</a>
        </nav>

    </header>


    <!-- Checkout Section -->
    <div class="checkout-container">

        <h2>Checkout</h2>

        <form action="place_order.php" method="POST" class="checkout-form">

            <!-- Customer Details -->
            <h3>Customer Details</h3>

            <label>Full Name</label>

            <input
                type="text"
                name="customer_name"
                placeholder="Enter your full name"
                required
            >


            <label>Email Address</label>

            <input
                type="email"
                name="email"
                placeholder="Enter your email address"
                required
            >


            <label>Phone Number</label>

            <input
                type="tel"
                name="phone"
                placeholder="Enter your phone number"
                pattern="[0-9]{10}"
                maxlength="10"
                required
            >


            <label>Delivery Address</label>

            <textarea
                name="address"
                placeholder="Enter your complete delivery address"
                required
            ></textarea>


            <!-- Order Summary -->
            <div class="order-summary">

                <h3>Order Summary</h3>

                <?php foreach ($_SESSION['cart'] as $id => $quantity): ?>

                    <?php

                    $subtotal = $products[$id]['price'] * (int)$quantity;

                    ?>

                    <div class="summary-item">

                        <span>
                            <?php echo htmlspecialchars($products[$id]['name']); ?>
                            × <?php echo (int)$quantity; ?>
                        </span>

                        <strong>
                            ₹<?php echo number_format($subtotal, 2); ?>
                        </strong>

                    </div>

                <?php endforeach; ?>


                <!-- Grand Total -->
                <div class="summary-item grand-total">

                    <strong>Total Amount</strong>

                    <strong>
                        ₹<?php echo number_format($total, 2); ?>
                    </strong>

                </div>

            </div>


            <!-- Place Order Button -->
            <button type="submit" class="place-order-btn">
                Place Order
            </button>

        </form>

    </div>


    <!-- Footer -->
    <footer>

        <p>© 2026 ShopEase. All Rights Reserved.</p>

        <p>Your one-stop destination for online shopping.</p>

    </footer>

</body>

</html>