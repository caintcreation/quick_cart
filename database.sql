-- =======================================================
-- QuickCart Studio - Complete Database Schema & Initial Data
-- Production-Ready Multi-Tenant E-Commerce Catalogue Platform
-- =======================================================

CREATE DATABASE IF NOT EXISTS quickcart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quickcart;

-- 1. Shops (Tenants)
CREATE TABLE IF NOT EXISTS shops (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  tagline VARCHAR(255) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  logo_path VARCHAR(255) DEFAULT NULL,
  published TINYINT(1) NOT NULL DEFAULT 1,
  whatsapp_number VARCHAR(30) NOT NULL,
  currency VARCHAR(8) NOT NULL DEFAULT 'KES',
  accent_color CHAR(7) NOT NULL DEFAULT '#f97316',
  contact_email VARCHAR(190) DEFAULT NULL,
  contact_phone VARCHAR(40) DEFAULT NULL,
  address TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Administrators & Platform Owners
CREATE TABLE IF NOT EXISTS admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  shop_id INT UNSIGNED NULL, -- NULL or 1 for platform_owner if managing system-wide
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('shop_admin','platform_owner') NOT NULL DEFAULT 'shop_admin',
  full_name VARCHAR(120) DEFAULT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_admins_shop (shop_id),
  CONSTRAINT fk_admin_shop FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Categories
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

-- 4. Products
CREATE TABLE IF NOT EXISTS products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  shop_id INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NULL,
  name VARCHAR(160) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  compare_at_price DECIMAL(10,2) NULL,
  category VARCHAR(100) DEFAULT 'General',
  image_path VARCHAR(255) DEFAULT NULL,
  stock INT UNSIGNED NOT NULL DEFAULT 0,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_products_shop_active (shop_id, active),
  INDEX idx_products_category (category_id),
  CONSTRAINT fk_product_shop FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE,
  CONSTRAINT fk_product_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Orders
CREATE TABLE IF NOT EXISTS orders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  shop_id INT UNSIGNED NOT NULL,
  customer_name VARCHAR(120) DEFAULT NULL,
  customer_phone VARCHAR(40) DEFAULT NULL,
  customer_address TEXT DEFAULT NULL,
  customer_notes TEXT DEFAULT NULL,
  items_json JSON NOT NULL,
  total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  currency VARCHAR(8) NOT NULL DEFAULT 'KES',
  reference VARCHAR(24) NOT NULL UNIQUE,
  status ENUM('pending','confirmed','processing','completed','cancelled') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_orders_shop_created (shop_id, created_at),
  CONSTRAINT fk_order_shop FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Order Items (Snapshot representation)
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

-- 7. Platform Pricing Plans
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

-- 8. Public Service Enquiries
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

-- 9. Platform Service Payment Records (Manual tracking)
CREATE TABLE IF NOT EXISTS payment_records (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  shop_id INT UNSIGNED NULL,
  plan_id INT UNSIGNED NULL,
  customer_name VARCHAR(120) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  currency VARCHAR(8) NOT NULL DEFAULT 'USD',
  payment_method VARCHAR(50) NOT NULL DEFAULT 'Bank Transfer',
  reference VARCHAR(100) NULL,
  notes TEXT NULL,
  payment_date DATE NOT NULL,
  recorded_by INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_payment_shop (shop_id),
  CONSTRAINT fk_payment_shop FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE SET NULL,
  CONSTRAINT fk_payment_admin FOREIGN KEY (recorded_by) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Platform Audit Logs
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

-- =======================================================
-- Seed Initial Demo Data
-- =======================================================

INSERT INTO shops (id, name, slug, tagline, description, whatsapp_number, currency, accent_color)
VALUES (1, 'QuickCart Demo', 'demo', 'Good products. Easy ordering.', 'Your favorite boutique for curated apparel, shoes, and everyday smart gadgets.', '254700000000', 'KES', '#f97316')
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO categories (id, shop_id, name, slug, description, sort_order) VALUES
(1, 1, 'Fashion', 'fashion', 'Trendy apparel and contemporary wardrobe essentials', 1),
(2, 1, 'Accessories', 'accessories', 'Handcrafted bags, leather belts, and wrist accessories', 2),
(3, 1, 'Electronics', 'electronics', 'Smart audio, chargers, and premium mobile accessories', 3)
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO products (id, shop_id, category_id, name, description, price, category, stock, active) VALUES
(1, 1, 1, 'Everyday Street Sneakers', 'Comfortable everyday sneakers built with breathable mesh and cushioned soles.', 2500.00, 'Fashion', 100, 1),
(2, 1, 2, 'Classic Leather Handbag', 'A spacious daily tote bag crafted from genuine pebbled vegan leather with gold accents.', 1800.00, 'Accessories', 100, 1),
(3, 1, 3, 'Wireless Noise-Cancelling Earbuds', 'Compact true-wireless earbuds with rich deep bass, 28h battery life, and USB-C fast charging.', 2200.00, 'Electronics', 100, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO pricing_plans (id, name, slug, price, billing_cycle, description, features_json, badge, is_popular, sort_order) VALUES
(1, 'Basic Catalogue', 'basic-catalogue', 49.00, 'one-time setup', 'Perfect for boutique sellers, Instagram/TikTok shops, and artisan makers starting their online journey.', '["Branded online storefront catalogue", "Custom categories & product listings", "High-res product images & descriptions", "Direct 1-tap WhatsApp ordering with prefilled message", "Mobile-first responsive design", "No recurring monthly builder fee"]', 'Starter', 0, 1),
(2, 'Business Shop', 'business-shop', 149.00, 'one-time setup', 'Complete self-serve shop management for growing retailers and established brands.', '["Everything in Basic Catalogue", "Independent Shop Admin Dashboard", "Product inventory & stock management", "Order records, line items & customer status tracking", "Shop custom branding, logo, accent colors & currency", "Category filters & live search discovery", "Printable order summaries"]', 'Most Popular', 1, 2),
(3, 'Managed Shop', 'managed-shop', 299.00, 'setup + $29/mo', 'Full-service turnkey solution with hands-off maintenance and priority operational support.', '["Everything in Business Shop", "Dedicated shop setup & catalogue entry (up to 50 items)", "Ongoing product updates & seasonal banner changes", "Assistance with custom domain connection & hosting setup", "Priority WhatsApp & email merchant support", "Quarterly performance & inventory reviews"]', 'Turnkey', 0, 3)
ON DUPLICATE KEY UPDATE name=VALUES(name);
