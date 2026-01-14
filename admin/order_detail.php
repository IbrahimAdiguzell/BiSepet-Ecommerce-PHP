<?php
/**
 * Admin Order Details View
 * * Displays comprehensive information about a specific order.
 * * Features:
 * - Detailed customer and shipping info.
 * - Line-item breakdown of purchased products.
 * - Print-ready layout capabilities.
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

// 2. Input Validation
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Order ID");
}

$order_id = (int)$_GET['id'];

// 3. Fetch Order Header (Joined with User Data)
$sql = "SELECT o.*, u.name as user_name, u.email 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    die("Order not found.");
}

// 4. Fetch Order Items (Assuming an 'order_items' table exists or parsing from JSON)
// Note: In a full implementation, you would query the 'order_items' table here.
// For this demo, we assume the previous checkout logic might not have populated a separate items table,
// but I will provide the query structure assuming standard normalization.
$items_sql = "SELECT * FROM order_items WHERE order_id = ?";
// If you haven't created 'order_items' table yet, this part will be empty. 
// Ideally, checkout.php should insert into both 'orders' and 'order_items'.
$stmt_items = $conn->prepare($items_sql);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items_result = $stmt_items->get_result();

// Helper for Status Badge
$status_labels = [
    0 => ['text' => 'Preparing', 'class' => 'bg-warning text-dark'],
    1 => ['text' => 'Shipped',   'class' => 'bg-info text-dark'],
    2 => ['text' => 'Delivered', 'class' => 'bg-success'],
    3 => ['text' => 'Cancelled', 'class' => 'bg-danger']
];
$current_status = $status_labels[$order['status']];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order #<?php echo $order_id; ?> Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <link href="../style.css" rel="stylesheet">
    
    <style>
        @media print {
            .sidebar, .no-print { display: none !important; }
            .content { margin-left: 0 !important; padding: 0 !important; }
            .card { border: none !important; shadow: none !important; }
        }
    </style>
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
        
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <a href="admin_orders.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
            <button onclick="window.print()" class="btn btn-dark">
                <i class="bi bi-printer"></i> Print Invoice
            </button>
        </div>

        <div class="card shadow-lg border-0">
            <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Order Details <span class="fw-light">#<?php echo $order['id']; ?></span></h5>
                <span class="badge <?php echo $current_status['class']; ?>">
                    <?php echo $current_status['text']; ?>
                </span>
            </div>
            
            <div class="card-body p-4">
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted fw-bold small">Customer Information</h6>
                        <div class="p-3 bg-light rounded border">
                            <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($order['user_name']); ?></p>
                            <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                            <p class="mb-0"><strong>Order Date:</strong> <?php echo date("d F Y, H:i", strtotime($order['created_at'])); ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted fw-bold small">Shipping Address</h6>
                        <div class="p-3 bg-light rounded border h-100">
                            <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                            <?php echo nl2br(htmlspecialchars($order['address'])); ?>
                        </div>
                    </div>
                </div>

                <h6 class="text-uppercase text-muted fw-bold small mb-3">Order Items</h6>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-secondary">
                            <tr>
                                <th>Product Name</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($items_result->num_rows > 0):
                                while($item = $items_result->fetch_assoc()): 
                                    $line_total = $item['price'] * $item['quantity'];
                            ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                    </td>
                                    <td class="text-center"><?php echo $item['quantity']; ?></td>
                                    <td class="text-end">€<?php echo number_format($item['price'], 2); ?></td>
                                    <td class="text-end fw-bold">€<?php echo number_format($line_total, 2); ?></td>
                                </tr>
                            <?php 
                                endwhile; 
                            else: 
                            ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        <em>No individual items found (Legacy Order).</em>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Grand Total</td>
                                <td class="text-end fw-bold fs-5 text-success">
                                    €<?php echo number_format($order['total_price'], 2); ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
        </div>

    </div>
</div>

</body>
</html>