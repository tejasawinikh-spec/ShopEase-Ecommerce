<?php
session_start();

include "config/db.php";

// Fetch products from MySQL database
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ShopEase - Online Shopping</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <!-- Navigation Bar -->
    <header class="navbar">

        <div class="logo">ShopEase.</div>

        <nav class="nav-links">

            <a href="index.php">Home</a>
            <a href="#categories">Categories</a>
            <a href="#products">Products</a>

            <?php if (isset($_SESSION["user_id"])) { ?>

                <span>
                    Welcome,
                    <?php echo htmlspecialchars($_SESSION["user_name"]); ?>!
                </span>

                <a href="user/my_orders.php">My Orders</a>

                <a href="user/logout.php">Logout</a>

            <?php } else { ?>

                <a href="user/login.php">Login</a>
                <a href="user/register.php">Register</a>

            <?php } ?>

            <a href="cart.php" class="cart-link">🛒 Cart</a>

        </nav>

    </header>


    <!-- Search Bar -->
    <div class="search-container">

        <input
            type="text"
            id="searchInput"
            placeholder="Search for products..."
            oninput="searchProducts()">

        <button type="button" onclick="searchProducts()">
            🔍 Search
        </button>

    </div>


    <!-- Hero Section -->
    <section class="hero">

        <h1>Welcome to ShopEase!</h1>

        <p>Discover amazing products at amazing prices.</p>

        <a href="#products" class="shop-btn">Shop Now</a>

    </section>


    <!-- Categories Section -->
    <section id="categories">

        <h2 class="section-title">Shop By Categories</h2>

        <div class="categories">

            <div class="category">👕 Fashion</div>
            <div class="category">📱 Electronics</div>
            <div class="category">💄 Beauty</div>
            <div class="category">🏠 Home & Living</div>
            <div class="category">⌚ Accessories</div>

        </div>

    </section>


    <!-- Featured Products Section -->
    <section id="products">

        <h2 class="section-title">Featured Products</h2>

        <div class="categories" id="productList">

            <?php if ($result && $result->num_rows > 0) { ?>

                <?php while ($product = $result->fetch_assoc()) { ?>

                    <!-- Dynamic Product Card -->
                    <div class="category product-card">

                        <img
                            src="assets/images/<?php echo htmlspecialchars($product['image']); ?>"
                            alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                        >

                        <h3>
                            <?php echo htmlspecialchars($product['product_name']); ?>
                        </h3>

                        <p>
                            ₹<?php echo number_format((float)$product['price'], 2); ?>
                        </p>

                        <!-- View Details Button -->
                        <a
                            href="product.php?id=<?php echo (int)$product['product_id']; ?>"
                            class="shop-btn"
                        >
                            View Details
                        </a>

                        <br><br>

                        <!-- Add to Cart Form -->
                        <form action="cart.php" method="POST">

                            <input
                                type="hidden"
                                name="product_id"
                                value="<?php echo (int)$product['product_id']; ?>"
                            >

                            <button type="submit" name="add_to_cart">
                                Add to Cart
                            </button>

                        </form>

                    </div>

                <?php } ?>

            <?php } else { ?>

                <p>No products available.</p>

            <?php } ?>

        </div>

        <!-- No Search Results Message -->
        <p id="noResults" class="no-results" style="display:none;">
            Sorry! No products found.
        </p>

    </section>


    <!-- Footer -->
    <footer>

        <p>© 2026 ShopEase. All Rights Reserved.</p>

        <p>Your one-stop destination for online shopping.</p>

    </footer>


    <!-- JavaScript Search Function -->
    <script>

        function searchProducts() {

            let input = document.getElementById("searchInput").value.trim().toLowerCase();

            let products = document.querySelectorAll(".product-card");

            let visibleProducts = 0;

            products.forEach(function(product) {

                let productName = product.querySelector("h3").textContent.toLowerCase();

                if (productName.includes(input)) {

                    product.style.display = "block";
                    visibleProducts++;

                } else {

                    product.style.display = "none";

                }

            });

            let noResults = document.getElementById("noResults");

            if (visibleProducts === 0) {

                noResults.style.display = "block";

            } else {

                noResults.style.display = "none";

            }

        }

    </script>

</body>
</html>