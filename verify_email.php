<?php
/**
 * Account Verification Module
 * * Handles the email verification process using a One-Time Password (OTP).
 * Validates the code entered by the user against the database record 
 * to activate the account.
 * * @package BiSepet
 * @subpackage Authentication
 */

session_start();
require_once 'db.php';

// Access Control: Ensure the user has gone through the registration flow
if (!isset($_SESSION['verify_email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['verify_email'];
$msg = "";

/*
|--------------------------------------------------------------------------
| Debug / Development Fallback
|--------------------------------------------------------------------------
| If the SMTP server is unavailable during development, the code is passed
| via URL parameter to allow testing the verification flow.
| TODO: Remove this block in production environment.
*/
if(isset($_GET['demo_code'])) {
    $code_val = htmlspecialchars($_GET['demo_code']);
    $msg = "<div class='alert alert-warning'>Dev Mode: Mail Server Unavailable. Code: <b>" . $code_val . "</b></div>";
}

/*
|--------------------------------------------------------------------------
| Verification Logic
|--------------------------------------------------------------------------
*/
if (isset($_POST['verify'])) {
    // Input Sanitization
    $code = $conn->real_escape_string($_POST['code']);

    // Validate the OTP against the user record
    $sql = "SELECT id FROM users WHERE email='$email' AND verification_code='$code'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Verification Successful: Activate the account
        $update = "UPDATE users SET is_verified = 1 WHERE email='$email'";
        
        if($conn->query($update)) {
            // Clear verification session and redirect to login
            // Using JS redirect for client-side alert feedback
            echo "<script>
                alert('Account verified successfully! You can now login.'); 
                window.location.href='login.php';
            </script>";
            exit();
        }
    } else {
        // Verification Failed
        $msg = "<div class='alert alert-danger'>Invalid verification code. Please try again.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Account Verification - BiSepet</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container mt-5" style="max-width:400px;">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-success text-white text-center py-3">
                <h5 class="mb-0">Verify Your Account 📧</h5>
            </div>
            
            <div class="card-body p-4">
                <p class="text-center text-muted mb-4">
                    Please enter the 6-digit code sent to <br>
                    <strong><?php echo htmlspecialchars($email); ?></strong>
                </p>
                
                <?php echo $msg; ?>

                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="mb-3">
                        <input type="text" name="code" class="form-control text-center fs-4 letter-spacing-2" 
                               placeholder="123456" maxlength="6" required autofocus autocomplete="off">
                    </div>
                    <button type="submit" name="verify" class="btn btn-success w-100 fw-bold">
                        Verify Code
                    </button>
                </form>
            </div>
            
            <div class="card-footer text-center bg-white border-0 pb-3">
                <small class="text-muted">Didn't receive the code? Check your spam folder.</small>
            </div>
        </div>
    </div>

</body>
</html>