<?php
/**
 * Admin Product Edit Module
 * * Facilitates the modification of existing product records.
 * * Key Features:
 * - Pre-population of form fields with existing data.
 * - Conditional image update (keeps old image if no new one is uploaded).
 * - Dynamic category selection with state preservation.
 * * @package BiSepet
 * @subpackage Admin
 */

session_start();

/*
|--------------------------------------------------------------------------
| Access Control
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != true) {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';

// Check ID Parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Product ID.");
}

$id = (int)$_GET['id'];
$message = "";

/*
|--------------------------------------------------------------------------
| Data Retrieval (Read)
|--------------------------------------------------------------------------
| Fetch current product data to populate the form.
*/
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("Product not found in database.");
}
$stmt->close();

/*
|--------------------------------------------------------------------------
| Form Processing (Update)
|--------------------------------------------------------------------------
*/
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Sanitize Inputs
    $name = $conn->real_escape_string($_POST['name']);
    $price = (float)$_POST['price'];
    $discount_rate = (int)$_POST['discount_rate'];
    $description = $conn->real_escape_string($_POST['description']);
    $category = $conn->real_escape_string($_POST['category']);
    $stock = (int)$_POST['stock'];
    
    // 2. Image Logic: Default to existing image
    $imageName = $product['productPict']; 

    // 3. Check if NEW image is uploaded
    if (!empty($_FILES['productPict']['name'])) {
        $target_dir = "../images/";
        $file_ext = pathinfo($_FILES["productPict"]["name"], PATHINFO_EXTENSION);
        $new_img_name = "prod_" . time() . "." . $file_ext; // Unique Name
        $target_file = $target_dir . $new_img_name;

        if (move_uploaded_file($_FILES["productPict"]["tmp_name"], $target_file)) {
            $imageName = $new_img_name; // Update variable if upload success
            
            // Optional: Delete old image to save space
            // if(file_exists("../images/".$product['productPict'])) unlink("../images/".$product['productPict']);
        }
    }

    // 4. Update Query (Prepared Statement)
    $update_sql = "UPDATE products 
                   SET name=?, price=?, discount_rate=?, description=?, category=?, stock=?, productPict=? 
                   WHERE id=?";
    
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sdissssi", $name, $price, $discount_rate, $description, $category, $stock, $imageName, $id);

    if ($stmt->execute()) {
        // Refresh data to show updated values immediately
        $product['name'] = $name;
        $product['price'] = $price;
        $product['discount_rate'] = $discount_rate;
        $product['description'] = $description;
        $product['category'] = $category;
        $product['stock'] = $stock;
        $product['productPict'] = $imageName;
        
        $message = "<div class='alert alert-success alert-dismissible fade show'>
                        <i class='bi bi-check-circle-fill me-2'></i> Product updated successfully!
                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                    </div>";
    } else {
        $message = "<div class='alert alert-danger'>Update Error: " . $conn->error . "</div>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Edit Product - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <link href="../style.css" rel="stylesheet">
</head>
<body>

<div class="admin-container">
    
    <div class="sidebar d-flex flex-column">
        <h4 class="text-center text-white mb-4 mt-2">
            <i class="bi bi-shield-lock"></i> Admin Panel
        </h4>
        <a href="admin_dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="admin_products.php" class="active"><i class="bi bi-box-seam"></i> Products</a>
        <a href="admin_orders.php"><i class="bi bi-truck"></i> Orders</a>
        <a href="admin_messages.php"><i class="bi bi-envelope"></i> Messages</a>
        <a href="../index.php" class="mt-auto mb-4 text-warning"><i class="bi bi-arrow-left"></i> Back to Site</a>
    </div>

    <div class="admin-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-secondary">Edit Product</h2>
            <a href="admin_products.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Return to List
            </a>
        </div>

        <?php echo $message; ?>

        <div class="card shadow-lg border-0">
            <div class="card-header bg-warning text-dark py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square"></i> Editing: <?php echo htmlspecialchars($product['name']); ?></h5>
            </div>
            <div class="card-body p-4">
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-4 text-center mb-4">
                            <label class="form-label fw-bold d-block text-start">Current Image</label>
                            <div class="border rounded p-2 bg-light mb-3">
                                <img src="../images/<?php echo $product['productPict']; ?>" 
                                     class="img-fluid rounded" 
                                     style="max-height: 250px; object-fit: contain;"
                                     onerror="this.src='https://via.placeholder.com/300x300?text=No+Image'">
                            </div>
                            
                            <div class="mb-3 text-start">
                                <label class="form-label small text-muted">Change Image (Optional)</label>
                                <input type="file" name="productPict" class="form-control form-control-sm" accept="image/*">
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold">Product Name</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Category</label>
                                    <select name="category" class="form-select" required>
                                        <option value="">Select Category...</option>
                                        <?php
                                        // Dynamic Category Loader with Selection Logic
                                        $main_cats = $conn->query("SELECT * FROM categories WHERE parent_id = 0 ORDER BY id ASC");
                                        while($main = $main_cats->fetch_assoc()){
                                            echo "<optgroup label='{$main['name']}'>"; 
                                            
                                            $sub_cats = $conn->query("SELECT * FROM categories WHERE parent_id = {$main['id']} ORDER BY name ASC");
                                            while($sub = $sub_cats->fetch_assoc()){
                                                // Check if this category matches the product's current category
                                                $selected = ($sub['slug'] == $product['category']) ? 'selected' : '';
                                                echo "<option value='{$sub['slug']}' $selected>{$sub['name']}</option>";
                                            }
                                            echo "</optgroup>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Stock Quantity</label>
                                    <input type="number" name="stock" class="form-control" value="<?php echo $product['stock']; ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Price (€)</label>
                                    <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $product['price']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold text-danger">Discount Rate (%)</label>
                                    <input type="number" name="discount_rate" class="form-control" value="<?php echo $product['discount_rate']; ?>" min="0" max="100">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Description</label>
                                <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <button type="submit" class="btn btn-warning fw-bold px-5 text-dark">
                                    <i class="bi bi-save"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

</body>
</html>