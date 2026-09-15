-- SEED DATA FOR SMM PANEL PART 1

-- 1. Insert Initial Roles
INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `permissions`) VALUES
(1, 'superadmin', 'Super Administrator', 'Full system access and control', '["*"]'),
(2, 'admin', 'Administrator', 'Administrative access to orders, users, services', '["manage_users","manage_services","manage_orders"]'),
(3, 'support', 'Support Staff', 'Access to support tickets and user assistance', '["manage_tickets"]')
ON DUPLICATE KEY UPDATE `display_name` = VALUES(`display_name`);

-- 2. Insert Default Admin User
-- Email: admin@smmpanel.local | Password: Admin@123456
INSERT INTO `admins` (`id`, `role_id`, `name`, `email`, `password`, `status`) VALUES
(1, 1, 'System Administrator', 'admin@smmpanel.local', '$2y$10$pqYWv4R8oKKu1OHabkcd/Ol44180u6K2w4168k7IUJisjdjhGtYDe', 'active')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 3. Insert Default Settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('site_name', 'SMM Elite', 'general'),
('site_description', 'Premium Social Media Marketing Growth Services and Management Platform', 'general'),
('support_email', 'support@smmpanel.local', 'general'),
('default_currency', 'USD', 'localization'),
('currency_symbol', '$', 'localization'),
('maintenance_mode', 'disabled', 'system'),
('user_registration', 'enabled', 'auth'),
('email_verification', 'optional', 'auth'),
('referral_system', 'enabled', 'affiliates'),
('referral_commission_rate', '5.00', 'affiliates')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- 4. Insert Default Currency
INSERT INTO `currencies` (`code`, `name`, `symbol`, `rate`, `is_default`, `status`) VALUES
('USD', 'US Dollar', '$', 1.000000, 1, 'active'),
('EUR', 'Euro', '€', 0.920000, 0, 'active'),
('GBP', 'British Pound', '£', 0.780000, 0, 'active')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 5. Insert Demo User
-- Email: user@smmpanel.local | Password: User@123456
INSERT INTO `users` (`id`, `name`, `email`, `password`, `referral_code`, `email_verified_at`, `status`) VALUES
(1, 'Demo Customer', 'user@smmpanel.local', '$2y$10$Xz0y6C.xDnifiQhMU08SNeIrJGdLog1YNOS8YNuu16CMQ7s9DP8be', 'DEMO2026', NOW(), 'active')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Insert User Wallet
INSERT INTO `wallets` (`id`, `user_id`, `balance`, `spent`, `currency`) VALUES
(1, 1, 50.0000, 12.5000, 'USD')
ON DUPLICATE KEY UPDATE `balance` = VALUES(`balance`);

-- 6. Insert Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `sort_order`, `status`) VALUES
(1, 'Instagram Growth & Engagement', 'instagram-growth', 'instagram', 1, 'active'),
(2, 'YouTube Views & Subscribers', 'youtube-growth', 'youtube', 2, 'active'),
(3, 'TikTok Followers & Likes', 'tiktok-services', 'video', 3, 'active'),
(4, 'Telegram Members & Channel Boost', 'telegram-members', 'send', 4, 'active'),
(5, 'Twitter / X Retweets & Impressions', 'twitter-x-growth', 'twitter', 5, 'active')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 7. Insert Services
INSERT INTO `services` (`id`, `category_id`, `name`, `service_type`, `price_per_k`, `min_quantity`, `max_quantity`, `description`, `dripfeed`, `refill`, `cancel_allowed`, `status`, `sort_order`) VALUES
(1, 1, 'Instagram Followers [High Quality - Non Drop - Instant - 30D Refill]', 'default', 1.2500, 50, 100000, 'Guaranteed real-profile Instagram followers. 30 days refill button included. Starts within 5-15 minutes.', 1, 1, 1, 'active', 1),
(2, 1, 'Instagram Likes [Super Fast - Real HQ - Instant Start]', 'default', 0.4500, 50, 50000, 'High-velocity instant post likes. Instant delivery speed 20k/hour. Safe for algorithmic explore page.', 0, 0, 1, 'active', 2),
(3, 2, 'YouTube High Retention Views [Speed 50K/Day - Lifetime Guarantee]', 'default', 2.8000, 500, 1000000, 'Organic user impression views with 3-5 min average watch duration. Safe for monetized channels.', 1, 1, 0, 'active', 3),
(4, 2, 'YouTube Monetization Watch Hours [Non-Drop - Real Organic]', 'package', 14.5000, 100, 4000, 'Watch time hours for meeting the 4,000 hour YouTube partner requirement. Video length > 15 mins recommended.', 0, 1, 0, 'active', 4),
(5, 3, 'TikTok Global Followers [Instant Start - Real Accounts]', 'default', 1.8500, 100, 250000, 'Real international TikTok accounts. Safe and non-drop with high retention.', 1, 1, 1, 'active', 5),
(6, 3, 'TikTok Video Likes [Fast Server - No Password Required]', 'default', 0.3500, 100, 500000, 'Instant likes for TikTok clips and videos. Starts automatically upon order placement.', 0, 0, 1, 'active', 6),
(7, 4, 'Telegram Channel Members [0-5% Drop - Real Active Profiles]', 'default', 1.1000, 100, 100000, 'Real looking channel subscribers for public/private Telegram channels. Low drop rate.', 0, 1, 1, 'active', 7),
(8, 5, 'Twitter / X Impressions & Engagements [Worldwide Real Active]', 'default', 0.6500, 500, 2000000, 'Boost viral metric visibility and tweet impressions. Safe for business and creator profiles.', 0, 0, 1, 'active', 8)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 8. Insert Sample Notifications for Demo User
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 1, 'Welcome to SMM Elite!', 'Your account has been set up successfully. We added $50.00 bonus balance to your wallet to get started.', 'success', 0, NOW()),
(2, 1, 'Special 10% Crypto Deposit Bonus Active', 'Get an additional 10% bonus credit on all deposits made via USDT / Bitcoin this week.', 'info', 0, NOW())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);
