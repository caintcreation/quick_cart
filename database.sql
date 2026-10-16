CREATE DATABASE IF NOT EXISTS quickcart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quickcart;

CREATE TABLE shops (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  tagline VARCHAR(255) DEFAULT NULL,
  whatsapp_number VARCHAR(30) NOT NULL,
  currency VARCHAR(8) NOT NULL DEFAULT 'KES',
  accent_color CHAR(7) NOT NULL DEFAULT '#f97316',
  description TEXT,
  logo_path VARCHAR(255) DEFAULT NULL,
  published TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  shop_id INT UNSIGNED NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('shop_admin','platform_owner') NOT NULL DEFAULT 'shop_admin',
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_admin_shop FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  shop_id INT UNSIGNED NOT NULL,
  name VARCHAR(160) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  category VARCHAR(100) DEFAULT 'General',
  image_path VARCHAR(255) DEFAULT NULL,
  stock INT UNSIGNED NOT NULL DEFAULT 0,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_products_shop_active (shop_id, active),
  CONSTRAINT fk_product_shop FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE orders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  shop_id INT UNSIGNED NOT NULL,
  customer_name VARCHAR(120) DEFAULT NULL,
  customer_phone VARCHAR(40) DEFAULT NULL,
  items_json JSON NOT NULL,
  total DECIMAL(10,2) NOT NULL DEFAULT 0,
  reference VARCHAR(24) NOT NULL UNIQUE,
  status ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_orders_shop_created (shop_id, created_at),
  CONSTRAINT fk_order_shop FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Demo shop. Replace the WhatsApp number with a real number in international format, digits only.
INSERT INTO shops (name, slug, tagline, whatsapp_number, currency, accent_color)
VALUES ('QuickCart Demo', 'demo', 'Good products. Easy ordering.', '254700000000', 'KES', '#f97316');

INSERT INTO products (shop_id, name, description, price, category, image_path, stock) VALUES
(1, 'Everyday Sneakers', 'Comfortable everyday sneakers.', 2500, 'Fashion', NULL, 100),
(1, 'Classic Handbag', 'A simple bag for daily use.', 1800, 'Accessories', NULL, 100),
(1, 'Wireless Earbuds', 'Compact earbuds for work and travel.', 2200, 'Electronics', NULL, 100);
