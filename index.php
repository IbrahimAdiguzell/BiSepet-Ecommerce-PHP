<?php
/**
 * Public Landing Page
 * * The main entry point of the application.
 * Aggregates various UI components: Hero slider, value propositions, 
 * category navigation, and the AI recommendation module.
 * * @package BiSepet
 * @subpackage Public
 */

require_once 'init.php'; 

/*
|--------------------------------------------------------------------------
| View Logic Configuration
|--------------------------------------------------------------------------
| Setup role-based flags for conditional UI rendering.
*/
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == true;
$isSeller = isset($_SESSION['role']) && $_SESSION['role'] == 'seller';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BiSepet - <?php echo isset($text[$lang]['slogan']) ? $text[$lang]['slogan'] : 'E-Commerce Platform'; ?></title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

  <style>
    /* Global Typography */
    body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; }
    .navbar-brand { font-weight: 700; font-size: 1.5rem; }
    
    /* Hero Carousel Components */
    .carousel-item { height: 400px; }
    .carousel-item img { object-fit: cover; height: 100%; filter: brightness(0.7); }
    .carousel-caption { bottom: 20%; }
    
    /* Value Proposition Boxes */
    .service-box { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: 0.3s; text-align: center; height: 100%; }
    .service-box:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    .service-icon { font-size: 2.5rem; color: #02A676; margin-bottom: 15px; }

    /* Category Navigation Cards */
    .cat-card { position: relative; overflow: hidden; border-radius: 15px; cursor: pointer; height: 200px; }
    .cat-card img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
    .cat-card:hover img { transform: scale(1.1); filter: brightness(0.6); }
    .cat-text { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-weight: bold; font-size: 1.5rem; text-shadow: 0 2px 5px rgba(0,0,0,0.7); pointer-events: none; text-align: center; width: 100%; }

    /* Promotional Banner */
    .deal-section { background: linear-gradient(45deg, #02A676, #00cdac); color: white; border-radius: 15px; padding: 40px; margin: 50px 0; }
    
    /* --- AI Module Styling --- */
    .ai-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: 0 20px 50px rgba(118, 75, 162, 0.3);
        color: white;
    }
    .ai-bg-glow {
        position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(0,0,0,0) 70%);
        animation: rotate 20s linear infinite;
        pointer-events: none;
    }
    @keyframes rotate { 100% { transform: rotate(360deg); } }
    .animate-pulse { animation: pulse 1.5s infinite; }
    @keyframes pulse { 0% { opacity: 0.5; } 50% { opacity: 1; } 100% { opacity: 0.5; } }
    .transition-btn { transition: 0.3s; }
    .transition-btn:hover { transform: scale(1.05); }
    
    .product-card img { height: 220px; object-fit: cover; }
    .api-banner { background-color: #e9ecef; font-size: 0.85rem; font-weight: bold; color: #333; }
  </style>
</head>

<body>
  
  <?php include 'navbar.php'; ?>

  <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-indicators">
      <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
      <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
      <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
    </div>
    <div class="carousel-inner">
      <div class="carousel-item active">
        <img src="https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?q=80&w=2070&auto=format&fit=crop" class="d-block w-100" alt="Tech Sale">
        <div class="carousel-caption d-none d-md-block text-start">
          <h1 class="display-3 fw-bold"><?php echo isset($text[$lang]['hero_1_title']) ? $text[$lang]['hero_1_title'] : 'Big Sale'; ?></h1>
          <p class="fs-4"><?php echo isset($text[$lang]['hero_1_desc']) ? $text[$lang]['hero_1_desc'] : ''; ?></p>
          <a href="products.php?category=elektronik" class="btn btn-primary btn-lg px-5 rounded-pill shadow"><?php echo isset($text[$lang]['btn_examine']) ? $text[$lang]['btn_examine'] : 'View'; ?></a>
        </div>
      </div>
      <div class="carousel-item">
        <img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=2070&auto=format&fit=crop" class="d-block w-100" alt="Fashion">
        <div class="carousel-caption d-none d-md-block">
          <h1 class="display-3 fw-bold"><?php echo isset($text[$lang]['hero_2_title']) ? $text[$lang]['hero_2_title'] : ''; ?></h1>
          <p class="fs-4"><?php echo isset($text[$lang]['hero_2_desc']) ? $text[$lang]['hero_2_desc'] : ''; ?></p>
          <a href="products.php?category=giyim" class="btn btn-light btn-lg px-5 rounded-pill text-dark fw-bold shadow"><?php echo isset($text[$lang]['btn_start']) ? $text[$lang]['btn_start'] : 'Start'; ?></a>
        </div>
      </div>
      <div class="carousel-item">
        <img src="https://images.unsplash.com/photo-1556228453-efd6c1ff04f6?q=80&w=2070&auto=format&fit=crop" class="d-block w-100" alt="Home Decor">
        <div class="carousel-caption d-none d-md-block text-end">
          <h1 class="display-3 fw-bold"><?php echo isset($text[$lang]['hero_3_title']) ? $text[$lang]['hero_3_title'] : ''; ?></h1>
          <p class="fs-4"><?php echo isset($text[$lang]['hero_3_desc']) ? $text[$lang]['hero_3_desc'] : ''; ?></p>
          <a href="products.php?category=ev" class="btn btn-warning btn-lg px-5 rounded-pill shadow text-dark fw-bold"><?php echo isset($text[$lang]['btn_opportunity']) ? $text[$lang]['btn_opportunity'] : 'See Deals'; ?></a>
        </div>
      </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon"></span>
    </button>
  </div>

  <div class="container mt-5">
    
    <div class="row g-4 mb-5">
        <div class="col-md-3 col-6">
            <div class="service-box">
                <i class="bi bi-truck service-icon"></i>
                <h6 class="fw-bold"><?php echo isset($text[$lang]['service_1']) ? $text[$lang]['service_1'] : 'Shipping'; ?></h6>
                <p class="small text-muted mb-0"><?php echo isset($text[$lang]['service_1_desc']) ? $text[$lang]['service_1_desc'] : ''; ?></p>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="service-box">
                <i class="bi bi-shield-check service-icon"></i>
                <h6 class="fw-bold"><?php echo isset($text[$lang]['service_2']) ? $text[$lang]['service_2'] : 'Secure'; ?></h6>
                <p class="small text-muted mb-0"><?php echo isset($text[$lang]['service_2_desc']) ? $text[$lang]['service_2_desc'] : ''; ?></p>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="service-box">
                <i class="bi bi-arrow-counterclockwise service-icon"></i>
                <h6 class="fw-bold"><?php echo isset($text[$lang]['service_3']) ? $text[$lang]['service_3'] : 'Returns'; ?></h6>
                <p class="small text-muted mb-0"><?php echo isset($text[$lang]['service_3_desc']) ? $text[$lang]['service_3_desc'] : ''; ?></p>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="service-box">
                <i class="bi bi-headset service-icon"></i>
                <h6 class="fw-bold"><?php echo isset($text[$lang]['service_4']) ? $text[$lang]['service_4'] : 'Support'; ?></h6>
                <p class="small text-muted mb-0"><?php echo isset($text[$lang]['service_4_desc']) ? $text[$lang]['service_4_desc'] : ''; ?></p>
            </div>
        </div>
    </div>

    <h3 class="mb-4 fw-bold text-center"><?php echo isset($text[$lang]['pop_cats']) ? $text[$lang]['pop_cats'] : 'Categories'; ?></h3>
    <div class="row g-3 mb-5">
        <div class="col-md-4">
            <div class="cat-card" onclick="window.location='products.php?category=elektronik'">
                <img src="https://images.unsplash.com/photo-1519389950473-47ba0277781c?q=80&w=600&auto=format&fit=crop" alt="Electronics">
                <div class="cat-text"><?php echo isset($text[$lang]['cat_elec']) ? $text[$lang]['cat_elec'] : 'ELECTRONICS'; ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="cat-card" onclick="window.location='products.php?category=giyim'">
                <img src="https://images.unsplash.com/photo-1445205170230-053b83016050?q=80&w=600" alt="Fashion">
                <div class="cat-text"><?php echo isset($text[$lang]['cat_fash']) ? $text[$lang]['cat_fash'] : 'FASHION'; ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="cat-card" onclick="window.location='products.php?category=spor'">
                <img src="https://images.unsplash.com/photo-1517649763962-0c623066013b?q=80&w=600" alt="Sports">
                <div class="cat-text"><?php echo isset($text[$lang]['cat_sport']) ? $text[$lang]['cat_sport'] : 'SPORTS'; ?></div>
            </div>
        </div>
    </div>

    <div class="ai-section mb-5 p-5 rounded-4 position-relative overflow-hidden">
        <div class="ai-bg-glow"></div>
        
        <div class="row align-items-center position-relative" style="z-index: 2;">
            <div class="col-md-4 text-center text-md-start">
                <div class="badge bg-warning text-dark mb-2 px-3 py-2 rounded-pill shadow-sm">
                    <i class="bi bi-stars"></i> Beta v1.0
                </div>
                <h2 class="fw-bold text-white mb-3">
                    <i class="bi bi-robot fs-1 me-2"></i> BiSepet AI
                </h2>
                <p class="text-white-50 fs-5 mb-4">
                    <?php echo $lang == 'en' ? 'Undecided? Let our AI algorithm analyze the best products for you.' : 'Karasız mı kaldınız? Yapay zeka algoritmamız sizin için en uygun ürünleri analiz etsin.'; ?>
                </p>
                <button id="startAiBtn" class="btn btn-light btn-lg rounded-pill fw-bold px-5 shadow transition-btn" onclick="fetchAiSuggestions()">
                    <i class="bi bi-magic me-2"></i> <?php echo $lang == 'en' ? 'Suggest for Me' : 'Bana Öneri Yap'; ?>
                </button>
            </div>

            <div class="col-md-8">
                <div id="aiLoading" class="text-center text-white py-5" style="display: none;">
                    <div class="spinner-border text-warning mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                    <h5 class="fw-bold animate-pulse"><?php echo $lang == 'en' ? 'Analyzing user data...' : 'Kullanıcı verileri analiz ediliyor...'; ?></h5>
                    <small class="text-white-50" id="aiStatusText">...</small>
                </div>

                <div id="aiResults" class="row g-3" style="display: none;"></div>
                
                <div id="aiPlaceholder" class="text-center text-white-50 py-5 border border-dashed rounded-4 border-light opacity-25">
                    <i class="bi bi-cpu fs-1"></i>
                    <p class="mt-2"><?php echo $lang == 'en' ? 'Results will appear here' : 'Sonuçlar burada görünecek'; ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0"><?php echo $text[$lang]['featured']; ?></h3>
        <a href="products.php" class="btn btn-outline-dark btn-sm rounded-pill px-3"><?php echo $text[$lang]['view_all']; ?> <i class="bi bi-arrow-right"></i></a>
    </div>
    
    <div class="row g-4">
      <?php
      if (isset($conn)) {
          // Fetch random products for "Discovery" UX
          $sql = "SELECT * FROM products ORDER BY RAND() LIMIT 4";
          $result = $conn->query($sql);

          if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
              // Data Mapping
              $pId = (int)$row['id'];
              $pName = htmlspecialchars($row['name']);
              $price = (float)$row['price'];
              $discount = isset($row['discount_rate']) ? (int)$row['discount_rate'] : 0;
              $final_price = $price - ($price * $discount / 100);
              $imgSrc = !empty($row['productPict']) ? "images/".$row['productPict'] : "https://via.placeholder.com/300x200?text=No+Img";

              echo "
              <div class='col-md-3 col-sm-6'>
                <div class='card product-card shadow-sm h-100 border-0'>
                  <a href='product_detail.php?id={$pId}' class='position-relative'>
                    <img src='{$imgSrc}' class='card-img-top rounded-top' alt='{$pName}' onerror=\"this.src='https://via.placeholder.com/300?text=No+Image'\">
                    " . ($discount > 0 ? "<span class='position-absolute top-0 start-0 badge bg-danger m-2'>-$discount%</span>" : "") . "
                  </a>
                  <div class='card-body text-center'>
                    <h6 class='card-title text-truncate'>
                        <a href='product_detail.php?id={$pId}' class='text-decoration-none text-dark fw-bold'>{$pName}</a>
                    </h6>
                    <div class='mb-3'>";
                    if($discount > 0){
                        echo "<span class='text-muted text-decoration-line-through me-2 small'>€$price</span>";
                        echo "<span class='fw-bold text-danger'>€".number_format($final_price, 2)."</span>";
                    } else {
                        echo "<span class='fw-bold text-dark'>€$price</span>";
                    }
              echo "</div>
                    <form method='POST' action='cart.php'>
                        <input type='hidden' name='product_id' value='{$pId}'>
                        <button type='submit' name='add_to_cart' class='btn btn-outline-success w-100 rounded-pill btn-sm'>
                            {$text[$lang]['add_to_cart']}
                        </button>
                    </form>
                  </div>
                </div>
              </div>";
            }
          } else {
            echo "<p class='text-center text-muted col-12'>{$text[$lang]['no_product']}</p>";
          }
      }
      ?>
    </div> 

    <div class="deal-section d-flex flex-column flex-md-row align-items-center justify-content-between">
        <div>
            <h2 class="fw-bold mb-2"><?php echo isset($text[$lang]['deal_title']) ? $text[$lang]['deal_title'] : 'Special Deal'; ?></h2>
            <p class="fs-5 mb-0"><?php echo isset($text[$lang]['deal_desc']) ? $text[$lang]['deal_desc'] : ''; ?></p>
            <p class="small opacity-75"><?php echo isset($text[$lang]['deal_note']) ? $text[$lang]['deal_note'] : ''; ?></p>
        </div>
        <a href="products.php" class="btn btn-light btn-lg fw-bold px-5 rounded-pill shadow mt-3 mt-md-0"><?php echo isset($text[$lang]['deal_btn']) ? $text[$lang]['deal_btn'] : 'View'; ?></a>
    </div>

  </div>

  <?php include 'footer.php'; ?>

  <script>
        /**
         * Triggers the AI recommendation engine.
         * Simulates a complex analysis process for better UX perception
         * before fetching result data via AJAX.
         */
        function fetchAiSuggestions() {
            const btn = document.getElementById('startAiBtn');
            const loading = document.getElementById('aiLoading');
            const results = document.getElementById('aiResults');
            const placeholder = document.getElementById('aiPlaceholder');
            const statusText = document.getElementById('aiStatusText');

            // UI Reset & Loading State
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> <?php echo $lang == 'en' ? 'Processing...' : 'İşleniyor...'; ?>';
            placeholder.style.display = 'none';
            results.style.display = 'none';
            loading.style.display = 'block';

            // Simulated Analysis Steps (UX Enhancement)
            const steps = <?php echo $lang == 'en' ? 
                '["Scanning category preferences...", "Checking price history...", "Matching stock status...", "Selecting optimal products..."]' : 
                '["Kategori tercihleri taranıyor...", "Fiyat geçmişi kontrol ediliyor...", "Stok durumu eşleştiriliyor...", "En uygun ürünler seçiliyor..."]'; 
            ?>;
            
            let step = 0;
            const statusInterval = setInterval(() => {
                if(step < steps.length) {
                    statusText.innerText = steps[step];
                    step++;
                }
            }, 2000); 

            // Asynchronous Data Fetch
            fetch('get_ai_products.php')
                .then(response => response.json())
                .then(data => {
                    clearInterval(statusInterval);
                    
                    // Render Results
                    let html = '';
                    data.forEach(product => {
                        let priceHtml = '';
                        if(product.discount > 0) {
                            priceHtml = `<small class="text-decoration-line-through text-white-50">€${product.price}</small> <span class="fw-bold text-warning">€${product.final_price}</span>`;
                        } else {
                            priceHtml = `<span class="fw-bold text-white">€${product.price}</span>`;
                        }

                        html += `
                        <div class="col-md-4 fade-in">
                            <div class="card h-100 border-0 shadow-sm" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                                <img src="${product.image}" class="card-img-top" style="height: 150px; object-fit: cover; opacity: 0.9;">
                                <div class="card-body text-white p-2 text-center">
                                    <h6 class="card-title text-truncate small mb-1">${product.name}</h6>
                                    <div class="mb-2">${priceHtml}</div>
                                    <form method="POST" action="cart.php">
                                        <input type="hidden" name="product_id" value="${product.id}">
                                        <button type="submit" name="add_to_cart" class="btn btn-sm btn-light w-100 fw-bold rounded-pill" style="font-size: 0.8rem;">
                                            <?php echo $text[$lang]['add_to_cart']; ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>`;
                    });

                    // Reveal Content with Delay
                    setTimeout(() => { 
                        loading.style.display = 'none';
                        results.innerHTML = html;
                        results.style.display = 'flex';
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> <?php echo $lang == 'en' ? 'Try Again' : 'Tekrar Dene'; ?>';
                    }, 500);
                })
                .catch(err => {
                    console.error("AI Fetch Error:", err);
                    loading.style.display = 'none';
                    btn.disabled = false;
                    btn.innerHTML = 'Error / Hata';
                });
        }
    </script>

</body>
</html>