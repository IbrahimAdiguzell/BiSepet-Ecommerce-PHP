<?php
/**
 * Admin Product Creation Module
 * * Provides an interface for administrators to add new products to the catalog.
 * Features dynamic category selection (nested structure), file upload handling,
 * and input sanitization for database integrity.
 * * @package BiSepet
 * @subpackage Admin
 */

session_start();

/*
|--------------------------------------------------------------------------
| Access Control (RBAC)
|--------------------------------------------------------------------------
| Strict check for admin privileges. Redirects unauthorized users to login.
*/
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != true) {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';

$message = "";

/*
|--------------------------------------------------------------------------
| Form Submission Handler
|--------------------------------------------------------------------------
| Processes the POST request to insert a new product record.
| 1. Sanitize text inputs.
| 2. Handle file upload (Image).
| 3. Execute INSERT query.
*/
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Input Sanitization
    $name = $conn->real_escape_string($_POST['name']);
    $price = (float)$_POST['price'];
    $discount_rate = (int)$_POST['discount_rate']; // Discount percentage
    $description = $conn->real_escape_string($_POST['description']);
    $category = $conn->real_escape_string($_POST['category']); // Stores category slug
    $stock = (int)$_POST['stock'];

    // File Upload Handling
    $target_dir = "../images/";
    // Ensure unique filename to prevent overwrites (Timestamp strategy)
    $file_ext = pathinfo($_FILES["productPict"]["name"], PATHINFO_EXTENSION);
    $img_name = "prod_" . time() . "." . $file_ext; 
    $target_file = $target_dir . $img_name;
    
    // Validate and Move File
    if (move_uploaded_file($_FILES["productPict"]["tmp_name"], $target_file)) {
        
        // Database Insertion
        $sql = "INSERT INTO products (name, price, discount_rate, description, category, stock, productPict) 
                VALUES ('$name', '$price', '$discount_rate', '$description', '$category', '$stock', '$img_name')";
        
        if ($conn->query($sql) === TRUE) {
            $message = "<div class='alert alert-success'>✅ Product added successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Database Error: " . $conn->error . "</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>File Upload Failed. Check directory permissions.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Add Product - Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border: none; border-radius: 12px; }
        .card-header { border-radius: 12px 12px 0 0 !important; }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg">
                <div class="card-header bg-success text-white py-3">
                    <h4 class="mb-0"><i class="bi bi-plus-circle"></i> Add New Product</h4>
                </div>
                <div class="card-body p-4">
                    <?php echo $message; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Product Name</label>
                                <input type="text" name="name" class="form-control" required placeholder="e.g. Wireless Headset">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Category</label>
                                <select name="category" class="form-select" required>
                                    <option value="">Select Category...</option>
                                    <?php
                                    /*
                                    |--------------------------------------------------------------------------
                                    | Dynamic Category Loader (Nested)
                                    |--------------------------------------------------------------------------
                                    | Fetches parent categories and their subcategories to build a 
                                    | grouped select list (optgroup).
                                    */
                                    $main_cats = $conn->query("SELECT * FROM categories WHERE parent_id = 0 ORDER BY id ASC");
                                    while($main = $main_cats->fetch_assoc()){
                                        echo "<optgroup label='{$main['name']}'>"; // Parent Category Group
                                        
                                        // Fetch Subcategories
                                        $sub_cats = $conn->query("SELECT * FROM categories WHERE parent_id = {$main['id']} ORDER BY name ASC");
                                        while($sub = $sub_cats->fetch_assoc()){
                                            // Value uses 'slug' for consistent URL routing in catalog
                                            echo "<option value='{$sub['slug']}'>{$sub['name']}</option>";
                                        }
                                        echo "</optgroup>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Price (€)</label>
                                <input type="number" step="0.01" name="price" class="form-control" required placeholder="0.00">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold text-danger">Discount (%)</label>
                                <input type="number" name="discount_rate" class="form-control" value="0" min="0" max="100">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Stock Quantity</label>
                                <input type="number" name="stock" class="form-control" value="10" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Detailed product description..."></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Product Image</label>
                            <input type="file" name="productPict" class="form-control" required accept="image/*">
                            <div class="form-text text-muted">Supported formats: JPG, PNG, WEBP. Max size: 2MB.</div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="admin_products.php" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-success px-5 fw-bold">Save Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>