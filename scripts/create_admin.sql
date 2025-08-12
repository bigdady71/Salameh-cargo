USE salameh_cargo;

-- Create admins table if it doesn't exist
CREATE TABLE IF NOT EXISTS `admins` (
    `admin_id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` VARCHAR(20) NOT NULL DEFAULT 'admin',
    `is_active` BOOLEAN DEFAULT TRUE,
    `failed_attempts` INT DEFAULT 0,
    `locked_until` DATETIME NULL,
    `last_login_at` DATETIME NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert new admin
-- Username: hsyn
-- Password: tryu123
INSERT INTO `admins` (`username`, `password_hash`, `role`, `is_active`, `failed_attempts`) 
VALUES (
    'hsyn',
    '$2y$10$SIUkDvH6cFDHPqRlXfSs6eVgb8PqrgXIgXDHE/ShYDn/1HJbmE.Ym',
    'superadmin',
    1,
    0
);
