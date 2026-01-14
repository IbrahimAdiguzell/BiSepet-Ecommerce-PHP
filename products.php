<?php
/**
 * Product Catalog & Listing Module
 * * Serves as the main browsing interface for customers.
 * Supports advanced filtering, search functionality, and category navigation.
 * Implements persistent URL parameters for seamless user experience across language switches.
 * * @package BiSepet
 * @subpackage Catalog
 */

require_once 'init.php'; 

/*
|--------------------------------------------------------------------------
| Access Control & Configuration
|--------------------------------------------------------------------------
| Determine user role for conditional UI rendering (Admin/Seller specific links).
*/
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == true;
$isSeller = isset($_SESSION['role']) && $_SESSION['role'] == 'seller';

/*
|--------------------------------------------------------------------------
| Filter Logic & Query Construction
|--------------------------------------------------------------------------
| Builds dynamic SQL WHERE clauses based on GET parameters.
| Preserves existing query strings during language toggling to maintain state.
*/
$where = "WHERE 1=1"; 
$search_term = "";
$category_filter = "";

// Maintain filter state in URL
$queryString = $_SERVER['QUERY_STRING'];
$queryString = preg_replace('/(&?)lang=[^&]*/', '', $queryString);

// Category Filter
if (isset($_GET['category'])) {
    $category_filter = $conn->real_escape_string($_GET['category']);
    $where .= " AND (category LIKE '%$category_filter%')"; 
}

// Search Filter
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = $conn->real_escape_string($_GET['search']);
    $where .= " AND name LIKE '%$search_term%'";
}

// Fetch Products
$sql = "SELECT * FROM products $where ORDER BY id DESC";
$result = $conn->query($sql);

// Fetch Sidebar Categories (Parent Categories Only)
$cat_sql = "SELECT * FROM categories WHERE parent_id = 0 ORDER BY id ASC";
$cat_result = $conn->query($cat_sql);
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
  <meta charset="UTF-8">
  <title><?php echo isset($text[$lang]['products']) ? $text[$lang]['products'] : 'Products'; ?> - BiSepet</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <style>
    body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
    .sidebar-header { background-color: #003840; color: white; padding: 15px; font-weight: 600; border-radius: 12px 12px 0 0; }
    .sidebar-card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .accordion-button:not(.collapsed) { background-color: #e6fffa; color: #02A676; }
    .accordion-button { color: #444; font-weight: 500; }
    .list-group-item:hover { background-color: #f8f9fa; color: #02A676; padding-left: 20px; transition: 0.2s; }
    .product-card { transition: 0.3s; border: none; border-radius: 12px; overflow: hidden; background: white; }
    .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    .product-card img { height: 200px; object-fit: cover; }
    .api-banner { background-color: #e9ecef; font-size: 0.9rem; font-weight: bold; color: #333; }
  </style>
</head>
<body>

  <div class="text-center py-1 api-banner border-bottom"><?php echo getDovizKuru(); ?></div>

  <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container">
      <a class="navbar-brand" href="index.php"><i class="bi bi-bag-check-fill"></i> BiSepet</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
      
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link active" href="products.php"><?php echo $text[$lang]['products']; ?></a></li>
          <li class="nav-item"><a class="nav-link" href="cart.php"><i class="bi bi-cart3"></i> <?php echo $text[$lang]['cart']; ?></a></li>
          
          <li class="nav-item"><a class="nav-link" href="my_orders.php"><?php echo $text[$lang]['my_orders']; ?></a></li>

          <?php 
          // Role-Based Navigation Items
          if ($isAdmin) {
            echo "<li class='nav-item'><a class='nav-link text-warning fw-bold' href='admin/admin_products.php'><i class='bi bi-gear-fill'></i> {$text[$lang]['admin_panel']}</a></li>";
          } 
          elseif ($isSeller) {
            echo "<li class='nav-item'><a class='nav-link text-info fw-bold' href='seller_panel.php'><i class='bi bi-shop'></i> Seller Panel</a></li>";
          }
          ?>
        </ul>
        
        <ul class="navbar-nav align-items-center">
            <li class="nav-item me-3">
                <a href="?lang=tr&<?php echo $queryString; ?>" class="badge text-bg-light text-decoration-none">TR</a> | 
                <a href="?lang=en&<?php echo $queryString; ?>" class="badge text-bg-secondary text-decoration-none">EN</a>
            </li>

            <?php
            if (isset($_SESSION['user_name'])) {
                echo "<li class='nav-item'><span class='nav-link text-light'>👋 {$text[$lang]['welcome_user']}, " . htmlspecialchars($_SESSION['user_name']) . "</span></li>";
                echo "<li class='nav-item'><a class='nav-link' href='logout.php'>{$text[$lang]['logout']}</a></li>";
            } else {
                echo "<li class='nav-item'><a class='nav-link' href='login.php'>{$text[$lang]['login']}</a></li>";
                echo "<li class='nav-item'><a class='nav-link' href='register.php'>{$text[$lang]['register']}</a></li>";
            }
            ?>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container mt-4 mb-5">
    <div class="row g-4">
        
        <div class="col-lg-3">
            <div class="sidebar-card bg-white shadow-sm mb-4">
                <div class="sidebar-header"><i class="bi bi-grid-fill me-2"></i> Categories</div>
                <div class="p-2 border-bottom">
                    <a href="products.php" class="btn btn-outline-success w-100 btn-sm fw-bold">
                        <?php echo $lang == 'en' ? 'All Products' : 'Tüm Ürünler'; ?>
                    </a>
                </div>

                <div class="accordion accordion-flush" id="categoryAccordion">
                    <?php
                    if ($cat_result && $cat_result->num_rows > 0) {
                        while ($main_cat = $cat_result->fetch_assoc()) {
                            $main_id = $main_cat['id'];
                            
                            // i18n for Category Names
                            $main_name = ($lang == 'en' && !empty($main_cat['name_en'])) ? $main_cat['name_en'] : $main_cat['name'];
                            $main_icon = $main_cat['icon'];
                            
                            // Fetch Subcategories
                            $sub_sql = "SELECT * FROM categories WHERE parent_id = $main_id";
                            $sub_result = $conn->query($sub_sql);
                            $has_sub = ($sub_result && $sub_result->num_rows > 0);
                            
                            echo "
                            <div class='accordion-item'>
                                <h2 class='accordion-header'>
                                    <button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#cat{$main_id}'>
                                        <i class='bi {$main_icon} me-2'></i> {$main_name}
                                    </button>
                                </h2>
                                <div id='cat{$main_id}' class='accordion-collapse collapse' data-bs-parent='#categoryAccordion'>
                                    <div class='list-group list-group-flush'>";
                                    
                                    if ($has_sub) {
                                        while ($sub_cat = $sub_result->fetch_assoc()) {
                                            $sub_name = ($lang == 'en' && !empty($sub_cat['name_en'])) ? $sub_cat['name_en'] : $sub_cat['name'];
                                            echo "<a href='?category={$sub_cat['slug']}' class='list-group-item'><i class='bi bi-chevron-right'></i> {$sub_name}</a>";
                                        }
                                    } else {
                                        $all_text = $lang == 'en' ? 'All' : 'Tüm';
                                        echo "<a href='?category={$main_cat['slug']}' class='list-group-item ps-4'>$all_text {$main_name}</a>";
                                    }

                            echo "  </div>
                                </div>
                            </div>";
                        }
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="input-group shadow-sm mb-4">
                <input type="text" id="liveSearchInput" class="form-control form-control-lg border-0" 
                       placeholder="<?php echo isset($text[$lang]['search_placeholder']) ? $text[$lang]['search_placeholder'] : 'Search...'; ?>" 
                       value="<?php echo htmlspecialchars($search_term); ?>">
                <button class="btn btn-success px-4" type="button">
                    <?php echo isset($text[$lang]['search_btn']) ? $text[$lang]['search_btn'] : 'Search'; ?>
                </button>
            </div>

            <div class="row g-3" id="productsContainer">
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $pId = (int)$row['id'];
                        $imgSrc = !empty($row['productPict']) ? "images/".$row['productPict'] : "https://via.placeholder.com/300x200";
                        
                        $price = (float)$row['price'];
                        $discount = isset($row['discount_rate']) ? (int)$row['discount_rate'] : 0;
                        $final_price = $price - ($price * $discount / 100);
                        
                        echo "
                        <div class='col-6 col-md-4 product-item'>
                            <div class='card product-card h-100 shadow-sm'>
                                <a href='product_detail.php?id={$pId}' class='position-relative'>
                                    "; 
                                    if($discount > 0) { 
                                        echo "<span class='position-absolute top-0 start-0 badge bg-danger m-2'>-$discount%</span>"; 
                                    } 
                                    echo "
                                    <img src='{$imgSrc}' class='card-img-top'>
                                </a>
                                <div class='card-body d-flex flex-column p-3'>
                                    <h6 class='card-title text-truncate product-title mb-1'>
                                        <a href='product_detail.php?id={$pId}' class='text-decoration-none text-dark'>{$row['name']}</a>
                                    </h6>
                                    <div class='mt-auto'>";
                                    
                                    if($discount > 0){
                                        echo "<div class='text-decoration-line-through text-muted small'>€".number_format($price, 2)."</div>";
                                        echo "<div class='fw-bold text-danger fs-5'>€".number_format($final_price, 2)."</div>";
                                    } else {
                                        echo "<div class='fw-bold text-primary fs-5'>€".number_format($price, 2)."</div>";
                                    }

                                    echo "
                                        <form method='POST' action='cart.php' class='mt-2'>
                                            <input type='hidden' name='product_id' value='{$pId}'>
                                            <button type='submit' name='add_to_cart' class='btn btn-success w-100 btn-sm'>{$text[$lang]['add_to_cart']}</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>";
                    }
                } else {
                    echo "<div class='col-12 text-center py-5 text-muted'>{$text[$lang]['no_product']}</div>";
                }
                ?>
            </div>
        </div>
    </div>
  </div>

  <?php include 'footer.php'; ?>
  
  <script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById('liveSearchInput');
        const productItems = document.querySelectorAll('.product-item');
        
        // Real-time filtering based on DOM elements
        searchInput.addEventListener('keyup', function() {
            const filter = searchInput.value.toLowerCase();
            productItems.forEach(item => {
                const title = item.querySelector('.product-title').textContent.toLowerCase();
                item.style.display = title.includes(filter) ? "" : "none";
            });
        });
    });
  </script>
</body>
</html>