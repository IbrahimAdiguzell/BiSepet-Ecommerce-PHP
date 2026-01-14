<?php
/**
 * Admin Order Listing Module
 * * Lists all customer orders with status indicators and detail links.
 * * Features:
 * - Inner Join with Users table for customer details.
 * - Dynamic Status Badges.
 * - Responsive Table Layout.
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
| Data Retrieval
|--------------------------------------------------------------------------
| Fetch orders ordered by date (Newest first).
| We assume 'orders' table links to 'users' via 'user_id'.
*/
$sql = "SELECT o.*, u.name as customer_name, u.email as customer_email 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC";

$result = $conn->query($sql);

// Status Helper Function
function getStatusBadge($status) {
    switch ($status) {
        case 0: return '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Preparing</span>';
        case 1: return '<span class="badge bg-info text-dark"><i class="bi bi-truck"></i> Shipped</span>';
        case 2: return '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Delivered</span>';
        case 3: return '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Cancelled</span>';
        default: return '<span class="badge bg-secondary">Unknown</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Orders - Admin Panel</title>
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
        <a href="orders.php" class="active"><i class="bi bi-truck"></i> Orders</a> <a href="admin_messages.php"><i class="bi bi-envelope"></i> Messages</a>
        <a href="../index.php" class="mt-auto mb-4 text-warning"><i class="bi bi-arrow-left"></i> Back to Site</a>
    </div>

    <div class="admin-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-secondary">Order History</h2>
            <span class="badge bg-primary fs-6"><?php echo $result->num_rows; ?> Total Orders</span>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Customer</th>
                                <th>Total Price</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold">#<?php echo $row['id']; ?></td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($row['customer_name']); ?></div>
                                            <div class="small text-muted"><?php echo htmlspecialchars($row['customer_email']); ?></div>
                                        </td>
                                        <td class="text-success fw-bold">
                                            €<?php echo number_format($row['total_price'], 2); ?>
                                        </td>
                                        <td>
                                            <span class="small text-muted">
                                                <i class="bi bi-calendar3"></i> 
                                                <?php echo date("d.m.Y H:i", strtotime($row['created_at'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo getStatusBadge($row['status']); ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="order_details.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary shadow-sm fw-bold">
                                                <i class="bi bi-eye"></i> View Details
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                                        No orders found.
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