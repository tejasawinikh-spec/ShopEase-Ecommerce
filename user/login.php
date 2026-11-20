<?php
session_start();

include "../config/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {

        $error = "Please enter your email and password.";

    } else {

        // Find the user using their email
        $stmt = $conn->prepare(
            "SELECT user_id, name, email, password, role FROM users WHERE email = ?"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 1) {

            $user = $result->fetch_assoc();

            // Verify the entered password
            if (password_verify($password, $user["password"])) {

                // Create a secure login session
                session_regenerate_id(true);

                $_SESSION["user_id"] = $user["user_id"];
                $_SESSION["user_name"] = $user["name"];
                $_SESSION["user_email"] = $user["email"];
                $_SESSION["user_role"] = $user["role"];

                // Redirect to homepage
                header("Location: ../index.php");
                exit();

            } else {

                $error = "Incorrect email or password.";

            }

        } else {

            $error = "Incorrect email or password.";

        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - ShopEase</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f2ff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .login-container {
            background: white;
            width: 380px;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #1c1f4c;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #1c1f4c;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background: #00848c;
        }

        .error {
            color: #d00000;
            text-align: center;
            margin-bottom: 15px;
        }

        p {
            text-align: center;
            margin-top: 20px;
        }

        a {
            color: #00848c;
            text-decoration: none;
        }
    </style>

</head>

<body>

    <div class="login-container">

        <h2>Welcome Back!</h2>

        <?php if (!empty($error)) { ?>
            <p class="error">
                <?php echo htmlspecialchars($error); ?>
            </p>
        <?php } ?>

        <form method="POST" action="">

            <label>Email Address</label>
            <input
                type="email"
                name="email"
                placeholder="Enter your email"
                required
            >

            <label>Password</label>
            <input
                type="password"
                name="password"
                placeholder="Enter your password"
                required
            >

            <button type="submit">Login</button>

        </form>

        <p>
            Don't have an account?
            <a href="register.php">Register here</a>
        </p>

        <p>
            <a href="../index.php">Back to Home</a>
        </p>

    </div>

</body>

</html>