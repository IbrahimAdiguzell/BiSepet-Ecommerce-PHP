<?php
/**
 * Admin Logout Controller
 * * Terminates the administrator session securely.
 * * Clears all session data and redirects to the admin login interface.
 * * @package BiSepet
 * @subpackage Admin/Auth
 */

session_start();

/*
|--------------------------------------------------------------------------
| Session Teardown
|--------------------------------------------------------------------------
| 1. Unset all session variables (Memory cleanup).
| 2. Destroy the session cookie/file (Storage cleanup).
*/
session_unset();
session_destroy();

/*
|--------------------------------------------------------------------------
| Redirection
|--------------------------------------------------------------------------
| Redirect back to the Admin Login page.
*/
header("Location: login.php");
exit();
?>