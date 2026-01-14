<?php
/**
 * Password Recovery Controller
 * * Manages the password reset flow.
 * * In a production environment, this script should:
 * 1. Generate a cryptographic token.
 * 2. Store the token with an expiration time in the database.
 * 3. Send a secure link via email (using the Mail Service).
 * * @package BiSepet
 * @subpackage Auth
 */

require_once 'init.php'; 

// Initialize state variables
$msg = "";
$error = "";

/*
|--------------------------------------------------------------------------
| Reset Logic Handler
|--------------------------------------------------------------------------
| Processes the form submission.
| NOTE: To prevent "User Enumeration" attacks, the feedback message should
| stay generic (e.g., "If this email exists, we sent a link") regardless of
| whether the email is actually in the database or not.
*/
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize and Validate Email
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // TODO: Implement actual Token Generation & DB Update here.
        // Example: $token = bin2hex(random_bytes(32));
        
        // Simulation Feedback
        $msg = ($lang == 'tr') 
            ? "✅ Sıfırlama bağlantısı e-posta adresinize gönderildi! (Lütfen spam kutusunu kontrol edin)" 
            : "✅ Password reset link has been sent to your email! (Please check your spam folder)";
    } else {
        $error = ($lang == 'tr') ? "Geçersiz e-posta formatı." : "Invalid email format.";
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo isset($text[$lang]['forgot_pass']) ? $text[$lang]['forgot_pass'] : 'Recovery'; ?> - BiSepet</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f0f2f5; }
        .recovery-card { max-width: 400px; margin: 80px auto; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); }
        .btn-brand { background-color: #02A676; color: white; width: 100%; padding: 12px; border-radius: 8px; font-weight: 600; transition: 0.3s; border:none; }
        .btn-brand:hover { background-color: #008c63; color: white; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(2, 166, 118, 0.3); }
        .icon-box { color: #02A676; background: #e6fffa; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin: 0 auto 20px; }
    </style>
</head>
<body>

    <nav class="navbar navbar-light bg-white shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php" style="color: #02A676;">
                <i class="bi bi-bag-check-fill"></i> BiSepet
            </a>
            <div>
                <a href="?lang=tr" class="text-decoration-none fw-bold text-secondary me-2 small">TR</a> | 
                <a href="?lang=en" class="text-decoration-none fw-bold text-secondary ms-2 small">EN</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card recovery-card p-4 text-center">
            
            <div class="icon-box">
                <i class="bi bi-shield-lock-fill" style="font-size: 2.5rem;"></i>
            </div>
            
            <h3 class="fw-bold mb-2">
                <?php echo isset($text[$lang]['reset_title']) ? $text[$lang]['reset_title'] : 'Reset Password'; ?>
            </h3>
            <p class="text-muted small mb-4">
                <?php echo isset($text[$lang]['reset_desc']) ? $text[$lang]['reset_desc'] : 'Enter email to receive instructions.'; ?>
            </p>
            
            <?php if($msg): ?>
                <div class="alert alert-success small border-0 bg-success-subtle text-success">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <?php if($error): ?>
                <div class="alert alert-danger small border-0 bg-danger-subtle text-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3 text-start">
                    <label class="form-label text-muted small fw-bold">
                        <?php echo isset($text[$lang]['email_label']) ? $text[$lang]['email_label'] : 'Email Address'; ?>
                    </label>
                    <input type="email" name="email" class="form-control form-control-lg fs-6" required placeholder="name@example.com">
                </div>
                <button type="submit" class="btn btn-brand mb-3">
                    <?php echo isset($text[$lang]['send_link']) ? $text[$lang]['send_link'] : 'Send Link'; ?>
                </button>
            </form>

            <a href="login.php" class="text-decoration-none text-secondary small fw-bold">
                <i class="bi bi-arrow-left me-1"></i> 
                <?php echo isset($text[$lang]['back_login']) ? $text[$lang]['back_login'] : 'Back to Login'; ?>
            </a>
        </div>
    </div>
    
</body>
</html>