DROP DATABASE IF EXISTS `chickenjoy`;
CREATE DATABASE `chickenjoy` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `chickenjoy`;

CREATE TABLE `categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `category_code` VARCHAR(20) NOT NULL UNIQUE, 
  `category_name` VARCHAR(255) NOT NULL,      
  `description` TEXT,                       
  `status` ENUM('active', 'hidden') DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `categories` (`id`,`category_code`, `category_name`, `status`) VALUES
(1,'L01', 'Gà Rán', 'active'),
(2,'L02', 'Burger', 'active'),
(3,'L03', 'Combo ăn trưa', 'hidden'),
(4,'L04', 'Mỳ ý', 'active'),
(5,'L05', 'Pizza', 'active'),
(6,'L06', 'Món ăn kèm', 'active'),
(7,'L07', 'Nước Uống', 'active'),
(8,'L08', 'Tráng miệng', 'active');

CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `fullname` VARCHAR(255) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(15) NOT NULL,
  `gender` ENUM('nam', 'nu', 'khac') DEFAULT 'khac',
  `address` TEXT NOT NULL,
  `role` ENUM('user', 'admin') DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB;

CREATE TABLE `import_receipts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `receipt_code` VARCHAR(20) NOT NULL UNIQUE,  
  `import_date` DATE NOT NULL,
  `total_amount` DECIMAL(15, 2) DEFAULT 0.00,  
  `status` ENUM('pending', 'completed') DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `import_receipts` (`id`, `receipt_code`, `import_date`, `total_amount`, `status`) VALUES
(1, 'PN001', '2025-10-15', 4150000.00, 'completed'),
(2, 'PN002', '2025-10-25', 3200000.00, 'completed'),
(3, 'PN003', '2025-11-01', 5000000.00, 'completed'),
(4, 'PN004', '2025-11-02', 1505000.00, 'completed'),
(5, 'PN005', '2025-11-10', 4600000.00, 'completed'),
(6, 'PN006', '2025-11-15', 3300000.00, 'completed'),
(7, 'PN007', '2025-11-20', 4000000.00, 'completed'),
(8, 'PN008', '2025-11-25', 3200000.00, 'completed'),
(9, 'PN009', '2025-12-01', 3800000.00, 'completed'),
(10, 'PN010', '2025-12-05', 3000000.00, 'completed'),
(11, 'PN011', '2025-12-10', 6500000.00, 'completed'),
(12, 'PN012', '2025-12-15', 3100000.00, 'completed'),
(13, 'PN013', '2025-12-20', 3500000.00, 'completed'),
(14, 'PN014', '2025-12-25', 2500000.00, 'completed'),
(15, 'PN015', '2026-01-05', 4800000.00, 'completed'),
(16, 'PN016', '2026-01-15', 2700000.00, 'completed'), 
(17, 'PN017', '2026-02-02', 5400000.00, 'completed'), 
(18, 'PN018', '2026-02-20', 7500000.00, 'completed'), 
(19, 'PN019', '2026-03-01', 5600000.00, 'completed'),
(20, 'PN020', '2026-03-10', 3500000.00, 'pending');

CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_code` varchar(20) UNIQUE DEFAULT NULL,  
  `category_id` int(11) DEFAULT 1,                 
  `product_name` varchar(255) NOT NULL,            
  `category` varchar(100) NOT NULL,                
  `description` text DEFAULT NULL,                 
  `unit` varchar(50) NOT NULL DEFAULT 'Phần',      
  `import_price` decimal(15, 2) DEFAULT 0.00,      
  `profit_rate` decimal(5, 2) DEFAULT 0.20,        
  `price` decimal(15, 2) NOT NULL,                 
  `stock` int(11) DEFAULT 0,                       
  `calories` int(11) DEFAULT 0,                    
  `protein` int(11) DEFAULT 0,                     
  `carbs` int(11) DEFAULT 0,                       
  `fat` int(11) DEFAULT 0,                         
  `image` varchar(255) DEFAULT 'default.jpg',      
  `is_new` tinyint(1) DEFAULT 0,                   
  `status` ENUM('active', 'hidden') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO `products` (`id`, `product_code`, `category_id`, `product_name`, `category`, `description`, `unit`, `import_price`, `profit_rate`, `price`, `stock`, `calories`, `protein`, `carbs`, `fat`, `image`, `is_new`, `status`) VALUES
(1, 'SP001', 1, 'Gà rán giòn tan', 'GaRan', 'Gà được tẩm ướp vị đặc biệt, chiên giòn vàng ươm', 'Miếng', 50000, 0.78, 89000, 50, 320, 25, 12, 18, 'ga-ran-4.jpg', 1, 'active'),
(2, 'SP002', 1, 'Gà rán Hàn Quốc', 'GaRan', 'Gà rán theo cách Hàn Quốc với sốt đặc trưng', 'Phần', 70000, 0.70, 119000, 30, 350, 23, 15, 20, 'ga-ran-2.jpg', 1, 'active'),
(3, 'SP003', 1, 'Đùi gà giòn', 'GaRan', 'Đùi gà giòn tan, thịt bên trong mọng nước', 'Miếng', 50000, 0.78, 89000, 40, 280, 20, 10, 15, 'ga-ran-3.jpg', 0, 'active'),
(4, 'SP004', 1, 'Combo gia đình', 'GaRan', '8 miếng gà rán đủ loại cho cả gia đình', 'Phần', 200000, 0.49, 299000, 15, 1200, 80, 45, 60, 'ga-ran-1.jpg', 0, 'active'),
(5, 'SP005', 1, 'Cánh gà rán giòn', 'GaRan', 'Cánh gà siêu to khổng lồ giòn rụm', 'Miếng', 25000, 0.80, 45000, 60, 210, 15, 8, 12, 'canhga.png', 0, 'active'),
(17,'SP006', 2, 'Hamburger Bò Phô Mai', 'Hamburger', 'Nhân thịt bò Úc nướng và phô Mai tan chảy', 'Cái', 45000, 0.75, 79000, 20, 450, 22, 35, 25, 'hamburger-2.jpg', 0, 'active'),
(6, 'SP007', 2, 'Hamburger Gà Giòn', 'Hamburger', 'Nhân gà chiên xù xốt Mayonnaise', 'Cái', 30000, 0.83, 55000, 25, 420, 20, 38, 18, 'hamburgerga.png', 0, 'active'),
(7, 'SP008', 2, 'Double Burger', 'Hamburger', 'Hai lớp nhân thịt bò cực đã cho người cực đói', 'Cái', 50000, 0.90, 95000, 10, 680, 40, 42, 35, 'hamburgerthit.png', 1, 'active'),
(8, 'SP009', 4, 'Mì Ý sốt bò bằm', 'MiY', 'Mì Ý truyền thống đậm vị Ý, mê ly', 'Phần', 40000, 0.72, 69000, 20, 550, 18, 70, 12, 'miybobam.png', 0, 'active'),
(18, 'SP010', 4, 'Mì Ý hải sản', 'MiY', 'Xốt cà chua và hải sản tươi ngon', 'Phần', 60000, 0.65, 99000, 12, 500, 20, 65, 10, 'mi-y-1.jpg', 1, 'active'),
(9,  'SP011', 4, 'Mì Ý sốt kem', 'MiY', 'Vị béo ngậy của kem tươi và nấm', 'Phần', 45000, 0.66, 75000, 15, 620, 15, 60, 30, 'mi-y-2.png', 0, 'active'),
(10, 'SP012', 6, 'Khoai tây chiên', 'KhoaiTay', 'Giòn lâu, vui lâu - món ăn kèm tuyệt vời', 'Phần', 20000, 1.00, 40000, 100, 312, 3, 41, 15, 'khoai-tay-2.jpg', 0, 'active'),
(11, 'SP013', 6, 'Khoai tây lắc phô mai', 'KhoaiTay', 'Khoai tây vàng giòn lắc bột phô mai đặc biệt', 'Phần', 25000, 0.80, 45000, 80, 350, 4, 43, 17, 'khoai-tay-1.jpg', 0, 'active'),
(19, 'SP014', 7, 'Nước nha đam', 'NuocUong', 'Nước nha đam lạnh sảng khoái, ít đường', 'Ly', 20000, 0.75, 35000, 40, 120, 0, 30, 0, 'nuoc-.uong.jpg', 0, 'active'),
(12, 'SP015', 7, 'Coca Cola mát lạnh', 'NuocUong', 'Nước giải khát có gas cực đã', 'Ly', 8000, 0.87, 15000, 200, 140, 0, 39, 0, 'coca.png', 0, 'active'),
(13, 'SP016', 7, 'Trà đào miếng', 'NuocUong', 'Trà thanh mát kèm miếng đào thơm giòn', 'Ly', 15000, 1.00, 30000, 45, 130, 0, 32, 0, 'Tradao.png', 0, 'active'),
(20, 'SP017', 1, 'Gà rán cay nồng', 'GaRan', 'Vị cay cực hạn cho tín đồ ăn cay', 'Miếng', 25000, 0.68, 42000, 25, 330, 24, 13, 19, 'ga-ran-cay.jpg', 0, 'active'),
(14, 'SP018', 2, 'Hamburger Tôm', 'Hamburger', 'Nhân tôm tươi giòn rụm, xốt đặc biệt', 'Cái', 40000, 0.62, 65000, 18, 380, 18, 40, 15, 'burgertom.png', 1, 'active'),
(15, 'SP019', 4, 'Mì Ý xốt xúc xích', 'MiY', 'Dành cho các bạn nhỏ với xúc xích đức', 'Phần', 25000, 0.80, 45000, 22, 400, 12, 55, 10, 'miyxucxich.png', 0, 'active'),
(16, 'SP020', 6, 'Khoai tây múi cau', 'KhoaiTay', 'Khoai tây cắt múi cau lạ mắt, giòn tan', 'Phần', 20000, 0.75, 35000, 50, 310, 3, 40, 14, 'khoaitaymuicau.png', 0, 'active'),
(21, 'SP021', 7, 'Pepsi', 'NuocUong', 'Nước giải khát phổ biến toàn thế giới', 'Ly', 8000, 0.87, 15000, 150, 140, 0, 39, 0, 'pepsi.png', 0, 'active'),
(22, 'SP022', 7, 'Trà sữa Chicken Joy', 'NuocUong', 'Trà sữa trân châu đậm vị, béo ngậy', 'Ly', 20000, 0.75, 35000, 30, 250, 2, 45, 8, 'trasua.png', 1, 'active');

CREATE TABLE `import_receipt_details` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `receipt_id` INT(11) NOT NULL,               
  `product_id` INT(11) NOT NULL,                
  `quantity` INT(11) NOT NULL,                  
  `import_price` DECIMAL(15, 2) NOT NULL,      
  `subtotal` DECIMAL(15, 2) GENERATED ALWAYS AS (`quantity` * `import_price`) STORED,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`receipt_id`) REFERENCES `import_receipts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO `import_receipt_details` (`receipt_id`, `product_id`, `quantity`, `import_price`) VALUES
(1, 1, 50, 50000.00), (1, 6, 20, 45000.00), (1, 12, 30, 20000.00), (1, 16, 10, 15000.00),
(2, 2, 30, 70000.00), (2, 8, 10, 50000.00), (2, 14, 30, 20000.00),
(3, 4, 15, 200000.00), (3, 3, 20, 50000.00), (3, 9, 25, 40000.00),
(4, 1, 20, 45000.00), (4, 3, 15, 40333.33),
(5, 1, 50, 50000.00), (5, 2, 30, 70000.00),  
(6, 6, 40, 45000.00), (6, 7, 50, 30000.00),  
(7, 12, 100, 20000.00), (7, 13, 80, 25000.00), 
(8, 15, 200, 8000.00), (8, 21, 200, 8000.00), 
(9, 9, 50, 40000.00), (9, 11, 40, 45000.00), 
(10, 3, 60, 50000.00), 
(11, 4, 20, 200000.00), (11, 5, 100, 25000.00),
(12, 8, 30, 50000.00), (12, 18, 40, 40000.00),
(13, 14, 100, 20000.00), (13, 16, 100, 15000.00),
(14, 17, 50, 25000.00), (14, 19, 50, 25000.00), 
(15, 1, 60, 50000.00), (15, 6, 40, 45000.00),  
(16, 10, 30, 60000.00), (16, 11, 20, 45000.00), 
(17, 12, 150, 20000.00), (17, 21, 300, 8000.00), 
(18, 4, 25, 200000.00), (18, 17, 100, 25000.00),
(19, 2, 40, 70000.00), (19, 18, 50, 40000.00), (19, 15, 100, 8000.00), 
(20, 5, 100, 25000.00), (20, 20, 50, 20000.00);

CREATE TABLE `orders` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `receiver_name` VARCHAR(255) NOT NULL, 
  `total_price` DECIMAL(15, 2) NOT NULL,
  `order_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
  `payment_method` ENUM('cod', 'banking', 'online') DEFAULT 'cod',
  `address` TEXT NOT NULL,                
  `phone` VARCHAR(20) NOT NULL,           
  `order_note` TEXT,                      
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB;

CREATE TABLE `order_details` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `price_at_purchase` DECIMAL(15, 2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB;

CREATE TABLE `cart` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB;