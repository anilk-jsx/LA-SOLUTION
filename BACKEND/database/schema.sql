-- ==============================================================================
-- LA SOLUTION PARTNERSHIP - DATABASE SCHEMA
-- Only maintains inquiries (Leads) and admin_users (Authentication)
-- Character Set: utf8mb4, Collation: utf8mb4_unicode_ci
-- ==============================================================================

-- 1. Inquiries Table (CRM Leads & Partnership Requests)
CREATE TABLE IF NOT EXISTS `inquiries` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `lead_code` VARCHAR(25) NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(35) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `interest_type` VARCHAR(120) NOT NULL,
    `vertical` VARCHAR(180) NOT NULL,
    `location` VARCHAR(180) DEFAULT 'Not specified',
    `message` TEXT DEFAULT NULL,
    `status` ENUM('New', 'Contacted', 'In Discussion', 'MOU Signed', 'Closed', 'Archived') NOT NULL DEFAULT 'New',
    `priority` ENUM('Low', 'Medium', 'High', 'Urgent') NOT NULL DEFAULT 'Medium',
    `notes` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_lead_code` (`lead_code`),
    KEY `idx_inquiries_status` (`status`),
    KEY `idx_inquiries_vertical` (`vertical`),
    KEY `idx_inquiries_interest_type` (`interest_type`),
    KEY `idx_inquiries_created_at` (`created_at`),
    KEY `idx_inquiries_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Admin Users Table (Authentication & Access Control)
CREATE TABLE IF NOT EXISTS `admin_users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(60) NOT NULL,
    `email` VARCHAR(120) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(120) NOT NULL,
    `role` ENUM('superadmin', 'admin', 'viewer') NOT NULL DEFAULT 'admin',
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `auth_token` VARCHAR(64) DEFAULT NULL,
    `token_expires_at` DATETIME DEFAULT NULL,
    `last_login_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_admin_username` (`username`),
    UNIQUE KEY `uk_admin_email` (`email`),
    KEY `idx_admin_token` (`auth_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
