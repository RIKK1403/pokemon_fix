-- RK.POKE Multi-User DB Setup for Laragon MySQL
-- Run in phpMyAdmin or mysql -u root -p

CREATE DATABASE IF NOT EXISTS `pokemon` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pokemon`;

-- Users (from localStorage 'rkpoke_users')
CREATE TABLE `users` (
  `id` VARCHAR(36) PRIMARY KEY,
  `username` VARCHAR(20) UNIQUE NOT NULL,
  `fullname` VARCHAR(50),
  `email` VARCHAR(100),
  `whatsapp` VARCHAR(15),
  `password_hash` VARCHAR(64) NOT NULL,
  `join_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `last_login` DATETIME NULL,
  `is_active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- Listings (from 'rkpoke_all_listings')
CREATE TABLE `listings` (
  `id` VARCHAR(36) PRIMARY KEY,
  `type` ENUM('direct','auction') NOT NULL,
  `card_name` VARCHAR(100) NOT NULL,
  `set` VARCHAR(50),
  `rarity` VARCHAR(20),
  `condition` VARCHAR(20),
  `price` DECIMAL(12,0) NULL, -- direct
  `start_price` DECIMAL(12,0) NULL, -- auction
  `min_bid_increment` DECIMAL(12,0) DEFAULT 10000,
  `buy_now_price` DECIMAL(12,0) NULL,
  `end_time` DATETIME NULL, -- auction
  `platform` VARCHAR(20),
  `link` TEXT,
  `image` VARCHAR(500),
  `desc` TEXT,
  `seller_name` VARCHAR(50),
  `seller_username` VARCHAR(20),
  `seller_id` VARCHAR(36),
  `date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `bids` JSON NULL,
  INDEX `seller_id` (`seller_id`),
  INDEX `type_endtime` (`type`, `end_time`)
) ENGINE=InnoDB;

-- Reports
CREATE TABLE `reports` (
  `id` VARCHAR(36) PRIMARY KEY,
  `listing_id` VARCHAR(36),
  `reason` TEXT,
  `reporter` VARCHAR(20),
  `date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`listing_id`) REFERENCES `listings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

SELECT 'DB ready! Tables: users, listings, reports' as status;

