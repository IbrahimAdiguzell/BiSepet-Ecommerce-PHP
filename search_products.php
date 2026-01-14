<?php
/**
 * Product Search & Filter Service
 * * Handles dynamic product retrieval based on search keywords and category filters.
 * Constructs secure SQL queries and returns rendered HTML fragments for the frontend.
 * * @package BiSepet
 * @subpackage Catalog
 */

require_once 'db.php';

/*
|--------------------------------------------------------------------------
| Input Sanitization & Validation
|--------------------------------------------------------------------------
| Cleaning GET parameters to prevent SQL Injection attacks before usage.
| Using ternary operators for default empty string assignment.
*/
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';

/*
|--------------------------------------------------------------------------
| Dynamic Query Construction
|--------------------------------------------------------------------------
| The 'WHERE 1=1' pattern is used to simplify the logic of appending 
| multiple dynamic 'AND' conditions without complex conditional checks.
*/
$where = "WHERE 1=1";

if ($search != '') {
    // Perform partial match search on product name
    $where .= " AND name LIKE '%$search%'";
}

if ($category != '') {
    // Filter by specific category slug
    $where .= " AND category = '$category'";
}

// Execute the final query
$sql = "SELECT * FROM products $where ORDER BY id DESC";
$result = $conn->query($sql);

/*
|--------------------------------------------------------------------------
| View Rendering (HTML Fragments)
|--------------------------------------------------------------------------
| Iterates through the dataset and renders responsive product cards.
| This output is typically injected into the DOM via AJAX/Fetch.
*/
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // XSS Protection for Output
        $pName = htmlspecialchars($row['name']);
        $pPrice = htmlspecialchars($row['price']); // Consider number_format() for currency
        $pImg = htmlspecialchars($row['productPict']);
        $pId = (int)$row['id'];
        
        // Render Product Component
        echo "
        <div class='col-md-4 col-sm-6 fade-in'>
          <div class='card product-card shadow-sm h-100 border-0 transition-hover'>
            <a href='product_detail.php?id={$pId}' class='position-relative overflow-hidden'>
                <img src='images/{$pImg}' class='card-img-top' alt='{$pName}' 
                     onerror=\"this.src='https://via.placeholder.com/300x200?text=No+Image'\"
                     style='height: 200px; object-fit: cover;'>
            </a>
            <div class='card-body d-flex flex-column'>
              <h5 class='card-title text-truncate'>
                <a href='product_detail.php?id={$pId}' class='text-decoration-none text-dark fw-bold'>{$pName}</a>
              </h5>
              <p class='fw-bold text-primary mb-3 fs-5'>€{$pPrice}</p>
              
              <div class='mt-auto'>
                  <form method='POST' action='cart.php'>
                    <input type='hidden' name='product_id' value='{$pId}'>
                    <button type='submit' name='add_to_cart' class='btn btn-success w-100 mb-2 rounded-pill btn-sm'>
                      <i class='bi bi-cart-plus'></i> Add to Cart
                    </button>
                  </form>
                  <a href='product_detail.php?id={$pId}' class='btn btn-outline-secondary w-100 btn-sm rounded-pill'>
                    View Details
                  </a>
              </div>
            </div>
          </div>
        </div>";
    }
} else {
    // Empty State Handling
    echo "
    <div class='col-12 text-center py-5'>
        <div class='text-muted opacity-75'>
            <i class='bi bi-search fs-1'></i>
            <p class='mt-3 fs-5'>No products found matching your criteria.</p>
        </div>
    </div>";
}
?>