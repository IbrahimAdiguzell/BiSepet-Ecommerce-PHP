<?php
/**
 * Authentication Logout Controller
 * * Safely terminates the user session and cleans up server-side data.
 * Ensures complete destruction of session variables to prevent
 * unauthorized access via stale sessions.
 * * @package BiSepet
 * @subpackage Auth
 */

// Initialize the session to access current state
session_start();

/*
|--------------------------------------------------------------------------
| Session Teardown
|--------------------------------------------------------------------------
| 1. session_unset(): Frees all session variables currently registered.
| 2. session_destroy(): Destroys the data associated with the current session
|    from the storage (file system or database).
*/
session_unset();
session_destroy();

/*
|--------------------------------------------------------------------------
| Redirection
|--------------------------------------------------------------------------
| Redirect the user to the public homepage (Landing Page) after a successful logout.
*/
header("Location: index.php");
exit(); // Ensure no further code is executed
?>