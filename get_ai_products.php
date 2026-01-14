<?php
/**
 * AI Recommendation Engine Endpoint (API)
 * * Serves as the backend interface for the frontend AI module.
 * Simulates a complex recommendation algorithm by retrieving a stochastic
 * selection of products to mimic personalized suggestions.
 * * Returns data in JSON format for asynchronous consumption via Fetch API.
 * * @package BiSepet
 * @subpackage API
 */

require_once 'db.php';

// Set Header for JSON Response
header('Content-Type: application/json; charset=utf-8');

/*
|--------------------------------------------------------------------------
| Latency Simulation (UX Pattern)
|--------------------------------------------------------------------------
| Deliberately introduces a 1.5s delay to simulate complex server-side 
| analysis (Machine Learning inference time). This enhances the user's 
| perception of value regarding the "AI" process.
*/
usleep(1500000); 

/*
|--------------------------------------------------------------------------
| Recommendation Algorithm
|--------------------------------------------------------------------------
| Current Strategy: Randomized Discovery.
| Fetches a subset of products to promote catalog diversity.
| TODO: Implement collaborative filtering or content-based filtering in v2.0.
*/
$sql = "SELECT * FROM products ORDER BY RAND() LIMIT 3";
$result = $conn->query($sql);

$products = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        
        // Business Logic: Price Calculation
        $price = (float)$row['price'];
        $discount = (int)$row['discount_rate'];
        $final_price = $price - ($price * $discount / 100);

        // Data Transformation Object (DTO) structure
        $products[] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            // Fallback image handling
            'image' => empty($row['productPict']) ? 'https://via.placeholder.com/300x200?text=No+Image' : 'images/'.$row['productPict'],
            'price' => number_format($price, 2),
            'final_price' => number_format($final_price, 2),
            'discount' => $discount
        ];
    }
}

// Return Payload
echo json_encode($products);
?>