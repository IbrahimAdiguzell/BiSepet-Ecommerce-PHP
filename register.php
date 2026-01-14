<?php
/**
 * User Registration & Seller Onboarding Module
 * * Handles new user sign-ups and seller application requests.
 * Implements secure password hashing, input validation, and role assignment logic.
 * * @package BiSepet
 * @subpackage Auth
 */

require_once 'init.php';

// Redirect authenticated users to the dashboard/home
if(isset($_SESSION['user_email'])){
    header("Location: index.php");
    exit();
}

$error = "";
$success = "";

/*
|--------------------------------------------------------------------------
| Registration Logic Handler
|--------------------------------------------------------------------------
| Processes the POST request from the registration form.
| 1. Sanitizes user inputs.
| 2. Determines user role (Customer vs. Seller).
| 3. Hashes the password using Bcrypt (via PASSWORD_DEFAULT).
| 4. Inserts the record into the database with appropriate approval status.
*/
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Input Sanitization to prevent XSS and SQL Injection
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $gender = $conn->real_escape_string($_POST['gender']);
    
    // Role Determination Logic
    $is_seller = isset($_POST['is_seller']);
    $shop_name = $is_seller ? $conn->real_escape_string($_POST['shop_name']) : NULL;
    $role = $is_seller ? 'seller' : 'user';
    
    // Approval Status: Sellers require admin approval (0), Users are auto-approved (1)
    $is_approved = $is_seller ? 0 : 1; 

    // Secure Password Hashing
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Duplicate Email Check
    $check = $conn->query("SELECT id FROM users WHERE email='$email'");
    if($check->num_rows > 0){
        $error = "This email address is already registered.";
    } else {
        // Construct the SQL Query
        // Using NULL for shop_name if not a seller
        $sql = "INSERT INTO users (name, email, password, gender, role, shop_name, is_approved) 
                VALUES ('$name', '$email', '$hashed_password', '$gender', '$role', " . ($shop_name ? "'$shop_name'" : "NULL") . ", $is_approved)";
        
        if ($conn->query($sql) === TRUE) {
            if($is_seller){
                // Feedback for Seller Application
                $success = "✅ Application received! Please wait for admin approval.";
            } else {
                // Feedback for User Registration
                $success = "✅ Registration successful! Redirecting to login...";
                header("refresh:2;url=login.php");
            }
        } else {
            // Error Logging (In production, log to file instead of showing user)
            $error = "Database Error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title>Register - BiSepet</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .auth-card { width: 100%; max-width: 420px; background: white; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); padding: 40px; }
        .brand-title { color: #02A676; font-weight: 700; font-size: 1.8rem; text-decoration: none; display: block; text-align: center; margin-bottom: 20px; }
        .form-control, .form-select { padding: 12px 15px; border-radius: 8px; border: 1px solid #dee2e6; background-color: #f8f9fa; }
        .form-control:focus, .form-select:focus { border-color: #02A676; box-shadow: 0 0 0 0.2rem rgba(2, 166, 118, 0.25); background-color: #fff; }
        .btn-primary { background-color: #02A676; border: none; padding: 12px; border-radius: 8px; font-weight: 600; width: 100%; transition: 0.3s; }
        .btn-primary:hover { background-color: #008c63; }
        .seller-box { background-color: #e6fffa; border: 1px dashed #02A676; border-radius: 8px; padding: 15px; display: none; margin-bottom: 15px; }
    </style>
    
    <script>
        function toggleShopInput() {
            var checkBox = document.getElementById("sellerCheck");
            var shopInput = document.getElementById("shopNameDiv");
            // Toggle visibility based on checkbox state
            shopInput.style.display = checkBox.checked ? "block" : "none";
            // Dynamically set 'required' attribute for validation
            document.getElementById("shopName").required = checkBox.checked;
        }
    </script>
</head>
<body>

    <div class="auth-card">
        <a href="index.php" class="brand-title">BiSepet</a>
        <h4 class="text-center fw-bold mb-4" style="color: #333;">
            <?php echo isset($text[$lang]['create_account']) ? $text[$lang]['create_account'] : 'Create Account'; ?>
        </h4>
        
        <?php if($error): ?><div class="alert alert-danger text-center small py-2"><?php echo $error; ?></div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success text-center small py-2"><?php echo $success; ?></div><?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label small text-muted fw-bold"><?php echo isset($text[$lang]['name_label']) ? $text[$lang]['name_label'] : 'Full Name'; ?></label>
                <input type="text" name="name" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label small text-muted fw-bold"><?php echo isset($text[$lang]['email_label']) ? $text[$lang]['email_label'] : 'Email Address'; ?></label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label small text-muted fw-bold"><?php echo isset($text[$lang]['pass_label']) ? $text[$lang]['pass_label'] : 'Password'; ?></label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label text-muted small fw-bold"><?php echo isset($text[$lang]['gender']) ? $text[$lang]['gender'] : 'Gender'; ?></label>
                <select name="gender" class="form-select">
                    <option value="Unisex"><?php echo isset($text[$lang]['unisex']) ? $text[$lang]['unisex'] : 'Prefer not to say'; ?></option>
                    <option value="Male"><?php echo isset($text[$lang]['male']) ? $text[$lang]['male'] : 'Male'; ?></option>
                    <option value="Female"><?php echo isset($text[$lang]['female']) ? $text[$lang]['female'] : 'Female'; ?></option>
                </select>
            </div>
            
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_seller" id="sellerCheck" onclick="toggleShopInput()">
                <label class="form-check-label fw-bold small" for="sellerCheck" style="cursor: pointer;">
                    <?php echo isset($text[$lang]['be_seller']) ? $text[$lang]['be_seller'] : 'I want to open a store (Seller)'; ?>
                </label>
            </div>

            <div id="shopNameDiv" class="seller-box">
                <label class="text-success fw-bold small mb-1"><?php echo isset($text[$lang]['shop_name']) ? $text[$lang]['shop_name'] : 'Shop Name'; ?></label>
                <input type="text" name="shop_name" id="shopName" class="form-control mb-1">
                <small class="text-muted d-block" style="font-size: 0.75rem;">* Requires admin approval.</small>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <?php echo isset($text[$lang]['register_btn']) ? $text[$lang]['register_btn'] : 'Register'; ?>
            </button>
        </form>

        <div class="text-center mt-4 pt-3 border-top">
            <span class="text-muted small"><?php echo isset($text[$lang]['have_account']) ? $text[$lang]['have_account'] : 'Already have an account?'; ?></span>
            <a href="login.php" class="text-decoration-none fw-bold" style="color: #02A676;">
                <?php echo isset($text[$lang]['login_btn']) ? $text[$lang]['login_btn'] : 'Login'; ?>
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