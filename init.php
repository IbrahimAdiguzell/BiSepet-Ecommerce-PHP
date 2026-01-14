<?php
/**
 * BiSepet E-Commerce Platform
 * Core Initialization File
 * * Bu dosya tüm sayfalarda dahil edilerek; oturum yönetimi, 
 * veritabanı bağlantısı, dil yapılandırması ve global fonksiyonları yükler.
 * * @package BiSepet
 * @version 1.0.0
 */

// Oturum başlatılmamışsa başlat (Session Fixation koruması için check)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Veritabanı bağlantı dosyasını dahil et
// __DIR__ kullanımı dosya yolu çakışmalarını önler
require_once __DIR__ . '/db.php';

/*
|--------------------------------------------------------------------------
| Dil ve Yerelleştirme (Localization - i18n) Yapılandırması
|--------------------------------------------------------------------------
| Kullanıcının dil tercihini GET parametresi veya Session üzerinden yönetir.
| Varsayılan dil: Türkçe (tr)
*/

if (isset($_GET['lang'])) {
    // Güvenlik: Sadece izin verilen dilleri kabul et
    $allowed_langs = ['tr', 'en'];
    if(in_array($_GET['lang'], $allowed_langs)) {
        $_SESSION['lang'] = $_GET['lang'];
    }
}

// Aktif dili belirle
$lang = $_SESSION['lang'] ?? 'tr';

/*
|--------------------------------------------------------------------------
| Dil Sözlüğü (Translation Dictionary)
|--------------------------------------------------------------------------
| UI elementleri için çeviri dizileri.
| İleride veritabanına veya .json dosyalarına taşınabilir.
*/
$text = [
    'tr' => [
        // --- Navigation & Auth ---
        'products' => 'Ürünler', 
        'cart' => 'Sepet', 
        'order' => 'Sipariş', 
        'my_orders' => 'Siparişlerim', 
        'login' => 'Giriş', 
        'register' => 'Kayıt Ol', 
        'logout' => 'Çıkış', 
        'admin_panel' => 'Yönetim Paneli', 
        'welcome_user' => 'Merhaba',
        
        // --- Hero & Services ---
        'hero_1_title' => 'Büyük Teknoloji İndirimi',
        'hero_1_desc' => 'Seçili elektronik ürünlerde %40\'a varan indirimler.',
        'btn_examine' => 'Hemen İncele',
        
        'hero_2_title' => 'Yeni Sezon Modası',
        'hero_2_desc' => 'Tarzını yansıtacak en şık kombinler.',
        'btn_start' => 'Alışverişe Başla',
        
        'hero_3_title' => 'Eviniz İçin Her Şey',
        'hero_3_desc' => 'Mobilyadan dekorasyona evinize renk katacak ürünler.',
        'btn_opportunity' => 'Fırsatları Gör',

        'service_1' => 'Hızlı Kargo', 'service_1_desc' => 'Aynı gün kargolama',
        'service_2' => 'Güvenli Ödeme', 'service_2_desc' => '256-bit SSL koruması',
        'service_3' => 'Kolay İade', 'service_3_desc' => '14 gün içinde koşulsuz iade',
        'service_4' => '7/24 Destek', 'service_4_desc' => 'Her zaman yanınızdayız',

        // --- Categories & Deals ---
        'pop_cats' => 'Popüler Kategoriler',
        'cat_elec' => 'ELEKTRONİK',
        'cat_fash' => 'MODA & GİYİM',
        'cat_sport' => 'SPOR & OUTDOOR',

        'deal_title' => '🎉 Günün Fırsatı!',
        'deal_desc' => 'Elektronik ürünlerde sepette ekstra %10 İndirim.',
        'deal_note' => 'Sınırlı süre için geçerlidir.',
        'deal_btn' => 'Fırsatı Yakala',
        
        // --- Profile & Settings ---
        'profile_title' => 'Profil Ayarları',
        'personal_info' => 'Kişisel Bilgiler',
        'contact_info' => 'İletişim Bilgileri',
        'city_label' => 'Şehir',
        'phone_label' => 'Telefon',
        'address_label' => 'Adres',
        'save_changes' => 'Kaydet',
        'update_success' => 'Profil güncellendi.',

        // --- Reviews ---
        'reviews_title' => 'Ürün Değerlendirmeleri',
        'write_review' => 'Yorum Yap',
        'rating' => 'Puanınız',
        'comment_label' => 'Yorumunuz',
        'photo_label' => 'Fotoğraf (Opsiyonel)',
        'submit_review' => 'Gönder',
        'no_reviews' => 'Henüz yorum yok.',

        // --- Forms & General ---
        'email_label' => 'E-Posta',
        'pass_label' => 'Şifre',
        'name_label' => 'Ad Soyad',
        'forgot_pass' => 'Şifremi Unuttum',
        'login_btn' => 'Giriş Yap',
        'register_btn' => 'Kayıt Ol',
        'no_account' => 'Hesabınız yok mu?',
        'have_account' => 'Zaten üye misiniz?',
        'create_account' => 'Hesap Oluştur',
        'reset_title' => 'Şifre Sıfırlama',
        'reset_desc' => 'E-posta adresinizi girin.',
        'send_link' => 'Gönder',
        'back_login' => 'Giriş Ekranı',
        'be_seller' => 'Mağaza Aç (Satıcı Başvurusu)',
        'shop_name' => 'Mağaza Adı',
        
        'gender' => 'Cinsiyet',
        'male' => 'Erkek',
        'female' => 'Kadın',
        'unisex' => 'Belirtmek İstemiyorum',

        'search_placeholder' => 'Ürün, kategori veya marka ara...',
        'search_btn' => 'Ara',

        'add_to_cart' => 'Sepete Ekle', 'detail' => 'İncele', 'buy_now' => 'Hemen Al',
        'update' => 'Güncelle', 'delete' => 'Sil', 'checkout' => 'Ödeme Yap',
        'back' => 'Geri', 'view_all' => 'Tümünü Gör',
        
        'welcome' => 'Hoş Geldiniz',
        'slogan' => 'Keşfetmeye Başla',
        'start_shopping' => 'Alışveriş',
        'featured' => 'Öne Çıkanlar',
        'no_product' => 'Ürün bulunamadı.',
        'empty_cart' => 'Sepetiniz boş.',
        'cart_title' => 'Alışveriş Sepeti',
        'order_success' => 'Sipariş Alındı',
        'payment_title' => 'Ödeme Bilgileri',
        
        'image' => 'Görsel', 'p_name' => 'Ürün', 'price' => 'Fiyat', 
        'quantity' => 'Adet', 'total' => 'Toplam', 'action' => 'İşlem',
        
        'about' => 'Hakkımızda', 
        'about_text' => 'En iyi ürünler, en uygun fiyatlarla.',
        'contact' => 'İletişim', 
        'follow' => 'Bizi Takip Edin', 
        'rights' => 'Tüm Hakları Saklıdır.',
        'links_title' => 'Hızlı Erişim',
        'home' => 'Anasayfa',
        'write_us' => 'Bize Ulaşın',
        'write_desc' => 'Görüş ve önerilerinizi bekliyoruz.',
        'name_ph' => 'Adınız',
        'email_ph' => 'E-posta',
        'msg_ph' => 'Mesajınız...',
        'send_btn' => 'Gönder'
    ],
    'en' => [
        // --- Navigation & Auth ---
        'products' => 'Products', 'cart' => 'Cart', 'order' => 'Order', 
        'my_orders' => 'My Orders', 'login' => 'Login', 'register' => 'Register', 
        'logout' => 'Logout', 'admin_panel' => 'Admin Panel', 'welcome_user' => 'Hello',
        
        // --- Hero & Services ---
        'hero_1_title' => 'Big Tech Sale',
        'hero_1_desc' => 'Up to 40% off on selected electronics.',
        'btn_examine' => 'Shop Now',
        
        'hero_2_title' => 'New Season Fashion',
        'hero_2_desc' => 'Stylish combinations for you.',
        'btn_start' => 'Explore',
        
        'hero_3_title' => 'Home & Living',
        'hero_3_desc' => 'Furniture and decoration essentials.',
        'btn_opportunity' => 'View Deals',

        'service_1' => 'Fast Shipping', 'service_1_desc' => 'Same day dispatch',
        'service_2' => 'Secure Payment', 'service_2_desc' => '256-bit SSL secured',
        'service_3' => 'Easy Return', 'service_3_desc' => '14-day return policy',
        'service_4' => '24/7 Support', 'service_4_desc' => 'Always here for you',

        'pop_cats' => 'Trending Categories',
        'cat_elec' => 'ELECTRONICS',
        'cat_fash' => 'FASHION',
        'cat_sport' => 'SPORTS',

        'deal_title' => '🎉 Daily Deal!',
        'deal_desc' => 'Extra 10% off on electronics.',
        'deal_note' => 'Limited time offer.',
        'deal_btn' => 'Grab Deal',
        
        // --- Profile & Settings ---
        'profile_title' => 'Account Settings',
        'personal_info' => 'Personal Info',
        'contact_info' => 'Contact Info',
        'city_label' => 'City',
        'phone_label' => 'Phone',
        'address_label' => 'Address',
        'save_changes' => 'Save Changes',
        'update_success' => 'Updated successfully.',

        // --- Reviews ---
        'reviews_title' => 'Customer Reviews',
        'write_review' => 'Write a Review',
        'rating' => 'Rating',
        'comment_label' => 'Comment',
        'photo_label' => 'Photo (Optional)',
        'submit_review' => 'Submit',
        'no_reviews' => 'No reviews yet.',

        // --- Forms & General ---
        'email_label' => 'Email',
        'pass_label' => 'Password',
        'name_label' => 'Full Name',
        'forgot_pass' => 'Forgot Password?',
        'login_btn' => 'Login',
        'register_btn' => 'Sign Up',
        'no_account' => 'No account?',
        'have_account' => 'Already a member?',
        'create_account' => 'Create Account',
        'reset_title' => 'Reset Password',
        'reset_desc' => 'Enter email to reset.',
        'send_link' => 'Send Link',
        'back_login' => 'Back to Login',
        'be_seller' => 'Become a Seller',
        'shop_name' => 'Shop Name',
        
        'gender' => 'Gender',
        'male' => 'Male',
        'female' => 'Female',
        'unisex' => 'N/A',

        'search_placeholder' => 'Search products...',
        'search_btn' => 'Search',

        'add_to_cart' => 'Add to Cart', 'detail' => 'Details', 'buy_now' => 'Buy Now',
        'update' => 'Update', 'delete' => 'Delete', 'checkout' => 'Checkout',
        'back' => 'Back', 'view_all' => 'View All',
        
        'welcome' => 'Welcome',
        'slogan' => 'Discover thousands of products.',
        'start_shopping' => 'Shop Now',
        'featured' => 'Featured',
        'no_product' => 'No products found.',
        'empty_cart' => 'Cart is empty.',
        'cart_title' => 'Shopping Cart',
        'order_success' => 'Order Received!',
        'payment_title' => 'Payment Details',
        
        'image' => 'Image', 'p_name' => 'Product', 'price' => 'Price', 
        'quantity' => 'Qty', 'total' => 'Total', 'action' => 'Action',
        
        'about' => 'About Us', 
        'about_text' => 'Best products, best prices.',
        'contact' => 'Contact', 
        'follow' => 'Follow Us', 
        'rights' => 'All Rights Reserved.',
        'links_title' => 'Quick Links',
        'home' => 'Home',
        'write_us' => 'Feedback',
        'write_desc' => 'We\'d love to hear from you.',
        'name_ph' => 'Name',
        'email_ph' => 'Email',
        'msg_ph' => 'Message...',
        'send_btn' => 'Send'
    ]
];

/**
 * TCMB (Türkiye Cumhuriyet Merkez Bankası) API üzerinden anlık döviz kurlarını çeker.
 * * @return string Döviz kurlarını içeren formatlı HTML string veya boş string.
 */
function getDovizKuru() {
    // Hata bastırma operatörü (@) geçici API sorunlarında sayfa akışını bozmamak için kullanıldı.
    $url = "https://www.tcmb.gov.tr/kurlar/today.xml";
    $xml = @simplexml_load_file($url);
    
    if ($xml) {
        $usd = $xml->Currency[0]->BanknoteSelling;
        $eur = $xml->Currency[3]->BanknoteSelling;
        return "💰 USD: $usd TL | 💶 EUR: $eur TL";
    }
    return "";
}

// --- Feedback Handler ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_feedback'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $message = $conn->real_escape_string($_POST['message']);

    $sql_msg = "INSERT INTO messages (name, email, message) VALUES ('$name', '$email', '$message')";
    
    if ($conn->query($sql_msg) === TRUE) {
        echo "<script>alert('Teşekkürler, mesajınız iletildi.'); window.location.href='index.php';</script>";
    } else {
        // Loglama yapılabilir
        echo "<script>alert('Bir hata oluştu.');</script>";
    }
}
?>