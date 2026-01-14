<?php
/**
 * User Order History Module
 * * Retrieves and displays the authenticated user's past orders.
 * Implements status code mapping for UI visualization (Badges/Icons)
 * and handles "Empty State" scenarios for better UX.
 * * @package BiSepet
 * @subpackage OrderManagement
 */

require_once 'init.php';

/*
|--------------------------------------------------------------------------
| Authentication Guard
|--------------------------------------------------------------------------
| Ensure only logged-in users can access their order history.
*/
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Localization Dictionary (View-Specific)
|--------------------------------------------------------------------------
| Defines translation strings specific to the order history interface.
*/
$page_text = [
    'tr' => [
        'title' => 'Siparişlerim', 'order_no' => 'Sipariş No', 'date' => 'Tarih', 
        'total' => 'Tutar', 'status' => 'Durum', 'address' => 'Teslimat Bilgileri', 
        'empty_title' => 'Henüz Bir Siparişiniz Yok 😔', 
        'empty_desc' => 'Sepetiniz boş görünüyor. Hemen binlerce ürün arasından dilediğini seç ve alışverişin keyfini çıkar!', 
        'back' => 'Alışverişe Başla',
        'st_0' => 'Hazırlanıyor', 'st_1' => 'Kargolandı', 'st_2' => 'Teslim Edildi', 'st_3' => 'İptal Edildi'
    ],
    'en' => [
        'title' => 'My Orders', 'order_no' => 'Order #', 'date' => 'Date', 
        'total' => 'Total', 'status' => 'Status', 'address' => 'Delivery Info', 
        'empty_title' => 'No Orders Yet 😔', 
        'empty_desc' => 'Your cart looks empty. Choose from thousands of products and enjoy shopping!', 
        'back' => 'Start Shopping',
        'st_0' => 'Preparing', 'st_1' => 'Shipped', 'st_2' => 'Delivered', 'st_3' => 'Cancelled'
    ]
];

// UI State Configuration
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == true;
$isSeller = isset($_SESSION['role']) && $_SESSION['role'] == 'seller';

/*
|--------------------------------------------------------------------------
| Data Retrieval
|--------------------------------------------------------------------------
| Fetch orders sorted by most recent date (DESC).
*/
$u_name = $_SESSION['user_name'];
$sql = "SELECT * FROM orders WHERE user_name = '$u_name' ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_text[$lang]['title']; ?> - BiSepet</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        .order-card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: transform 0.2s; }
        .order-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .price-tag { font-size: 1.2rem; font-weight: 700; color: #02A676; }
        .address-box { background-color: #f8f9fa; padding: 15px; border-radius: 8px; font-size: 0.9rem; color: #555; border: 1px dashed #ccc; }
        .api-banner { background-color: #e9ecef; font-size: 0.9rem; font-weight: bold; color: #333; }
        
        /* Empty State UX Patterns */
        .empty-state-card { background: white; border-radius: 20px; padding: 3rem; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.08); }
        .empty-img { max-width: 250px; margin-bottom: 1.5rem; }
        .transition-btn { transition: 0.3s; }
        .transition-btn:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(25, 135, 84, 0.3); }
    </style>
</head>
<body>

<div class="text-center py-1 api-banner border-bottom"><?php echo getDovizKuru(); ?></div>

<?php 
/*
|--------------------------------------------------------------------------
| Navigation Component
|--------------------------------------------------------------------------
| Including the global navbar. 
| Note: Since we have a 'navbar.php' file, ideally we should use:
| include 'navbar.php'; 
| However, preserving inline code as requested for this specific file structure.
*/
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm mb-5">
    <div class="container">
      <a class="navbar-brand" href="index.php"><i class="bi bi-bag-check-fill"></i> BiSepet</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
      
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="products.php"><?php echo $text[$lang]['products']; ?></a></li>
          <li class="nav-item"><a class="nav-link" href="cart.php"><i class="bi bi-cart3"></i> <?php echo $text[$lang]['cart']; ?></a></li>
          <li class="nav-item"><a class="nav-link active" href="my_orders.php"><?php echo $text[$lang]['my_orders']; ?></a></li>

          <?php 
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
                <a href="?lang=tr" class="badge text-bg-light text-decoration-none">TR</a> | 
                <a href="?lang=en" class="badge text-bg-secondary text-decoration-none">EN</a>
            </li>
            <?php
            if (isset($_SESSION['user_name'])) {
                echo "<li class='nav-item dropdown'>
                    <a class='nav-link dropdown-toggle text-white fw-bold' href='#' role='button' data-bs-toggle='dropdown'>
                        👋 " . htmlspecialchars($_SESSION['user_name']) . "
                    </a>
                    <ul class='dropdown-menu dropdown-menu-end'>
                        <li><a class='dropdown-item' href='profile.php'><i class='bi bi-person-circle me-2'></i> Profile</a></li>
                        <li><a class='dropdown-item' href='my_orders.php'><i class='bi bi-box-seam me-2'></i> {$text[$lang]['my_orders']}</a></li>
                        <li><hr class='dropdown-divider'></li>
                        <li><a class='dropdown-item text-danger' href='logout.php'><i class='bi bi-box-arrow-right me-2'></i> {$text[$lang]['logout']}</a></li>
                    </ul>
                </li>";
            }
            ?>
        </ul>
      </div>
    </div>
</nav>

<div class="container pb-5" style="min-height: 60vh;">
    
    <?php if($result && $result->num_rows > 0): ?>
        <h2 class="mb-4 fw-bold text-secondary"><i class="bi bi-box-seam"></i> <?php echo $page_text[$lang]['title']; ?></h2>
        <div class="row g-4">
            <?php while($row = $result->fetch_assoc()): 
                // Date Formatting
                $date = date("d.m.Y H:i", strtotime($row['created_at']));
                $addr = isset($row['address']) ? $row['address'] : 'No address provided';
                $clean_address = nl2br(htmlspecialchars($addr)); 
                
                /*
                |--------------------------------------------------------------------------
                | Status Code Mapping Logic
                |--------------------------------------------------------------------------
                | Maps integer status codes from DB to UI components (Badges & Icons).
                | 0: Preparing, 1: Shipped, 2: Delivered, 3: Cancelled
                */
                $status_code = isset($row['status']) ? $row['status'] : 0;
                $badge_class = "bg-warning text-dark"; 
                $icon = "bi-clock-history";
                $status_text = $page_text[$lang]['st_0'];

                if($status_code == 1) { 
                    $badge_class = "bg-info text-dark"; 
                    $icon = "bi-truck"; 
                    $status_text = $page_text[$lang]['st_1']; 
                } 
                elseif($status_code == 2) { 
                    $badge_class = "bg-success"; 
                    $icon = "bi-check-circle-fill"; 
                    $status_text = $page_text[$lang]['st_2']; 
                } 
                elseif($status_code == 3) { 
                    $badge_class = "bg-danger"; 
                    $icon = "bi-x-circle"; 
                    $status_text = $page_text[$lang]['st_3']; 
                }
            ?>
            <div class='col-lg-6'>
                <div class='card order-card h-100'>
                    <div class='card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom-0'>
                        <div>
                            <span class='text-muted small'><?php echo $page_text[$lang]['order_no']; ?></span>
                            <h5 class='mb-0 fw-bold'>#<?php echo $row['id']; ?></h5>
                        </div>
                        <span class='badge <?php echo $badge_class; ?> rounded-pill px-3 py-2 fs-6'>
                            <i class='bi <?php echo $icon; ?>'></i> <?php echo $status_text; ?>
                        </span>
                    </div>
                    
                    <div class='card-body'>
                        <div class='d-flex justify-content-between align-items-center mb-3'>
                            <div class='text-muted'>
                                <i class='bi bi-calendar-event'></i> <?php echo $date; ?>
                            </div>
                            <div class='price-tag'>
                                €<?php echo number_format($row['total_price'], 2); ?>
                            </div>
                        </div>
                        
                        <div class='mb-2 fw-bold text-secondary small'>
                            <i class='bi bi-geo-alt-fill'></i> <?php echo $page_text[$lang]['address']; ?>
                        </div>
                        <div class='address-box'>
                            <?php echo $clean_address; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

    <?php else: ?>
        <div class="row justify-content-center py-5">
            <div class="col-md-8 col-lg-6">
                <div class="empty-state-card">
                    <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-cart-2130356-1800917.png" 
                         alt="Empty State" class="img-fluid empty-img">
                    
                    <h3 class="fw-bold text-dark mb-3"><?php echo $page_text[$lang]['empty_title']; ?></h3>
                    <p class="text-muted mb-4 fs-5">
                        <?php echo $page_text[$lang]['empty_desc']; ?>
                    </p>

                    <a href="products.php" class="btn btn-success btn-lg rounded-pill px-5 fw-bold shadow transition-btn">
                        <i class="bi bi-cart-check-fill me-2"></i> <?php echo $page_text[$lang]['back']; ?>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php include 'footer.php'; ?>

</body>
</html>