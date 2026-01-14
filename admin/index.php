<?php
/**
 * Admin Panel Entry Point (Dashboard)
 * * Merges Key Performance Indicators (KPIs) with "Featured Products" management.
 * * Fixes:
 * - Removed nested PHP tags (Syntax Error).
 * - Moved session_start() to the top (Header Error).
 * - Secured DELETE queries with Prepared Statements (SQL Injection Fix).
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

// 2. Action Handler: Delete Product (Secure)
if (isset($_POST['delete'])) {
    $p_id = (int)$_POST['product_id'];
    
    // Using Prepared Statement for security
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $p_id);
    
    if ($stmt->execute()) {
        $msg = "<div class='alert alert-success alert-dismissible fade show'>
                    <i class='bi bi-check-circle-fill'></i> Product removed successfully.
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                </div>";
    }
    $stmt->close();
}

// 3. Data Retrieval: Featured Products (In Slider)
// Fetches only the products marked to be shown in the slider/homepage
$sql = "SELECT * FROM products WHERE in_slider = 1 ORDER BY id DESC LIMIT 6";
$result = $conn->query($sql);

// 4. Data Retrieval: Quick Stats
$total_orders = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'];
$total_sales  = $conn->query("SELECT SUM(total_price) as s FROM orders")->fetch_assoc()['s'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - BiSepet</title>
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
        <a href="index.php" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="admin_products.php"><i class="bi bi-box-seam"></i> Products</a>
        <a href="admin_orders.php"><i class="bi bi-truck"></i> Orders</a>
        <a href="admin_sellers.php"><i class="bi bi-shop"></i> Sellers</a>
        <a href="../index.php" class="mt-auto mb-4 text-warning"><i class="bi bi-arrow-left"></i> Back to Site</a>
    </div>

    <div class="admin-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
                <p class="text-muted">Here is what's happening in your store today.</p>
            </div>
            <div>
                <a href="../logout.php" class="btn btn-outline-danger btn-sm fw-bold">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>

        <?php if(isset($msg)) echo $msg; ?>

        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="card bg-primary text-white border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-uppercase opacity-75">Total Orders</h6>
                            <h2 class="fw-bold mb-0"><?php echo $total_orders; ?></h2>
                        </div>
                        <i class="bi bi-box-seam fs-1 opacity-50"></i>
                    </div>
                    <div class="card-footer bg-white bg-opacity-10 border-0">
                        <a href="admin_orders.php" class="text-white text-decoration-none small">View Orders <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-success text-white border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-uppercase opacity-75">Total Revenue</h6>
                            <h2 class="fw-bold mb-0">€<?php echo number_format($total_sales, 2); ?></h2>
                        </div>
                        <i class="bi bi-currency-euro fs-1 opacity-50"></i>
                    </div>
                    <div class="card-footer bg-white bg-opacity-10 border-0">
                        <small>Lifetime Sales</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold text-secondary"><i class="bi bi-star-fill text-warning"></i> Featured Products (Slider)</h4>
            <a href="add_product.php" class="btn btn-sm btn-success fw-bold"><i class="bi bi-plus-lg"></i> Add Product</a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Product</th>
                                <th>Price</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <img src="../images/<?php echo $row['productPict']; ?>" 
                                                     class="rounded border me-3" 
                                                     width="60" height="60" 
                                                     style="object-fit: cover;"
                                                     onerror="this.src='https://via.placeholder.com/60'">
                                                <div>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($row['name']); ?></div>
                                                    <div class="small text-muted">ID: #<?php echo $row['id']; ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fw-bold text-success">
                                            €<?php echo number_format($row['price'], 2); ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <form method="POST" onsubmit="return confirm('Remove from slider?');">
                                                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" name="delete" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i> Remove
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-muted">
                                        No featured products found.
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

</body>
</html>