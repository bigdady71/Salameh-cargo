USE salameh_cargo;

-- Drop and recreate the admins table
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
    `admin_id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` VARCHAR(20) NOT NULL DEFAULT 'admin',
    `is_active` TINYINT(1) DEFAULT 1,
    `failed_attempts` INT DEFAULT 0,
    `locked_until` DATETIME NULL,
    `last_login_at` DATETIME NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert the admin user
INSERT INTO `admins` (`username`, `password_hash`, `role`, `is_active`) VALUES
('hsyn', '$2y$10$ouxd/6nqFr6TT6vWz5qUGuFLiLA1JGN21B7jaPXoZh7zKYuhiNLO2', 'superadmin', 1);
