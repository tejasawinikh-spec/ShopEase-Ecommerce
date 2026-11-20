<?php

session_start();

include "../config/db.php";

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: ../user/login.php");
    exit();
}

// Check admin role
if ($_SESSION["user_role"] !== "admin") {
    echo "Access Denied! Only administrators can access this page.";
    exit();
}

// Fetch registered customers
$sql = "SELECT user_id, name, email, created_at
        FROM users
        WHERE role = 'user'
        ORDER BY created_at DESC";

$result = $conn->query($sql);

// Count customers
$count_result = $conn->query(
    "SELECT COUNT(*) AS total FROM users WHERE role = 'user'"
);

$total_customers = $count_result->fetch_assoc()["total"];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Customer Management - ShopEase</title>

    <style>

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

        .container {
            width: 90%;
            margin: 40px auto;
        }

        .summary {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
        }

        .summary h2 {
            color: #00848c;
            font-size: 30px;
        }

        .customer-table {
            background: white;
            padding: 20px;
            border-radius: 10px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #1c1f4c;
            color: white;
        }

        tr:hover {
            background: #f4f2ff;
        }

        .back-btn {
            display: inline-block;
            background: #00848c;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .back-btn:hover {
            background: #037272;
        }

        .empty {
            text-align: center;
            padding: 25px;
            color: #777;
        }

    </style>

</head>

<body>

    <header>

        <h1>ShopEase Customer Management</h1>

        <p>
            Welcome, <?php echo htmlspecialchars($_SESSION["user_name"]); ?>!
        </p>

    </header>


    <div class="container">

        <a href="dashboard.php" class="back-btn">
            ← Back to Dashboard
        </a>


        <!-- Customer Statistics -->

        <div class="summary">

            <h3>Total Registered Customers</h3>

            <h2>
                <?php echo $total_customers; ?>
            </h2>

        </div>


        <!-- Customer Details -->

        <div class="customer-table">

            <h2>Customer Details</h2>

            <?php if ($result && $result->num_rows > 0) { ?>

                <table>

                    <tr>
                        <th>Customer ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Registration Date</th>
                    </tr>

                    <?php while ($customer = $result->fetch_assoc()) { ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars($customer["user_id"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($customer["name"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($customer["email"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($customer["created_at"]); ?>
                            </td>

                        </tr>

                    <?php } ?>

                </table>

            <?php } else { ?>

                <p class="empty">
                    No registered customers found.
                </p>

            <?php } ?>

        </div>

    </div>

</body>

</html>