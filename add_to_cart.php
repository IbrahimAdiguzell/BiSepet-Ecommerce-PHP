<?php
/**
 * Asynchronous Cart Controller (AJAX Endpoint)
 * * Handles "Add to Cart" requests sent via JavaScript/AJAX.
 * * Uses Session storage to manage cart state without database writes for temporary data.
 * * Returns JSON response for seamless frontend integration (e.g., Toast notifications).
 * * @package BiSepet
 * @subpackage Cart
 */

require_once 'init.php';

// Set response header to JSON
header('Content-Type: application/json');

/*
|--------------------------------------------------------------------------
| Input Validation
|--------------------------------------------------------------------------
| Check if the request contains the necessary product ID.
*/
if (!isset($_POST['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request: Missing Product ID']);
    exit();
}

// Cast to integer for security
$product_id = (int)$_POST['id'];

/*
|--------------------------------------------------------------------------
| Database Lookup (Secure)
|--------------------------------------------------------------------------
| Verify product existence and fetch details using Prepared Statements.
*/
$stmt = $conn->prepare("SELECT id, name, price, productPict FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Product not found!']);
    exit();
}

$product = $result->fetch_assoc();

/*
|--------------------------------------------------------------------------
| Cart State Management
|--------------------------------------------------------------------------
| 1. Initialize cart session if not exists.
| 2. Check if product is already in cart -> Increment Quantity.
| 3. If new product -> Add to Session array.
*/
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_SESSION['cart'][$product_id])) {
    // Increment quantity
    $_SESSION['cart'][$product_id]['quantity'] += 1;
} else {
    // Add new item
    $_SESSION['cart'][$product_id] = [
        'id'       => $product['id'],
        'name'     => $product['name'],
        'price'    => (float)$product['price'],
        'image'    => $product['productPict'],
        'quantity' => 1
    ];
}

/*
|--------------------------------------------------------------------------
| Response Payload
|--------------------------------------------------------------------------
| Return success status, message, and current total item count for UI update.
*/
$total_items = count($_SESSION['cart']);

echo json_encode([
    'status'     => 'success',
    'message'    => "{$product['name']} added to cart!",
    'cart_count' => $total_items
]);

$stmt->close();
?>