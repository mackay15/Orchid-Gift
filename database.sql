-- ORCHID GIFT & MORE MANAGEMENT SYSTEM DATABASE SCHEMA
-- Generated for import in phpMyAdmin / MySQL CLI

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- 1. DATABASE CREATION
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `orchid_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `orchid_db`;

-- --------------------------------------------------------
-- 2. TABLE STRUCTURES
-- --------------------------------------------------------

-- USERS Table
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `role` ENUM('customer', 'cashier', 'admin') NOT NULL DEFAULT 'customer',
  `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
  `google_id` VARCHAR(255) UNIQUE DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CATEGORIES Table
CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) UNIQUE NOT NULL,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PRODUCTS Table
CREATE TABLE IF NOT EXISTS `products` (
  `product_id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(10,2) NOT NULL,
  `stock_quantity` INT NOT NULL DEFAULT 0,
  `image_url` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ORDERS Table
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT DEFAULT NULL,
  `cashier_id` INT DEFAULT NULL,
  `order_type` ENUM('online', 'walk-in') NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `order_status` ENUM('Pending', 'Processing', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
  `payment_status` ENUM('Unpaid', 'Paid') NOT NULL DEFAULT 'Unpaid',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  FOREIGN KEY (`cashier_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ORDER_ITEMS Table
CREATE TABLE IF NOT EXISTS `order_items` (
  `item_id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `total_price` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- REVIEWS Table
CREATE TABLE IF NOT EXISTS `reviews` (
  `review_id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `customer_id` INT NOT NULL,
  `rating` INT NOT NULL,
  `review` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
  FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- WISHLISTS Table
CREATE TABLE IF NOT EXISTS `wishlists` (
  `wishlist_id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SALES Table
CREATE TABLE IF NOT EXISTS `sales` (
  `sales_id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `payment_method` ENUM('Cash', 'Card', 'Mobile Money') NOT NULL,
  `transaction_id` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PAYMENTS Table
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL,
  `payment_status` ENUM('Pending', 'Completed', 'Failed') NOT NULL DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NOTIFICATIONS Table
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CASHIER_LOGS Table
CREATE TABLE IF NOT EXISTS `cashier_logs` (
  `log_id` INT AUTO_INCREMENT PRIMARY KEY,
  `cashier_id` INT NOT NULL,
  `action` VARCHAR(100) NOT NULL,
  `details` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`cashier_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. SEEDING INITIAL DATA
-- --------------------------------------------------------

-- Seed Default Accounts (Passwords: admin123, cashier123, customer123)
INSERT INTO `users` (`username`, `password`, `email`, `full_name`, `role`, `status`) VALUES 
('admin', '$2a$10$iLcQ767KAbD8CrXfdHu2IOKML4a0K7oj5cD6B14GVXNmFr4pDGHOy', 'admin@orchid.com', 'Orchid Admin', 'admin', 'Active'),
('cashier', '$2a$10$ld0hgj8Wm0ZlkNxIjpGJyu19zsUDPNzFFFcduHzaHeoxkIzlebPA6', 'cashier@orchid.com', 'Sarah Cashier', 'cashier', 'Active'),
('customer', '$2a$10$PKWWtWI4QNdCxU/1upGgUecigH9rJfyPDRejylL5BhWJ02qF4BzSe', 'customer@orchid.com', 'John Doe Customer', 'customer', 'Active');

-- Seed Default Categories
INSERT INTO `categories` (`category_id`, `name`, `description`) VALUES 
(1, 'Flowers', 'Fresh cut roses, mixed bouquets, floral baskets, and custom arrangements.'),
(2, 'Chocolate Gifts', 'Imported artisanal chocolates, premium truffles, and sweet combination boxes.'),
(3, 'Cakes', 'Freshly baked designer cakes, custom message cupcakes, and celebration treats.'),
(4, 'Hampers', 'Curated luxury hampers containing selection of gourmet foods, wine, and keepsakes.'),
(5, 'Customized Gifts', 'Personalized photo frames, engraved mugs, custom jewelry boxes, and keychains.'),
(6, 'Perfumes', 'Luxurious original designer fragrances for men and women.'),
(7, 'Jewelry', 'Elegant necklaces, sterling silver bracelets, earrings, and fashion statement pieces.'),
(8, 'Greeting Cards', 'Beautiful pop-up greeting cards, handcrafted letters, and warm wishes cards.');

-- Seed Premium Products linked to seeded Category IDs
INSERT INTO `products` (`category_id`, `name`, `description`, `price`, `stock_quantity`, `image_url`) VALUES 
(1, 'Midnight Rose Bouquet', 'A stunning arrangement of 12 premium dark red roses wrapped in black silk paper with a lavender ribbon.', 45.00, 15, 'https://images.unsplash.com/photo-1561181286-d3fee7d55364?auto=format&fit=crop&w=600&q=80'),
(1, 'Orchid Meadow Vase', 'Elegant violet and purple orchids in a premium glass bowl vase.', 60.00, 8, 'https://images.unsplash.com/photo-1526047932273-341f2a7631f9?auto=format&fit=crop&w=600&q=80'),
(2, 'Gourmet Gold Truffles', 'A premium golden box of 24 handcrafted Belgian dark and milk chocolate truffles.', 32.50, 25, 'https://images.unsplash.com/photo-1548907040-4d42b52145ca?auto=format&fit=crop&w=600&q=80'),
(2, 'Chocolate Lover Hamper', 'Assorted Lindt bars, dark cocoa nibs, and chocolate-dipped almonds in a lavender basket.', 45.00, 10, 'https://images.unsplash.com/photo-1511381939415-e44015466834?auto=format&fit=crop&w=600&q=80'),
(3, 'Velvet Orchid Cake', 'A delicious 1kg Red Velvet cake layered with premium cream cheese frosting and edible flower decoration.', 35.00, 5, 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=600&q=80'),
(4, 'Luxury Spa & Tea Hamper', 'Relaxation hamper with lavender essential oils, floral tea leaves, mug, and organic honey.', 75.00, 12, 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=600&q=80'),
(5, 'Custom Engraved Wooden Frame', 'Premium oak wood photo frame with personalized engraving at the bottom.', 20.00, 50, 'https://images.unsplash.com/photo-1534447677768-be436bb09401?auto=format&fit=crop&w=600&q=80'),
(6, 'Orchid Noir Parfum', 'A deep floral woodsy signature parfum spray for women (100ml).', 85.00, 10, 'https://images.unsplash.com/photo-1541643600914-78b084683601?auto=format&fit=crop&w=600&q=80'),
(7, 'Sterling Silver Rose Pendant', 'Elegant handcrafted 925 sterling silver necklace with a rose pendant in a soft violet box.', 55.00, 15, 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=600&q=80'),
(8, '3D Pop-Up Orchid Card', 'Laser-cut pop-up card displaying a beautiful blooming orchid garden.', 7.50, 40, 'https://images.unsplash.com/photo-1513151233558-d860c5398176?auto=format&fit=crop&w=600&q=80');

-- Seed Sample Customer Reviews
INSERT INTO `reviews` (`product_id`, `customer_id`, `rating`, `review`, `status`) VALUES 
(1, 3, 5, 'Absolutely gorgeous! The roses were fresh and smelled amazing. Worth every penny.', 'Approved'),
(1, 3, 4, 'Very nice presentation, but delivery was delayed by about 20 minutes.', 'Approved');

-- Mock transactions history for dashboard representation
INSERT INTO `orders` (`order_id`, `customer_id`, `order_type`, `total_amount`, `order_status`, `payment_status`, `created_at`) VALUES 
(1, 3, 'online', 45.00, 'Completed', 'Paid', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 3, 'online', 45.00, 'Completed', 'Paid', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3, 3, 'online', 45.00, 'Completed', 'Paid', DATE_SUB(NOW(), INTERVAL 1 DAY));

INSERT INTO `order_items` (`order_id`, `product_id`, `quantity`, `unit_price`, `total_price`) VALUES 
(1, 1, 1, 45.00, 45.00),
(2, 1, 1, 45.00, 45.00),
(3, 1, 1, 45.00, 45.00);

INSERT INTO `payments` (`order_id`, `amount`, `payment_method`, `payment_status`, `created_at`) VALUES 
(1, 45.00, 'Card', 'Completed', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 45.00, 'Card', 'Completed', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3, 45.00, 'Card', 'Completed', DATE_SUB(NOW(), INTERVAL 1 DAY));

INSERT INTO `sales` (`order_id`, `total_amount`, `payment_method`, `transaction_id`, `created_at`) VALUES 
(1, 45.00, 'Card', 'TXN581290', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 45.00, 'Card', 'TXN918342', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3, 45.00, 'Card', 'TXN419357', DATE_SUB(NOW(), INTERVAL 1 DAY));

COMMIT;
