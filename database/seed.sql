-- ========================================================
-- Yulio's Ice Ordering System - Initial Seed Data
-- ========================================================

USE `ice_ordering_system`;

-- 1. Default Admin User (Password: admin123)
-- Hash generated via password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO `users` (`username`, `password_hash`, `full_name`, `email`, `role`) VALUES
('admin', '$2y$10$EOayJwDhOzYB4alPZFHHOe.Od2nIiVCG58WiIhfwQ0ZYeJDGkOY7S', 'Yulio Admin', 'admin@yuliosice.com', 'admin');

-- 2. Products Catalog (Reflecting existing products + reseller wholesale tiers)
INSERT INTO `products` (`name`, `slug`, `description`, `weight_kg`, `retail_price`, `reseller_price`, `stock_quantity`, `image_path`, `is_active`) VALUES
('1kg Cube Ice Pack', '1kg-cube-pack', 'Pure crystalline tube ice cubes in a 1kg sealed bag. Perfect for home drinks and small coolers.', 1.00, 20.00, 15.00, 150, 'assets/images/YuliosCpic1.jpg', 1),
('3kg Cube Ice Pack', '3kg-cube-pack', 'Triple-filtered clear ice cubes in a 3kg pack. Great for small parties and family gatherings.', 3.00, 45.00, 38.00, 80, 'assets/images/YuliosCpic2.jpg', 1),
('5kg Cube Ice Pack', '5kg-cube-pack', 'Convenient 5kg bag of purified cube ice. Great for medium coolers, food stalls, and gatherings.', 5.00, 65.00, 51.00, 50, 'assets/images/YuliosCpic4.jpg', 1),
('10kg Ice Sack', '10kg-ice-sack', 'Heavy-duty 10kg sack of dense purified ice. Built for sari-sari stores, milk tea shops, and large coolers.', 10.00, 120.00, 98.00, 45, 'assets/images/YuliosCpic3.jpg', 1);

-- 3. Initial Inventory Audit Logs
INSERT INTO `inventory_logs` (`product_id`, `change_qty`, `previous_stock`, `current_stock`, `reason`, `reference_id`) VALUES
(1, 150, 0, 150, 'Restock', NULL),
(2, 80, 0, 80, 'Restock', NULL),
(3, 45, 0, 45, 'Restock', NULL),
(4, 30, 0, 30, 'Restock', NULL);

-- 4. Sample Customers
INSERT INTO `customers` (`name`, `phone`, `email`, `default_address`, `customer_type`, `notes`) VALUES
('Aling Nena Sari-Sari Store', '09171234567', 'nena@example.com', 'Block 12 Lot 4, Mabini St., Brgy. San Isidro', 'Reseller', 'Regular morning reseller delivery'),
('Juan Dela Cruz', '09289876543', 'juan@example.com', '124 Rizal Avenue, Poblacion', 'Retail', 'Residential house, blue gate');

-- 5. Sample Order
INSERT INTO `orders` (`order_number`, `customer_id`, `customer_name`, `contact_number`, `delivery_address`, `order_type`, `fulfillment_type`, `delivery_date`, `notes`, `subtotal`, `delivery_fee`, `total_price`, `payment_method`, `payment_status`, `status`) VALUES
('ORD-20261008-0001', 1, 'Aling Nena Sari-Sari Store', '09171234567', 'Block 12 Lot 4, Mabini St., Brgy. San Isidro', 'Reseller', 'Delivery', CURDATE(), 'Deliver before 10 AM please', 570.00, 30.00, 600.00, 'Cash on Delivery', 'Unpaid', 'Pending');

-- 6. Sample Order Line Items
INSERT INTO `order_items` (`order_id`, `product_id`, `product_name`, `quantity`, `unit_price`, `total_price`) VALUES
(1, 3, '10kg Ice Sack', 3, 190.00, 570.00);

