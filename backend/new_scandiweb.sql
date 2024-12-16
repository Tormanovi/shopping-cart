-- Table structure for table `cart`
DROP TABLE IF EXISTS `cart`;
CREATE TABLE `cart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` varchar(191) NOT NULL, -- Reduced length for compatibility
  `quantity` int NOT NULL,
  `attributes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`, `attributes`(191)) -- Limited index length
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `order_products`
CREATE TABLE `order_products` (
  `order_id` varchar(191) NOT NULL, -- Reduced length for compatibility
  `product_id` varchar(191) NOT NULL, -- Reduced length for compatibility
  PRIMARY KEY (`order_id`, `product_id`), -- Limited combined key length
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_products_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_products_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `orders`
CREATE TABLE `orders` (
  `id` varchar(191) NOT NULL, -- Reduced length for compatibility
  `total` float NOT NULL,
  `currency` varchar(10) NOT NULL,
  `status` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `photos`
CREATE TABLE `photos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` varchar(191) NOT NULL, -- Reduced length for compatibility
  `photo_url` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `photos_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `product_attributes`
CREATE TABLE `product_attributes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` varchar(191) NOT NULL, -- Reduced length for compatibility
  `attribute_name` varchar(191) NOT NULL, -- Reduced length for compatibility
  `attribute_value` varchar(191) NOT NULL, -- Reduced length for compatibility
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_attributes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `products`
CREATE TABLE `products` (
  `id` varchar(191) NOT NULL, -- Reduced length for compatibility
  `name` varchar(191) NOT NULL, -- Reduced length for compatibility
  `category` varchar(191) NOT NULL, -- Reduced length for compatibility
  `in_stock` tinyint(1) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `currency_symbol` varchar(10) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
