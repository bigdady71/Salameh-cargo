-- database/schema.sql
CREATE DATABASE IF NOT EXISTS salameh_cargo
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE salameh_cargo;

-- 1) users
CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  full_name       VARCHAR(255) NOT NULL,
  email           VARCHAR(255),
  phone           VARCHAR(50)  NOT NULL UNIQUE,
  shipping_code   VARCHAR(100) UNIQUE,
  address         VARCHAR(255),
  country         VARCHAR(100),
  id_number       VARCHAR(100),
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2) shipments
CREATE TABLE shipments (
  shipment_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  tracking_number     VARCHAR(100) UNIQUE,
  product_description TEXT,
  cbm           DECIMAL(10,2) NOT NULL DEFAULT 0,
  cartons       INT           NOT NULL DEFAULT 0,
  weight        DECIMAL(10,2) NOT NULL DEFAULT 0,
  gross_weight  DECIMAL(10,2) NOT NULL DEFAULT 0,
  total_amount  DECIMAL(10,2) NOT NULL DEFAULT 0,
  status        VARCHAR(50)   NOT NULL DEFAULT 'En Route',
  origin        VARCHAR(100),
  destination   VARCHAR(100),
  pickup_date   DATETIME NULL,
  delivery_date DATETIME NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_shipments_user_id (user_id),
  INDEX idx_shipments_status (status),
  CONSTRAINT fk_shipments_user
    FOREIGN KEY (user_id) REFERENCES users(user_id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 3) shipment_scrapes
CREATE TABLE shipment_scrapes (
  scrape_id INT AUTO_INCREMENT PRIMARY KEY,
  shipment_id INT NOT NULL,
  source_site VARCHAR(50) NOT NULL,
  status      VARCHAR(100),
  status_raw  TEXT,
  scrape_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_scrapes_shipment_id (shipment_id),
  INDEX idx_scrapes_time (scrape_time),
  CONSTRAINT fk_scrapes_shipment
    FOREIGN KEY (shipment_id) REFERENCES shipments(shipment_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 4) admins
CREATE TABLE admins (
  admin_id INT AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          VARCHAR(50)  NOT NULL DEFAULT 'manager',
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5) logs
CREATE TABLE logs (
  log_id INT AUTO_INCREMENT PRIMARY KEY,
  action_type VARCHAR(50) NOT NULL,
  actor_id    INT NOT NULL COMMENT 'positive=user_id, negative=admin_id',
  related_shipment_id INT NULL,
  details     TEXT,
  timestamp   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_logs_actor (actor_id),
  INDEX idx_logs_shipment (related_shipment_id),
  INDEX idx_logs_time (timestamp)
) ENGINE=InnoDB;

-- Optional temp store for OTP if you don't use Twilio Verify (enable if needed)
-- CREATE TABLE otp_codes (
--   id INT AUTO_INCREMENT PRIMARY KEY,
--   phone VARCHAR(50) NOT NULL,
--   code_hash VARCHAR(255) NOT NULL,
--   expires_at DATETIME NOT NULL,
--   attempts TINYINT NOT NULL DEFAULT 0,
--   created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
--   INDEX (phone),
--   INDEX (expires_at)
-- ) ENGINE=InnoDB;
-- database/schema.sql
CREATE DATABASE IF NOT EXISTS salameh_cargo
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE salameh_cargo;

-- 1) users
CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  full_name       VARCHAR(255) NOT NULL,
  email           VARCHAR(255),
  phone           VARCHAR(50)  NOT NULL UNIQUE,
  shipping_code   VARCHAR(100) UNIQUE,
  address         VARCHAR(255),
  country         VARCHAR(100),
  id_number       VARCHAR(100),
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2) shipments
CREATE TABLE shipments (
  shipment_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  tracking_number     VARCHAR(100) UNIQUE,
  product_description TEXT,
  cbm           DECIMAL(10,2) NOT NULL DEFAULT 0,
  cartons       INT           NOT NULL DEFAULT 0,
  weight        DECIMAL(10,2) NOT NULL DEFAULT 0,
  gross_weight  DECIMAL(10,2) NOT NULL DEFAULT 0,
  total_amount  DECIMAL(10,2) NOT NULL DEFAULT 0,
  status        VARCHAR(50)   NOT NULL DEFAULT 'En Route',
  origin        VARCHAR(100),
  destination   VARCHAR(100),
  pickup_date   DATETIME NULL,
  delivery_date DATETIME NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_shipments_user_id (user_id),
  INDEX idx_shipments_status (status),
  CONSTRAINT fk_shipments_user
    FOREIGN KEY (user_id) REFERENCES users(user_id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 3) shipment_scrapes
CREATE TABLE shipment_scrapes (
  scrape_id INT AUTO_INCREMENT PRIMARY KEY,
  shipment_id INT NOT NULL,
  source_site VARCHAR(50) NOT NULL,
  status      VARCHAR(100),
  status_raw  TEXT,
  scrape_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_scrapes_shipment_id (shipment_id),
  INDEX idx_scrapes_time (scrape_time),
  CONSTRAINT fk_scrapes_shipment
    FOREIGN KEY (shipment_id) REFERENCES shipments(shipment_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 4) admins
CREATE TABLE admins (
  admin_id INT AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          VARCHAR(50)  NOT NULL DEFAULT 'manager',
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5) logs
CREATE TABLE logs (
  log_id INT AUTO_INCREMENT PRIMARY KEY,
  action_type VARCHAR(50) NOT NULL,
  actor_id    INT NOT NULL COMMENT 'positive=user_id, negative=admin_id',
  related_shipment_id INT NULL,
  details     TEXT,
  timestamp   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_logs_actor (actor_id),
  INDEX idx_logs_shipment (related_shipment_id),
  INDEX idx_logs_time (timestamp)
) ENGINE=InnoDB;

-- Optional temp store for OTP if you don't use Twilio Verify (enable if needed)
-- CREATE TABLE otp_codes (
--   id INT AUTO_INCREMENT PRIMARY KEY,
--   phone VARCHAR(50) NOT NULL,
--   code_hash VARCHAR(255) NOT NULL,
--   expires_at DATETIME NOT NULL,
--   attempts TINYINT NOT NULL DEFAULT 0,
--   created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
--   INDEX (phone),
--   INDEX (expires_at)
-- ) ENGINE=InnoDB;
