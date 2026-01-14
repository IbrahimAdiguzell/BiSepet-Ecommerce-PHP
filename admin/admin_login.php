<?php
/**
 * Admin Panel Authentication Gateway
 * * A dedicated login interface for system administrators.
 * * Security Features:
 * - SQL Injection protection via Prepared Statements.
 * - Role-Based Access Control (RBAC): Strictly enforces 'admin' role.
 * - Password Hashing verification (Bcrypt).
 * * @package BiSepet
 * @subpackage Admin
 */

session_start();
require_once '../db.php';

/*
|--------------------------------------------------------------------------
| Session Guard
|--------------------------------------------------------------------------
| If the admin is already logged in, redirect immediately to the dashboard.
*/
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header("Location: admin_dashboard.php");
    exit();
}

$error = "";

/*
|--------------------------------------------------------------------------
| Authentication Logic
|--------------------------------------------------------------------------
| Validates credentials against the centralized 'users' table, 
| ensuring the user has the 'admin' role privileges.
*/
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize Input
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // Secure Query: Check for email AND 'admin' role simultaneously
    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ? AND role = 'admin'");
    
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            
            // Verify Hash
            if (password_verify($password, $row['password'])) {
                // Regenerate Session ID to prevent Session Fixation attacks
                session_regenerate_id(true);

                // Set Admin Session
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['name'];
                $_SESSION['user_email'] = $email;
                $_SESSION['role'] = 'admin';
                $_SESSION['is_admin'] = true;

                header("Location: admin_dashboard.php");
                exit();
            } else {
                $error = "Invalid credentials.";
            }
        } else {
            $error = "Access Denied: You do not have admin privileges or user not found.";
        }
        $stmt->close();
    } else {
        $error = "Database error: Unable to prepare statement.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - BiSepet</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #1e2024 0%, #232526 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .admin-card {
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
        }
        .form-control {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid #444;
            color: #fff;
        }
        .form-control:focus {
            background: rgba(0, 0, 0, 0.4);
            border-color: #0d6efd;
            color: #fff;
            box-shadow: none;
        }
        .btn-admin {
            background: #0d6efd;
            border: none;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 1px;
        }
        .btn-admin:hover {
            background: #0b5ed7;
        }
    </style>
</head>
<body>

    <div class="admin-card text-center text-white">
        <div class="mb-4">
            <i class="bi bi-shield-lock-fill display-1 text-primary"></i>
        </div>
        <h3 class="fw-bold mb-1">Admin Panel</h3>
        <p class="text-white-50 mb-4 small">BiSepet Management System</p>

        <?php if($error): ?>
            <div class="alert alert-danger text-start py-2 small border-0 bg-danger text-white mb-4">
                <i class="bi bi-exclamation-circle me-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-floating mb-3">
                <input type="email" name="email" class="form-control" id="floatingInput" placeholder="name@example.com" required>
                <label for="floatingInput" class="text-secondary">Email Address</label>
            </div>
            <div class="form-floating mb-4">
                <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password" required>
                <label for="floatingPassword" class="text-secondary">Password</label>
            </div>
            
            <button type="submit" class="btn btn-admin btn-primary w-100 rounded-pill mb-3">
                SECURE LOGIN <i class="bi bi-arrow-right-short"></i>
            </button>
        </form>
        
        <div class="mt-3">
            <a href="../index.php" class="text-white-50 text-decoration-none small hover-white">
                <i class="bi bi-arrow-left"></i> Back to Site
            </a>
        </div>
    </div>

</body>
</html>