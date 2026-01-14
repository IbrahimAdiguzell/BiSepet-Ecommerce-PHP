<?php
/**
 * Checkout & Order Processing Controller
 * * Manages the final step of the purchase flow.
 * * Handles address data serialization, payment method selection,
 * order persistence using Prepared Statements, and session state reset.
 * * @package BiSepet
 * @subpackage Transaction
 */

require_once 'init.php';

/*
|--------------------------------------------------------------------------
| Pre-flight Validation (Guard Clauses)
|--------------------------------------------------------------------------
| Ensure the user is authenticated and the cart is not empty before
| attempting to render the checkout interface.
*/
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php?msg=login_required");
    exit();
}

if (!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
    header("Location: products.php?msg=empty_cart");
    exit();
}

$success_msg = "";
$error_msg = "";

/*
|--------------------------------------------------------------------------
| Order Transaction Handler
|--------------------------------------------------------------------------
| 1. Serialize address data into a structured string.
| 2. Calculate final totals.
| 3. Persist order data to MySQL using Prepared Statements (Security).
| 4. Clear cart session (State Reset).
*/
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['complete_order'])) {
    $user_name = $_SESSION['user_name'];
    
    // Data Serialization: Combine detailed address fields for flat storage
    $full_address = "Recipient: " . htmlspecialchars($_POST['fullname']) . "\n" .
                    "Phone: " . htmlspecialchars($_POST['phone']) . "\n" .
                    "Address: " . htmlspecialchars($_POST['address']) . "\n" .
                    "City/District: " . htmlspecialchars($_POST['city']) . " / " . htmlspecialchars($_POST['district']) . "\n" .
                    "Payment Method: " . htmlspecialchars($_POST['payment_method']);

    // Aggregate Totals
    $total_price = 0;
    foreach($_SESSION['cart'] as $item) {
        $total_price += $item['price'] * $item['quantity'];
    }

    // Database Persistence (Secure Prepared Statement)
    $stmt = $conn->prepare("INSERT INTO orders (user_name, total_price, address, created_at) VALUES (?, ?, ?, NOW())");
    
    if ($stmt) {
        // Bind parameters: s = string, d = double/float
        $stmt->bind_param("sds", $user_name, $total_price, $full_address);
        
        if ($stmt->execute()) {
            $last_order_id = $stmt->insert_id;
            
            // State Reset: Clear the shopping cart
            unset($_SESSION['cart']);
            
            $success_msg = "Order successfully placed! Order ID: #" . $last_order_id;
        } else {
            // Log error internally
            $error_msg = "Transaction failed: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_msg = "Database preparation error.";
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo isset($text[$lang]['checkout']) ? $text[$lang]['checkout'] : 'Checkout'; ?> - BiSepet</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f3f4f6; }
        .checkout-container { max-width: 1100px; margin: 0 auto; }
        .form-section { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .summary-card { position: sticky; top: 20px; border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        
        /* Payment Options Styling */
        .payment-option { border: 2px solid #eee; border-radius: 8px; padding: 15px; cursor: pointer; transition: all 0.2s; }
        .payment-option:hover { border-color: #02A676; background-color: #f9fffb; }
        .payment-option.active { border-color: #02A676; background-color: #e6fffa; }
        .form-check-input:checked { background-color: #02A676; border-color: #02A676; }
        
        .btn-confirm { background-color: #02A676; border: none; padding: 15px; font-weight: 600; font-size: 1.1rem; transition: 0.3s; }
        .btn-confirm:hover { background-color: #007369; transform: translateY(-2px); }
    </style>
</head>
<body>

<nav class="navbar navbar-light bg-white border-bottom py-3 mb-4">
    <div class="container justify-content-center">
        <a class="navbar-brand fw-bold fs-4 text-success" href="index.php">
            <i class="bi bi-bag-check-fill"></i> BiSepet <span class="text-dark small">| Checkout</span>
        </a>
    </div>
</nav>

<div class="container checkout-container mb-5">
    
    <?php if($success_msg): ?>
        <div class="card text-center p-5 shadow border-0">
            <div class="display-1 text-success mb-3"><i class="bi bi-check-circle-fill"></i></div>
            <h2 class="mb-3">Order Received!</h2>
            <p class="lead text-muted"><?php echo $success_msg; ?></p>
            <div class="mt-4">
                <a href="index.php" class="btn btn-outline-success">Continue Shopping</a>
                <a href="my_orders.php" class="btn btn-success">View My Orders</a>
            </div>
        </div>
    
    <?php elseif($error_msg): ?>
        <div class="alert alert-danger shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error_msg; ?>
        </div>
    
    <?php else: ?>
    <form method="POST">
        <div class="row g-4">
            
            <div class="col-lg-8">
                
                <div class="form-section">
                    <h4 class="mb-4"><i class="bi bi-geo-alt-fill text-primary"></i> Shipping Information</h4>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="fullname" class="form-control" required placeholder="John Doe">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phone" class="form-control" required placeholder="+90 555 000 0000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" required placeholder="Istanbul">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">District</label>
                            <input type="text" name="district" class="form-control" required placeholder="Kadikoy">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Full Address</label>
                            <textarea name="address" class="form-control" rows="2" required placeholder="Street, Building, Apt No..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h4 class="mb-4"><i class="bi bi-credit-card-2-front-fill text-primary"></i> Payment Method</h4>
                    
                    <div class="d-flex flex-column gap-3">
                        <label class="payment-option d-flex align-items-center gap-3 active">
                            <input class="form-check-input" type="radio" name="payment_method" value="Credit Card" checked onchange="togglePayment('cc')">
                            <div>
                                <span class="fw-bold d-block">Credit / Debit Card</span>
                                <small class="text-muted">Secure SSL encrypted payment</small>
                            </div>
                            <div class="ms-auto text-primary fs-4">
                                <i class="bi bi-credit-card-fill"></i>
                            </div>
                        </label>

                        <div id="cc-form" class="bg-light p-3 rounded border">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label small text-muted">Card Number</label>
                                    <input type="text" class="form-control" placeholder="0000 0000 0000 0000" maxlength="19">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Card Holder</label>
                                    <input type="text" class="form-control" placeholder="Name on Card">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted">Exp. Date</label>
                                    <input type="text" class="form-control" placeholder="MM/YY" maxlength="5">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted">CVV</label>
                                    <input type="text" class="form-control" placeholder="123" maxlength="3">
                                </div>
                            </div>
                        </div>

                        <label class="payment-option d-flex align-items-center gap-3">
                            <input class="form-check-input" type="radio" name="payment_method" value="Bank Transfer" onchange="togglePayment('bank')">
                            <div>
                                <span class="fw-bold d-block">Bank Transfer (EFT/Wire)</span>
                                <small class="text-muted">IBAN details will be shared after order</small>
                            </div>
                            <div class="ms-auto text-success fs-4">
                                <i class="bi bi-bank"></i>
                            </div>
                        </label>

                        <div id="bank-info" class="alert alert-info d-none mb-0 small">
                            <i class="bi bi-info-circle me-1"></i> Please make the transfer to the IBAN provided on the confirmation screen.
                        </div>

                        <label class="payment-option d-flex align-items-center gap-3">
                            <input class="form-check-input" type="radio" name="payment_method" value="Cash on Delivery" onchange="togglePayment('door')">
                            <div>
                                <span class="fw-bold d-block">Cash on Delivery</span>
                                <small class="text-muted">+2.90 Service Fee</small>
                            </div>
                            <div class="ms-auto text-warning fs-4">
                                <i class="bi bi-box-seam-fill"></i>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card summary-card bg-white">
                    <div class="card-header bg-dark text-white py-3">
                        <h5 class="mb-0"><i class="bi bi-receipt"></i> Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush mb-3">
                            <?php 
                            $total = 0;
                            foreach($_SESSION['cart'] as $item) {
                                $subtotal = $item['price'] * $item['quantity'];
                                $total += $subtotal;
                                echo "<li class='list-group-item d-flex justify-content-between lh-sm px-0'>
                                        <div>
                                            <h6 class='my-0 small'>{$item['name']}</h6>
                                            <small class='text-muted'>x{$item['quantity']}</small>
                                        </div>
                                        <span class='text-muted'>€".number_format($subtotal, 2)."</span>
                                      </li>";
                            }
                            ?>
                        </ul>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <strong>€<?php echo number_format($total, 2); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-4 text-success">
                            <span>Shipping</span>
                            <strong>Free</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="h5">Total</span>
                            <span class="h4 text-success fw-bold">€<?php echo number_format($total, 2); ?></span>
                        </div>
                        
                        <button type="submit" name="complete_order" class="btn btn-confirm w-100 rounded-3 text-white shadow-sm">
                            Complete Order <i class="bi bi-arrow-right-circle ms-2"></i>
                        </button>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted"><i class="bi bi-shield-lock-fill"></i> SSL Secured Payment</small>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
    <?php endif; ?>
</div>

<script>
    function togglePayment(type) {
        // Reset Visibility
        document.getElementById('cc-form').classList.add('d-none');
        document.getElementById('bank-info').classList.add('d-none');

        // Toggle selected section
        if (type === 'cc') {
            document.getElementById('cc-form').classList.remove('d-none');
        } else if (type === 'bank') {
            document.getElementById('bank-info').classList.remove('d-none');
        }
        
        // Update Active State Styling
        document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('active'));
        // Find the closest parent label and add active class
        const radios = document.getElementsByName('payment_method');
        for(let i = 0; i < radios.length; i++) {
            if(radios[i].checked) {
                radios[i].closest('.payment-option').classList.add('active');
            }
        }
    }
</script>

</body>
</html>