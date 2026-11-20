<?php
session_start();

include "../config/db.php";

// Admin access protection
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "admin") {
    header("Location: ../user/login.php");
    exit();
}

$message = "";


// IMAGE UPLOAD FUNCTION
function uploadProductImage($fieldName, &$error)
{
    if (!isset($_FILES[$fieldName])) {
        return null;
    }

    // No new image selected
    if ($_FILES[$fieldName]["error"] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($_FILES[$fieldName]["error"] !== UPLOAD_ERR_OK) {
        $error = "Image upload failed. Please try again.";
        return null;
    }

    // Maximum file size: 5 MB
    if ($_FILES[$fieldName]["size"] > 5 * 1024 * 1024) {
        $error = "Image size must be less than 5 MB.";
        return null;
    }

    // Validate image MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($_FILES[$fieldName]["tmp_name"]);

    $allowedTypes = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"
    ];

    if (!isset($allowedTypes[$mime])) {
        $error = "Only JPG, PNG and WEBP images are allowed.";
        return null;
    }

    // Generate a unique filename
    $extension = $allowedTypes[$mime];

    $filename = "product_" . bin2hex(random_bytes(8)) . "." . $extension;

    // Image upload folder
    $uploadDirectory = __DIR__ . "/../assets/images/";

    if (!is_dir($uploadDirectory)) {
        $error = "Image upload folder does not exist.";
        return null;
    }

    // Save image
    if (!move_uploaded_file(
        $_FILES[$fieldName]["tmp_name"],
        $uploadDirectory . $filename
    )) {
        $error = "Unable to save the uploaded image.";
        return null;
    }

    return $filename;
}


// ADD PRODUCT
if (isset($_POST["add_product"])) {

    $name = trim($_POST["product_name"]);
    $description = trim($_POST["description"]);
    $price = (float) $_POST["price"];
    $category = trim($_POST["category"]);

    $image = uploadProductImage("image", $message);

    if (
        $message === "" &&
        ($name === "" || $price <= 0 || $category === "" || $image === null)
    ) {
        $message = "Please enter valid product details and select an image.";
    }

    if ($message === "") {

        $stmt = $conn->prepare(
            "INSERT INTO products
            (product_name, description, price, category, image)
            VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssdss",
            $name,
            $description,
            $price,
            $category,
            $image
        );

        if ($stmt->execute()) {
            $stmt->close();

            header("Location: products.php?message=added");
            exit();
        } else {
            $message = "Unable to add product.";
        }

        $stmt->close();
    }
}


// DELETE PRODUCT
if (isset($_POST["delete_product"])) {

    $product_id = (int) $_POST["product_id"];

    $stmt = $conn->prepare(
        "DELETE FROM products WHERE product_id = ?"
    );

    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {

        $stmt->close();

        header("Location: products.php?message=deleted");
        exit();

    } else {
        $message = "Unable to delete product.";
    }

    $stmt->close();
}


// EDIT / UPDATE PRODUCT
if (isset($_POST["update_product"])) {

    $product_id = (int) $_POST["product_id"];

    $name = trim($_POST["product_name"]);
    $description = trim($_POST["description"]);
    $price = (float) $_POST["price"];
    $category = trim($_POST["category"]);

    // Get existing product image
    $findImage = $conn->prepare(
        "SELECT image FROM products WHERE product_id = ?"
    );

    $findImage->bind_param("i", $product_id);
    $findImage->execute();

    $existingProduct = $findImage->get_result()->fetch_assoc();

    $findImage->close();

    if (!$existingProduct) {

        $message = "Product not found.";

    } else {

        // Upload new image if selected
        $newImage = uploadProductImage("image", $message);

        // Keep old image if no new image was selected
        $image = $newImage ?? $existingProduct["image"];

        if (
            $message === "" &&
            ($name === "" || $price <= 0 || $category === "")
        ) {
            $message = "Please enter valid product details.";
        }

        if ($message === "") {

            $stmt = $conn->prepare(
                "UPDATE products
                 SET product_name = ?,
                     description = ?,
                     price = ?,
                     category = ?,
                     image = ?
                 WHERE product_id = ?"
            );

            $stmt->bind_param(
                "ssdssi",
                $name,
                $description,
                $price,
                $category,
                $image,
                $product_id
            );

            if ($stmt->execute()) {

                $stmt->close();

                header("Location: products.php?message=updated");
                exit();

            } else {
                $message = "Unable to update product.";
            }

            $stmt->close();
        }
    }
}


// SUCCESS MESSAGES
if (isset($_GET["message"])) {

    if ($_GET["message"] === "added") {
        $message = "Product added successfully!";

    } elseif ($_GET["message"] === "updated") {
        $message = "Product updated successfully!";

    } elseif ($_GET["message"] === "deleted") {
        $message = "Product deleted successfully!";
    }
}


// FETCH PRODUCTS
$result = $conn->query(
    "SELECT * FROM products ORDER BY product_id DESC"
);


// CHECK WHETHER A PRODUCT IS BEING EDITED
$edit_product = null;

if (isset($_GET["edit"])) {

    $edit_id = (int) $_GET["edit"];

    $stmt = $conn->prepare(
        "SELECT * FROM products WHERE product_id = ?"
    );

    $stmt->bind_param("i", $edit_id);
    $stmt->execute();

    $edit_result = $stmt->get_result();

    $edit_product = $edit_result->fetch_assoc();

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Product Management - ShopEase</title>

    <style>

        * {
            box-sizing: border-box;
        }

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

        header a {
            color: white;
            text-decoration: none;
        }

        .container {
            width: 90%;
            max-width: 1100px;
            margin: 30px auto;
        }

        .form-box,
        .table-box {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        h2 {
            color: #1c1f4c;
        }

        label {
            font-weight: bold;
        }

        input,
        textarea {
            width: 100%;
            padding: 12px;
            margin: 8px 0 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-family: Arial, sans-serif;
        }

        input[type="file"] {
            background: #f9f9f9;
        }

        small {
            color: #666;
        }

        button,
        .edit-btn {
            background: #00848c;
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        button:hover,
        .edit-btn:hover {
            background: #037272;
        }

        .delete-btn {
            background: #d62828;
        }

        .delete-btn:hover {
            background: #a4161a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #1c1f4c;
            color: white;
        }

        img {
            width: 65px;
            height: 65px;
            object-fit: contain;
        }

        .message {
            background: #d9f5df;
            color: #176b2c;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .back-link {
            color: #00848c;
            text-decoration: none;
        }

        .cancel-link {
            margin-left: 10px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        @media (max-width: 600px) {

            .container {
                width: 95%;
            }

            .form-box,
            .table-box {
                padding: 15px;
            }

            th,
            td {
                padding: 8px;
                font-size: 13px;
            }

        }

    </style>

</head>


<body>


<header>

    <h1>ShopEase - Product Management</h1>

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


    <?php if ($message !== "") { ?>

        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php } ?>


    <!-- ADD / EDIT PRODUCT FORM -->

    <div class="form-box">

        <h2>
            <?php
            echo $edit_product
                ? "Edit Product"
                : "Add New Product";
            ?>
        </h2>


        <form method="POST" action="" enctype="multipart/form-data">


            <?php if ($edit_product) { ?>

                <input
                    type="hidden"
                    name="product_id"
                    value="<?php echo (int)$edit_product["product_id"]; ?>"
                >

            <?php } ?>


            <label>Product Name</label>

            <input
                type="text"
                name="product_name"
                placeholder="Enter product name"
                value="<?php echo htmlspecialchars($edit_product["product_name"] ?? ""); ?>"
                required
            >


            <label>Description</label>

            <textarea
                name="description"
                placeholder="Enter product description"
                rows="3"
            ><?php echo htmlspecialchars($edit_product["description"] ?? ""); ?></textarea>


            <label>Price (₹)</label>

            <input
                type="number"
                name="price"
                placeholder="Enter price"
                min="0.01"
                step="0.01"
                value="<?php echo htmlspecialchars((string)($edit_product["price"] ?? "")); ?>"
                required
            >


            <label>Category</label>

            <input
                type="text"
                name="category"
                placeholder="Example: Electronics"
                value="<?php echo htmlspecialchars($edit_product["category"] ?? ""); ?>"
                required
            >


            <!-- IMAGE UPLOAD -->

            <label>Product Image</label>

            <input
                type="file"
                name="image"
                accept="image/jpeg,image/png,image/webp"
                <?php echo $edit_product ? "" : "required"; ?>
            >

            <small>
                Allowed formats: JPG, PNG and WEBP.
                Maximum size: 5 MB.
            </small>


            <?php if ($edit_product) { ?>

                <p><strong>Current Image:</strong></p>

                <img
                    src="../assets/images/<?php echo htmlspecialchars($edit_product["image"]); ?>"
                    alt="Current Product Image"
                    style="width:100px; height:100px; object-fit:contain;"
                >

                <p>
                    <small>
                        Select a new image only if you want to replace the current image.
                    </small>
                </p>

            <?php } ?>


            <br><br>


            <?php if ($edit_product) { ?>

                <button type="submit" name="update_product">
                    Update Product
                </button>

                <a
                    href="products.php"
                    class="back-link cancel-link"
                >
                    Cancel
                </a>

            <?php } else { ?>

                <button type="submit" name="add_product">
                    Add Product
                </button>

            <?php } ?>


        </form>

    </div>



    <!-- PRODUCT LIST -->

    <div class="table-box">

        <h2>All Products</h2>


        <div class="table-wrapper">

            <table>

                <tr>

                    <th>ID</th>

                    <th>Image</th>

                    <th>Product Name</th>

                    <th>Price</th>

                    <th>Category</th>

                    <th>Actions</th>

                </tr>


                <?php if ($result && $result->num_rows > 0) { ?>


                    <?php while ($product = $result->fetch_assoc()) { ?>

                        <tr>

                            <td>
                                <?php echo (int)$product["product_id"]; ?>
                            </td>


                            <td>

                                <img
                                    src="../assets/images/<?php echo htmlspecialchars($product["image"]); ?>"
                                    alt="<?php echo htmlspecialchars($product["product_name"]); ?>"
                                >

                            </td>


                            <td>
                                <?php echo htmlspecialchars($product["product_name"]); ?>
                            </td>


                            <td>
                                ₹<?php echo number_format((float)$product["price"], 2); ?>
                            </td>


                            <td>
                                <?php echo htmlspecialchars($product["category"]); ?>
                            </td>


                            <td>

                                <a
                                    class="edit-btn"
                                    href="products.php?edit=<?php echo (int)$product["product_id"]; ?>"
                                >
                                    Edit
                                </a>


                                <form
                                    method="POST"
                                    action=""
                                    style="display:inline;"
                                >

                                    <input
                                        type="hidden"
                                        name="product_id"
                                        value="<?php echo (int)$product["product_id"]; ?>"
                                    >


                                    <button
                                        type="submit"
                                        name="delete_product"
                                        class="delete-btn"
                                        onclick="return confirm('Are you sure you want to delete this product?');"
                                    >
                                        Delete
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php } ?>


                <?php } else { ?>

                    <tr>

                        <td colspan="6">
                            No products available.
                        </td>

                    </tr>

                <?php } ?>


            </table>

        </div>

    </div>


</div>


</body>

</html>