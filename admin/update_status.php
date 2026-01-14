<?php
/**
 * Admin Order Update Controller
 * * Dedicated interface for modifying order status.
 * * Features:
 * - Current status visualization.
 * - Secure state transition using Prepared Statements.
 * - Consistent UI with the rest of the Admin Panel.
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

// 1. Input Validation
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_orders.php");
    exit();
}

$order_id = (int)$_GET['id'];
$msg = "";

/*
|--------------------------------------------------------------------------
| Configuration: Order Statuses
|--------------------------------------------------------------------------
*/
$status_map = [
    0 => 'Preparing',
    1 => 'Shipped',
    2 => 'Delivered',
    3 => 'Cancelled'
];

/*
|--------------------------------------------------------------------------
| Action Handler: Update Status
|--------------------------------------------------------------------------
*/
if (isset($_POST['update'])) {
    $new_status = (int)$_POST['status'];
    
    // Secure Update
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $order_id);
    
    if ($stmt->execute()) {
        $msg = "<div class='alert alert-success alert-dismissible fade show'>
                    <i class='bi bi-check-circle-fill me-2'></i> Order status updated successfully!
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                </div>";
    } else {
        $msg = "<div class='alert alert-danger'>Update failed: " . $conn->error . "</div>";
    }
    $stmt->close();
}

/*
|--------------------------------------------------------------------------
| Data Retrieval
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("SELECT o.*, u.name, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) die("Order not found.");
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Update Order #<?php echo $order_id; ?></title>
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
        <a href="admin_products.php"><i class="bi bi-box-seam"></i> Products</a>
        <a href="admin_orders.php" class="active"><i class="bi bi-truck"></i> Orders</a>
        <a href="admin_messages.php"><i class="bi bi-envelope"></i> Messages</a>
        <a href="../index.php" class="mt-auto mb-4 text-warning"><i class="bi bi-arrow-left"></i> Back to Site</a>
    </div>

    <div class="admin-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-secondary">Manage Order #<?php echo $order_id; ?></h2>
            <a href="admin_orders.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>

        <?php echo $msg; ?>

        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Update Status</h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <div class="mb-4 p-3 bg-light rounded border">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Customer:</span>
                                <span class="fw-bold"><?php echo htmlspecialchars($order['name']); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Amount:</span>
                                <span class="fw-bold text-success">€<?php echo number_format($order['total_price'], 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Current Status:</span>
                                <span class="badge bg-secondary"><?php echo $status_map[$order['status']]; ?></span>
                            </div>
                        </div>

                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Select New Status</label>
                                <select name="status" class="form-select form-select-lg">
                                    <option value="0" <?php echo ($order['status'] == 0) ? 'selected' : ''; ?>>Preparing</option>
                                    <option value="1" <?php echo ($order['status'] == 1) ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="2" <?php echo ($order['status'] == 2) ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="3" <?php echo ($order['status'] == 3) ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="update" class="btn btn-primary btn-lg fw-bold shadow-sm">
                                    <i class="bi bi-save"></i> Save Changes
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>