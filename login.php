<?php
/**
 * User Authentication & Login Controller
 * * Handles user login requests, credential validation, and session initialization.
 * Implements role-based redirection (Admin vs Seller vs User) and checks account status.
 * * @package BiSepet
 * @subpackage Auth
 */

require_once 'init.php'; 

/*
|--------------------------------------------------------------------------
| Guest Guard
|--------------------------------------------------------------------------
| Prevent authenticated users from accessing the login page.
| Redirects active sessions to the dashboard.
*/
if(isset($_SESSION['user_email'])){
    header("Location: index.php");
    exit();
}

$error = "";

/*
|--------------------------------------------------------------------------
| Authentication Logic
|--------------------------------------------------------------------------
| 1. Sanitize inputs.
| 2. Lookup user by email.
| 3. Verify password hash (Bcrypt).
| 4. Check account status (Seller Approval).
| 5. Initialize session and route based on role.
*/
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Input Sanitization
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // Credential Lookup
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Secure Password Verification
        if (password_verify($password, $row['password'])) { 
            
            // Account Status Validation: Check if Seller is approved
            if ($row['role'] == 'seller' && $row['is_approved'] == 0) {
                $error = "⚠️ Your seller application is pending admin approval.";
            } 
            else {
                // Session Initialization
                $_SESSION['user_email'] = $email;
                $_SESSION['user_name'] = $row['name'];
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['role'] = $row['role'];

                // Role-Based Routing
                if ($row['role'] == 'admin') {
                    $_SESSION['is_admin'] = true;
                    header("Location: admin/admin_products.php");
                } elseif ($row['role'] == 'seller') {
                    $_SESSION['is_admin'] = false;
                    header("Location: seller_panel.php");
                } else {
                    $_SESSION['is_admin'] = false;
                    header("Location: index.php");
                }
                exit();
            }

        } else {
            // Generic error message for security (or specific)
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo isset($text[$lang]['login']) ? $text[$lang]['login'] : 'Login'; ?> - BiSepet</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .auth-card { width: 100%; max-width: 420px; background: white; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); padding: 40px; }
        .brand-title { color: #02A676; font-weight: 700; font-size: 1.8rem; text-decoration: none; display: block; text-align: center; margin-bottom: 20px; }
        .form-control { padding: 12px 15px; border-radius: 8px; border: 1px solid #dee2e6; background-color: #f8f9fa; }
        .form-control:focus { border-color: #02A676; box-shadow: 0 0 0 0.2rem rgba(2, 166, 118, 0.25); background-color: #fff; }
        .btn-primary { background-color: #02A676; border: none; padding: 12px; border-radius: 8px; font-weight: 600; width: 100%; transition: 0.3s; }
        .btn-primary:hover { background-color: #008c63; }
    </style>
</head>
<body>

    <div class="auth-card">
        <a href="index.php" class="brand-title">BiSepet</a>
        <h4 class="text-center fw-bold mb-4" style="color: #333;">
            <?php echo isset($text[$lang]['welcome']) ? $text[$lang]['welcome'] : 'Welcome Back'; ?>
        </h4>
        
        <?php if($error): ?>
            <div class="alert alert-danger text-center small py-2"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label small text-muted fw-bold">
                    <?php echo isset($text[$lang]['email_label']) ? $text[$lang]['email_label'] : 'Email Address'; ?>
                </label>
                <input type="email" name="email" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label small text-muted fw-bold">
                    <?php echo isset($text[$lang]['pass_label']) ? $text[$lang]['pass_label'] : 'Password'; ?>
                </label>
                <input type="password" name="password" class="form-control" required>
            </div>
            
            <div class="d-flex justify-content-end mb-3">
                <a href="forgot_password.php" class="text-decoration-none small text-muted">
                    <?php echo isset($text[$lang]['forgot_pass']) ? $text[$lang]['forgot_pass'] : 'Forgot Password?'; ?>
                </a>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <?php echo isset($text[$lang]['login_btn']) ? $text[$lang]['login_btn'] : 'Login'; ?>
            </button>
        </form>

        <div class="text-center mt-4 pt-3 border-top">
            <span class="text-muted small">
                <?php echo isset($text[$lang]['no_account']) ? $text[$lang]['no_account'] : "Don't have an account?"; ?>
            </span>
            <a href="register.php" class="text-decoration-none fw-bold" style="color: #02A676;">
                <?php echo isset($text[$lang]['create_account']) ? $text[$lang]['create_account'] : 'Create Account'; ?>
            </a>
        </div>
        
        <div class="text-center mt-2">
            <a href="?lang=tr" class="text-decoration-none small text-secondary fw-bold me-2">TR</a>
            <span class="text-muted small">|</span>
            <a href="?lang=en" class="text-decoration-none small text-secondary fw-bold ms-2">EN</a>
        </div>
    </div>

</body>
</html>