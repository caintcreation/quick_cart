# QuickCart Studio — PHP/MySQL starter

A small reusable WhatsApp-first product catalogue starter. This starter includes a public storefront, product search, WhatsApp order links, a MySQL schema, and a basic admin login/dashboard. It is a foundation, not yet a production-ready multi-tenant SaaS.

## Requirements
- PHP 8.1+ with PDO MySQL
- MySQL/MariaDB (XAMPP is suitable locally)
- A browser

## Run locally with XAMPP
1. Copy this folder into `C:\xampp\htdocs\quickcart-studio`.
2. Start Apache and MySQL in XAMPP.
3. Open phpMyAdmin at `http://localhost/phpmyadmin` and import `database.sql`.
4. Check `includes/config.php`; the defaults match a typical fresh XAMPP installation. Change them if your MySQL credentials differ.
5. Open `http://localhost/quickcart-studio/`.
6. The demo shop is at `http://localhost/quickcart-studio/?shop=demo`.

For an existing database, import [`migration.sql`](migration.sql) after taking a
backup. A fresh installation should import `database.sql` instead. Products
created through the admin dashboard include stock and can be published or
hidden. Customers submit a validated order to `order.php`; the order is
saved
before the encoded WhatsApp handoff opens.

### Troubleshooting: `PDOException: could not find driver`

This means the PHP runtime used by Apache does not have the MySQL PDO driver
loaded. In XAMPP:

1. Open **XAMPP Control Panel** and click **Config** next to Apache, then open
   `php.ini` (the file should normally be `C:\xampp\php\php.ini`).
2. Find `extension=pdo_mysql`. If it starts with `;`, remove the semicolon.
   Also confirm that `extension_dir="C:\xampp\php\ext"` points to the XAMPP
   `ext` directory.
3. Save the file and restart Apache. Restart it even if the setting was
   already enabled, because PHP reads `php.ini` only when Apache starts.
4. If the error remains, open `http://localhost/dashboard/phpinfo.php` (or
   create a temporary page containing `<?php phpinfo();`) and check **Loaded
   Configuration File**. Enable `pdo_mysql` in that file instead; Apache may
   be using a different PHP installation.

Do not rely only on `php -m` in a terminal: the command-line PHP executable
can use a different `php.ini` from the PHP installation used by Apache.

## Create the first admin account
Generate a password hash using PHP from a terminal in the project folder:

```bash
php -r "echo password_hash('CHANGE-THIS-PASSWORD', PASSWORD_DEFAULT), PHP_EOL;"
```

Copy the generated hash, then run this SQL in phpMyAdmin (replace the email and hash):

```sql
INSERT INTO admins (shop_id, email, password_hash)
VALUES (1, 'owner@example.com', 'PASTE_GENERATED_HASH_HERE');
```

Then sign in at `http://localhost/quickcart-studio/admin/login.php`.

## Configure the WhatsApp number
Edit the demo shop record in phpMyAdmin. Store the number with country code and digits only, e.g. `2547XXXXXXXX` for a Kenyan number. The starter currently uses a placeholder number.

```sql
UPDATE shops SET whatsapp_number='2547XXXXXXXX' WHERE slug='demo';
```

## Folder structure
- `index.php` — public product catalogue
- `database.sql` — tables and demo data
- `includes/config.php` — PDO connection and helpers
- `admin/login.php` — password-verified admin login
- `admin/dashboard.php` — basic product listing
- `admin/logout.php` — session logout
- `assets/style.css` — responsive storefront styling
- `assets/app.js` — live product search
- `uploads/` — reserved for product images; upload handling is not implemented yet

## Important next steps before taking real customers
1. Implement CSRF tokens for all state-changing forms.
2. Build create/edit/delete product routes with server-side validation and prepared statements.
3. Add secure image upload checks (MIME detection, file-size limits, random filenames, and non-executable upload storage).
4. Add shop creation and owner provisioning; never trust a shop ID supplied by the browser. Derive it from the authenticated session.
5. Add rate limiting and login throttling; use HTTPS and secure session cookie settings in production.
6. Move database credentials to environment variables and turn off PHP error display in production.
7. Back up the database and test restore procedures.
8. The demo storefront currently accepts `?shop=slug`; add a proper router and test tenant isolation before using it as a multi-customer SaaS.
9. Orders table is a planned schema only; this starter does not yet save orders to the database. WhatsApp links open a conversation instead.

## Deployment note
Develop and demonstrate locally first. Choose hosting that explicitly supports PHP and MySQL/MariaDB. GitHub Pages cannot execute PHP. Check a host's current database, storage, inactivity, and free-tier restrictions before using it for customer data. A free tier is suitable for demos but may not be reliable enough for a paying customer's live shop.
