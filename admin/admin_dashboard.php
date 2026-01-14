<?php
/**
 * Admin Analytics Dashboard
 * * Aggregates key performance indicators (KPIs) and visualizes data.
 * * Features: Total revenue calculation, order status distribution (Doughnut Chart),
 * and category inventory analysis (Bar Chart).
 * * @package BiSepet
 * @subpackage Admin
 */

session_start();

/*
|--------------------------------------------------------------------------
| Access Control (RBAC)
|--------------------------------------------------------------------------
| Ensures only authenticated administrators can access sensitive metrics.
*/
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != true) {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php'; 

/*
|--------------------------------------------------------------------------
| KPI Aggregation Service
|--------------------------------------------------------------------------
| Fetches high-level metrics for the summary cards.
*/
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='user'")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(total_price) as total FROM orders")->fetch_assoc()['total'];

/*
|--------------------------------------------------------------------------
| Data Visualization Pre-processing
|--------------------------------------------------------------------------
| Prepares datasets for Chart.js rendering.
*/

// DataSet 1: Order Status Distribution
$status_counts = [0, 0, 0, 0]; // Mapping: 0:Prep, 1:Ship, 2:Delivered, 3:Cancel
$res = $conn->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
while($row = $res->fetch_assoc()){
    $status_counts[$row['status']] = $row['count'];
}

// DataSet 2: Inventory by Category (Top 5)
$cat_labels = [];
$cat_data = [];
$res_cat = $conn->query("SELECT category, COUNT(*) as count FROM products GROUP BY category LIMIT 5");
while($row = $res_cat->fetch_assoc()){
    $cat_labels[] = ucfirst($row['category']); // Capitalize for UI
    $cat_data[] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - BiSepet</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <link href="../style.css" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="admin-container">
    
    <div class="sidebar d-flex flex-column">
        <h4 class="text-center text-white mb-4 mt-2">
            <i class="bi bi-shield-lock"></i> Admin Panel
        </h4>
        <a href="admin_dashboard.php" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="admin_products.php"><i class="bi bi-box-seam"></i> Products</a>
        <a href="admin_orders.php"><i class="bi bi-truck"></i> Orders</a>
        <a href="../index.php" class="mt-auto mb-4 text-warning"><i class="bi bi-arrow-left"></i> Back to Site</a>
    </div>

    <div class="admin-content">
        <h2 class="mb-4 fw-bold text-secondary">Dashboard Overview</h2>

        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card bg-primary text-white border-0 shadow h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0 fw-bold"><?php echo $total_orders; ?></h3>
                                <small class="text-white-50">Total Orders</small>
                            </div>
                            <i class="bi bi-cart-check fs-1 opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white border-0 shadow h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0 fw-bold">€<?php echo number_format($total_revenue, 2); ?></h3>
                                <small class="text-white-50">Total Revenue</small>
                            </div>
                            <i class="bi bi-currency-euro fs-1 opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark border-0 shadow h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0 fw-bold"><?php echo $total_products; ?></h3>
                                <small class="text-muted">Active Products</small>
                            </div>
                            <i class="bi bi-box-seam fs-1 opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white border-0 shadow h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0 fw-bold"><?php echo $total_users; ?></h3>
                                <small class="text-white-50">Registered Users</small>
                            </div>
                            <i class="bi bi-people fs-1 opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-pie-chart"></i> Order Status Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="orderChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-bar-chart"></i> Inventory by Category</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="catChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Configuration for Order Status Chart
    const ctxOrder = document.getElementById('orderChart');
    new Chart(ctxOrder, {
        type: 'doughnut',
        data: {
            labels: ['Preparing', 'Shipped', 'Delivered', 'Cancelled'],
            datasets: [{
                data: <?php echo json_encode(array_values($status_counts)); ?>,
                backgroundColor: ['#ffc107', '#0dcaf0', '#198754', '#dc3545'],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Configuration for Category Chart
    const ctxCat = document.getElementById('catChart');
    new Chart(ctxCat, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($cat_labels); ?>,
            datasets: [{
                label: 'Product Count',
                data: <?php echo json_encode($cat_data); ?>,
                backgroundColor: '#02A676',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } },
            plugins: { legend: { display: false } }
        }
    });
</script>

</body>
</html>