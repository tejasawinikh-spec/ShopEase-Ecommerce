```php
<?php

session_start();

// Connect to database
include "config/db.php";

// Fetch products from MySQL
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

if (!$result) {
    die("Error fetching products: " . $conn->error);
}

// Store products in an associative array
$products = [];

while ($row = $result->fetch_assoc()) {

    $products[$row['product_id']] = [
        "name" => $row['product_name'],
        "price" => (float)$row['price'],
        "image" => $row['image']
    ];

}

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add product to cart
if (isset($_POST['add_to_cart'])) {

    $product_id = (int)($_POST['product_id'] ?? 0);

    if (isset($products[$product_id])) {

        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]++;
        } else {
            $_SESSION['cart'][$product_id] = 1;
        }

    }

    header("Location: cart.php");
    exit();
}

// Update product quantity
if (isset($_POST['update_cart'])) {

    $product_id = (int)($_POST['product_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if (isset($_SESSION['cart'][$product_id])) {

        if ($action === "increase") {

            $_SESSION['cart'][$product_id]++;

        }

        if ($action === "decrease") {

            $_SESSION['cart'][$product_id]--;

            if ($_SESSION['cart'][$product_id] <= 0) {
                unset($_SESSION['cart'][$product_id]);
            }

        }

    }

    header("Location: cart.php");
    exit();
}

// Remove product from cart
if (isset($_POST['remove_product'])) {

    $product_id = (int)($_POST['product_id'] ?? 0);

    unset($_SESSION['cart'][$product_id]);

    header("Location: cart.php");
    exit();
}

// Remove products that no longer exist in the database
foreach ($_SESSION['cart'] as $id => $quantity) {

    if (!isset($products[$id])) {
        unset($_SESSION['cart'][$id]);
    }

}

// Calculate total
$total = 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Shopping Cart - ShopEase</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <style>

        .cart-container {
            max-width: 900px;
            margin: 50px auto;
            padding: 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #ddd;
            gap: 15px;
            flex-wrap: wrap;
        }

        .cart-item h3 {
            color: #25213b;
            margin-bottom: 10px;
        }

        .cart-item p {
            margin: 8px 0;
        }

        .cart-total {
            text-align: right;
            font-size: 22px;
            color: #6c45d9;
            margin-top: 25px;
        }

        .quantity-buttons {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 12px;
        }

        .quantity-buttons button {
            padding: 7px 13px;
            background: #6c45d9;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .remove-btn {
            background: #e74c3c !important;
            margin-top: 12px;
        }

        .continue-btn {
            display: inline-block;
            margin-top: 20px;
            margin-right: 10px;
            padding: 12px 22px;
            background: #6c45d9;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .empty-cart {
            text-align: center;
            padding: 40px;
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


    <!-- Shopping Cart -->
    <div class="cart-container">

        <h2 class="section-title">🛒 Your Shopping Cart</h2>

        <?php if (!empty($_SESSION['cart'])): ?>

            <?php foreach ($_SESSION['cart'] as $id => $quantity): ?>

                <?php

                $subtotal = $products[$id]['price'] * $quantity;

                $total += $subtotal;

                ?>

                <!-- Cart Item -->
                <div class="cart-item">

                    <div>

                        <h3>
                            <?php echo htmlspecialchars($products[$id]['name']); ?>
                        </h3>

                        <p>
                            Price:
                            ₹<?php echo number_format($products[$id]['price'], 2); ?>
                        </p>

                        <p>
                            Quantity: <?php echo (int)$quantity; ?>
                        </p>


                        <!-- Increase and Decrease Quantity -->
                        <div class="quantity-buttons">

                            <!-- Decrease Quantity -->
                            <form method="POST" action="cart.php">

                                <input
                                    type="hidden"
                                    name="product_id"
                                    value="<?php echo (int)$id; ?>"
                                >

                                <input
                                    type="hidden"
                                    name="action"
                                    value="decrease"
                                >

                                <button type="submit" name="update_cart">
                                    −
                                </button>

                            </form>


                            <strong><?php echo (int)$quantity; ?></strong>


                            <!-- Increase Quantity -->
                            <form method="POST" action="cart.php">

                                <input
                                    type="hidden"
                                    name="product_id"
                                    value="<?php echo (int)$id; ?>"
                                >

                                <input
                                    type="hidden"
                                    name="action"
                                    value="increase"
                                >

                                <button type="submit" name="update_cart">
                                    +
                                </button>

                            </form>

                        </div>


                        <!-- Remove Product -->
                        <form method="POST" action="cart.php">

                            <input
                                type="hidden"
                                name="product_id"
                                value="<?php echo (int)$id; ?>"
                            >

                            <button
                                type="submit"
                                name="remove_product"
                                class="remove-btn"
                            >
                                Remove
                            </button>

                        </form>

                    </div>


                    <!-- Product Subtotal -->
                    <div>

                        <strong>
                            ₹<?php echo number_format($subtotal, 2); ?>
                        </strong>

                    </div>

                </div>

            <?php endforeach; ?>


            <!-- Cart Total -->
            <div class="cart-total">

                <strong>
                    Total: ₹<?php echo number_format($total, 2); ?>
                </strong>

            </div>


        <?php else: ?>

            <!-- Empty Cart Message -->
            <div class="empty-cart">

                <h3>Your cart is empty!</h3>

                <p>Add some products to start shopping.</p>

            </div>

        <?php endif; ?>


        <!-- Continue Shopping -->
        <a href="index.php#products" class="continue-btn">
            Continue Shopping
        </a>


        <!-- Checkout Button -->
        <?php if (!empty($_SESSION['cart'])): ?>

            <a href="checkout.php" class="continue-btn">
                Proceed to Checkout →
            </a>

        <?php endif; ?>

    </div>


    <!-- Footer -->
    <footer>

        <p>© 2026 ShopEase. All Rights Reserved.</p>

        <p>Your one-stop destination for online shopping.</p>

    </footer>

</body>

</html>
```