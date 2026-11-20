<?php
session_start();

include "config/db.php";

// Check whether a product ID is provided
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid product.");
}

$product_id = (int) $_GET["id"];

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Product not found.");
}

$product = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo htmlspecialchars($product["product_name"]); ?> - ShopEase
    </title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <header>
        <h2>ShopEase</h2>

        <nav>
            <a href="index.php">Home</a>
            <a href="index.php#products">Products</a>
            <a href="cart.php">Cart</a>

            <?php if (isset($_SESSION["user_id"])) { ?>
                <a href="user/my_orders.php">My Orders</a>
                <a href="user/logout.php">Logout</a>
            <?php } else { ?>
                <a href="user/login.php">Login</a>
                <a href="user/register.php">Register</a>
            <?php } ?>
        </nav>
    </header>

    <main style="max-width: 1000px; margin: 50px auto; padding: 20px;">

        <div style="display: flex; gap: 40px; flex-wrap: wrap; align-items: center;">

            <div style="flex: 1; min-width: 280px;">
                <img
                    src="assets/images/<?php echo htmlspecialchars($product['image']); ?>"
                    alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                    style="width: 100%; max-width: 450px; border-radius: 12px;"
                >
            </div>

            <div style="flex: 1; min-width: 280px;">

                <h1>
                    <?php echo htmlspecialchars($product["product_name"]); ?>
                </h1>

                <h3>
                    Category:
                    <?php echo htmlspecialchars($product["category"]); ?>
                </h3>

                <h2>
                    ₹<?php echo number_format($product["price"], 2); ?>
                </h2>

                <h3>Product Description</h3>

                <p>
                    <?php echo nl2br(htmlspecialchars($product["description"])); ?>
                </p>

                <br>

                <a href="index.php#products">
                    ← Back to Shopping
                </a>

            </div>

        </div>

    </main>

    <footer>
        <p>&copy; 2026 ShopEase. All Rights Reserved.</p>
    </footer>

</body>
</html>