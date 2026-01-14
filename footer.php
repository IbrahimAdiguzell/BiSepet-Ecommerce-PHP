<?php
/**
 * Global Footer Component
 * * Provides site-wide navigation links, social media integration, 
 * and a quick feedback form.
 * Implements responsive layout using Bootstrap grid system.
 * * @package BiSepet
 * @subpackage UI
 */
?>

<footer class="bg-dark text-white pt-5 pb-4 mt-5">
    <div class="container">
        <div class="row">
            
            <div class="col-md-4 mb-4">
                <h5 class="text-uppercase fw-bold text-warning mb-3">BiSepet</h5>
                <p class="small text-secondary">
                    <?php echo isset($text[$lang]['about_text']) ? $text[$lang]['about_text'] : 'Your trusted e-commerce partner.'; ?>
                </p>
                <div class="d-flex gap-3">
                    <a href="#" class="text-white social-link"><i class="bi bi-facebook fs-5"></i></a>
                    <a href="#" class="text-white social-link"><i class="bi bi-instagram fs-5"></i></a>
                    <a href="#" class="text-white social-link"><i class="bi bi-twitter fs-5"></i></a>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <h5 class="text-uppercase fw-bold text-warning mb-3"><?php echo isset($text[$lang]['links_title']) ? $text[$lang]['links_title'] : 'Quick Links'; ?></h5>
                <ul class="list-unstyled">
                    <li><a href="index.php" class="text-decoration-none text-secondary hover-white"><?php echo isset($text[$lang]['home']) ? $text[$lang]['home'] : 'Home'; ?></a></li>
                    <li><a href="products.php" class="text-decoration-none text-secondary hover-white"><?php echo isset($text[$lang]['products']) ? $text[$lang]['products'] : 'Products'; ?></a></li>
                    <li><a href="cart.php" class="text-decoration-none text-secondary hover-white"><?php echo isset($text[$lang]['cart']) ? $text[$lang]['cart'] : 'Cart'; ?></a></li>
                    <li><a href="my_orders.php" class="text-decoration-none text-secondary hover-white"><?php echo isset($text[$lang]['my_orders']) ? $text[$lang]['my_orders'] : 'My Orders'; ?></a></li>
                </ul>
            </div>

            <div class="col-md-5 mb-4">
                <div class="bg-secondary p-4 rounded shadow-sm" style="--bs-bg-opacity: .2;">
                    <h5 class="fw-bold text-warning mb-3">
                        <i class="bi bi-envelope-paper"></i> <?php echo isset($text[$lang]['write_us']) ? $text[$lang]['write_us'] : 'Contact Us'; ?>
                    </h5>
                    <p class="small text-white-50 mb-3"><?php echo isset($text[$lang]['write_desc']) ? $text[$lang]['write_desc'] : 'We value your feedback.'; ?></p>
                    
                    <form method="POST">
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="text" name="name" class="form-control form-control-sm" placeholder="<?php echo isset($text[$lang]['name_ph']) ? $text[$lang]['name_ph'] : 'Name'; ?>" required>
                            </div>
                            <div class="col-6">
                                <input type="email" name="email" class="form-control form-control-sm" placeholder="<?php echo isset($text[$lang]['email_ph']) ? $text[$lang]['email_ph'] : 'Email'; ?>" required>
                            </div>
                        </div>
                        <div class="mt-2">
                            <textarea name="message" class="form-control form-control-sm" rows="2" placeholder="<?php echo isset($text[$lang]['msg_ph']) ? $text[$lang]['msg_ph'] : 'Message'; ?>" required></textarea>
                        </div>
                        <button type="submit" name="send_feedback" class="btn btn-warning btn-sm w-100 mt-2 fw-bold">
                            <?php echo isset($text[$lang]['send_btn']) ? $text[$lang]['send_btn'] : 'Send'; ?>
                        </button>
                    </form>
                </div>
            </div>

        </div>

        <hr class="mb-4 border-secondary">
        
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start small text-secondary">
                &copy; <?php echo date("Y"); ?> <strong>BiSepet</strong>. <?php echo isset($text[$lang]['rights']) ? $text[$lang]['rights'] : 'All Rights Reserved.'; ?>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <i class="bi bi-credit-card text-secondary fs-4 me-2" title="Credit Card"></i>
                <i class="bi bi-paypal text-secondary fs-4 me-2" title="PayPal"></i>
                <i class="bi bi-bank text-secondary fs-4" title="Bank Transfer"></i>
            </div>
        </div>
    </div>

    <style>
        .hover-white:hover { color: #fff !important; transition: 0.3s; padding-left: 5px; }
        .social-link:hover { opacity: 0.8; transform: translateY(-3px); display: inline-block; transition: 0.3s; }
    </style>
</footer>