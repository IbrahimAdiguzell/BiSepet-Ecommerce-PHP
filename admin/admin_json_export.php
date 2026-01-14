<?php
/**
 * Data Export Service (JSON)
 * * Exposes the product catalog as a JSON endpoint for backup or external integration.
 * * Security: Protected by Admin Session Guard.
 * * Format: JSON (Pretty Print, Unicode).
 * * @package BiSepet
 * @subpackage Admin/API
 */

session_start();

// Set Headers for JSON API
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1
header('Pragma: no-cache'); // HTTP 1.0
header('Expires: 0'); // Proxies

/*
|--------------------------------------------------------------------------
| Security: Access Control
|--------------------------------------------------------------------------
| Prevent unauthorized access (IDOR / Information Disclosure).
| Only authenticated administrators can consume this endpoint.
*/
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != true) {
    http_response_code(403); // Forbidden
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access. Admin privileges required.'
    ]);
    exit();
}

require_once '../db.php'; 

/*
|--------------------------------------------------------------------------
| Data Retrieval & Serialization
|--------------------------------------------------------------------------
| Fetch all products and enforce strict data types for JSON compatibility.
*/
$sql = "SELECT * FROM products ORDER BY id ASC";
$result = $conn->query($sql);

$products = array();

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Data Normalization / Type Casting
        $products[] = [
            'id'            => (int)$row['id'],
            'name'          => $row['name'],
            'category'      => $row['category'],
            'price'         => (float)$row['price'],
            'discount_rate' => (int)$row['discount_rate'],
            'stock'         => (int)$row['stock'],
            'description'   => $row['description'],
            'image_url'     => $row['productPict'], // Or full path if needed
            'created_at'    => $row['created_at'] ?? null
        ];
    }
}

/*
|--------------------------------------------------------------------------
| Output Generation
|--------------------------------------------------------------------------
| Return the dataset. 'JSON_PRETTY_PRINT' is used for human readability 
| during development/debugging.
*/
echo json_encode([
    'meta' => [
        'total_count' => count($products),
        'generated_at' => date('Y-m-d H:i:s')
    ],
    'data' => $products
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$conn->close();
?>