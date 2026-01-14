-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 14 Oca 2026, 01:16:21
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `e-commercedb`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin', '12345');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `parent_id` int(11) DEFAULT 0,
  `icon` varchar(50) DEFAULT NULL,
  `name_en` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `parent_id`, `icon`, `name_en`) VALUES
(1, 'Elektronik', 'elektronik', 0, 'bi-laptop', 'Electronics'),
(2, 'Moda & Giyim', 'giyim', 0, 'bi-tshirt', 'Fashion & Clothing'),
(3, 'Ev & Yaşam', 'ev', 0, 'bi-house-door', 'Home & Living'),
(4, 'Spor & Outdoor', 'spor', 0, 'bi-bicycle', 'Sports & Outdoor'),
(5, 'Kozmetik', 'kozmetik', 0, 'bi-heart', 'Cosmetics'),
(6, 'Kitap & Hobi', 'kitap', 0, 'bi-book', 'Books & Hobby'),
(7, 'Aksesuar', 'aksesuar', 0, 'bi-watch', 'Accessories'),
(8, 'Otomotiv', 'otomotiv', 0, 'bi-car-front', 'Automotive'),
(9, 'Anne & Bebek', 'bebek', 0, 'bi-emoji-smile', 'Mom & Baby'),
(10, 'Yapı Market', 'yapi-market', 0, 'bi-tools', 'Hardware Store'),
(11, 'Süpermarket', 'supermarket', 0, 'bi-basket', 'Supermarket'),
(12, 'Ofis', 'ofis', 0, 'bi-printer', 'Office'),
(13, 'Pet Shop', 'petshop', 0, 'bi-github', 'Pet Shop'),
(14, 'Mücevher', 'mucevher', 0, 'bi-gem', 'Jewelry'),
(15, 'Bahçe', 'bahce', 0, 'bi-tree', 'Garden'),
(16, 'Telefon', 'telefon', 1, NULL, 'Phone'),
(17, 'Bilgisayar', 'bilgisayar', 1, NULL, 'Computer'),
(18, 'TV & Ses', 'tv', 1, NULL, NULL),
(19, 'Kamera', 'kamera', 1, NULL, NULL),
(20, 'Kadın', 'kadin', 2, NULL, 'Women'),
(21, 'Erkek', 'erkek', 2, NULL, 'Men'),
(22, 'Çocuk', 'cocuk', 2, NULL, NULL),
(23, 'Ayakkabı', 'ayakkabi', 2, NULL, NULL),
(24, 'Mobilya', 'mobilya', 3, NULL, 'Furniture'),
(25, 'Dekorasyon', 'dekorasyon', 3, NULL, NULL),
(26, 'Mutfak', 'mutfak', 3, NULL, NULL),
(27, 'Ev Tekstili', 'tekstil', 3, NULL, NULL),
(28, 'Fitness', 'fitness', 4, NULL, NULL),
(29, 'Kamp', 'kamp', 4, NULL, NULL),
(30, 'Bisiklet', 'bisiklet', 4, NULL, NULL),
(31, 'Parfüm', 'parfum', 5, NULL, NULL),
(32, 'Makyaj', 'makyaj', 5, NULL, NULL),
(33, 'Cilt Bakımı', 'cilt', 5, NULL, NULL),
(34, 'Edebiyat', 'edebiyat', 6, NULL, NULL),
(35, 'Sınav', 'sinav', 6, NULL, NULL),
(36, 'Kırtasiye', 'kirtasiye', 6, NULL, NULL),
(37, 'Saat', 'saat', 7, NULL, NULL),
(38, 'Çanta', 'canta', 7, NULL, NULL),
(39, 'Gözlük', 'gozluk', 7, NULL, NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `rating` int(11) DEFAULT 5,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `comments`
--

INSERT INTO `comments` (`id`, `product_id`, `user_id`, `user_name`, `comment`, `rating`, `created_at`) VALUES
(1, 1, 13, 'Ahmet Tan', 'Baya hos bir telefon,ve satici cok ilgiliydi.', 5, '2026-01-02 18:47:33'),
(2, 6, 15, 'admin', 'Urun kullanisli ve 2 yil garanti sunmasi cabasi.', 5, '2026-01-02 19:53:20');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `messages`
--

INSERT INTO `messages` (`id`, `name`, `email`, `message`, `created_at`) VALUES
(1, 'ibrahim', 'ibrahimadiguzel17@gmail.com', 'merhaba', '2025-12-31 13:48:11'),
(2, 'Hamdi Tanpinar', 'hamdi123@gmail.com', 'Bence sitenin bir kac eksigi var;birincisi daha fazla urun ve satici icin pr calismasi sart,ikincisi ise profil kismi duzeltilmeli.\r\n', '2026-01-02 21:01:24');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `orders`
--

INSERT INTO `orders` (`id`, `user_name`, `total_price`, `address`, `created_at`, `status`) VALUES
(1, 'demo10', 299.90, 'Teslim Alan: demo\nTel: 05667375611\nAdres: karsiyaka falan filan iste adres girdim varsayalim \nŞehir/İlçe: Balikesir / Gonen\nÖdeme Yöntemi: Kapıda Ödeme', '2025-12-25 10:48:32', 0),
(2, 'berkay', 4000.00, 'Teslim Alan: Berkay Sahin\nTel: 05684921010\nAdres: mahalle...\nŞehir/İlçe: Istanbul / Kartal\nÖdeme Yöntemi: Kredi Kartı', '2026-01-02 23:35:52', 0);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL COMMENT 'Owned to any product',
  `product_id` int(11) NOT NULL COMMENT 'Product ID’s',
  `product_name` varchar(100) NOT NULL COMMENT 'Product name',
  `price` float NOT NULL COMMENT 'Product price'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`) VALUES
(1, 5, 1, 'Dark Red Shirt', 199.99);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `productPict` varchar(255) DEFAULT NULL,
  `category` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `in_slider` tinyint(1) DEFAULT 0,
  `stock` int(11) DEFAULT 100,
  `discount_rate` int(11) DEFAULT 0,
  `seller_id` int(11) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `productPict`, `category`, `created_at`, `in_slider`, `stock`, `discount_rate`, `seller_id`, `brand`) VALUES
(1, 'Akıllı Telefon', 'Yeni nesil yüksek performanslı akıllı telefon', 12500.00, 'phone.jpg', 1, '2025-11-05 22:39:50', 0, 100, 0, NULL, NULL),
(2, 'Tişört', 'Pamuklu erkek tişört - beyaz', 299.90, 'tshirt.jpg', 2, '2025-11-05 22:39:50', 0, 100, 0, NULL, NULL),
(3, 'Kadın Kol Saati', 'Altın renkli zarif tasarım', 749.50, 'watch.jpg', 3, '2025-11-05 22:39:50', 0, 100, 0, NULL, NULL),
(4, 'Terlik', 'Hos bir terlik...', 25.00, '1767146283_61SxW2eMpeL.jpg', 0, '2025-12-31 01:58:03', 0, 10, 0, NULL, NULL),
(5, 'Canta', 'Canta', 200.00, 'ff0a8e0c754d97569751a9e9efcdb875.jpg', 0, '2025-12-31 06:58:13', 0, 10, 10, NULL, NULL),
(6, 'Macbook Air ', '', 2000.00, '74e758ff7dbe23c394cd4646fcd9d11a.jpg', 0, '2026-01-02 13:20:18', 0, 10, 0, 13, NULL),
(8, 'Iphone 17', 'Apple\'in yeni nesil telefonu.', 1990.00, 'f72214c5280b86bfff97a13dece66dfa.jpg', 0, '2026-01-02 20:48:05', 0, 100, 5, 13, 'Apple');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `rating` int(11) DEFAULT 5,
  `comment` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `user_name`, `rating`, `comment`, `image`, `created_at`) VALUES
(1, 8, 15, 'admin', 5, 'Urun elime ulasti,gercekten cok memnun kaldim. Tesekkurler TeknoStore <3', 'review_1767389380_900.jpg', '2026-01-02 21:29:41'),
(2, 8, 16, 'berkay', 5, 'iphone telefon', 'review_1767448402_159.jpg', '2026-01-03 13:53:22');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_code` varchar(6) DEFAULT NULL,
  `shop_name` varchar(100) DEFAULT NULL,
  `is_approved` tinyint(4) DEFAULT 1,
  `gender` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `is_verified`, `verification_code`, `shop_name`, `is_approved`, `gender`, `phone`, `city`, `address`) VALUES
(2, 'ahmet1324', 'ahmetkalkan13@gmail.com', '$2y$10$FNfc07jDC1fJ9teLYyCnd.JlrRRLulutOUlzFd3THvA61pn1mhVOS', 'user', '2025-11-06 11:02:23', 0, NULL, NULL, 1, NULL, NULL, NULL, NULL),
(5, 'ahmetkalkan', 'ahmetkalkan18@gmail.com', '$2y$10$4.aD7lkRMNYTcT80UBSqyeC/8sUlnnYQDn1Z9CrpwEG8vVlFwA29q', 'user', '2025-11-06 13:58:49', 0, NULL, NULL, 1, NULL, NULL, NULL, NULL),
(6, 'hamza34', 'hamzasarikayaa@gmail.com', '$2y$10$2Fsn8.j6K1XbvwVT/cbtheNtlueArKemZMAcFOiHI4chMuK9JXS4i', 'user', '2025-12-25 07:40:57', 0, '695267', NULL, 1, NULL, NULL, NULL, NULL),
(7, 'demo10', 'demomail@gmail.com', '$2y$10$FnEj9Prg1rV7s/LMb0s3ueL7kVvX3LVW7w3V24sjHRDtoydZQ.Rdi', 'user', '2025-12-25 07:42:45', 1, '182087', NULL, 1, NULL, NULL, NULL, NULL),
(11, 'ahmet', 'ahmetakkaya@gmail.com', '$2y$10$ZYePcdB.hs0CxdWRjXl2neo4evq4IkjPXjgdNKRwBYyHWT.oEDrlC', 'user', '2025-12-31 13:45:41', 0, NULL, NULL, 1, NULL, NULL, NULL, NULL),
(13, 'Ahmet Tan', 'ahmettan10@gmail.com', '$2y$10$DX2QTOUpNUgEmHKauKHSNeoB3ir/iYBmUqM8kAGBmc3UHE5uSJkCe', 'seller', '2026-01-02 13:12:47', 0, NULL, 'TeknoStore', 1, NULL, NULL, NULL, NULL),
(14, 'ufuk', 'ufukgorkem0@gmail.com', '$2y$10$qejazsBAlyq5cPKEthI8xeqtm9ao8NJU60NYLYCMw0x.DN9j3TfkG', 'seller', '2026-01-02 19:00:47', 0, NULL, 'FormaDunyam', 1, NULL, NULL, NULL, NULL),
(15, 'admin', 'admingercek@gmail.com', '$2y$10$U4fItYq4yALadGGNIF64CunePu2zpqT9RtyzbxFwrkciGQkEMB5gW', 'admin', '2026-01-02 19:01:09', 0, NULL, NULL, 1, NULL, NULL, NULL, NULL),
(16, 'berkay', 'berkaysahin@gmail.com', '$2y$10$uL1poufuZZ0rOPPRPoyMluRJEavvNoMpRBR.JTd0/uLtgHEE38qeW', 'user', '2026-01-02 20:33:09', 0, NULL, NULL, 1, 'Male', NULL, NULL, NULL),
(17, 'Kadir', 'kadirinanir@gmail.com', '$2y$10$kHhz6UVCcPLaDXmTfd99GeOXu.iPbtcLODvNV1cqoE2bM7xa398b.', 'user', '2026-01-03 13:55:57', 0, NULL, NULL, 1, 'Male', NULL, NULL, NULL);

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- Tablo için AUTO_INCREMENT değeri `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Tablo için AUTO_INCREMENT değeri `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
