-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `pokemon_fix` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pokemon_fix`;

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(20) UNIQUE NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `fullname` VARCHAR(50),
  `email` VARCHAR(100),
  `whatsapp` VARCHAR(15),
  `join_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listings table
CREATE TABLE IF NOT EXISTS `listings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `type` ENUM('direct', 'auction') NOT NULL,
  `card_name` VARCHAR(100) NOT NULL,
  `set` VARCHAR(50),
  `rarity` VARCHAR(20),
  `condition` VARCHAR(20),
  `price` DECIMAL(12,0) DEFAULT NULL,
  `start_price` DECIMAL(12,0),
  `link` VARCHAR(500),
  `platform` VARCHAR(20),
  `buy_now_price` DECIMAL(12,0) DEFAULT NULL,
  `min_bid_increment` DECIMAL(12,0) DEFAULT 10000,
  `end_time` DATETIME DEFAULT NULL,
  `image` VARCHAR(500),
  `desc` TEXT,
  `bids` JSON DEFAULT NULL,
  `date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample users: usera (pass: 'password123'), userb (pass: 'password123')
INSERT INTO `users` (`username`, `password_hash`, `fullname`, `email`, `whatsapp`) VALUES
('usera', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'User A', 'usera@example.com', '08123456789'),
('userb', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'User B', 'userb@example.com', '08987654321');

-- Indexes for performance
CREATE INDEX `idx_user_listings` ON `listings` (`user_id`);
CREATE INDEX `idx_listing_type_date` ON `listings` (`type`, `date` DESC);

-- Run this in phpMyAdmin/HeidiSQL after creating DB pokemon_fix
-- Credentials: localhost, root, (empty pass), Laragon default DB
