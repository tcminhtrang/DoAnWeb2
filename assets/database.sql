DROP DATABASE IF EXISTS `chickenjoy`;
CREATE DATABASE `chickenjoy` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `chickenjoy`;

-- ==========================================
-- PHẦN 1: GIỮ NGUYÊN 100% CẤU TRÚC BẢNG CỦA CẬU
-- ==========================================

CREATE TABLE `categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `category_code` VARCHAR(20) NOT NULL UNIQUE, 
  `category_name` VARCHAR(255) NOT NULL,      
  `description` TEXT,                       
  `status` ENUM('active', 'hidden') DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

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

CREATE TABLE `promotions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `discount_percent` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` varchar(20) DEFAULT 'active',
  PRIMARY KEY (`id`)
);

CREATE TABLE `points` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `config_key` varchar(50) NOT NULL,
  `config_name` varchar(255) NOT NULL,
  `config_value` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `config_key` (`config_key`)
);

-- CÁC LỆNH ALTER TABLE GIỮ NGUYÊN THEO CODE CỦA BẠN
ALTER TABLE `users` ADD `points` INT(11) DEFAULT 0 AFTER `role`;
ALTER TABLE `orders` ADD `discount_amount` DECIMAL(15, 2) DEFAULT 0 AFTER `total_price`;
ALTER TABLE `orders` ADD `promo_code` VARCHAR(50) DEFAULT NULL AFTER `discount_amount`;
ALTER TABLE `users` ADD COLUMN `status` ENUM('active', 'locked') DEFAULT 'active';
ALTER TABLE `users` ADD COLUMN `reset_token` VARCHAR(255) DEFAULT NULL, ADD COLUMN `reset_expired` DATETIME DEFAULT NULL;
ALTER TABLE `orders` MODIFY `status` ENUM('pending','confirmed','delivered','cancelled') DEFAULT 'pending';
ALTER TABLE `orders` ADD COLUMN `ward` VARCHAR(255) AFTER `address`;
ALTER TABLE `users` ADD COLUMN `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP;


-- ==========================================
-- PHẦN 2: CHÈN DỮ LIỆU PHONG PHÚ (SAU KHI ĐÃ ALTER TABLE)
-- ==========================================

INSERT INTO `categories` (`id`,`category_code`, `category_name`, `status`) VALUES
(1,'L01', 'Gà Rán', 'active'), (2,'L02', 'Burger', 'active'), (3,'L03', 'Combo ăn trưa', 'hidden'),
(4,'L04', 'Mỳ ý', 'active'), (5,'L05', 'Pizza', 'active'), (6,'L06', 'Món ăn kèm', 'active'),
(7,'L07', 'Nước Uống', 'active'), (8,'L08', 'Tráng miệng', 'active');

INSERT INTO `points` (`config_key`, `config_name`, `config_value`) VALUES 
('money_per_point', 'Số tiền để tích 1 điểm (VNĐ)', 10000);

INSERT INTO `promotions` (`code`, `name`, `discount_percent`, `start_date`, `end_date`, `status`) VALUES
('SINHVIEN', 'Ưu đãi dành cho Học sinh Sinh viên', 20, '2026-01-01', '2026-12-31', 'active'),
('FLASH3', 'Flash Sale cuối tháng 3', 25, '2026-03-20', '2026-03-31', 'active'),
('THU5VUI', 'Thứ 5 Vui vẻ - Ăn thả ga', 15, '2026-03-01', '2026-12-31', 'active'),
('HE2026', 'Chào Hè rực rỡ 2026', 10, '2026-04-01', '2026-06-30', 'active'),
('TET2026', 'Vui Tết Nguyên Đán 2026', 20, '2026-02-01', '2026-02-20', 'locked'),
('VALENTINE', 'Lễ Tình nhân ngọt ngào', 15, '2026-02-10', '2026-02-15', 'locked'),
('QUOCTEPHUNU', 'Mừng ngày Quốc tế Phụ Nữ 8/3', 15, '2026-03-01', '2026-03-10', 'locked');

INSERT INTO `import_receipts` (`id`, `receipt_code`, `import_date`, `total_amount`, `status`) VALUES
(1, 'PN001', '2025-10-15', 4150000.00, 'completed'), (2, 'PN002', '2025-10-25', 3200000.00, 'completed'),
(3, 'PN003', '2025-11-01', 5000000.00, 'completed'), (4, 'PN004', '2025-11-02', 1505000.00, 'completed'),
(5, 'PN005', '2025-11-10', 4600000.00, 'completed'), (6, 'PN006', '2025-11-15', 3300000.00, 'completed'),
(7, 'PN007', '2025-11-20', 4000000.00, 'completed'), (8, 'PN008', '2025-11-25', 3200000.00, 'completed'),
(9, 'PN009', '2025-12-01', 3800000.00, 'completed'), (10, 'PN010', '2025-12-05', 3000000.00, 'completed'),
(11, 'PN011', '2025-12-10', 6500000.00, 'completed'), (12, 'PN012', '2025-12-15', 3100000.00, 'completed'),
(13, 'PN013', '2025-12-20', 3500000.00, 'completed'), (14, 'PN014', '2025-12-25', 2500000.00, 'completed'),
(15, 'PN015', '2026-01-05', 4800000.00, 'completed'), (16, 'PN016', '2026-01-15', 2700000.00, 'completed'), 
(17, 'PN017', '2026-02-02', 5400000.00, 'completed'), (18, 'PN018', '2026-02-20', 7500000.00, 'completed'), 
(19, 'PN019', '2026-03-01', 5600000.00, 'completed'), (20, 'PN020', '2026-03-10', 3500000.00, 'pending');

INSERT INTO `products` (`id`, `product_code`, `category_id`, `product_name`, `category`, `description`, `unit`, `import_price`, `profit_rate`, `price`, `stock`, `calories`, `protein`, `carbs`, `fat`, `image`, `is_new`, `status`) VALUES
(1, 'SP001', 1, 'Gà rán giòn tan', 'GaRan', 'Gà được tẩm ướp vị đặc biệt, chiên giòn vàng ươm', 'Miếng', 50000, 0.78, 89000, 50, 320, 25, 12, 18, 'ga-ran-4.jpg', 1, 'active'),
(2, 'SP002', 1, 'Gà rán Hàn Quốc', 'GaRan', 'Gà rán theo cách Hàn Quốc với sốt đặc trưng', 'Phần', 70000, 0.70, 119000, 30, 350, 23, 15, 20, 'ga-ran-2.jpg', 1, 'active'),
(3, 'SP003', 1, 'Đùi gà giòn', 'GaRan', 'Đùi gà giòn tan, thịt bên trong mọng nước', 'Miếng', 50000, 0.78, 89000, 40, 280, 20, 10, 15, 'ga-ran-3.jpg', 0, 'active'),
(4, 'SP004', 1, 'Combo gia đình', 'GaRan', '8 miếng gà rán đủ loại cho cả gia đình', 'Phần', 200000, 0.49, 299000, 15, 1200, 80, 45, 60, 'ga-ran-1.jpg', 0, 'active'),
(5, 'SP005', 1, 'Cánh gà rán giòn', 'GaRan', 'Cánh gà siêu to khổng lồ giòn rụm', 'Miếng', 25000, 0.80, 45000, 60, 210, 15, 8, 12, 'canhga.png', 0, 'active'),
(6, 'SP007', 2, 'Hamburger Gà Giòn', 'Hamburger', 'Nhân gà chiên xù xốt Mayonnaise', 'Cái', 30000, 0.83, 55000, 25, 420, 20, 38, 18, 'hamburgerga.png', 0, 'active'),
(7, 'SP008', 2, 'Double Burger', 'Hamburger', 'Hai lớp nhân thịt bò cực đã cho người cực đói', 'Cái', 50000, 0.90, 95000, 10, 680, 40, 42, 35, 'hamburgerthit.png', 1, 'active'),
(8, 'SP009', 4, 'Mì Ý sốt bò bằm', 'MiY', 'Mì Ý truyền thống đậm vị Ý, mê ly', 'Phần', 40000, 0.72, 69000, 20, 550, 18, 70, 12, 'miybobam.png', 0, 'active'),
(9, 'SP011', 4, 'Mì Ý sốt kem', 'MiY', 'Vị béo ngậy của kem tươi và nấm', 'Phần', 45000, 0.66, 75000, 15, 620, 15, 60, 30, 'mi-y-2.png', 0, 'active'),
(10, 'SP012', 6, 'Khoai tây chiên', 'KhoaiTay', 'Giòn lâu, vui lâu - món ăn kèm tuyệt vời', 'Phần', 20000, 1.00, 40000, 100, 312, 3, 41, 15, 'khoai-tay-2.jpg', 0, 'active'),
(11, 'SP013', 6, 'Khoai tây lắc phô mai', 'KhoaiTay', 'Khoai tây vàng giòn lắc bột phô mai đặc biệt', 'Phần', 25000, 0.80, 45000, 80, 350, 4, 43, 17, 'khoai-tay-1.jpg', 0, 'active'),
(12, 'SP015', 7, 'Coca Cola mát lạnh', 'NuocUong', 'Nước giải khát có gas cực đã', 'Ly', 8000, 0.87, 15000, 200, 140, 0, 39, 0, 'coca.png', 0, 'active'),
(13, 'SP016', 7, 'Trà đào miếng', 'NuocUong', 'Trà thanh mát kèm miếng đào thơm giòn', 'Ly', 15000, 1.00, 30000, 45, 130, 0, 32, 0, 'Tradao.png', 0, 'active'),
(14, 'SP018', 2, 'Hamburger Tôm', 'Hamburger', 'Nhân tôm tươi giòn rụm, xốt đặc biệt', 'Cái', 40000, 0.62, 65000, 18, 380, 18, 40, 15, 'burgertom.png', 1, 'active'),
(15, 'SP019', 4, 'Mì Ý xốt xúc xích', 'MiY', 'Dành cho các bạn nhỏ với xúc xích đức', 'Phần', 25000, 0.80, 45000, 22, 400, 12, 55, 10, 'miyxucxich.png', 0, 'active'),
(16, 'SP020', 6, 'Khoai tây múi cau', 'KhoaiTay', 'Khoai tây cắt múi cau lạ mắt, giòn tan', 'Phần', 20000, 0.75, 35000, 50, 310, 3, 40, 14, 'khoaitaymuicau.png', 0, 'active'),
(17, 'SP006', 2, 'Hamburger Bò Phô Mai', 'Hamburger', 'Nhân thịt bò Úc nướng và phô Mai tan chảy', 'Cái', 45000, 0.75, 79000, 20, 450, 22, 35, 25, 'hamburger-2.jpg', 0, 'active'),
(18, 'SP010', 4, 'Mì Ý hải sản', 'MiY', 'Xốt cà chua và hải sản tươi ngon', 'Phần', 60000, 0.65, 99000, 12, 500, 20, 65, 10, 'mi-y-1.jpg', 1, 'active'),
(19, 'SP014', 7, 'Nước nha đam', 'NuocUong', 'Nước nha đam lạnh sảng khoái, ít đường', 'Ly', 20000, 0.75, 35000, 40, 120, 0, 30, 0, 'nuoc-.uong.jpg', 0, 'active'),
(20, 'SP017', 1, 'Gà rán cay nồng', 'GaRan', 'Vị cay cực hạn cho tín đồ ăn cay', 'Miếng', 25000, 0.68, 42000, 25, 330, 24, 13, 19, 'ga-ran-cay.jpg', 0, 'active'),
(21, 'SP021', 7, 'Pepsi', 'NuocUong', 'Nước giải khát phổ biến toàn thế giới', 'Ly', 8000, 0.87, 15000, 150, 140, 0, 39, 0, 'pepsi.png', 0, 'active'),
(22, 'SP022', 7, 'Trà sữa Chicken Joy', 'NuocUong', 'Trà sữa trân châu đậm vị, béo ngậy', 'Ly', 20000, 0.75, 35000, 30, 250, 2, 45, 8, 'trasua.png', 1, 'active');

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

-- Thêm Mật khẩu 123456 cho toàn bộ account để dễ test
INSERT INTO `users` (`fullname`, `email`, `password`, `phone`, `gender`, `address`, `role`, `points`, `status`) VALUES
('Quản Trị Viên 1', 'admin@jollibee.vn', '123456', '0999999991', 'nam', 'Trụ sở chính', 'admin', 0, 'active'),
('Quản Trị Viên 2', 'manager@chickenjoy.vn', '123456', '0999999992', 'nu', 'Chi nhánh 2', 'admin', 0, 'active'),
('Kế Toán Trưởng', 'ketoan@chickenjoy.vn', '123456', '0999999993', 'nu', 'Chi nhánh 3', 'admin', 0, 'active'),
('Nguyễn Văn An', 'vana@gmail.com', '123456', '0903456789', 'nam', '123 Đường ABC, Hà Nội', 'user', 20, 'active'),
('Trần Thị Bình', 'thib@gmail.com', '123456', '0778123456', 'nu', '456 Đường DEF, HCM', 'user', 43, 'locked'),
('Lê Minh Cường', 'minhc@gmail.com', '123456', '0369987654', 'nam', '789 Đường GHI, Đà Nẵng', 'user', 71, 'active'),
('Phạm Thị Duyên', 'phamd@gmail.com', '123456', '0912345671', 'nu', '120 Võ Văn Ngân, Thủ Đức', 'user', 150, 'active'),
('Hoàng Văn Em', 'hoange@gmail.com', '123456', '0912345672', 'nam', '45 Lê Văn Việt, Quận 9', 'user', 50, 'active'),
('Đinh Tuấn Phong', 'tuanp@gmail.com', '123456', '0912345673', 'nam', '789 Nguyễn Đình Chiểu, Q3', 'user', 0, 'active'),
('Ngô Bạch Giai', 'bachg@gmail.com', '123456', '0912345674', 'nam', '321 Lũy Bán Bích, Tân Phú', 'user', 200, 'active'),
('Vũ Thị Hoa', 'vuh@gmail.com', '123456', '0912345675', 'nu', '654 Quang Trung, Gò Vấp', 'user', 75, 'active'),
('Bùi Kim Inh', 'kimi@gmail.com', '123456', '0912345676', 'nu', '987 Phạm Văn Đồng', 'user', 300, 'locked'),
('Lý Cường J', 'cuongj@gmail.com', '123456', '0912345677', 'nam', '147 Hậu Giang, Quận 6', 'user', 20, 'active'),
('Tô Ngọc Khánh', 'ngock@gmail.com', '123456', '0912345678', 'nu', '258 Nguyễn Văn Linh, Q7', 'user', 120, 'active'),
('Hồ Trọng Lâm', 'trongl@gmail.com', '123456', '0912345679', 'nam', '369 Tôn Đức Thắng, Q1', 'user', 500, 'active'),
('Châu Mỹ Mẫn', 'mym@gmail.com', '123456', '0912345680', 'nu', '741 Lê Đại Hành, Q11', 'user', 45, 'active'),
('Đặng Nam N', 'namn@gmail.com', '123456', '0912345681', 'nam', '852 Ba Tháng Hai, Q10', 'user', 90, 'active'),
('Phan Thúy Oanh', 'thuyo@gmail.com', '123456', '0912345682', 'nu', '963 Trần Hưng Đạo, Q5', 'user', 10, 'active'),
('Dương Long Phát', 'longp@gmail.com', '123456', '0912345683', 'nam', '159 NTMK, Quận 1', 'user', 0, 'locked'),
('Tống Quỳnh Q', 'quynhq@gmail.com', '123456', '0912345684', 'nu', '753 Điện Biên Phủ', 'user', 350, 'active'),
('Trịnh Duy R', 'duyr@gmail.com', '123456', '0912345685', 'nam', '951 Pasteur, Quận 3', 'user', 80, 'active'),
('Đào Ly Sương', 'lys@gmail.com', '123456', '0912345686', 'nu', '357 An Dương Vương', 'user', 210, 'active'),
('Mai Tuấn Tú', 'tuant@gmail.com', '123456', '0912345687', 'nam', '456 Kinh Dương Vương', 'user', 60, 'active'),
('Thái Lan W', 'lanw@gmail.com', '123456', '0912345690', 'nu', '321 Trường Chinh, Tân Bình', 'user', 25, 'active');

INSERT INTO `orders` (`id`, `user_id`, `receiver_name`, `total_price`, `discount_amount`, `promo_code`, `order_date`, `status`, `payment_method`, `address`, `ward`, `phone`, `order_note`) VALUES
(1, 4, 'Nguyễn Văn An', 104000, 0, NULL, '2026-02-02 10:15:00', 'delivered', 'cod', '123 Đường ABC', 'Phường 1', '0903456789', NULL),
(2, 5, 'Trần Thị Bình', 190400, 47600, 'SINHVIEN', '2026-02-03 11:30:00', 'delivered', 'banking', '456 Đường DEF', 'Phường 5', '0778123456', 'Giao trong giờ hành chính'),
(3, 6, 'Lê Minh Cường', 299000, 0, NULL, '2026-02-04 12:45:00', 'delivered', 'online', '789 Đường GHI', 'Phường 7', '0369987654', NULL),
(4, 7, 'Phạm Thị Duyên', 152000, 38000, 'SINHVIEN', '2026-02-05 13:20:00', 'delivered', 'cod', '120 Võ Văn Ngân', 'Phường Bình Thọ', '0912345671', 'Đang đói giao lẹ'),
(5, 8, 'Hoàng Văn Em', 148000, 0, NULL, '2026-02-06 18:00:00', 'delivered', 'cod', '45 Lê Văn Việt', 'Phường Hiệp Phú', '0912345672', NULL),
(6, 9, 'Đinh Tuấn Phong', 96000, 24000, 'SINHVIEN', '2026-02-07 19:10:00', 'delivered', 'banking', '789 Nguyễn Đình Chiểu', 'Phường 6', '0912345673', NULL),
(7, 10, 'Ngô Bạch Giai', 134000, 0, NULL, '2026-02-08 20:05:00', 'delivered', 'cod', '321 Lũy Bán Bích', 'Phường Hòa Thạnh', '0912345674', NULL),
(8, 11, 'Vũ Thị Hoa', 56000, 14000, 'SINHVIEN', '2026-02-09 09:30:00', 'delivered', 'banking', '654 Quang Trung', 'Phường 11', '0912345675', 'Xin thêm tương cà'),
(9, 12, 'Bùi Kim Inh', 198000, 0, NULL, '2026-02-10 11:45:00', 'delivered', 'online', '987 Phạm Văn Đồng', 'Phường 3', '0912345676', NULL),
(10, 13, 'Lý Cường J', 72000, 18000, 'SINHVIEN', '2026-02-11 12:15:00', 'delivered', 'cod', '147 Hậu Giang', 'Phường 5', '0912345677', 'Khách đổi ý không mua nữa'),
(11, 14, 'Tô Ngọc Khánh', 104000, 0, NULL, '2026-02-12 18:30:00', 'delivered', 'banking', '258 Nguyễn Văn Linh', 'Phường Tân Thuận Tây', '0912345678', NULL),
(12, 15, 'Hồ Trọng Lâm', 190400, 47600, 'SINHVIEN', '2026-02-13 19:40:00', 'delivered', 'cod', '369 Tôn Đức Thắng', 'Phường Bến Nghé', '0912345679', NULL),
(13, 16, 'Châu Mỹ Mẫn', 299000, 0, NULL, '2026-02-14 20:10:00', 'delivered', 'online', '741 Lê Đại Hành', 'Phường 15', '0912345680', 'Giao sau 6h tối'),
(14, 17, 'Đặng Nam N', 152000, 38000, 'SINHVIEN', '2026-02-15 11:20:00', 'delivered', 'cod', '852 Ba Tháng Hai', 'Phường 14', '0912345681', 'Lấy giùm hóa đơn đỏ'),
(15, 18, 'Phan Thúy Oanh', 148000, 0, NULL, '2026-02-16 12:05:00', 'delivered', 'banking', '963 Trần Hưng Đạo', 'Phường 7', '0912345682', NULL),
(16, 19, 'Dương Long Phát', 96000, 24000, 'SINHVIEN', '2026-02-17 14:50:00', 'delivered', 'cod', '159 NTMK', 'Phường Bến Thành', '0912345683', 'Đừng bấm chuông, con đang ngủ'),
(17, 20, 'Tống Quỳnh Q', 134000, 0, NULL, '2026-02-18 16:30:00', 'cancelled', 'online', '753 Điện Biên Phủ', 'Phường 22', '0912345684', 'Sai địa chỉ giao hàng'),
(18, 21, 'Trịnh Duy R', 56000, 14000, 'SINHVIEN', '2026-02-19 08:45:00', 'delivered', 'banking', '951 Pasteur', 'Phường 8', '0912345685', NULL),
(19, 22, 'Đào Ly Sương', 198000, 0, NULL, '2026-02-20 19:15:00', 'delivered', 'cod', '357 An Dương Vương', 'Phường 3', '0912345686', NULL),
(20, 23, 'Mai Tuấn Tú', 72000, 18000, 'SINHVIEN', '2026-02-21 12:25:00', 'delivered', 'online', '456 Kinh Dương Vương', 'Phường An Lạc A', '0912345687', 'Không hành tây'),
(21, 24, 'Thái Lan W', 104000, 0, NULL, '2026-02-22 09:10:00', 'delivered', 'cod', '321 Trường Chinh', 'Phường 14', '0912345690', NULL),
(22, 4, 'Nguyễn Văn An', 190400, 47600, 'SINHVIEN', '2026-02-23 11:30:00', 'delivered', 'banking', '123 Đường ABC', 'Phường 1', '0903456789', NULL),
(23, 5, 'Trần Thị Bình', 299000, 0, NULL, '2026-02-24 14:15:00', 'delivered', 'online', '456 Đường DEF', 'Phường 5', '0778123456', NULL),
(24, 6, 'Lê Minh Cường', 152000, 38000, 'SINHVIEN', '2026-02-25 10:45:00', 'cancelled', 'cod', '789 Đường GHI', 'Phường 7', '0369987654', 'Khách báo bận'),
(25, 7, 'Phạm Thị Duyên', 148000, 0, NULL, '2026-02-26 16:20:00', 'delivered', 'banking', '120 Võ Văn Ngân', 'Phường Bình Thọ', '0912345671', NULL),
(26, 8, 'Hoàng Văn Em', 96000, 24000, 'SINHVIEN', '2026-02-27 18:10:00', 'delivered', 'cod', '45 Lê Văn Việt', 'Phường Hiệp Phú', '0912345672', NULL),
(27, 9, 'Đinh Tuấn Phong', 134000, 0, NULL, '2026-02-28 10:30:00', 'delivered', 'online', '789 Nguyễn Đình Chiểu', 'Phường 6', '0912345673', NULL),
(28, 10, 'Ngô Bạch Giai', 59500, 10500, 'THU5VUI', '2026-03-01 18:45:00', 'delivered', 'cod', '321 Lũy Bán Bích', 'Phường Hòa Thạnh', '0912345674', 'Gọi trước khi tới'),
(29, 11, 'Vũ Thị Hoa', 158400, 39600, 'SINHVIEN', '2026-03-02 12:15:00', 'delivered', 'banking', '654 Quang Trung', 'Phường 11', '0912345675', NULL),
(30, 12, 'Bùi Kim Inh', 76500, 13500, 'THU5VUI', '2026-03-03 19:00:00', 'delivered', 'cod', '987 Phạm Văn Đồng', 'Phường 3', '0912345676', NULL),
(31, 13, 'Lý Cường J', 104000, 0, NULL, '2026-03-04 11:30:00', 'delivered', 'online', '147 Hậu Giang', 'Phường 5', '0912345677', NULL),
(32, 14, 'Tô Ngọc Khánh', 202300, 35700, 'THU5VUI', '2026-03-05 13:20:00', 'delivered', 'cod', '258 Nguyễn Văn Linh', 'Phường Tân Thuận Tây', '0912345678', NULL),
(33, 15, 'Hồ Trọng Lâm', 239200, 59800, 'SINHVIEN', '2026-03-06 09:10:00', 'delivered', 'banking', '369 Tôn Đức Thắng', 'Phường Bến Nghé', '0912345679', 'Để trước cửa nhà'),
(34, 16, 'Châu Mỹ Mẫn', 190000, 0, NULL, '2026-03-07 18:30:00', 'delivered', 'cod', '741 Lê Đại Hành', 'Phường 15', '0912345680', NULL),
(35, 17, 'Đặng Nam N', 125800, 22200, 'THU5VUI', '2026-03-08 14:40:00', 'delivered', 'online', '852 Ba Tháng Hai', 'Phường 14', '0912345681', NULL),
(36, 18, 'Phan Thúy Oanh', 96000, 24000, 'SINHVIEN', '2026-03-09 17:55:00', 'delivered', 'cod', '963 Trần Hưng Đạo', 'Phường 7', '0912345682', NULL),
(37, 19, 'Dương Long Phát', 113900, 20100, 'THU5VUI', '2026-03-10 20:20:00', 'cancelled', 'banking', '159 NTMK', 'Phường Bến Thành', '0912345683', 'Sai địa chỉ giao hàng'),
(38, 20, 'Tống Quỳnh Q', 70000, 0, NULL, '2026-03-11 11:05:00', 'delivered', 'cod', '753 Điện Biên Phủ', 'Phường 22', '0912345684', NULL),
(39, 21, 'Trịnh Duy R', 158400, 39600, 'SINHVIEN', '2026-03-12 12:50:00', 'delivered', 'online', '951 Pasteur', 'Phường 8', '0912345685', NULL),
(40, 22, 'Đào Ly Sương', 76500, 13500, 'THU5VUI', '2026-03-13 16:30:00', 'delivered', 'cod', '357 An Dương Vương', 'Phường 3', '0912345686', NULL),
(41, 23, 'Mai Tuấn Tú', 104000, 0, NULL, '2026-03-14 08:45:00', 'delivered', 'banking', '456 Kinh Dương Vương', 'Phường An Lạc A', '0912345687', NULL),
(42, 24, 'Thái Lan W', 202300, 35700, 'THU5VUI', '2026-03-15 19:15:00', 'delivered', 'cod', '321 Trường Chinh', 'Phường 14', '0912345690', NULL),
(43, 4, 'Nguyễn Văn An', 239200, 59800, 'SINHVIEN', '2026-03-16 12:25:00', 'delivered', 'online', '123 Đường ABC', 'Phường 1', '0903456789', 'Xin nhiều tương ớt'),
(44, 5, 'Trần Thị Bình', 190000, 0, NULL, '2026-03-17 15:40:00', 'cancelled', 'cod', '456 Đường DEF', 'Phường 5', '0778123456', 'Hết hàng món khách chọn'),
(45, 6, 'Lê Minh Cường', 125800, 22200, 'THU5VUI', '2026-03-18 10:10:00', 'delivered', 'banking', '789 Đường GHI', 'Phường 7', '0369987654', NULL),
(46, 7, 'Phạm Thị Duyên', 96000, 24000, 'SINHVIEN', '2026-03-19 18:00:00', 'confirmed', 'cod', '120 Võ Văn Ngân', 'Phường Bình Thọ', '0912345671', 'Giao cổng phụ chung cư'),
(47, 8, 'Hoàng Văn Em', 100500, 33500, 'FLASH3', '2026-03-20 12:10:00', 'confirmed', 'online', '45 Lê Văn Việt', 'Phường Hiệp Phú', '0912345672', 'Cho em thêm đá'),
(48, 9, 'Đinh Tuấn Phong', 59500, 10500, 'THU5VUI', '2026-03-21 09:00:00', 'pending', 'cod', '789 Nguyễn Đình Chiểu', 'Phường 6', '0912345673', NULL),
(49, 10, 'Ngô Bạch Giai', 148500, 49500, 'FLASH3', '2026-03-22 18:20:00', 'pending', 'banking', '321 Lũy Bán Bích', 'Phường Hòa Thạnh', '0912345674', NULL),
(50, 11, 'Vũ Thị Hoa', 72000, 18000, 'SINHVIEN', '2026-03-23 11:45:00', 'pending', 'cod', '654 Quang Trung', 'Phường 11', '0912345675', 'Giao tới gọi liền'),
(51, 10, 'Ngô Bạch Giai', 269250, 89750, 'FLASH3', '2026-03-24 10:15:00', 'delivered', 'online', '321 Lũy Bán Bích', 'Phường Hòa Thạnh', '0912345674', 'Giao tới cổng công ty gọi em'),
(52, 15, 'Hồ Trọng Lâm', 188000, 47000, 'SINHVIEN', '2026-03-24 12:30:00', 'confirmed', 'banking', '369 Tôn Đức Thắng', 'Phường Bến Nghé', '0912345679', 'Ăn trưa, giao đúng giờ giúp mình'),
(53, 5, 'Trần Thị Bình', 154000, 0, NULL, '2026-03-24 14:45:00', 'pending', 'cod', '456 Đường DEF', 'Phường 5', '0778123456', NULL),
(54, 21, 'Trịnh Duy R', 246000, 82000, 'FLASH3', '2026-03-24 18:20:00', 'processing', 'online', '951 Pasteur', 'Phường 8', '0912345685', 'Lấy nhiều tương cà, không lấy tương ớt'),
(55, 8, 'Hoàng Văn Em', 113900, 20100, 'THU5VUI', '2026-03-24 20:05:00', 'delivered', 'cod', '45 Lê Văn Việt', 'Phường Hiệp Phú', '0912345672', 'Cho thêm đá viên');

INSERT INTO `order_details` (`order_id`, `product_id`, `quantity`, `price_at_purchase`) VALUES
(1, 1, 1, 89000), (1, 12, 1, 15000),
(2, 2, 2, 119000),
(3, 4, 1, 299000),
(4, 7, 2, 95000),
(5, 8, 1, 69000), (5, 17, 1, 79000),
(6, 10, 3, 40000),
(7, 3, 1, 89000), (7, 11, 1, 45000),
(8, 6, 1, 55000), (8, 12, 1, 15000),
(9, 18, 2, 99000),
(10, 5, 1, 45000), (10, 15, 1, 45000),
(11, 1, 1, 89000), (11, 12, 1, 15000),
(12, 2, 2, 119000),
(13, 4, 1, 299000),
(14, 7, 2, 95000),
(15, 8, 1, 69000), (15, 17, 1, 79000),
(16, 10, 3, 40000),
(17, 3, 1, 89000), (17, 11, 1, 45000),
(18, 6, 1, 55000), (18, 12, 1, 15000),
(19, 18, 2, 99000),
(20, 5, 1, 45000), (20, 15, 1, 45000),
(21, 1, 1, 89000), (21, 12, 1, 15000),
(22, 2, 2, 119000),
(23, 4, 1, 299000),
(24, 7, 2, 95000),
(25, 8, 1, 69000), (25, 17, 1, 79000),
(26, 10, 3, 40000),
(27, 3, 1, 89000), (27, 11, 1, 45000),
(28, 6, 1, 55000), (28, 12, 1, 15000),
(29, 18, 2, 99000),
(30, 5, 1, 45000), (30, 15, 1, 45000),
(31, 1, 1, 89000), (31, 12, 1, 15000),
(32, 2, 2, 119000),
(33, 4, 1, 299000),
(34, 7, 2, 95000),
(35, 8, 1, 69000), (35, 17, 1, 79000),
(36, 10, 3, 40000),
(37, 3, 1, 89000), (37, 11, 1, 45000),
(38, 6, 1, 55000), (38, 12, 1, 15000),
(39, 18, 2, 99000),
(40, 5, 1, 45000), (40, 15, 1, 45000),
(41, 1, 1, 89000), (41, 12, 1, 15000),
(42, 2, 2, 119000),
(43, 4, 1, 299000),
(44, 7, 2, 95000),
(45, 8, 1, 69000), (45, 17, 1, 79000),
(46, 10, 3, 40000),
(47, 3, 1, 89000), (47, 11, 1, 45000),
(48, 6, 1, 55000), (48, 12, 1, 15000),
(49, 18, 2, 99000),
(50, 5, 1, 45000), (50, 15, 1, 45000),
(51, 4, 1, 299000), (51, 12, 4, 15000),
(52, 7, 2, 95000), (52, 11, 1, 45000),
(53, 2, 1, 119000), (53, 22, 1, 35000),
(54, 9, 2, 75000), (54, 1, 2, 89000),
(55, 18, 1, 99000), (55, 19, 1, 35000);

INSERT INTO `cart` (`user_id`, `product_id`, `quantity`) VALUES
(4, 1, 2), (4, 12, 1), (4, 10, 1), 
(6, 4, 1), (6, 21, 2),             
(7, 9, 1), (7, 13, 1),             
(8, 7, 2), (8, 10, 2),             
(1, 2, 1), (1, 12, 1);