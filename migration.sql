-- Run this once against an existing QuickCart database. Fresh installs should
-- use database.sql instead.
ALTER TABLE shops
  ADD COLUMN description TEXT NULL AFTER tagline,
  ADD COLUMN logo_path VARCHAR(255) NULL AFTER description,
  ADD COLUMN published TINYINT(1) NOT NULL DEFAULT 1 AFTER logo_path;

ALTER TABLE admins
  ADD COLUMN role ENUM('shop_admin','platform_owner') NOT NULL DEFAULT 'shop_admin' AFTER password_hash,
  ADD COLUMN active TINYINT(1) NOT NULL DEFAULT 1 AFTER role;

ALTER TABLE products
  ADD COLUMN stock INT UNSIGNED NOT NULL DEFAULT 0 AFTER image_path;
UPDATE products SET stock = 100 WHERE stock = 0;

ALTER TABLE orders
  ADD COLUMN reference VARCHAR(24) NULL AFTER total;

UPDATE orders SET reference = CONCAT('QC-', id) WHERE reference IS NULL;
ALTER TABLE orders MODIFY reference VARCHAR(24) NOT NULL;
ALTER TABLE orders ADD UNIQUE KEY uq_orders_reference (reference);
