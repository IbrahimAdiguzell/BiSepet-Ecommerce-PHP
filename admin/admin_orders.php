<?php
/**
 * Admin Order Management Controller
 * * Handles the lifecycle of customer orders (View, Update Status).
 * * Features:
 * - Join query to fetch customer details along with order data.
 * - Secure status updates using Prepared Statements.
 * - Dynamic status badge rendering.
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
| Status Definition (State Machine)
|--------------------------------------------------------------------------
| Centralized definition for order statuses to ensure consistency.
*/
$order_statuses = [
    0 => ['label' => 'Preparing', 'class' => 'bg-warning text-dark', 'icon' => 'bi-box-seam'],
    1 => ['label' => 'Shipped',   'class' => 'bg-info text-dark',    'icon' => 'bi-truck'],
    2 => ['label' => 'Delivered', 'class' => 'bg-success',           'icon' => 'bi-check-circle'],
    3 => ['label' => 'Cancelled', 'class' => 'bg-danger',            'icon' => 'bi-x-circle']
];

/*
|--------------------------------------------------------------------------
| Action Handler: Update Status
|--------------------------------------------------------------------------
*/
if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = (int)$_POST['status'];
    
    // Secure Update
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $order_id);
    
    if ($stmt->execute()) {
        $msg = "<div class='alert alert-success alert-dismissible fade show'>
                    <i class='bi bi-check-circle-fill me-2'></i> Order #{$order_id} status updated successfully!
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                </div>";
    } else {
        $msg = "<div class='alert alert-danger'>Update failed: " . $conn->error . "</div>";
    }
    $stmt->close();
}

/*
|--------------------------------------------------------------------------
| Data Retrieval (Joined View)
|--------------------------------------------------------------------------
| Fetches orders and joins with the 'users' table to get customer names.
*/
$sql = "SELECT o.*, u.name as user_name, u.email 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Order Management - Admin</title>
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
        <a href="admin_orders.php" class="active"><i class="bi bi-truck"></i> Orders</a>
        <a href="admin_messages.php"><i class="bi bi-envelope"></i> Messages</a>
        <a href="../index.php" class="mt-auto mb-4 text-warning"><i class="bi bi-arrow-left"></i> Back to Site</a>
    </div>

    <div class="admin-content">
        
        <h2 class="mb-4 fw-bold text-secondary">Order Management</h2>
        
        <?php if(isset($msg)) echo $msg; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">Order ID</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th class="pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): 
                                    $current_status = $order_statuses[$row['status']];
                                ?>
                                    <tr>
                                        <td class="ps-4 fw-bold">#<?php echo $row['id']; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light rounded-circle p-2 me-2 text-primary">
                                                    <i class="bi bi-person-fill"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($row['user_name']); ?></div>
                                                    <div class="small text-muted"><?php echo htmlspecialchars($row['email']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fw-bold text-success">€<?php echo number_format($row['total_price'], 2); ?></td>
                                        <td>
                                            <span class="text-muted small">
                                                <i class="bi bi-calendar3"></i> 
                                                <?php echo date("d.m.Y H:i", strtotime($row['created_at'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill <?php echo $current_status['class']; ?> px-3 py-2">
                                                <i class="bi <?php echo $current_status['icon']; ?> me-1"></i>
                                                <?php echo $current_status['label']; ?>
                                            </span>
                                        </td>
                                        <td class="pe-4">
                                            <form method="POST" class="d-flex align-items-center gap-2">
                                                <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                                
                                                <select name="status" class="form-select form-select-sm" style="width: 140px; border-color: #ddd;">
                                                    <?php foreach($order_statuses as $key => $val): ?>
                                                        <option value="<?php echo $key; ?>" <?php if($row['status'] == $key) echo 'selected'; ?>>
                                                            <?php echo $val['label']; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                
                                                <button type="submit" name="update_status" class="btn btn-sm btn-primary" title="Update Status">
                                                    <i class="bi bi-arrow-repeat"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">No orders found.</td>
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