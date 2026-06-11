-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 01, 2026 at 04:19 PM
-- Server version: 10.4.34-MariaDB
-- PHP Version: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `company_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `merchandise`
--

CREATE TABLE `merchandise` (
  `id` int(11) NOT NULL,
  `merch_name` varchar(200) NOT NULL,
  `price` bigint(20) NOT NULL DEFAULT 0,
  `warehouse_stock` int(11) NOT NULL DEFAULT 0,
  `category_id` int(11) NOT NULL,
  `enter_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `min_stock_alert` int(11) DEFAULT 5,
  `weight_gram` int(11) DEFAULT 0,
  `dimensions` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image` varchar(500) DEFAULT NULL,
  `thumbnail` varchar(500) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `merchandise`
--

INSERT INTO `merchandise` (`id`, `merch_name`, `price`, `warehouse_stock`, `category_id`, `enter_date`, `description`, `barcode`, `sku`, `is_active`, `min_stock_alert`, `weight_gram`, `dimensions`, `created_at`, `updated_at`, `image`, `thumbnail`) VALUES
(1, 'پنکه 3منظوره ویداس مدل «VIR-8036»', 7780000, 3, 2, '2026-05-28', 'توان مصرفی این مدل پنکه ویداس 70وات با تقسیم قدرت در 3 مرحله است. پنکه ویداس مدل «VIR-8036» دارای 2 حالت سکون،چپ و راست است.این دستگاه دارای تایمر متغیر تا 7.5 ساعت است', '2900621501413', 'VIR-8036', 1, 2, 7500, '50x15x74 سانتی‌متر', '2026-05-28 11:24:48', '2026-05-28 11:35:20', 'uploads/merchandise/1779968120_6a182878af5d3.jpg', 'uploads/merchandise/1779968120_6a182878b1598.jpg'),
(2, 'کنسول بازی PlayStation 4 Slim', 85597990, 2, 2, '2026-05-28', 'پشتیبانی از HDR10 (با به‌روزرسانی نرم‌افزاری) / پشتیبانی از صدای چندکاناله مثل PCM، Dolby Digital و', '', 'PS4', 1, 1, 2100, '28.8x26.5x3.9 سانتی‌متر', '2026-05-28 11:42:34', '2026-05-28 13:15:57', 'uploads/merchandise/1779968554_6a182a2aa73a8.jpg', 'uploads/merchandise/1779968554_6a182a2aa8a45.jpg'),
(3, 'گیتار الکتریک', 45000000, 1, 2, '2026-05-28', '', '370001506', 'Suzuki', 1, 0, 1200, '4/4', '2026-05-28 11:50:02', '2026-05-28 11:50:02', 'uploads/merchandise/1779969002_6a182bea12e8e.jpg', 'uploads/merchandise/1779969002_6a182bea15269.jpg'),
(4, 'عروسک استیچ', 850000, 2, 6, '2026-05-28', '', '۱۲۳', 'JIMI110', 1, 0, 500, '300x200x550 میلی‌متر', '2026-05-28 13:15:41', '2026-05-28 13:15:41', 'uploads/merchandise/1779974141_6a183ffd48623.jpg', 'uploads/merchandise/1779974141_6a183ffd49fae.jpg'),
(5, 'هارد کیس گیتار الکتریک', 6500000, 2, 2, '2026-05-28', '', '404', '404', 1, 0, 900, '120x50x74 سانتی‌متر', '2026-05-28 13:21:08', '2026-05-28 13:21:08', 'uploads/merchandise/1779974468_6a18414444714.jpg', 'uploads/merchandise/1779974468_6a184144465d7.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `merch_categories`
--

CREATE TABLE `merch_categories` (
  `id` int(11) NOT NULL,
  `cat_name` varchar(100) NOT NULL,
  `cat_slug` varchar(100) NOT NULL,
  `cat_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `merch_categories`
--

INSERT INTO `merch_categories` (`id`, `cat_name`, `cat_slug`, `cat_order`, `is_active`, `created_at`) VALUES
(1, 'کامپیوتری', 'کامپیوتری', 1, 1, '2026-05-28 10:43:54'),
(2, 'الکترونیک', 'الکترونیک', 2, 1, '2026-05-28 10:43:54'),
(3, 'بهداشتی', 'بهداشتی', 3, 1, '2026-05-28 10:43:54'),
(4, 'آرایشی', 'آرایشی', 4, 1, '2026-05-28 10:43:54'),
(5, 'لوازم تحریر', 'لوازم-تحریر', 5, 1, '2026-05-28 10:43:54'),
(6, 'اسباب‌بازی', 'اسباب‌بازی', 6, 1, '2026-05-28 10:43:54'),
(7, 'ابزار', 'ابزار', 7, 1, '2026-05-28 10:43:54');

-- --------------------------------------------------------

--
-- Table structure for table `nKind`
--

CREATE TABLE `nKind` (
  `id` int(11) NOT NULL,
  `kind_name` varchar(100) NOT NULL,
  `kind_order` int(11) DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nKind`
--

INSERT INTO `nKind` (`id`, `kind_name`, `kind_order`) VALUES
(1, 'خوراکی', 1),
(2, 'شوینده', 2),
(3, 'تنقلات', 3),
(4, 'پوشاکی', 4),
(5, 'قبض', 5),
(6, 'خرید اینترنتی', 6),
(7, 'متفرقه', 7),
(8, 'بهداشتی ', 8);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `pName` varchar(200) NOT NULL,
  `pPrice` bigint(20) NOT NULL,
  `pDate` date NOT NULL,
  `pKind_id` int(11) NOT NULL,
  `pUser_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `pName`, `pPrice`, `pDate`, `pKind_id`, `pUser_id`, `created_at`) VALUES
(8, 'یک کیلو قهوه', 2950000, '2026-05-26', 1, 1, '2026-05-26 08:38:43'),
(9, 'حبوبات و روغن کنجد', 2400000, '2026-05-26', 1, 1, '2026-05-26 08:39:57'),
(7, 'خرید گوشت و پروتئین', 3547000, '2026-05-26', 1, 1, '2026-05-26 08:37:37'),
(6, 'برنج ایرانی معطر', 4950000, '2026-05-26', 1, 1, '2026-05-26 08:36:25'),
(10, 'یک سطل ماست', 250000, '2026-05-26', 1, 1, '2026-05-26 08:40:57'),
(11, 'نوشابه کوکا', 2326000, '2026-05-26', 1, 1, '2026-05-26 08:42:40'),
(12, 'نوشیدنی مخصوص آقا پارسا', 530000, '2026-05-26', 3, 3, '2026-05-26 08:46:03'),
(13, 'سس و قارچ و سبزی خورشتی و پنیر', 2950000, '2026-05-25', 1, 1, '2026-05-26 09:28:42'),
(14, 'افق کورش خرید روزانه', 1950000, '2026-05-27', 1, 1, '2026-05-27 12:04:31'),
(15, 'استپ به خانه باباجون', 65000, '2026-05-28', 7, 1, '2026-05-28 16:14:45'),
(16, 'آگهی دیوار php', 69000, '2026-05-28', 7, 1, '2026-05-28 16:15:44'),
(17, 'افق کوروش', 890000, '2026-05-28', 1, 1, '2026-05-29 05:43:57'),
(18, 'کرم ضدآفتاب و دارو فشار', 1500000, '2026-05-28', 8, 1, '2026-05-29 05:49:40');

-- --------------------------------------------------------

--
-- Table structure for table `stock_log`
--

CREATE TABLE `stock_log` (
  `id` int(11) NOT NULL,
  `merchandise_id` int(11) NOT NULL,
  `old_stock` int(11) NOT NULL,
  `new_stock` int(11) NOT NULL,
  `change_date` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Position` varchar(100) DEFAULT NULL,
  `Date1` timestamp NOT NULL DEFAULT current_timestamp(),
  `DateLastLogin` datetime DEFAULT NULL,
  `Description` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `FirstName`, `LastName`, `Username`, `Password`, `Position`, `Date1`, `DateLastLogin`, `Description`) VALUES
(1, 'مدیر', 'سیستم', 'admin', '14471447', 'مدیر ارشد', '2026-05-25 10:51:53', '2026-06-01 13:33:30', 'پادشاه'),
(2, 'درسا', 'احمدی گوهری', 'Dorsa', '123456', 'وزیر ارتباطات', '2026-05-25 11:02:49', '2026-05-26 12:22:10', 'پرنسس درسا مسئول امور روابط با خارج از خانواده'),
(3, 'پارسا', 'احمدی گوهری', 'Parsa', '123456', 'معاون کلانتر', '2026-05-25 11:10:00', '2026-05-25 14:44:15', 'جناب ولیعهد هستند'),
(4, 'حبیبه', 'احمدی گوهری', 'Habibeh', '123456', 'مدیر داخلی', '2026-05-29 05:52:34', NULL, '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `merchandise`
--
ALTER TABLE `merchandise`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_barcode` (`barcode`),
  ADD KEY `idx_sku` (`sku`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `merch_categories`
--
ALTER TABLE `merch_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cat_name` (`cat_name`),
  ADD UNIQUE KEY `cat_slug` (`cat_slug`);

--
-- Indexes for table `nKind`
--
ALTER TABLE `nKind`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kind_name` (`kind_name`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date` (`pDate`),
  ADD KEY `idx_kind` (`pKind_id`),
  ADD KEY `idx_user` (`pUser_id`);

--
-- Indexes for table `stock_log`
--
ALTER TABLE `stock_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `merchandise_id` (`merchandise_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `merchandise`
--
ALTER TABLE `merchandise`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `merch_categories`
--
ALTER TABLE `merch_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `nKind`
--
ALTER TABLE `nKind`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `stock_log`
--
ALTER TABLE `stock_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
