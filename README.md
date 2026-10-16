# QuickCart Studio — Production-Ready Multi-Tenant E-Commerce Platform

QuickCart Studio is a commercial, multi-tenant online shop builder designed for small businesses, fashion boutiques, shoe and accessory sellers, cosmetics brands, electronics retailers, and social media (Instagram, TikTok, Facebook) merchants.

It turns online product catalogues into instant, 1-tap **prefilled WhatsApp orders**, removing checkout friction, eliminating third-party card processing fees, and providing shop owners with real-time inventory and order management.

---

## 🌟 Key Highlights & Architecture

- **True Multi-Tenancy**: Centralized MySQL database with strict foreign-key tenant isolation. Platform owners can provision unlimited independent customer shops (`?shop=slug`).
- **Zero-Friction WhatsApp Checkout**: Real server-side URL encoding creates clean, pre-filled WhatsApp messages containing product names, quantities, currency calculations, and order reference tracking IDs (`QC-XXXXXX`).
- **Atomic Stock Reservation**: Orders decrement stock using transactional MySQL isolation (`stock = stock - ? WHERE stock >= ?`), preventing overselling during concurrent traffic.
- **Enterprise-Grade Admin Panels**:
  - **Platform Owner Dashboard**: Multi-tenant metrics, shop creation, shop publication toggles, dynamic pricing package manager, lead enquiry manager, and service revenue ledger.
  - **Shop Merchant Dashboard**: Real-time sales analytics, product management with secure image uploads, category sorting, order status lifecycles, and printable packing slips.
- **Modern Responsive Design**: Light & Dark mode toggle (persisted via `localStorage`), CSS custom properties, touch-friendly mobile layouts, and crisp SVG icons (zero emoji reliance).
- **Security & Data Integrity**: CSRF token validation on all state-changing POST requests, MIME binary inspection for uploads, execution-disabled upload directories (`.htaccess`), and bcrypt/Argon2 password hashing.

---

## 🚀 Local Installation Guide (XAMPP on Windows)

### 1. Requirements
- Windows 10/11
- [XAMPP](https://www.apachefriends.org/) with **Apache** and **MySQL** (PHP 8.1+ with `pdo_mysql`, `mbstring`, `fileinfo` enabled)
- A modern web browser

### 2. Project Location
Ensure this project directory is located at:
```text
C:\xampp\htdocs\quickcart-studio
```

### 3. Database Setup (via phpMyAdmin)
1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Open your browser and navigate to `http://localhost/phpmyadmin/`.
3. Create a new database named `quickcart` with collation `utf8mb4_unicode_ci`.
4. Click on the `quickcart` database, select **Import**, choose [`database.sql`](file:///c:/xampp/htdocs/quickcart-studio/database.sql), and click **Go**.

*Note for existing databases*: If you already had an earlier version of the database installed, run [`migration_v2.sql`](file:///c:/xampp/htdocs/quickcart-studio/migration_v2.sql) to add the latest tables.

### 4. Configuration
Database settings are located in [`includes/config.php`](file:///c:/xampp/htdocs/quickcart-studio/includes/config.php). The defaults match standard local XAMPP credentials:
```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'quickcart');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 5. Accessing the Application
- **Public Marketing Website**: `http://localhost/quickcart-studio/` or `http://localhost/quickcart-studio/home.php`
- **Demo Storefront**: `http://localhost/quickcart-studio/index.php?shop=demo`
- **Merchant / Owner Sign In**: `http://localhost/quickcart-studio/admin/login.php`

---

## 🔐 Administrative Accounts

### Platform Owner Account (Super Admin)
- **Email**: `admin@quickcartstudio.com`
- **Password**: `Admin2026!Secure`
- *Capabilities*: Provision new customer shops, edit pricing tiers, view service payment ledgers, and manage incoming leads.

### Demo Shop Admin Account
- **Email**: `maduvwe88@gmail.com`
- *Capabilities*: Manage products, stock, categories, orders, and shop settings for the demo boutique.

To create or change any admin password via CLI:
```bash
C:\xampp\php\php.exe -r "echo password_hash('YourNewPassword', PASSWORD_DEFAULT), PHP_EOL;"
```
Then update the `password_hash` column in the `admins` table.

---

## 📁 Project Structure

```text
quickcart-studio/
├── admin/
│   ├── categories.php          # Category manager with slug & sort ordering
│   ├── dashboard.php           # Shop-specific analytics, KPIs & recent orders
│   ├── login.php               # Secure sign in with CSRF & session regeneration
│   ├── logout.php              # Session cookie destruction & audit log
│   ├── orders.php              # Order lifecycles, line items & printable packing slips
│   ├── platform_enquiries.php  # Public contact inquiries & lead manager
│   ├── platform_overview.php   # Multi-shop console & new shop provisioning
│   ├── platform_payments.php   # Service payments & revenue ledger
│   ├── platform_plans.php      # Dynamic pricing package editor
│   ├── product.php             # Backward compatibility redirect
│   ├── products.php            # Product CRUD, stock controls & validated image uploads
│   └── settings.php            # Shop branding, accent colors, logo & WhatsApp configuration
├── assets/
│   ├── app.js                  # Theme switcher, mobile nav, live filtering & file preview
│   └── style.css               # Complete responsive design system (Light/Dark themes)
├── includes/
│   ├── config.php              # PDO singleton, auth guards, CSRF & upload security
│   └── icons.php               # Crisp vector SVG icons library
├── templates/
│   ├── admin_footer.php        # Admin layout footer
│   ├── admin_header.php        # Admin sidebar, mobile drawer & topbar
│   ├── error_403.php           # 403 Forbidden template
│   ├── error_404.php           # 404 Not Found template
│   ├── footer.php              # Public marketing footer
│   └── header.php              # Public marketing navigation & theme toggle
├── uploads/
│   └── .htaccess               # Execution protection (prevents PHP execution in uploads)
├── about.php                   # Mission, target markets & platform value
├── contact.php                 # Lead capture form with honeypot & server validation
├── database.sql                # Complete SQL schema & initial demonstration data
├── features.php                # Detailed overview of working technical capabilities
├── home.php                    # Public landing page with showcase & pricing
├── index.php                   # Smart router (serves marketing home or specific shop)
├── migration.sql               # Legacy migration file
├── migration_v2.sql            # Migration v2 (categories, orders, plans, payments)
├── order.php                   # Atomic stock deduction & WhatsApp URL generation
├── pricing.php                 # Transparent setup tiers, inclusion checklists & FAQs
├── privacy.php                 # Privacy policy
├── storefront.php              # Customer-facing product catalogue with search & filters
└── terms.php                   # Terms of service
```

---

## 🛡️ Security Implementation Details

1. **Upload Hardening**:
   - Files uploaded to `uploads/` are analyzed using PHP's `finfo` extension for genuine binary MIME types (`image/jpeg`, `image/png`, `image/webp`, `image/gif`).
   - File extensions are strictly enforced and filenames are randomized (`bin2hex(random_bytes(16))`).
   - `uploads/.htaccess` explicitly blocks execution of `.php`, `.phtml`, scripts, and CGI binaries.
2. **Tenant Scoping**:
   - Every shop-specific query explicitly checks `WHERE shop_id = :shop_id`.
   - The active `shop_id` is derived from the server session, never trusted blindly from user-supplied GET/POST parameters.
3. **CSRF Protection**:
   - Cryptographically random CSRF tokens (`random_bytes(32)`) protect all state-changing POST forms.
4. **Anti-Spam Form Security**:
   - Contact form includes invisible honeypot traps that silently reject automated spam submissions.

---

## 🌐 Production Deployment Guide (cPanel / Apache / VPS)

1. Upload the project files to your server's `public_html` directory.
2. In your hosting MySQL manager, create a new database and import `database.sql`.
3. Set environment variables (or update `includes/config.php`):
   - `QC_DB_HOST`
   - `QC_DB_NAME`
   - `QC_DB_USER`
   - `QC_DB_PASS`
4. Ensure the `uploads/` directory has write permissions (`chmod 755 uploads`).
5. Ensure SSL/HTTPS is enabled; QuickCart Studio will automatically configure strict `Secure` and `SameSite=Lax` cookies.
