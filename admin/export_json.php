<?php
/**
 * Data Export Service (JSON Backup)
 * * Generates a full backup of the product catalog in JSON format.
 * * Features:
 * - Output Buffering to prevent file corruption.
 * - Strict Data Typing (Int/Float casting) for JSON standards.
 * - Metadata inclusion (Timestamp, Counts).
 * * @package BiSepet
 * @subpackage Admin/IO
 */

// Start Output Buffering to capture any accidental whitespace/errors
ob_start();

session_start();

/*
|--------------------------------------------------------------------------
| Security: Access Control Layer
|--------------------------------------------------------------------------
| Strictly restrict access to Administrators.
*/
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != true) {
    // Clear buffer and send Forbidden response
    ob_end_clean(); 
    http_response_code(403);
    die("⛔ Access Denied: Administrator privileges required.");
}

// Database Connection
require_once '../db.php';

/*
|--------------------------------------------------------------------------
| Data Retrieval & Normalization
|--------------------------------------------------------------------------
| Fetch all products and cast numeric strings to actual numbers for JSON.
*/
$sql = "SELECT * FROM products ORDER BY id ASC";
$result = $conn->query($sql);

$export_data = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Data Normalization (Type Casting)
        $export_data[] = [
            'id'            => (int)$row['id'],
            'name'          => $row['name'],
            'category'      => $row['category'],
            'price'         => (float)$row['price'],
            'discount_rate' => (int)$row['discount_rate'],
            'stock'         => (int)$row['stock'],
            'description'   => $row['description'],
            'image'         => $row['productPict'],
            'created_at'    => $row['created_at'] ?? date('Y-m-d H:i:s')
        ];
    }
}

/*
|--------------------------------------------------------------------------
| Payload Construction
|--------------------------------------------------------------------------
| Wrap the data with metadata for context.
*/
$payload = [
    'metadata' => [
        'generated_by' => $_SESSION['user_name'] ?? 'System Admin',
        'generated_at' => date('Y-m-d H:i:s'),
        'record_count' => count($export_data),
        'system'       => 'BiSepet E-Commerce Engine'
    ],
    'products' => $export_data
];

/*
|--------------------------------------------------------------------------
| File Download Execution
|--------------------------------------------------------------------------
| 1. Clear any previous output buffers.
| 2. Set headers to force download.
| 3. Output the JSON string.
*/
$filename = "bisepet_backup_" . date("Ymd_Hi") . ".json";
$json_output = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

if ($json_output === false) {
    ob_end_clean();
    die("JSON Encoding Error: " . json_last_error_msg());
}

// Clear the buffer to ensure the file contains ONLY the JSON
ob_end_clean();

// Headers
header('Content-Description: File Transfer');
header('Content-Type: application/json; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . strlen($json_output));

// Flush and Exit
echo $json_output;
exit;
?>