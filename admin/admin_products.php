<?php
/**
 * Admin Product Inventory Controller
 * * Manages the product lifecycle: List, Delete, and Stock Monitoring.
 * * Features:
 * - Secure deletion logic.
 * - Real-time stock alerts (Visual indicators).
 * - Price/Discount calculation in view layer.
 * * @package BiSepet
 * @subpackage Admin
 */

session_start();

// 1. Access Control
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != true) {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';

// 2. Action Handler: Delete Product
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Secure Delete using Prepared Statement
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: admin_products.php?msg=deleted");
        exit();
    }
    $stmt->close();
}

// 3. Data Retrieval
$sql = "SELECT * FROM products ORDER BY id DESC";
$result = $conn->query($sql);

$total_products = $result->num_rows;
$low_stock = 0;
$products = [];

if($result){
    while($row = $result->fetch_assoc()){
        $products[] = $row;
        if($row['stock'] < 10) $low_stock++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Management - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
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
            <div>
                <h2 class="fw-bold text-secondary">Inventory Management</h2>
                <p class="text-muted mb-0">Manage your catalog, stock levels, and pricing.</p>
            </div>
            <a href="add_product.php" class="btn btn-primary shadow-sm fw-bold">
                <i class="bi bi-plus-lg me-2"></i> Add New Product
            </a>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-primary text-white h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-2 opacity-75">Total Products</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $total_products; ?></h2>
                        </div>
                        <i class="bi bi-boxes fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-warning text-dark h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-2 opacity-75">Low Stock (< 10)</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $low_stock; ?></h2>
                        </div>
                        <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4 d-flex align-items-end justify-content-end">
                <a href="export_json.php" class="btn btn-outline-dark fw-bold w-100 h-100 d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-filetype-json fs-4"></i>
                    <span>Export JSON Backup</span>
                </a>
            </div>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg']=='deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 border-start border-success border-5">
                <i class="bi bi-check-circle-fill me-2"></i> Product deleted successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">Image</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Pricing</th>
                                <th>Stock Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($products)): ?>
                                <?php foreach($products as $row): 
                                    $discount = isset($row['discount_rate']) ? $row['discount_rate'] : 0;
                                    $final_price = $row['price'] - ($row['price'] * $discount / 100);
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <img src="../images/<?php echo $row['productPict']; ?>" 
                                             class="rounded border" 
                                             style="width: 50px; height: 50px; object-fit: cover;"
                                             onerror="this.src='https://via.placeholder.com/50?text=Err'">
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($row['name']); ?></div>
                                        <div class="small text-muted">ID: #<?php echo $row['id']; ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?php echo htmlspecialchars($row['category']); ?>
                                        </span>
                                    </td>
                                    
                                    <td>
                                        <?php if($discount > 0): ?>
                                            <div class="d-flex flex-column">
                                                <small class="text-decoration-line-through text-muted">€<?php echo number_format($row['price'], 2); ?></small>
                                                <span class="fw-bold text-success">€<?php echo number_format($final_price, 2); ?></span>
                                                <small class="text-danger fw-bold" style="font-size: 0.75rem;">-<?php echo $discount; ?>% OFF</small>
                                            </div>
                                        <?php else: ?>
                                            <span class="fw-bold text-dark">€<?php echo number_format($row['price'], 2); ?></span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if($row['stock'] < 5): ?>
                                            <span class="badge bg-danger">Critical: <?php echo $row['stock']; ?></span>
                                        <?php elseif($row['stock'] < 10): ?>
                                            <span class="badge bg-warning text-dark">Low: <?php echo $row['stock']; ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-success">In Stock: <?php echo $row['stock']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="text-end pe-4">
                                        <a href="product_update.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="admin_products.php?delete=<?php echo $row['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Are you sure you want to delete this product? This action cannot be undone.');"
                                           title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-box2 fs-1 d-block mb-2 opacity-50"></i>
                                        No products found in the inventory.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>