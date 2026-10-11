-- ========================================================
-- Yulio's Ice Ordering System - Database Schema
-- Charset: utf8mb4 (Full Unicode support for emojis, special chars)
-- ========================================================

CREATE DATABASE IF NOT EXISTS `ice_ordering_system` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `ice_ordering_system`;

-- Disable foreign key checks while defining or resetting tables
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `ai_logs`;
DROP TABLE IF EXISTS `inventory_logs`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- 1. Users Table (Aunt & Staff Admins)
-- --------------------------------------------------------
CREATE TABLE `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NULL,
    `role` ENUM('admin', 'staff') NOT NULL DEFAULT 'admin',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. Customers Table (Repeat Buyers & Resellers)
-- --------------------------------------------------------
CREATE TABLE `customers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(25) NOT NULL,
    `email` VARCHAR(100) NULL,
    `default_address` TEXT NOT NULL,
    `customer_type` ENUM('Retail', 'Reseller') NOT NULL DEFAULT 'Retail',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_customer_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Products Table (Dynamic Ice Catalog)
-- Replaces old hardcoded columns (qty_1kg, qty_3kg, qty_10kg)
-- --------------------------------------------------------
CREATE TABLE `products` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `weight_kg` DECIMAL(6, 2) NOT NULL DEFAULT 1.00,
    `retail_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `reseller_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `stock_quantity` INT NOT NULL DEFAULT 0,
    `image_path` VARCHAR(255) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_product_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Orders Table
-- --------------------------------------------------------
CREATE TABLE `orders` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_number` VARCHAR(32) NOT NULL UNIQUE,
    `customer_id` INT UNSIGNED NULL,
    `customer_name` VARCHAR(100) NOT NULL,
    `contact_number` VARCHAR(25) NOT NULL,
    `delivery_address` TEXT NOT NULL,
    `order_type` ENUM('Retail', 'Reseller') NOT NULL DEFAULT 'Retail',
    `fulfillment_type` ENUM('Delivery', 'Pickup') NOT NULL DEFAULT 'Delivery',
    `delivery_date` DATE NULL,
    `notes` TEXT NULL,
    `subtotal` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `delivery_fee` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `total_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `payment_method` ENUM('Cash on Delivery', 'GCash', 'Cash on Pickup') NOT NULL DEFAULT 'Cash on Delivery',
    `payment_status` ENUM('Unpaid', 'Paid') NOT NULL DEFAULT 'Unpaid',
    `status` ENUM('Pending', 'Confirmed', 'Preparing', 'Out for Delivery', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_orders_customer` FOREIGN KEY (`customer_id`) 
        REFERENCES `customers` (`id`) ON DELETE SET NULL,
    INDEX `idx_order_status` (`status`),
    INDEX `idx_order_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. Order Items Table (Relational Line Items)
-- --------------------------------------------------------
CREATE TABLE `order_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `product_name` VARCHAR(100) NOT NULL,
    `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `total_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    CONSTRAINT `fk_items_order` FOREIGN KEY (`order_id`) 
        REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_items_product` FOREIGN KEY (`product_id`) 
        REFERENCES `products` (`id`) ON DELETE RESTRICT,
    INDEX `idx_items_order` (`order_id`),
    INDEX `idx_items_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 6. Inventory Logs Table (Audit trail for Aunt's stock)
-- --------------------------------------------------------
CREATE TABLE `inventory_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT UNSIGNED NOT NULL,
    `change_qty` INT NOT NULL,
    `previous_stock` INT NOT NULL,
    `current_stock` INT NOT NULL,
    `reason` ENUM('Order Deduction', 'Restock', 'Damage/Melted', 'Manual Adjustment') NOT NULL DEFAULT 'Manual Adjustment',
    `reference_id` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_inv_product` FOREIGN KEY (`product_id`) 
        REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 7. AI Logs Table (Ollama Conversation & Forecasting logs)
-- --------------------------------------------------------
CREATE TABLE `ai_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `session_id` VARCHAR(64) NOT NULL,
    `user_role` ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    `prompt` TEXT NOT NULL,
    `response` TEXT NOT NULL,
    `model_name` VARCHAR(50) NOT NULL DEFAULT 'llama3',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ai_session` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

