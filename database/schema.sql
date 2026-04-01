-- =============================================================
-- Marrakech Guide — Full MySQL Schema
-- =============================================================
-- Run this file ONCE during installation.
-- Compatible with MySQL 5.7+ / MariaDB 10.3+
-- =============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ------------------------------------------------------------
-- Database (create if needed, then use it)
-- ------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `marrakech_guide`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `marrakech_guide`;

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(120)     NOT NULL,
  `email`         VARCHAR(180)     NOT NULL,
  `password_hash` VARCHAR(255)     NOT NULL,
  `role`          ENUM('user','admin') NOT NULL DEFAULT 'user',
  `phone`         VARCHAR(30)      DEFAULT NULL,
  `created_at`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: services  (tours / experiences)
-- ============================================================
CREATE TABLE IF NOT EXISTS `services` (
  `id`                INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `title`             VARCHAR(200)     NOT NULL,
  `tagline`           VARCHAR(300)     DEFAULT NULL,
  `category`          VARCHAR(60)      NOT NULL DEFAULT 'Cultural',
  `fa_icon`           VARCHAR(80)      DEFAULT 'fa-compass',
  `price`             DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
  `duration`          VARCHAR(80)      DEFAULT NULL,
  `location`          VARCHAR(120)     DEFAULT NULL,
  `difficulty`        VARCHAR(60)      DEFAULT 'Easy',
  `language`          VARCHAR(120)     DEFAULT 'English, French',
  `group_type`        ENUM('private','group') NOT NULL DEFAULT 'group',
  `max_people`        TINYINT UNSIGNED DEFAULT 12,
  `rating`            DECIMAL(3,2)     DEFAULT 5.00,
  `reviews`           SMALLINT UNSIGNED DEFAULT 0,
  `image`             TEXT             DEFAULT NULL,
  `description`       TEXT             DEFAULT NULL,
  `long_desc`         LONGTEXT         DEFAULT NULL,
  `highlights`        JSON             DEFAULT NULL,   -- array of {icon, text}
  `included`          JSON             DEFAULT NULL,   -- array of strings
  `not_included`      JSON             DEFAULT NULL,   -- array of strings
  `meeting_point`     VARCHAR(255)     DEFAULT NULL,
  `cancel_policy`     TEXT             DEFAULT NULL,
  `badge`             VARCHAR(80)      DEFAULT NULL,
  `what_to_bring`     TEXT             DEFAULT NULL,
  `min_age`           VARCHAR(60)      DEFAULT NULL,
  `is_active`         TINYINT(1)       NOT NULL DEFAULT 1,
  `sort_order`        SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`        DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_services_category` (`category`),
  KEY `idx_services_active`   (`is_active`),
  KEY `idx_services_price`    (`price`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: bookings
-- ============================================================
CREATE TABLE IF NOT EXISTS `bookings` (
  `id`             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `reference`      VARCHAR(20)      NOT NULL,          -- e.g. MG-20240101-0001
  `service_id`     INT UNSIGNED     NOT NULL,
  `user_id`        INT UNSIGNED     DEFAULT NULL,      -- NULL = guest booking
  `customer_name`  VARCHAR(120)     NOT NULL,
  `customer_email` VARCHAR(180)     NOT NULL,
  `customer_phone` VARCHAR(30)      NOT NULL,
  `booking_date`   DATE             NOT NULL,
  `people`         TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `unit_price`     DECIMAL(10,2)    NOT NULL,
  `total_price`    DECIMAL(10,2)    NOT NULL,
  `status`         ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `payment_status` ENUM('unpaid','paid','refunded')        NOT NULL DEFAULT 'unpaid',
  `notes`          TEXT             DEFAULT NULL,
  `admin_notes`    TEXT             DEFAULT NULL,
  `created_at`     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_bookings_reference` (`reference`),
  KEY `idx_bookings_service`  (`service_id`),
  KEY `idx_bookings_user`     (`user_id`),
  KEY `idx_bookings_date`     (`booking_date`),
  KEY `idx_bookings_status`   (`status`),
  CONSTRAINT `fk_bookings_service` FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_bookings_user`    FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: payments
-- ============================================================
CREATE TABLE IF NOT EXISTS `payments` (
  `id`               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `booking_id`       INT UNSIGNED     NOT NULL,
  `paypal_order_id`  VARCHAR(100)     DEFAULT NULL,
  `paypal_capture_id`VARCHAR(100)     DEFAULT NULL,
  `amount`           DECIMAL(10,2)    NOT NULL,
  `currency`         VARCHAR(5)       NOT NULL DEFAULT 'USD',
  `status`           ENUM('created','approved','captured','failed','refunded') NOT NULL DEFAULT 'created',
  `payer_email`      VARCHAR(180)     DEFAULT NULL,
  `payer_name`       VARCHAR(120)     DEFAULT NULL,
  `raw_response`     LONGTEXT         DEFAULT NULL,    -- full PayPal JSON response
  `created_at`       DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_payments_booking`  (`booking_id`),
  KEY `idx_payments_paypal`   (`paypal_order_id`),
  CONSTRAINT `fk_payments_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: settings  (key-value store)
-- ============================================================
CREATE TABLE IF NOT EXISTS `settings` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT        DEFAULT NULL,
  `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DEFAULT SETTINGS
-- ============================================================
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
  ('business_name',    'Marrakech Guide'),
  ('whatsapp',         '+212600000000'),
  ('email',            'tarik@marrakechguide.com'),
  ('address',          'Marrakech, Morocco'),
  ('currency',         'USD'),
  ('paypal_mode',      'sandbox'),
  ('paypal_client_id', ''),
  ('paypal_secret',    ''),
  ('site_tagline',     'Your Private Tour Guide in Morocco'),
  ('meta_description', 'Discover Morocco with Tarik, your personal Marrakech guide.');

-- ============================================================
-- DEFAULT ADMIN USER  (password: Admin@1234)
-- ============================================================
INSERT INTO `users` (`name`, `email`, `password_hash`, `role`) VALUES
  ('Tarik Belasri', 'admin@marrakechguide.com',
   '$2y$12$7wExdQ.NQUfqM7xtZFeMJ.fJQUeO.3pMGCLvg9VyxkK9kQvG.3qlS',
   'admin');

-- ============================================================
-- SEED: 10 Default Experiences
-- ============================================================
INSERT INTO `services`
  (`title`,`tagline`,`category`,`fa_icon`,`price`,`duration`,`location`,
   `difficulty`,`language`,`group_type`,`max_people`,`rating`,`reviews`,
   `image`,`description`,`long_desc`,`highlights`,`included`,`not_included`,
   `meeting_point`,`cancel_policy`,`badge`,`what_to_bring`,`min_age`,`sort_order`)
VALUES

-- 1. Medina Walking Tour
('Medina Walking Tour',
 'Uncover the secrets of the ancient medina with a local expert',
 'Cultural','fa-walking',45.00,'3 hours','Marrakech Medina','Easy',
 'English, French','group',12,5.00,127,
 'https://images.unsplash.com/photo-1539020140153-e479b8c22e70?w=800',
 'Explore the labyrinthine streets of Marrakech\'s UNESCO-listed medina.',
 'Dive deep into the heart of Marrakech on this intimate walking tour through one of the world\'s most captivating ancient cities. Your knowledgeable local guide will lead you through narrow alleyways and bustling souks, revealing stories and secrets that most visitors never discover.\n\nWe\'ll visit the famous Jemaa el-Fna square, the vibrant spice market, historic mosques, traditional tanneries, and hidden riads. Along the way, you\'ll learn about Moroccan history, architecture, and daily life from someone who grew up in these very streets.',
 '[{"icon":"fa-mosque","text":"Visit historic mosques and medersa"},{"icon":"fa-shopping-bag","text":"Navigate the famous souks"},{"icon":"fa-palette","text":"See traditional artisan workshops"},{"icon":"fa-camera","text":"Discover photogenic hidden corners"}]',
 '["Professional local guide","Small group (max 12 people)","Bottled water","Entry to Ben Youssef Medersa"]',
 '["Meals and drinks","Hotel pickup/drop-off","Gratuities (optional)"]',
 'Jemaa el-Fna Square, near the Café de France',
 'Free cancellation up to 24 hours before the tour.',
 'Best Seller','Comfortable walking shoes, sunscreen, hat','8 years+',1),

-- 2. High Atlas Trek
('High Atlas Trek',
 'Conquer breathtaking mountain trails with panoramic views',
 'Adventure','fa-mountain',85.00,'Full day (8h)','High Atlas Mountains','Moderate',
 'English, French, Amazigh','group',8,4.90,89,
 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800',
 'Trek through stunning Berber villages and dramatic mountain scenery.',
 'Experience the raw beauty of the High Atlas Mountains on this full-day trekking adventure. Starting from the village of Imlil, we\'ll hike through traditional Berber communities, terraced fields, and dramatic valleys with views of Toubkal—North Africa\'s highest peak.\n\nThis moderate trek suits active travelers with some hiking experience. We\'ll enjoy a traditional Berber lunch in a local home and learn about the fascinating culture of the mountain communities.',
 '[{"icon":"fa-mountain","text":"Trek to stunning mountain viewpoints"},{"icon":"fa-home","text":"Visit authentic Berber villages"},{"icon":"fa-utensils","text":"Traditional Berber lunch included"},{"icon":"fa-binoculars","text":"Views of Mount Toubkal"}]',
 '["Transport Marrakech–Imlil–Marrakech","Professional mountain guide","Traditional Berber lunch","Bottled water","Hiking poles"]',
 '["Personal travel insurance","Hotel pickup (available +$10)","Gratuities"]',
 'Our office in Marrakech (transport provided to Imlil)',
 'Free cancellation up to 48 hours before. Weather cancellations fully refunded.',
 'Nature Lover','Good hiking boots, layers, sunscreen, sunglasses','12 years+',2),

-- 3. Sahara Desert Overnight
('Sahara Desert Overnight',
 'Sleep under a million stars in the golden dunes of Merzouga',
 'Adventure','fa-sun',199.00,'2 days / 1 night','Merzouga Sahara','Easy',
 'English, French, Arabic','private',6,5.00,64,
 'https://images.unsplash.com/photo-1509316785289-025f5b846b35?w=800',
 'An unforgettable overnight journey into the heart of the Sahara Desert.',
 'Embark on the ultimate Moroccan adventure—an overnight trip to the legendary Sahara Desert. We\'ll depart early from Marrakech in a comfortable 4×4, crossing the High Atlas Mountains via the dramatic Tizi n\'Tichka pass, visiting the ancient kasbah of Aït Benhaddou (UNESCO World Heritage Site), and arriving at the golden dunes of Merzouga by sunset.\n\nAs the sun dips below the horizon, mount a camel for a magical ride into the dunes to your private Berber camp. Enjoy a traditional dinner under the stars, Gnawa music around the campfire, and wake to a spectacular Sahara sunrise.',
 '[{"icon":"fa-camel","text":"Sunset camel ride into the dunes"},{"icon":"fa-campground","text":"Private luxury desert camp"},{"icon":"fa-star","text":"Stargazing in the Sahara"},{"icon":"fa-sunrise","text":"Sunrise over the golden dunes"},{"icon":"fa-landmark","text":"Aït Benhaddou UNESCO site visit"}]',
 '["Private 4×4 transport","Professional guide","Camel ride","Luxury tent accommodation","Dinner and breakfast","All entry fees"]',
 '["Personal expenses","Travel insurance","Gratuities","Alcoholic beverages"]',
 'Your hotel or riad in Marrakech (pickup included)',
 'Free cancellation up to 72 hours before departure.',
 'Most Popular','Warm layers for the desert night, sunscreen, camera','6 years+',3),

-- 4. Private Bespoke Tour
('Private Bespoke Tour',
 'Your Morocco, your way — fully customized private experience',
 'Private','fa-crown',200.00,'Flexible','Anywhere in Morocco','Flexible',
 'English, French, Spanish, Arabic','private',10,5.00,43,
 'https://images.unsplash.com/photo-1597212720158-0f5b8c0c0c0c?w=800',
 'A completely personalized Morocco experience designed around your interests.',
 'The ultimate Morocco experience: a fully tailored private tour designed exclusively for you. Whether you dream of exploring ancient kasbahs, discovering hidden artisan workshops, dining at a local family\'s home, or venturing off the beaten path to undiscovered villages—your wish is our itinerary.\n\nWe\'ll work with you before your arrival to plan every detail of your perfect day (or multi-day journey). Private transport, exclusive access, and a dedicated personal guide are all included.',
 '[{"icon":"fa-star","text":"100% customized itinerary"},{"icon":"fa-car","text":"Private luxury transport"},{"icon":"fa-user-tie","text":"Dedicated personal guide"},{"icon":"fa-door-open","text":"Exclusive access & hidden gems"}]',
 '["Private transport","Dedicated personal guide","All entry fees","Flexible itinerary","Hotel pickup/drop-off"]',
 '["Meals (unless specified)","Personal expenses","Gratuities"]',
 'Your hotel — we come to you',
 'Free cancellation up to 48 hours before.',
 'Premium','Comfortable clothes appropriate for activities','All ages',4),

-- 5. Moroccan Cooking Class
('Moroccan Cooking Class',
 'Master the art of Moroccan cuisine in a traditional riad kitchen',
 'Food & Culture','fa-utensils',65.00,'4 hours','Marrakech Medina','Easy',
 'English, French','group',10,4.95,156,
 'https://images.unsplash.com/photo-1466637574441-749b8f19452f?w=800',
 'Learn to cook authentic Moroccan dishes with a local chef in a beautiful riad.',
 'Immerse yourself in the flavors and aromas of Moroccan cuisine during this hands-on cooking experience in a traditional riad. Led by a passionate local chef, you\'ll visit the spice market to hand-pick fresh ingredients, then head to our riad kitchen to prepare a full Moroccan feast.\n\nYou\'ll learn to make traditional tagine, couscous, harira soup, Moroccan salads, and bastilla pastry. The class ends with everyone sitting down together to enjoy the meal you\'ve created with traditional mint tea.',
 '[{"icon":"fa-shopping-basket","text":"Morning spice market visit"},{"icon":"fa-fire","text":"Hands-on cooking with local chef"},{"icon":"fa-book","text":"Recipe booklet to take home"},{"icon":"fa-coffee","text":"Traditional mint tea ceremony"}]',
 '["All ingredients","Recipe booklet","Full meal you prepare","Mint tea","Apron"]',
 '["Hotel pickup","Gratuities","Alcoholic drinks"]',
 'Spice market (Rahba Kedima), Marrakech Medina',
 'Free cancellation up to 24 hours before.',
 'Foodie Favorite','Comfortable clothes you don\'t mind getting messy','8 years+',5),

-- 6. Traditional Hammam Experience
('Traditional Hammam Experience',
 'Rejuvenate body and soul in an authentic Moroccan bathhouse',
 'Wellness','fa-spa',55.00,'2.5 hours','Marrakech Medina','Easy',
 'English, French','private',4,4.85,92,
 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=800',
 'Experience the ancient Moroccan bathing ritual in a stunning traditional hammam.',
 'Discover the ancient art of the Moroccan hammam—a deeply relaxing ritual that has been central to Moroccan culture for centuries. Unlike touristy spa hammams, we\'ll guide you through an authentic local hammam experience in one of Marrakech\'s most beautiful traditional bathhouses.\n\nYou\'ll receive a traditional black soap (savon beldi) scrub, a kessa exfoliation treatment, a ghassoul clay mask, and a relaxing massage. Leave feeling completely renewed, with silky-smooth skin and a deep sense of tranquility.',
 '[{"icon":"fa-soap","text":"Black soap (beldi) treatment"},{"icon":"fa-hand-sparkles","text":"Kessa exfoliation scrub"},{"icon":"fa-leaf","text":"Ghassoul clay mask"},{"icon":"fa-pray","text":"Traditional relaxation ritual"}]',
 '["Hammam entry fee","All products (savon beldi, kessa, ghassoul)","Towel and slippers","Traditional mint tea after"]',
 '["Hotel pickup","Gratuities","Personal toiletries"]',
 'We\'ll meet you at your riad and escort you to the hammam',
 'Free cancellation up to 24 hours before.',
 'Wellness','Swimwear or underwear (required)','16 years+',6),

-- 7. Ourika Valley Day Trip
('Ourika Valley Day Trip',
 'Discover lush Berber valleys and cascading waterfalls near Marrakech',
 'Nature','fa-water',70.00,'Full day (7h)','Ourika Valley','Easy',
 'English, French','group',10,4.80,78,
 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=800',
 'Escape the city and discover the stunning natural beauty of the Ourika Valley.',
 'Just 30km from Marrakech lies one of Morocco\'s most beautiful valleys—lush, green, and dotted with traditional Berber villages. The Ourika Valley offers a complete contrast to the bustling medina, with fresh mountain air, clear river streams, and terraced gardens of herbs and vegetables.\n\nWe\'ll visit a traditional Berber home for mint tea, explore a local cooperative of argan and herbal products, hike to the magnificent Setti Fatma waterfalls (7 tiers!), and enjoy lunch at a riverside terrace restaurant.',
 '[{"icon":"fa-water","text":"Hike to the 7-tier Setti Fatma waterfalls"},{"icon":"fa-home","text":"Visit authentic Berber village"},{"icon":"fa-seedling","text":"Explore herb and argan cooperative"},{"icon":"fa-utensils","text":"Riverside Berber lunch"}]',
 '["Transport from Marrakech","Professional guide","Traditional Berber lunch","Mint tea tasting","Bottled water"]',
 '["Personal expenses","Gratuities","Optional waterfall hike guide"]',
 'Our office in Marrakech (transport included)',
 'Free cancellation up to 24 hours before.',
 NULL,'Comfortable walking shoes, swimwear (optional), sunscreen','5 years+',7),

-- 8. Essaouira Day Trip
('Essaouira Day Trip',
 'Explore the enchanting blue-and-white coastal city on the Atlantic',
 'Cultural','fa-umbrella-beach',80.00,'Full day (10h)','Essaouira','Easy',
 'English, French','group',12,4.90,112,
 'https://images.unsplash.com/photo-1548013146-72479768bada?w=800',
 'Discover the magical walled city of Essaouira on the Atlantic coast of Morocco.',
 'Just 2.5 hours from Marrakech, Essaouira is one of Morocco\'s most enchanting cities—a beautifully preserved blue-and-white medina on a dramatic Atlantic coastline. This UNESCO World Heritage Site has inspired painters, musicians, and writers for centuries.\n\nWe\'ll explore the fortified medina walls, the busy fish port, the artisan woodworking district, and the windswept beach. Essaouira is famous for its fresh seafood, Gnawa music tradition, and laid-back Bohemian atmosphere. A perfect day trip from Marrakech.',
 '[{"icon":"fa-fort-awesome","text":"Explore the UNESCO-listed medina"},{"icon":"fa-fish","text":"Visit the lively fish port"},{"icon":"fa-music","text":"Experience Gnawa music culture"},{"icon":"fa-umbrella-beach","text":"Windswept Atlantic beach walk"}]',
 '["Transport Marrakech–Essaouira–Marrakech","Professional guide","Free time for exploring","Bottled water"]',
 '["Meals","Personal expenses","Gratuities"]',
 'Our office in Marrakech (transport included)',
 'Free cancellation up to 24 hours before.',
 NULL,'Windbreaker jacket (it can be windy!), comfortable shoes, sunscreen','All ages',8),

-- 9. Sunrise Hot Air Balloon
('Sunrise Hot Air Balloon',
 'Float above the Marrakech plains as the sun rises over the Atlas Mountains',
 'Adventure','fa-globe',175.00,'4 hours','Marrakech Plains','Easy',
 'English, French, Arabic','group',16,5.00,67,
 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800',
 'An unforgettable sunrise flight over the magical landscapes surrounding Marrakech.',
 'Experience the ultimate aerial adventure: a magical hot air balloon flight over the Marrakech plains as the sun rises over the Atlas Mountains. This is one of Morocco\'s most spectacular experiences, offering breathtaking panoramic views that stretch from the ancient medina to the snow-capped peaks of the High Atlas.\n\nYour experienced pilot will take you up to 1,000 meters above the palmeries, Berber villages, and desert landscapes. The flight ends with a traditional champagne (or juice) celebration breakfast in a desert camp.',
 '[{"icon":"fa-sun","text":"Magical sunrise over the Atlas Mountains"},{"icon":"fa-globe","text":"Panoramic views from 1,000 meters"},{"icon":"fa-champagne-glasses","text":"Champagne breakfast celebration"},{"icon":"fa-certificate","text":"Flight certificate provided"}]',
 '["Hotel pickup at 5:30am","Safety briefing","60-minute balloon flight","Champagne/juice breakfast","Flight certificate","Insurance"]',
 '["Gratuities","Personal expenses"]',
 'Hotel pickup included (Marrakech area)',
 'Non-refundable — weather cancellations rescheduled at no charge.',
 'Bucket List','Warm layers (cool at altitude), camera, no loose accessories','6 years+',9),

-- 10. Palaces & Gardens Tour
('Palaces & Gardens Tour',
 'Uncover the splendor of Marrakech\'s royal palaces and exotic gardens',
 'Cultural','fa-landmark',60.00,'4.5 hours','Marrakech','Easy',
 'English, French','group',12,4.85,134,
 'https://images.unsplash.com/photo-1551966775-a4ddc8df052b?w=800',
 'Explore the magnificent palaces, gardens and historic monuments of Marrakech.',
 'Marvel at the architectural splendor of Marrakech\'s most iconic palaces and the breathtaking beauty of its legendary gardens. This curated tour takes you through centuries of Moroccan royal history—from the ruins of the 16th-century El Badi Palace to the ornate Bahia Palace, and the world-famous Majorelle Garden.\n\nYour guide will bring these magnificent spaces to life with fascinating stories of sultans, intrigue, and artistic achievement. The tour includes all entry fees and the pace is leisurely, perfect for photography enthusiasts.',
 '[{"icon":"fa-landmark","text":"El Badi Palace ruins and storks"},{"icon":"fa-building","text":"Ornate Bahia Palace interiors"},{"icon":"fa-leaf","text":"Majorelle Garden and YSL Museum"},{"icon":"fa-camera","text":"Guided photo opportunities"}]',
 '["Professional guide","All entry fees (El Badi, Bahia, Majorelle)","Bottled water","Small group (max 12)"]',
 '["Transport (walking tour)","Meals","Gratuities","YSL Museum (optional +$12)"]',
 'Jemaa el-Fna Square, south exit',
 'Free cancellation up to 24 hours before.',
 NULL,'Comfortable shoes, sunhat, sunscreen, camera','All ages',10);

-- ============================================================
-- SAMPLE BOOKINGS (demo data)
-- ============================================================
INSERT INTO `bookings`
  (`reference`,`service_id`,`customer_name`,`customer_email`,`customer_phone`,
   `booking_date`,`people`,`unit_price`,`total_price`,`status`,`payment_status`)
VALUES
  ('MG-2024-00001', 1, 'Sophie Laurent',  'sophie@example.com', '+33612345678',
   DATE_ADD(CURDATE(), INTERVAL 3 DAY),  2, 45.00,  90.00, 'confirmed', 'paid'),
  ('MG-2024-00002', 3, 'James Wilson',    'james@example.com',  '+44791234567',
   DATE_ADD(CURDATE(), INTERVAL 5 DAY),  2, 199.00, 398.00,'pending',   'unpaid'),
  ('MG-2024-00003', 9, 'Maria González',  'maria@example.com',  '+34612345678',
   DATE_ADD(CURDATE(), INTERVAL 7 DAY),  4, 175.00, 700.00,'confirmed', 'paid'),
  ('MG-2024-00004', 5, 'Luca Rossi',      'luca@example.com',   '+39312345678',
   DATE_ADD(CURDATE(), INTERVAL 10 DAY), 3, 65.00,  195.00,'pending',   'unpaid'),
  ('MG-2024-00005', 2, 'Emma Johnson',    'emma@example.com',   '+12125551234',
   DATE_ADD(CURDATE(), INTERVAL 1 DAY),  1, 85.00,  85.00, 'cancelled', 'unpaid');
