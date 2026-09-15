-- Part 3 Database Migration
-- Coupons, Referrals, Support Tickets, Order Sync Cron, and Settings

ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `last_checked_at` TIMESTAMP NULL DEFAULT NULL AFTER `remains`;
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `coupon_id` INT UNSIGNED NULL DEFAULT NULL AFTER `charge`;
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `discount_amount` DECIMAL(14, 4) DEFAULT 0.0000 AFTER `coupon_id`;

ALTER TABLE `coupons` ADD COLUMN IF NOT EXISTS `min_order_amount` DECIMAL(14, 4) DEFAULT 0.0000 AFTER `amount`;
ALTER TABLE `coupons` ADD COLUMN IF NOT EXISTS `max_discount` DECIMAL(14, 4) DEFAULT NULL AFTER `min_order_amount`;
ALTER TABLE `coupons` ADD COLUMN IF NOT EXISTS `start_date` TIMESTAMP NULL DEFAULT NULL AFTER `max_discount`;
ALTER TABLE `coupons` ADD COLUMN IF NOT EXISTS `per_user_limit` INT UNSIGNED DEFAULT 1 AFTER `max_uses`;

ALTER TABLE `coupon_usage` ADD COLUMN IF NOT EXISTS `order_id` INT UNSIGNED NULL AFTER `user_id`;

ALTER TABLE `tickets` ADD COLUMN IF NOT EXISTS `category` VARCHAR(100) NULL AFTER `subject`;
ALTER TABLE `tickets` ADD COLUMN IF NOT EXISTS `assigned_to` INT UNSIGNED NULL AFTER `status`;

CREATE TABLE IF NOT EXISTS `referral_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `referrer_id` INT UNSIGNED NOT NULL,
    `referred_id` INT UNSIGNED NOT NULL,
    `qualifying_event` VARCHAR(50) NOT NULL,
    `event_reference` VARCHAR(100) NULL,
    `order_amount` DECIMAL(14, 4) NOT NULL,
    `commission_rate` DECIMAL(6, 2) NOT NULL,
    `reward_amount` DECIMAL(14, 4) NOT NULL,
    `status` VARCHAR(50) DEFAULT 'credited',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_refl_referrer` (`referrer_id`),
    INDEX `idx_refl_referred` (`referred_id`),
    CONSTRAINT `fk_refl_referrer` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_refl_referred` FOREIGN KEY (`referred_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
