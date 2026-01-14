<?php
/**
 * Admin Message Center (Inbox)
 * * Manages customer feedback and contact form submissions.
 * * Features:
 * - Secure message deletion.
 * - direct 'mailto' integration for quick replies.
 * - Chronological ordering (Newest first).
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
| Action Handler: Delete Message
|--------------------------------------------------------------------------
*/
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->bind_param("i", $del_id);
    
    if ($stmt->execute()) {
        $msg = "<div class='alert alert-success alert-dismissible fade show'>
                    Message deleted successfully.
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                </div>";
    }
    $stmt->close();
}

// Fetch Messages
$sql = "SELECT * FROM messages ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Message Center - Admin</title>
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
        <a href="admin_messages.php" class="active"><i class="bi bi-envelope"></i> Messages</a>
        <a href="../index.php" class="mt-auto mb-4 text-warning"><i class="bi bi-arrow-left"></i> Back to Site</a>
    </div>

    <div class="admin-content">
        
        <h2 class="mb-4 fw-bold text-secondary">Customer Messages</h2>
        
        <?php if(isset($msg)) echo $msg; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">Sender</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="ps-4" style="min-width: 200px;">
                                            <div class="fw-bold text-primary">
                                                <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($row['name']); ?>
                                            </div>
                                            <div class="small text-muted">
                                                <?php echo htmlspecialchars($row['email']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-wrap" style="max-width: 500px;">
                                                <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                                            </div>
                                        </td>
                                        <td style="min-width: 150px;">
                                            <i class="bi bi-clock small text-muted"></i> 
                                            <?php 
                                                // Handle potential null dates
                                                echo isset($row['created_at']) ? date("d.m.Y H:i", strtotime($row['created_at'])) : '-'; 
                                            ?>
                                        </td>
                                        <td class="text-end pe-4" style="min-width: 140px;">
                                            <a href="mailto:<?php echo $row['email']; ?>" class="btn btn-sm btn-outline-primary me-1" title="Reply via Email">
                                                <i class="bi bi-reply-fill"></i>
                                            </a>
                                            <a href="?delete=<?php echo $row['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Are you sure you want to delete this message?');"
                                               title="Delete Message">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                                        <span class="text-muted">Inbox is empty. No new messages.</span>
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