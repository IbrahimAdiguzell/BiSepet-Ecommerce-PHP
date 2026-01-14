<?php
/**
 * Seller Dashboard Module
 * * Provides a comprehensive interface for sellers to manage their inventory,
 * track stock levels, and analyze product distribution via charts.
 * Implements strict Role-Based Access Control (RBAC).
 * * @package BiSepet
 * @subpackage Seller
 */

require_once 'init.php';

/*
|--------------------------------------------------------------------------
| Access Control (RBAC)
|--------------------------------------------------------------------------
| Restrict access to authenticated users with the 'seller' role only.
| Unauthorized users are redirected to the login page.
*/
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'seller') {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];
$shop_name = "";

// Fetch Seller Details
$u_res = $conn->query("SELECT shop_name FROM users WHERE id=$seller_id");
if($u_res->num_rows > 0) {
    $shop_name = $u_res->fetch_assoc()['shop_name'];
}

/*
|--------------------------------------------------------------------------
| Product Deletion Logic (Secure)
|--------------------------------------------------------------------------
| Validates ownership before deletion to prevent IDOR vulnerabilities.
| Ensures sellers can only delete their own products.
*/
if(isset($_GET['delete'])){
    $del_id = (int)$_GET['delete'];
    
    // Ownership Check: AND seller_id = $seller_id
    $stmt = $conn->prepare("DELETE FROM products WHERE id=? AND seller_id=?");
    $stmt->bind_param("ii", $del_id, $seller_id);
    $stmt->execute();
    
    header("Location: seller_panel.php?msg=deleted");
    exit();
}

/*
|--------------------------------------------------------------------------
| Product Creation Handler
|--------------------------------------------------------------------------
| Handles form submission for adding new products to the inventory.
| Includes file upload handling and input sanitization.
*/
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    // Input Sanitization
    $name = $conn->real_escape_string($_POST['name']);
    $brand = $conn->real_escape_string($_POST['brand']); 
    
    $price = (float)$_POST['price'];
    $discount_rate = (int)$_POST['discount_rate'];
    $category = $conn->real_escape_string($_POST['category']);
    $stock = (int)$_POST['stock'];
    $description = $conn->real_escape_string($_POST['description']);

    // File Upload Handling
    $target_dir = "images/";
    $img_name = basename($_FILES["productPict"]["name"]);
    
    if(move_uploaded_file($_FILES["productPict"]["tmp_name"], $target_dir . $img_name)) {
        // Insert Product Data
        $sql = "INSERT INTO products (name, brand, price, discount_rate, category, stock, description, productPict, seller_id) 
                VALUES ('$name', '$brand', '$price', '$discount_rate', '$category', '$stock', '$description', '$img_name', '$seller_id')";
        
        if($conn->query($sql)){
            $message = "<div class='alert alert-success'>✅ Product added successfully!</div>";
        } else {
            // Log error for debugging
            $message = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Image upload failed. Check permissions.</div>";
    }
}

/*
|--------------------------------------------------------------------------
| Data Visualization Pre-processing
|--------------------------------------------------------------------------
| Prepares JSON data for Chart.js to visualize category distribution and stock levels.
*/
// Chart 1: Category Distribution
$chart_cats = []; $chart_counts = [];
$res_chart1 = $conn->query("SELECT category, COUNT(*) as total FROM products WHERE seller_id = $seller_id GROUP BY category");
while($row = $res_chart1->fetch_assoc()){ 
    $chart_cats[] = ucfirst($row['category']); 
    $chart_counts[] = $row['total']; 
}

// Chart 2: Top Stock Levels
$chart_products = []; $chart_stocks = [];
$res_chart2 = $conn->query("SELECT name, stock FROM products WHERE seller_id = $seller_id ORDER BY stock DESC LIMIT 5");
while($row = $res_chart2->fetch_assoc()){ 
    $chart_products[] = $row['name']; 
    $chart_stocks[] = $row['stock']; 
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Seller Dashboard - <?php echo htmlspecialchars($shop_name); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, #001f3f, #003366); color: white; } 
        .sidebar a { color: #adb5bd; text-decoration: none; display: block; padding: 12px 20px; transition: 0.3s; }
        .sidebar a:hover { background: rgba(255,255,255,0.1); color: white; padding-left: 25px; }
        .nav-header { padding: 25px 20px; background: rgba(0,0,0,0.2); font-weight: bold; border-bottom: 1px solid rgba(255,255,255,0.1); }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar p-0">
            <div class="nav-header">
                <i class="bi bi-shop h4 me-2"></i> <?php echo htmlspecialchars($shop_name); ?>
            </div>
            <div class="mt-3">
                <a href="seller_panel.php" class="text-white"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                <a href="index.php"><i class="bi bi-arrow-left me-2"></i> Back to Store</a>
                <a href="logout.php" class="text-danger mt-5"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
            </div>
        </div>

        <div class="col-md-10 p-4">
            
            <div class="row mb-4">
                <div class="col-md-6"><div class="card p-3 shadow-sm"><canvas id="categoryChart" style="max-height: 200px;"></canvas></div></div>
                <div class="col-md-6"><div class="card p-3 shadow-sm"><canvas id="stockChart" style="max-height: 200px;"></canvas></div></div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white fw-bold"><i class="bi bi-plus-circle"></i> Add New Product</div>
                        <div class="card-body">
                            <?php echo $message; ?>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-2">
                                    <label class="form-label small fw-bold">Product Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                
                                <div class="mb-2">
                                    <label class="form-label small fw-bold">Brand</label>
                                    <input type="text" name="brand" class="form-control" placeholder="e.g. Apple, Nike" required>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label small fw-bold">Category</label>
                                    <select name="category" class="form-select" required>
                                        <option value="">Select...</option>
                                        <?php
                                        // Dynamic Category Loader
                                        $main_cats = $conn->query("SELECT * FROM categories WHERE parent_id = 0 ORDER BY id ASC");
                                        while($main = $main_cats->fetch_assoc()){
                                            echo "<optgroup label='{$main['name']}'>";
                                            $sub_cats = $conn->query("SELECT * FROM categories WHERE parent_id = {$main['id']} ORDER BY name ASC");
                                            while($sub = $sub_cats->fetch_assoc()){
                                                echo "<option value='{$sub['slug']}'>{$sub['name']}</option>";
                                            }
                                            echo "</optgroup>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="row">
                                    <div class="col-6 mb-2">
                                        <label class="form-label small fw-bold">Price (€)</label>
                                        <input type="number" step="0.01" name="price" class="form-control" required>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <label class="form-label small fw-bold text-danger">Discount %</label>
                                        <input type="number" name="discount_rate" class="form-control" value="0" min="0" max="100">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-bold">Stock</label>
                                    <input type="number" name="stock" class="form-control" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-bold">Description</label>
                                    <textarea name="description" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Image</label>
                                    <input type="file" name="productPict" class="form-control" required>
                                </div>
                                <button type="submit" name="add_product" class="btn btn-primary w-100">Publish Product</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-list-check"></i> Active Inventory</span>
                        </div>
                        <div class="card-body p-0 table-responsive">
                            
                            <?php if(isset($_GET['msg']) && $_GET['msg']=='deleted'): ?>
                                <div class="alert alert-warning m-2">🗑️ Product deleted.</div>
                            <?php endif; ?>

                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Img</th>
                                        <th>Name</th>
                                        <th>Brand</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $res = $conn->query("SELECT * FROM products WHERE seller_id = $seller_id ORDER BY id DESC");
                                    if($res->num_rows > 0):
                                        while($row = $res->fetch_assoc()):
                                            // Price Calculation logic
                                            $discount = $row['discount_rate'];
                                            $final_price = $row['price'] - ($row['price'] * $discount / 100);
                                    ?>
                                    <tr>
                                        <td><img src="images/<?php echo $row['productPict']; ?>" width="50" class="rounded border"></td>
                                        <td>
                                            <span class="fw-bold"><?php echo htmlspecialchars($row['name']); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($row['brand'] ?? '-'); ?></span>
                                        </td>
                                        <td>
                                            <?php if($discount > 0): ?>
                                                <small class="text-decoration-line-through text-muted">€<?php echo $row['price']; ?></small>
                                                <br><span class="text-danger fw-bold">€<?php echo number_format($final_price, 2); ?></span>
                                                <span class="badge bg-danger">-%<?php echo $discount; ?></span>
                                            <?php else: ?>
                                                <span class="text-success fw-bold">€<?php echo $row['price']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($row['stock'] < 5): ?>
                                                <span class="badge bg-danger">Critical: <?php echo $row['stock']; ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-dark border"><?php echo $row['stock']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="seller_panel.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Confirm deletion?');">
                                                <i class="bi bi-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center py-5 text-muted">No products found in inventory.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const ctxCat = document.getElementById('categoryChart');
    const ctxStock = document.getElementById('stockChart');

    if(ctxCat) {
        new Chart(ctxCat, {
            type: 'doughnut',
            data: { 
                labels: <?php echo json_encode($chart_cats); ?>, 
                datasets: [{ 
                    data: <?php echo json_encode($chart_counts); ?>, 
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'] 
                }] 
            },
            options: { plugins: { title: { display: true, text: 'Inventory Distribution by Category' } } }
        });
    }

    if(ctxStock) {
        new Chart(ctxStock, {
            type: 'bar',
            data: { 
                labels: <?php echo json_encode($chart_products); ?>, 
                datasets: [{ 
                    label: 'Stock Level', 
                    data: <?php echo json_encode($chart_stocks); ?>, 
                    backgroundColor: '#2ecc71' 
                }] 
            },
            options: { 
                plugins: { title: { display: true, text: 'Highest Stock Products' } }, 
                scales: { y: { beginAtZero: true } } 
            }
        });
    }
</script>

</body>
</html>