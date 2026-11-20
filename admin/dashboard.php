? no::: 
```php
<?php
session_start();

include "../config/db.php";

// Check whether the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../user/login.php");
    exit();
}

// Check whether the logged-in user is an admin
if ($_SESSION["user_role"] !== "admin") {
    echo "Access Denied! Only administrators can access this page.";
    exit();
}

// Fetch total products
$product_result = $conn->query("SELECT COUNT(*) AS total FROM products");
$total_products = $product_result->fetch_assoc()["total"];

// Fetch total registered customers
$user_result = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'user'");
$total_users = $user_result->fetch_assoc()["total"];

// Fetch total orders
$order_result = $conn->query("SELECT COUNT(*) AS total FROM orders");
$total_orders = $order_result->fetch_assoc()["total"];

// Calculate revenue excluding cancelled orders
$revenue_result = $conn->query(
    "SELECT COALESCE(SUM(total_amount), 0) AS revenue
     FROM orders
     WHERE order_status != 'Cancelled'"
);

$total_revenue = $revenue_result->fetch_assoc()["revenue"];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard - ShopEase</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f4f2ff;
            margin: 0;
            padding: 0;
        }

        header {
            background: #1c1f4c;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .container {
            width: 85%;
            margin: 40px auto;
        }

        .welcome {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        /* Statistics Cards */

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 35px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            color: #555;
            font-size: 17px;
        }

        .stat-card h2 {
            color: #00848c;
            font-size: 30px;
        }

        /* Dashboard Management Cards */

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .card {
            background: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .card h3 {
            color: #1c1f4c;
        }

        .card a {
            display: inline-block;
            background: #00848c;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }

        .card a:hover {
            background: #037272;
        }

        .logout {
            color: white;
            text-decoration: none;
        }

    </style>

</head>

<body>

    <!-- Header -->

    <header>

        <h1>ShopEase Admin Dashboard</h1>

        <p>
            Welcome, <?php echo htmlspecialchars($_SESSION["user_name"]); ?>!
        </p>

        <a class="logout" href="../user/logout.php">Logout</a>

    </header>


    <div class="container">

        <!-- Welcome Section -->

        <div class="welcome">

            <h2>Welcome to Admin Panel</h2>

            <p>Manage your ShopEase online shopping website from here.</p>

        </div>


        <!-- Statistics Section -->

        <h2>Dashboard Statistics</h2>

        <div class="stats-container">

            <div class="stat-card">

                <h3>📦 Total Products</h3>

                <h2>
                    <?php echo $total_products; ?>
                </h2>

            </div>


            <div class="stat-card">

                <h3>👥 Total Customers</h3>

                <h2>
                    <?php echo $total_users; ?>
                </h2>

            </div>


            <div class="stat-card">

                <h3>🛒 Total Orders</h3>

                <h2>
                    <?php echo $total_orders; ?>
                </h2>

            </div>


            <div class="stat-card">

                <h3>💰 Total Revenue</h3>

                <h2>
                    ₹<?php echo number_format((float)$total_revenue, 2); ?>
                </h2>

            </div>

        </div>


        <!-- Management Section -->

        <h2>Manage Your Store</h2>

        <div class="dashboard-cards">

            <!-- Product Management -->

            <div class="card">

                <h3>Product Management</h3>

                <p>Add, edit and delete products.</p>

                <a href="products.php">Manage Products</a>

            </div>


            <!-- Order Management -->

            <div class="card">

                <h3>Order Management</h3>

                <p>View customer orders and update order status.</p>

                <a href="orders.php">Manage Orders</a>

            </div>


            <!-- View Website -->

            <div class="card">

                <h3>View Website</h3>

                <p>Visit your online shopping website.</p>

                <a href="../index.php">Visit ShopEase</a>

            </div>


            <!-- Customer Management -->

            <div class="card">

                <h3>Customer Management</h3>

                <p>View registered customers and their details.</p>

                <a href="customers.php">Manage Customers</a>

            </div>

        </div>

    </div>

</body>

</html>
```