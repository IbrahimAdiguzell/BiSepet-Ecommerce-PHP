<?php
/**
 * Shopping Cart Management Module
 * * Handles temporary state management for the shopping session.
 * * Operations: Add, Remove, Update Quantities, and Clear Cart.
 * * Data Source: Session-based storage (non-persistent until checkout).
 * * @package BiSepet
 * @subpackage Cart
 */

require_once 'init.php'; 

/*
|--------------------------------------------------------------------------
| Controller Logic: Cart Operations
|--------------------------------------------------------------------------
| Handles POST requests to modify the cart state.
| Uses strict type casting (int) for security and data integrity.
*/

// 1. Batch Update Quantities
if(isset($_POST['update_cart'])){
    foreach($_POST['quantity'] as $id => $qty){
        $id = (int)$id;
        $qty = (int)$qty;
        if($qty > 0 && isset($_SESSION['cart'][$id])){
            $_SESSION['cart'][$id]['quantity'] = $qty;
        }
    }
}

// 2. Remove Single Item
if(isset($_POST['remove_id'])){
    $id = (int)$_POST['remove_id'];
    if(isset($_SESSION['cart'][$id])){
        unset($_SESSION['cart'][$id]);
    }
}

// 3. Clear Entire Cart (Reset State)
if(isset($_POST['clear_cart'])){
    unset($_SESSION['cart']);
}

// 4. Add Item to Cart (with DB Validation)
if(isset($_POST['add_to_cart'])){
    $product_id = (int)$_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    // Fetch product metadata from DB to ensure validity and get current price
    $stmt = $conn->prepare("SELECT name, price, productPict FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0){
        $product = $result->fetch_assoc();
        
        // Logic: Increment quantity if exists, else initialize new item
        if(isset($_SESSION['cart'][$product_id])){
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            // Data Normalization for Session Storage
            $_SESSION['cart'][$product_id] = [
                'name' => $product['name'],
                'price' => (float)$product['price'],
                'image' => $product['productPict'],
                'quantity' => $quantity
            ];
        }
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
  <meta charset="UTF-8">
  <title><?php echo isset($text[$lang]['cart_title']) ? $text[$lang]['cart_title'] : 'Shopping Cart'; ?> - BiSepet</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <style>
    body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
    .navbar-brand { font-weight: 700; }
    .table img { object-fit: cover; border-radius: 8px; }
    .api-banner { background-color: #e9ecef; font-size: 0.9rem; font-weight: bold; color: #333; }
    
    /* Empty State Styling */
    .empty-cart-container { padding: 4rem 2rem; }
    .empty-cart-icon { font-size: 4rem; color: #dee2e6; margin-bottom: 1rem; }
  </style>
</head>

<body>

  <?php include 'navbar.php'; ?>

  <div class="container my-5" style="min-height: 60vh;">
    <h2 class="mb-4 text-center">
        <i class="bi bi-cart4"></i> <?php echo $text[$lang]['cart_title']; ?>
    </h2>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center mb-0">
                <thead class="table-dark">
                    <tr>
                    <th scope="col" class="text-start ps-4"><?php echo $text[$lang]['p_name']; ?></th>
                    <th scope="col"><?php echo $text[$lang]['price']; ?></th>
                    <th scope="col"><?php echo $text[$lang]['quantity']; ?></th>
                    <th scope="col"><?php echo $text[$lang]['total']; ?></th>
                    <th scope="col"><?php echo $text[$lang]['action']; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $grand_total = 0;
                    
                    // Check if session cart is populated
                    if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])){
                        foreach($_SESSION['cart'] as $product_id => $item){
                            // Calculate Line Totals
                            $line_total = $item['price'] * $item['quantity'];
                            $grand_total += $line_total;
                            
                            // Image Fallback Logic
                            $imgSrc = !empty($item['image']) ? "images/{$item['image']}" : "https://via.placeholder.com/50?text=No+Img";
                            ?>
                            <tr>
                                <td class='text-start ps-4'>
                                    <div class='d-flex align-items-center'>
                                        <a href='product_detail.php?id=<?php echo $product_id; ?>'>
                                            <img src='<?php echo $imgSrc; ?>' alt='<?php echo htmlspecialchars($item['name']); ?>' width='60' height='60' class='me-3 border shadow-sm'>
                                        </a>
                                        <span class='fw-bold'><?php echo htmlspecialchars($item['name']); ?></span>
                                    </div>
                                </td>
                                <td>€<?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <form method='POST' action='cart.php' class='d-flex justify-content-center align-items-center'>
                                        <input type='number' name='quantity[<?php echo $product_id; ?>]' value='<?php echo $item['quantity']; ?>' min='1' class='form-control form-control-sm me-2 text-center' style='width:60px;'>
                                        <button type='submit' name='update_cart' class='btn btn-sm btn-outline-primary border-0' title="Update Quantity">
                                            <i class='bi bi-arrow-clockwise fs-5'></i>
                                        </button>
                                    </form>
                                </td>
                                <td class='fw-bold text-success'>€<?php echo number_format($line_total, 2); ?></td>
                                <td>
                                    <form method='POST' action='cart.php'>
                                        <input type='hidden' name='remove_id' value='<?php echo $product_id; ?>'>
                                        <button type='submit' class='btn btn-sm btn-outline-danger border-0' title="Remove Item">
                                            <i class='bi bi-trash fs-5'></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        // Empty State View
                        echo "<tr>
                                <td colspan='5' class='text-center empty-cart-container'>
                                    <i class='bi bi-cart-x empty-cart-icon'></i>
                                    <p class='fs-5 text-muted'>{$text[$lang]['empty_cart']}</p>
                                    <a href='products.php' class='btn mt-3 text-white fw-bold shadow-sm' style='background-color: #007369; border: none; padding: 12px 30px; border-radius: 50px;'>
                                        <i class='bi bi-cart4'></i> {$text[$lang]['start_shopping']}
                                    </a>
                                </td>
                              </tr>";
                    }
                    ?>
                </tbody>
                </table>
            </div>

            <?php if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
            <div class="d-flex justify-content-between align-items-center p-4 bg-light border-top">
                <form method="POST" action="cart.php">
                    <button type="submit" name="clear_cart" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to clear the cart?');">
                        <i class="bi bi-trash3"></i> Clear Cart
                    </button>
                </form>
                
                <div class="text-end">
                    <h4 class="mb-2">Total: <span class="text-primary fw-bold">€<?php echo number_format($grand_total, 2); ?></span></h4>
                    <a href="checkout.php" class="btn btn-success btn-lg shadow-sm">
                        <i class="bi bi-credit-card"></i> <?php echo $text[$lang]['checkout']; ?>
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
  </div>

  <?php include 'footer.php'; ?>

</body>
</html>