<?php
/**
 * Product Detail & Review Controller
 * * Displays detailed product information and handles the user feedback system.
 * Features a server-side image processing pipeline (using GD Library) 
 * for resizing and watermarking user-uploaded photos.
 * * @package BiSepet
 * @subpackage Catalog
 */

require_once 'init.php';

/*
|--------------------------------------------------------------------------
| Request Validation
|--------------------------------------------------------------------------
| Validate that a numeric Product ID is present in the URL parameters.
| Redirect to the catalog if the ID is missing or invalid.
*/
if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$pId = (int)$_GET['id']; // Cast to int for basic SQL injection protection

// Fetch Product Metadata
$sql = "SELECT * FROM products WHERE id = $pId";
$res = $conn->query($sql);
if ($res->num_rows == 0) {
    die("Product not found / 404");
}
$product = $res->fetch_assoc();

/*
|--------------------------------------------------------------------------
| Business Logic: Pricing
|--------------------------------------------------------------------------
| Calculate the final price dynamically based on the discount rate.
*/
$price = (float)$product['price'];
$discount = isset($product['discount_rate']) ? (int)$product['discount_rate'] : 0;
$final_price = $price - ($price * $discount / 100);

/*
|--------------------------------------------------------------------------
| Review Submission Handler
|--------------------------------------------------------------------------
| Processes user ratings, comments, and optional image uploads.
*/
$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_review'])) {
    // Auth Check
    if (!isset($_SESSION['user_id'])) {
        $msg = "<div class='alert alert-warning'>Please login to leave a review.</div>";
    } else {
        $u_id = $_SESSION['user_id'];
        $u_name = $_SESSION['user_name'];
        $rating = (int)$_POST['rating'];
        $comment = $conn->real_escape_string($_POST['comment']);
        $img_db_name = NULL;

        /*
        |--------------------------------------------------------------------------
        | Image Processing Pipeline (GD Library)
        |--------------------------------------------------------------------------
        | 1. Validation: Check file extension and existence.
        | 2. Optimization: Resize image to max 800px width (Resource Management).
        | 3. Watermarking: Apply 'BiSepet User' text overlay for copyright.
        */
        if (!empty($_FILES['review_img']['name'])) {
            $upload_dir = "images/reviews/";
            // Ensure directory exists with correct permissions
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true); 

            $file_ext = strtolower(pathinfo($_FILES['review_img']['name'], PATHINFO_EXTENSION));
            $new_name = "review_" . time() . "_" . rand(100,999) . "." . $file_ext;
            $target_file = $upload_dir . $new_name;

            // Whitelist validation
            if (in_array($file_ext, ['jpg', 'jpeg', 'png'])) {
                
                // Step 1: Create Image Resource from Source
                if ($file_ext == 'png') $source = imagecreatefrompng($_FILES['review_img']['tmp_name']);
                else $source = imagecreatefromjpeg($_FILES['review_img']['tmp_name']);

                // Step 2: Calculate New Dimensions (Aspect Ratio Preservation)
                $width = imagesx($source);
                $height = imagesy($source);
                $new_width = 800;
                $new_height = floor($height * ($new_width / $width));

                // Create Virtual Canvas
                $virtual_image = imagecreatetruecolor($new_width, $new_height);
                
                // Handle PNG Transparency
                if($file_ext == 'png'){
                    imagealphablending($virtual_image, false);
                    imagesavealpha($virtual_image, true);
                }

                // Resize Operation (Resampling for better quality)
                imagecopyresampled($virtual_image, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

                // Step 3: Apply Watermark
                $text_color = imagecolorallocate($virtual_image, 255, 255, 255); // White Text
                $bg_color = imagecolorallocatealpha($virtual_image, 0, 0, 0, 60); // Semi-transparent Black BG
                
                // Draw Watermark Background & Text
                imagefilledrectangle($virtual_image, 10, $new_height-40, 150, $new_height-10, $bg_color);
                imagestring($virtual_image, 4, 20, $new_height-35, "BiSepet User", $text_color);

                // Step 4: Save Optimized Image to Filesystem
                if ($file_ext == 'png') imagepng($virtual_image, $target_file);
                else imagejpeg($virtual_image, $target_file, 80); // Compression Quality: 80%

                // Garbage Collection: Free up memory
                imagedestroy($virtual_image);
                imagedestroy($source);

                $img_db_name = $new_name;
            }
        }

        // Persist Review Data
        $sql_rev = "INSERT INTO reviews (product_id, user_id, user_name, rating, comment, image) 
                    VALUES ($pId, $u_id, '$u_name', $rating, '$comment', " . ($img_db_name ? "'$img_db_name'" : "NULL") . ")";
        
        if ($conn->query($sql_rev)) {
            $msg = "<div class='alert alert-success'>Review submitted successfully!</div>";
        } else {
            $msg = "<div class='alert alert-danger'>Database Error: " . $conn->error . "</div>";
        }
    }
}

// Fetch Reviews for Display
$reviews = $conn->query("SELECT * FROM reviews WHERE product_id = $pId ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $product['name']; ?> - BiSepet</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        .product-img-large { max-height: 500px; object-fit: contain; width: 100%; border-radius: 15px; border: 1px solid #eee; background: white; }
        .review-img { width: 100px; height: 100px; object-fit: cover; border-radius: 8px; cursor: pointer; transition: 0.3s; border: 2px solid #ddd; }
        .review-img:hover { transform: scale(1.1); border-color: #02A676; }
        .star-active { color: #ffc107; }
        .star-inactive { color: #e4e5e9; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container mt-4 mb-5">
    
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-5">
        <div class="row g-0">
            <div class="col-md-5 bg-white p-4 d-flex align-items-center justify-content-center">
                <img src="images/<?php echo $product['productPict']; ?>" class="product-img-large" alt="<?php echo $product['name']; ?>">
            </div>
            <div class="col-md-7">
                <div class="card-body p-5">
                    <h5 class="text-muted text-uppercase small fw-bold mb-2">
                        <?php echo htmlspecialchars($product['category']); ?> &bull; <?php echo htmlspecialchars($product['brand'] ?? 'General'); ?>
                    </h5>
                    <h1 class="fw-bold mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <div class="mb-4">
                        <?php if($discount > 0): ?>
                            <span class="fs-4 text-muted text-decoration-line-through me-2">€<?php echo number_format($price, 2); ?></span>
                            <span class="display-6 fw-bold text-danger">€<?php echo number_format($final_price, 2); ?></span>
                            <span class="badge bg-danger ms-2 align-middle">-%<?php echo $discount; ?> OFF</span>
                        <?php else: ?>
                            <span class="display-6 fw-bold text-primary">€<?php echo number_format($price, 2); ?></span>
                        <?php endif; ?>
                    </div>

                    <p class="lead text-secondary mb-4" style="font-size: 1rem;">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </p>

                    <div class="d-grid gap-2 d-md-flex">
                        <form method="POST" action="cart.php" class="flex-grow-1">
                            <input type="hidden" name="product_id" value="<?php echo $pId; ?>">
                            <button type="submit" name="add_to_cart" class="btn btn-success btn-lg w-100 rounded-pill shadow-sm">
                                <i class="bi bi-cart-plus-fill me-2"></i> <?php echo $text[$lang]['add_to_cart']; ?>
                            </button>
                        </form>
                    </div>
                    
                    <div class="mt-4 p-3 bg-light rounded small text-muted border">
                        <i class="bi bi-shield-check text-success me-1"></i> Original Product &bull; 
                        <i class="bi bi-truck text-success me-1 ms-2"></i> Fast Shipping &bull; 
                        <i class="bi bi-arrow-counterclockwise text-success me-1 ms-2"></i> Easy Return
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <h3 class="fw-bold mb-4">
                <i class="bi bi-star-fill text-warning"></i> <?php echo $text[$lang]['reviews_title']; ?> 
                <span class="text-muted fs-5">(<?php echo $reviews->num_rows; ?>)</span>
            </h3>

            <?php echo $msg; ?>

            <div class="card mb-4 shadow-sm border-0">
                <div class="card-body bg-light rounded-3 p-4">
                    <h5 class="fw-bold mb-3"><?php echo $text[$lang]['write_review']; ?></h5>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label small fw-bold"><?php echo $text[$lang]['rating']; ?></label>
                            <div class="rating-select">
                                <select name="rating" class="form-select w-auto">
                                    <option value="5">⭐⭐⭐⭐⭐ (5/5)</option>
                                    <option value="4">⭐⭐⭐⭐ (4/5)</option>
                                    <option value="3">⭐⭐⭐ (3/5)</option>
                                    <option value="2">⭐⭐ (2/5)</option>
                                    <option value="1">⭐ (1/5)</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold"><?php echo $text[$lang]['comment_label']; ?></label>
                            <textarea name="comment" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold"><?php echo $text[$lang]['photo_label']; ?></label>
                            <input type="file" name="review_img" class="form-control" accept="image/*">
                            <div class="form-text text-muted small">Images will be resized and watermarked automatically.</div>
                        </div>
                        <button type="submit" name="submit_review" class="btn btn-primary px-4 fw-bold">
                            <?php echo $text[$lang]['submit_review']; ?>
                        </button>
                    </form>
                </div>
            </div>

            <?php if ($reviews->num_rows > 0): ?>
                <?php while($rev = $reviews->fetch_assoc()): ?>
                    <div class="card mb-3 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width:40px; height:40px; font-weight:bold;">
                                        <?php echo strtoupper(substr($rev['user_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($rev['user_name']); ?></h6>
                                        <small class="text-muted"><?php echo date("d.m.Y", strtotime($rev['created_at'])); ?></small>
                                    </div>
                                </div>
                                <div class="text-warning">
                                    <?php for($i=0; $i<$rev['rating']; $i++) echo '<i class="bi bi-star-fill"></i>'; ?>
                                    <?php for($i=$rev['rating']; $i<5; $i++) echo '<i class="bi bi-star"></i>'; ?>
                                </div>
                            </div>
                            
                            <p class="mt-2 mb-2 text-dark"><?php echo nl2br(htmlspecialchars($rev['comment'])); ?></p>
                            
                            <?php if(!empty($rev['image'])): ?>
                                <div class="mt-3">
                                    <a href="images/reviews/<?php echo $rev['image']; ?>" target="_blank">
                                        <img src="images/reviews/<?php echo $rev['image']; ?>" class="review-img shadow-sm" alt="User Image">
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert alert-info border-0 shadow-sm">
                    <i class="bi bi-info-circle-fill me-2"></i> <?php echo $text[$lang]['no_reviews']; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>