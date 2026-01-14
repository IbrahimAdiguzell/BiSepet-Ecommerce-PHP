<?php
/**
 * Global Navigation Bar Component
 * * Renders the top navigation menu with responsive behavior.
 * Handles role-based visibility (Admin/Seller), language toggling state preservation,
 * and user session management (Login/Logout/Profile).
 * * @package BiSepet
 * @subpackage UI
 */

/*
|--------------------------------------------------------------------------
| Role Definition & State Management
|--------------------------------------------------------------------------
| Determine the current user's privileges to conditionally render menu items.
*/
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == true;
$isSeller = isset($_SESSION['role']) && $_SESSION['role'] == 'seller';

/*
|--------------------------------------------------------------------------
| URI Manipulation (UX Optimization)
|--------------------------------------------------------------------------
| Preserves existing GET parameters (like search filters or pagination)
| when switching languages. Regex removes the old 'lang' param to avoid duplication.
*/
$queryString = $_SERVER['QUERY_STRING'];
$queryString = preg_replace('/(&?)lang=[^&]*/', '', $queryString);
?>

<div class="text-center py-1 api-banner border-bottom"><?php echo getDovizKuru(); ?></div>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm mb-5">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="bi bi-bag-check-fill text-success"></i> BiSepet
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
      
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <?php echo isset($text[$lang]['home']) ? $text[$lang]['home'] : 'Home'; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="products.php"><?php echo $text[$lang]['products']; ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">
                        <i class="bi bi-cart3"></i> <?php echo $text[$lang]['cart']; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="my_orders.php"><?php echo $text[$lang]['my_orders']; ?></a>
                </li>

                <?php 
                // Conditional Rendering: Role-Based Menu Items
                if ($isAdmin) {
                    echo "<li class='nav-item'>
                            <a class='nav-link text-warning fw-bold' href='admin/admin_products.php'>
                                <i class='bi bi-gear-fill'></i> {$text[$lang]['admin_panel']}
                            </a>
                          </li>";
                } 
                elseif ($isSeller) {
                    echo "<li class='nav-item'>
                            <a class='nav-link text-info fw-bold' href='seller_panel.php'>
                                <i class='bi bi-shop'></i> Seller Panel
                            </a>
                          </li>";
                }
                ?>
            </ul>
        
            <ul class="navbar-nav align-items-center gap-3">
                <li class="nav-item">
                    <a href="?lang=tr&<?php echo $queryString; ?>" class="text-white text-decoration-none small">TR</a> 
                    <span class="text-secondary">|</span> 
                    <a href="?lang=en&<?php echo $queryString; ?>" class="text-white text-decoration-none small">EN</a>
                </li>

                <?php
                // User Session Control
                if (isset($_SESSION['user_name'])) {
                    // Authenticated User Dropdown
                    echo "<li class='nav-item dropdown'>
                            <a class='nav-link dropdown-toggle text-white fw-bold' href='#' role='button' data-bs-toggle='dropdown'>
                                👋 " . htmlspecialchars($_SESSION['user_name']) . "
                            </a>
                            <ul class='dropdown-menu dropdown-menu-end shadow'>
                                <li>
                                    <a class='dropdown-item' href='profile.php'>
                                        <i class='bi bi-person-circle me-2'></i> Profile
                                    </a>
                                </li>
                                <li>
                                    <a class='dropdown-item' href='my_orders.php'>
                                        <i class='bi bi-box-seam me-2'></i> {$text[$lang]['my_orders']}
                                    </a>
                                </li>
                                <li><hr class='dropdown-divider'></li>
                                <li>
                                    <a class='dropdown-item text-danger' href='logout.php'>
                                        <i class='bi bi-box-arrow-right me-2'></i> {$text[$lang]['logout']}
                                    </a>
                                </li>
                            </ul>
                        </li>";
                } else {
                    // Guest User Actions
                    echo "<li class='nav-item'>
                            <a class='btn btn-outline-light btn-sm' href='login.php'>{$text[$lang]['login']}</a>
                          </li>";
                    echo "<li class='nav-item'>
                            <a class='btn btn-success btn-sm' href='register.php'>{$text[$lang]['register']}</a>
                          </li>";
                }
                ?>
            </ul>
        </div>
    </div>
</nav>