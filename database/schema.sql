-- Salameh Cargo Database Schema
-- Version: 2025-08-10

CREATE DATABASE
IF NOT EXISTS salameh_cargo CHARACTER
SET utf8mb4
COLLATE utf8mb4_unicode_ci;
USE salameh_cargo;

-- Users table
CREATE TABLE users
(
    user_id INT
    AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR
    (255) NOT NULL,
    email VARCHAR
    (255) NULL,
    phone VARCHAR
    (50) NOT NULL UNIQUE,
    shipping_code VARCHAR
    (100) UNIQUE NULL,
    address VARCHAR
    (255) NULL,
    country VARCHAR
    (100) NULL,
    id_number VARCHAR
    (100) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

    -- Shipments table
    CREATE TABLE shipments
    (
        shipment_id INT
        AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    tracking_number VARCHAR
        (100) UNIQUE,
    container_number VARCHAR
        (100) NULL COMMENT 'Container number for containerised cargo.',
    bl_number VARCHAR
        (100) NULL COMMENT 'Bill of lading number used by carriers.',
    shipping_code VARCHAR
        (100) NULL COMMENT 'Internal shipping code or alternative identifier.',
    product_description TEXT NULL,
    cbm DECIMAL
        (10,2) DEFAULT 0,
    cartons INT DEFAULT 0,
    weight DECIMAL
        (10,2) DEFAULT 0,
    gross_weight DECIMAL
        (10,2) DEFAULT 0,
    total_amount DECIMAL
        (10,2) DEFAULT 0,
    status VARCHAR
        (50) DEFAULT 'En Route',
    origin VARCHAR
        (100) NULL,
    destination VARCHAR
        (100) NULL,
    pickup_date DATETIME NULL,
    delivery_date DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON
        UPDATE CURRENT_TIMESTAMP COMMENT 'Timestamp of the last shipment status update.',
    FOREIGN KEY
        (user_id) REFERENCES users
        (user_id) ON
        DELETE
        SET NULL
        ,
    INDEX idx_user_id
        (user_id),
    INDEX idx_tracking_number
        (tracking_number),
    INDEX idx_container_number
        (container_number),
    INDEX idx_bl_number
        (bl_number),
    INDEX idx_shipping_code
        (shipping_code),
    INDEX idx_status
        (status)
);

        -- Shipment scrapes table
        CREATE TABLE shipment_scrapes
        (
            scrape_id INT
            AUTO_INCREMENT PRIMARY KEY,
    shipment_id INT,
    source_site VARCHAR
            (50),
    status VARCHAR
            (100),
    status_raw TEXT,
    scrape_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY
            (shipment_id) REFERENCES shipments
            (shipment_id) ON
            DELETE CASCADE,
    INDEX idx_shipment_id (shipment_id),
    INDEX idx_source_site
            (source_site),
    INDEX idx_scrape_time
            (scrape_time)
);

            -- Admins table with enhanced security
            CREATE TABLE admins
            (
                admin_id INT
                AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR
                (100) UNIQUE,
    password_hash VARCHAR
                (255) COMMENT 'Stored using password_hash() with bcrypt/Argon2',
    role VARCHAR
                (50) DEFAULT 'manager',
    is_active TINYINT
                (1) DEFAULT 1 COMMENT 'Flag to activate or deactivate admin accounts.',
    failed_attempts TINYINT UNSIGNED DEFAULT 0 COMMENT 'Number of failed login attempts.',
    locked_until DATETIME NULL COMMENT 'Datetime until which the account is locked after multiple failed attempts.',
    last_login_at DATETIME NULL COMMENT 'Timestamp of last successful login.',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

                -- Logs table
                CREATE TABLE logs
                (
                    log_id INT
                    AUTO_INCREMENT PRIMARY KEY,
    action_type VARCHAR
                    (50),
    actor_id INT COMMENT 'positive=user_id, negative=admin_id',
    related_shipment_id INT NULL,
    details TEXT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_actor_id
                    (actor_id),
    INDEX idx_related_shipment_id
                    (related_shipment_id),
    INDEX idx_timestamp
                    (timestamp)
);
