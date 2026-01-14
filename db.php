<?php
/**
 * Database Connection Layer
 * * Establishes a persistent connection to the MySQL database using the MySQLi extension.
 * * Implements character set configuration (UTF-8mb4) for full Unicode support.
 * * @package BiSepet
 * @subpackage Database
 */

/*
|--------------------------------------------------------------------------
| Database Configuration
|--------------------------------------------------------------------------
| Define the database connection parameters.
| SECURITY WARNING: In a production environment, these credentials should
| be loaded from Environment Variables (.env) or a secure vault, 
| NEVER hardcoded in the source code.
*/

// Configuration Parameters
$servername = "localhost";
$username   = "root";
$password   = ""; 
$dbname     = "bisepet_db"; // Updated to match the provided SQL file name convention

/*
|--------------------------------------------------------------------------
| Connection Initialization
|--------------------------------------------------------------------------
| Attempt to create a new MySQLi instance.
| Error reporting is enabled to catch connection failures during development.
*/
try {
    // Suppress warnings (@) to handle exceptions manually if needed, 
    // though mysqli_report is preferred in strict mode.
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check for connection errors
    if ($conn->connect_error) {
        throw new Exception("Database Connection Failed: " . $conn->connect_error);
    }

    /*
    |--------------------------------------------------------------------------
    | Character Set Configuration
    |--------------------------------------------------------------------------
    | Set the charset to 'utf8mb4' to support extended characters (e.g., Emojis),
    | ensuring data consistency across the application.
    */
    if (!$conn->set_charset("utf8mb4")) {
        throw new Exception("Error loading character set utf8mb4: " . $conn->error);
    }

} catch (Exception $e) {
    // Log the error to the server's error log (do not expose stack trace to user)
    error_log($e->getMessage());
    
    // Terminate execution with a generic user-friendly message
    die("<h1>Service Unavailable</h1><p>We are experiencing technical difficulties. Please try again later.</p>");
}
?>