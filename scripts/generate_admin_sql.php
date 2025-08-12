<?php
// Generate password hash
$password = 'tryu123';
$hash = password_hash($password, PASSWORD_DEFAULT);

// Create SQL commands
$sql = "USE salameh_cargo;\n\n";
$sql .= "-- Drop and recreate the admins table\n";
$sql .= "DROP TABLE IF EXISTS `admins`;\n";
$sql .= "CREATE TABLE `admins` (
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
);\n\n";

$sql .= "-- Insert the admin user\n";
$sql .= "INSERT INTO `admins` (`username`, `password_hash`, `role`, `is_active`) VALUES
('hsyn', '" . $hash . "', 'superadmin', 1);\n";

// Save the SQL to a file
file_put_contents(__DIR__ . '/admin_setup.sql', $sql);

// Print the hash for verification
echo "Generated password hash: " . $hash . "\n";
echo "SQL file created: admin_setup.sql\n";
