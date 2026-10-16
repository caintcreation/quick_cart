-- QuickCart Studio - Database Schema Update v2.0
-- Safe migrations adding Categories, Order Items, Pricing Plans, Enquiries, Payment Records, Audit Logs & Column additions

-- 1. Table Alterations
ALTER TABLE products
  ADD COLUMN IF NOT EXISTS category_id INT UNSIGNED NULL AFTER shop_id,
  ADD COLUMN IF NOT EXISTS compare_at_price DECIMAL(10,2) NULL AFTER price,
  ADD INDEX IF NOT EXISTS idx_products_category (category_id);

ALTER TABLE orders
  MODIFY COLUMN status ENUM('pending','confirmed','processing','completed','cancelled') NOT NULL DEFAULT 'pending',
  ADD COLUMN IF NOT EXISTS currency VARCHAR(8) NOT NULL DEFAULT 'KES' AFTER total,
  ADD COLUMN IF NOT EXISTS customer_address TEXT NULL AFTER customer_phone,
  ADD COLUMN IF NOT EXISTS customer_notes TEXT NULL AFTER customer_address;

ALTER TABLE shops
  ADD COLUMN IF NOT EXISTS contact_email VARCHAR(190) NULL AFTER accent_color,
  ADD COLUMN IF NOT EXISTS contact_phone VARCHAR(40) NULL AFTER contact_email,
  ADD COLUMN IF NOT EXISTS address TEXT NULL AFTER contact_phone;

ALTER TABLE admins
  MODIFY COLUMN shop_id INT UNSIGNED NULL,
  ADD COLUMN IF NOT EXISTS full_name VARCHAR(120) NULL AFTER role;

-- 2. New Supporting Tables
CREATE TABLE IF NOT EXISTS categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  shop_id INT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(100) NOT NULL,
  description TEXT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_shop_category_slug (shop_id, slug),
  INDEX idx_category_shop (shop_id),
  CONSTRAINT fk_category_shop FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NULL,
  product_name VARCHAR(160) NOT NULL,
  quantity INT UNSIGNED NOT NULL DEFAULT 1,
  unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_items_order (order_id),
  CONSTRAINT fk_item_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_item_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pricing_plans (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(80) NOT NULL UNIQUE,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  billing_cycle VARCHAR(50) NOT NULL DEFAULT 'one-time',
  description TEXT NULL,
  features_json JSON NULL,
  badge VARCHAR(50) NULL,
  is_popular TINYINT(1) NOT NULL DEFAULT 0,
  active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS service_enquiries (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(40) NULL,
  business_name VARCHAR(140) NULL,
  plan_interest VARCHAR(100) NULL,
  message TEXT NOT NULL,
  status ENUM('new','contacted','closed') NOT NULL DEFAULT 'new',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_records (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  shop_id INT UNSIGNED NULL,
  plan_id INT UNSIGNED NULL,
  customer_name VARCHAR(120) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  currency VARCHAR(8) NOT NULL DEFAULT 'USD',
  payment_method VARCHAR(50) NOT NULL DEFAULT 'Manual/Cash',
  reference VARCHAR(100) NULL,
  notes TEXT NULL,
  payment_date DATE NOT NULL,
  recorded_by INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_payment_shop (shop_id),
  CONSTRAINT fk_payment_shop FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE SET NULL,
  CONSTRAINT fk_payment_admin FOREIGN KEY (recorded_by) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id INT UNSIGNED NULL,
  shop_id INT UNSIGNED NULL,
  action VARCHAR(100) NOT NULL,
  details TEXT NULL,
  ip_address VARCHAR(45) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_audit_admin (admin_id),
  INDEX idx_audit_shop (shop_id),
  CONSTRAINT fk_audit_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL,
  CONSTRAINT fk_audit_shop FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
