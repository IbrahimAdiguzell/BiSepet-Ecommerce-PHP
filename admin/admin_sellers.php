<?php
/**
 * Admin Seller Management Module
 * * Handles vendor onboarding, approval workflows, and performance monitoring.
 * * Features:
 * - One-click approval system.
 * - Aggregated metrics (Total Products, Average Rating) per seller.
 * - Prioritized listing (Pending approvals shown first).
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

/*
|--------------------------------------------------------------------------
| Action Handler: Approve Seller
|--------------------------------------------------------------------------
| Updates the 'is_approved' flag to 1, granting the user seller privileges.
*/
if(isset($_GET['approve_id'])) {
    $u_id = (int)$_GET['approve_id'];
    
    // Secure Update
    $stmt = $conn->prepare("UPDATE users SET is_approved = 1 WHERE id = ?");
    $stmt->bind_param("i", $u_id);
    
    if($stmt->execute()) {
        header("Location: admin_sellers.php?msg=approved");
        exit();
    }
    $stmt->close();
}

/*
|--------------------------------------------------------------------------
| Data Aggregation (Analytical Query)
|--------------------------------------------------------------------------
| Fetches sellers with computed metrics:
| 1. Product Count (via JOIN products)
| 2. Average Rating (via JOIN comments through products)
*/
$sql = "SELECT 
            u.id, u.name, u.email, u.shop_name, u.is_approved, u.created_at,
            COUNT(DISTINCT p.id) as product_count,
            AVG(c.rating) as average_score
        FROM users u
        LEFT JOIN products p ON u.id = p.seller_id
        LEFT JOIN comments c ON p.id = c.product_id
        WHERE u.role = 'seller'
        GROUP BY u.id
        ORDER BY u.is_approved ASC, u.id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Seller Management - Admin</title>
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
        <a href="admin_products.php"><i class="bi bi-box-seam"></i> Products</a>
        <a href="admin_orders.php"><i class="bi bi-truck"></i> Orders</a>
        <a href="admin_sellers.php" class="active"><i class="bi bi-shop"></i> Sellers</a>
        <a href="admin_messages.php"><i class="bi bi-envelope"></i> Messages</a>
        <a href="../index.php" class="mt-auto mb-4 text-warning"><i class="bi bi-arrow-left"></i> Back to Site</a>
    </div>

    <div class="admin-content">
        
        <h2 class="mb-4 fw-bold text-secondary">Vendor Management</h2>
        
        <?php if(isset($_GET['msg']) && $_GET['msg']=='approved'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm">
                <i class="bi bi-check-circle-fill me-2"></i> Seller approved successfully! Access granted.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">Shop Name</th>
                                <th>Seller Info</th>
                                <th>Status</th>
                                <th>Performance</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): 
                                    $score = $row['average_score'] ? number_format($row['average_score'], 1) : 'N/A';
                                    $joined_date = date("d M Y", strtotime($row['created_at']));
                                ?>
                                    <tr class="<?php echo $row['is_approved'] == 0 ? 'table-warning' : ''; ?>">
                                        <td class="ps-4">
                                            <div class="fw-bold text-primary fs-5">
                                                <i class="bi bi-shop me-2"></i><?php echo htmlspecialchars($row['shop_name']); ?>
                                            </div>
                                            <small class="text-muted">Joined: <?php echo $joined_date; ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($row['name']); ?></div>
                                            <div class="small text-muted">
                                                <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($row['email']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if($row['is_approved'] == 1): ?>
                                                <span class="badge bg-success rounded-pill px-3">
                                                    <i class="bi bi-check-circle me-1"></i> Active
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark rounded-pill px-3 animate-pulse">
                                                    <i class="bi bi-hourglass-split me-1"></i> Pending Approval
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="text-center">
                                                    <div class="fw-bold h5 mb-0"><?php echo $row['product_count']; ?></div>
                                                    <small class="text-muted" style="font-size: 0.75rem;">Products</small>
                                                </div>
                                                <div class="vr"></div>
                                                <div class="text-center">
                                                    <div class="fw-bold h5 mb-0 text-warning">
                                                        <?php echo $score; ?> <i class="bi bi-star-fill small"></i>
                                                    </div>
                                                    <small class="text-muted" style="font-size: 0.75rem;">Rating</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-4">
                                            <?php if($row['is_approved'] == 0): ?>
                                                <a href="admin_sellers.php?approve_id=<?php echo $row['id']; ?>" 
                                                   class="btn btn-success btn-sm fw-bold shadow-sm" 
                                                   onclick="return confirm('Do you want to approve this seller account?');">
                                                    <i class="bi bi-check-lg"></i> Approve
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-outline-secondary btn-sm" disabled>
                                                    <i class="bi bi-shield-check"></i> Verified
                                                </button>
                                                <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">No sellers found.</td>
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