<?php
/**
 * User Profile Management Module
 * * Handles user account settings including personal information updates
 * and secure password change operations.
 * Implements input sanitization and output escaping for security.
 * * @package BiSepet
 * @subpackage UserAccount
 */

require_once 'init.php';

/*
|--------------------------------------------------------------------------
| Authentication Guard
|--------------------------------------------------------------------------
| Restrict access to logged-in users only.
*/
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$u_id = $_SESSION['user_id'];
$message = "";

/*
|--------------------------------------------------------------------------
| Profile Information Update Handler
|--------------------------------------------------------------------------
| Updates user demographics (Name, Phone, City, Address).
| Synchronizes the session 'user_name' to reflect changes immediately.
*/
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    // Input Sanitization
    $name = $conn->real_escape_string($_POST['name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $city = $conn->real_escape_string($_POST['city']);
    $address = $conn->real_escape_string($_POST['address']);
    
    $sql_update = "UPDATE users SET name='$name', phone='$phone', city='$city', address='$address' WHERE id=$u_id";
    
    if ($conn->query($sql_update) === TRUE) {
        $message = "<div class='alert alert-success alert-dismissible fade show'>✅ {$text[$lang]['update_success']}<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
        // Session Synchronization
        $_SESSION['user_name'] = $name;
    } else {
        // Error logging
        $message = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

/*
|--------------------------------------------------------------------------
| Security: Password Change Logic
|--------------------------------------------------------------------------
| 1. Verifies the current password hash.
| 2. Validates new password confirmation.
| 3. Hashes the new password using Bcrypt (PASSWORD_DEFAULT) before storage.
*/
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    // Retrieve current hash for verification
    $sql = "SELECT password FROM users WHERE id = $u_id";
    $res = $conn->query($sql);
    $row = $res->fetch_assoc();

    if (password_verify($current_pass, $row['password'])) {
        if ($new_pass === $confirm_pass) {
            // Secure Hashing
            $new_hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password='$new_hashed' WHERE id=$u_id");
            $message = "<div class='alert alert-success alert-dismissible fade show'>🔒 Password updated successfully!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
        } else {
            $message = "<div class='alert alert-warning alert-dismissible fade show'>⚠️ New passwords do not match!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
        }
    } else {
        $message = "<div class='alert alert-danger alert-dismissible fade show'>❌ Current password is incorrect!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    }
}

// Fetch Current User Data for Form Pre-filling
$sql = "SELECT * FROM users WHERE id = $u_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $text[$lang]['profile_title']; ?> - BiSepet</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        .profile-card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; }
        .profile-header { background: linear-gradient(135deg, #02A676, #00cdac); padding: 40px; text-align: center; color: white; }
        .avatar-circle { width: 100px; height: 100px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem; margin: 0 auto 15px; border: 3px solid rgba(255,255,255,0.5); }
        .nav-pills .nav-link { color: #555; font-weight: 600; border-radius: 8px; padding: 12px 20px; }
        .nav-pills .nav-link.active { background-color: #02A676; color: white; }
        .form-label { font-weight: 600; font-size: 0.9rem; color: #555; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container mt-5 mb-5 pt-3">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <?php echo $message; ?>

            <div class="card profile-card">
                <div class="profile-header">
                    <div class="avatar-circle"><i class="bi bi-person-fill"></i></div>
                    <h3 class="fw-bold"><?php echo htmlspecialchars($user['name']); ?></h3>
                    <p class="opacity-75 mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
                    <span class="badge bg-light text-success mt-2 px-3 py-1 rounded-pill text-uppercase small fw-bold">
                        <?php echo $user['role']; ?>
                    </span>
                </div>

                <div class="card-body p-4">
                    
                    <ul class="nav nav-pills nav-fill mb-4 bg-light p-2 rounded" id="profileTab" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button">
                                <i class="bi bi-person-lines-fill me-2"></i> <?php echo $text[$lang]['personal_info']; ?>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button">
                                <i class="bi bi-shield-lock-fill me-2"></i> <?php echo $text[$lang]['pass_label']; ?>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="profileTabContent">
                        
                        <div class="tab-pane fade show active" id="info" role="tabpanel">
                            <form method="POST">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label"><?php echo $text[$lang]['name_label']; ?></label>
                                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><?php echo $text[$lang]['email_label']; ?></label>
                                        <input type="email" class="form-control bg-light" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><?php echo $text[$lang]['phone_label']; ?></label>
                                        <input type="text" name="phone" class="form-control" placeholder="05XX XXX XX XX" value="<?php echo htmlspecialchars($user['phone']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><?php echo $text[$lang]['city_label']; ?></label>
                                        <input type="text" name="city" class="form-control" placeholder="City" value="<?php echo htmlspecialchars($user['city']); ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label"><?php echo $text[$lang]['address_label']; ?></label>
                                        <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                                    </div>
                                </div>
                                <div class="text-end mt-4">
                                    <button type="submit" name="update_profile" class="btn btn-success fw-bold px-4">
                                        <i class="bi bi-save me-2"></i> <?php echo $text[$lang]['save_changes']; ?>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="password" role="tabpanel">
                            <form method="POST">
                                <div class="row g-3 justify-content-center">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label class="form-label">Current Password</label>
                                            <input type="password" name="current_password" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">New Password</label>
                                            <input type="password" name="new_password" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Confirm New Password</label>
                                            <input type="password" name="confirm_password" class="form-control" required>
                                        </div>
                                        <div class="alert alert-info small py-2">
                                            <i class="bi bi-info-circle"></i> Use a strong password for better security.
                                        </div>
                                        <button type="submit" name="change_password" class="btn btn-warning w-100 fw-bold text-dark">
                                            <i class="bi bi-key-fill me-2"></i> Update Password
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>