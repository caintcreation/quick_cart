# QuickCart Studio — Multi-Tenant E-Commerce & WhatsApp Shopping Platform

QuickCart Studio is an elegant, full-stack multi-tenant e-commerce platform built for small businesses, fashion boutiques, shoe retailers, cosmetics brands, electronics stores, and social media (Instagram, TikTok, WhatsApp) merchants.

It empowers business owners to showcase their catalogue online and receive instant, beautifully structured **WhatsApp orders** with real-time inventory management, one-click ordering, and shopping cart functionality.

---

## 🌟 Key Features

- **Multi-Tenant Architecture**: Provision and manage unlimited independent customer storefronts (`?shop=slug`) from a single central database.
- **Flexible Ordering Workflows**:
  - **1-Click "Buy Now"**: Fast single-item ordering modal capturing customer name, phone, address, and special notes.
  - **Interactive Shopping Cart**: Full multi-item shopping cart with quantity adjustments (`+` / `-`), item removal, and subtotal calculation stored locally in browser storage.
  - **Structured WhatsApp Messages**: Automatically formats order summaries, line-item breakdowns, subtotals, grand totals, and customer delivery details directly into the merchant's WhatsApp chat.
- **Real-Time Inventory Management**:
  - Atomic stock reservation prevents overselling during high-traffic order bursts.
  - Low-stock visual badges on storefront when 5 or fewer items remain.
- **Merchant & Admin Dashboard**:
  - Real-time sales KPIs, order tracking, and lifecycle updates (`pending`, `confirmed`, `processing`, `completed`, `cancelled`).
  - Printable packing slips for order fulfillment.
  - **One-Click CSV Export** for Excel and Google Sheets reporting.
  - Product management with secure image upload handling.
  - Category manager with custom slug ordering.
  - Custom branding: Store logos, accent colors, and custom taglines.
- **Design & User Experience**:
  - Modern, responsive design system supporting mobile, tablet, and desktop viewports.
  - Dark / Light mode toggle with automatic persistence.
  - Clean vector SVG icon system (no emoji placeholders).
- **Security & Integrity**:
  - CSRF protection across all state-changing actions.
  - Strict tenant isolation (`WHERE shop_id = :shop_id`).
  - MIME-type binary verification for uploaded images with randomized filenames.
  - Directory traversal and script execution prevention via `.htaccess`.

---

## 🚀 Quick Start Guide (Local XAMPP on Windows)

### 1. Requirements
- Windows 10 or 11
- [XAMPP](https://www.apachefriends.org/) with **Apache** and **MySQL** (PHP 8.2+ with `pdo_mysql`, `mbstring`, and `curl` extensions enabled)

### 2. Installation Path
Place the project into your local XAMPP web root:
```text
C:\xampp\htdocs\quickcart-studio
```

### 3. Database Setup
1. Start **Apache** and **MySQL** from the XAMPP Control Panel.
2. Open your web browser and go to `http://localhost/phpmyadmin/`.
3. Create a new database named `quickcart` with collation `utf8mb4_unicode_ci`.
4. Import [`database.sql`](database.sql) into the `quickcart` database.

### 4. Database Configuration
Database credentials can be adjusted in [`includes/config.php`](includes/config.php) or via environment variables:
```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'quickcart');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 5. Accessing the Application
- **Public Marketing Website**: `http://localhost/quickcart-studio/`
- **Demo Storefront Catalogue**: `http://localhost/quickcart-studio/index.php?shop=demo`
- **Admin & Merchant Login**: `http://localhost/quickcart-studio/admin/login.php`

---

## 🔐 Default Administrative Credentials

### Platform Super Admin
- **Email**: `admin@quickcartstudio.com`
- **Password**: `Admin2026!Secure`
- *Capabilities*: Provision new customer shops, manage platform pricing plans, view platform analytics, and review lead enquiries.

### Demo Shop Merchant Admin
- **Email**: `maduvwe88@gmail.com`
- *Capabilities*: Manage products, stock levels, categories, orders, and shop branding for the demo shop.

---

## 📱 How WhatsApp Ordering Works

1. **Shopper Experience**:
   - The customer browses the merchant's catalog.
   - They can either click **Buy Now** on a single product or click **+ Cart** on multiple products.
   - When checking out, a modal captures their Name, Phone / WhatsApp number, Delivery Address, and Special Instructions.
2. **Order Processing**:
   - QuickCart validates stock availability and executes an atomic stock deduction.
   - A unique order reference (e.g., `QC-4F8A92B1`) is created in the database.
3. **WhatsApp Dispatch**:
   - The customer is redirected directly to the merchant's official WhatsApp with a pre-formatted message:

```text
👋 Hello Demo Boutique, I would like to place an order:

🛒 ORDER SUMMARY (Ref: QC-B92F4A01)
──────────────────────────
• 2x Classic Linen Shirt — KES 3,500.00 (Subtotal: KES 7,000.00)
• 1x Leather Chelsea Boots — KES 8,900.00 (Subtotal: KES 8,900.00)
──────────────────────────
Total Amount: KES 15,900.00

📋 CUSTOMER DETAILS
• Name: Jane Doe
• Phone: +254 712 345 678
• Delivery Address: Westlands, Nairobi, Apt 4B
• Notes / Requests: Please deliver after 2 PM.

Please confirm availability and dispatch. Thank you!
```

---

## 📁 Repository Structure

```text
quickcart-studio/
├── admin/                      # Merchant & Platform Admin Panels
│   ├── categories.php          # Category sorting & management
│   ├── dashboard.php           # Analytics, KPIs, and recent orders
│   ├── login.php               # Secure sign in with CSRF & session protection
│   ├── logout.php              # Session destruction & audit logging
│   ├── orders.php              # Order lifecycles, CSV export & packing slips
│   ├── platform_enquiries.php  # Public contact inquiries & lead manager
│   ├── platform_overview.php   # Multi-shop console & new shop provisioning
│   ├── platform_payments.php   # Service payments & revenue ledger
│   ├── platform_plans.php      # Platform pricing package editor
│   ├── products.php            # Product CRUD, stock controls & image uploads
│   └── settings.php            # Shop branding, logo, colors & WhatsApp number
├── assets/
│   ├── app.js                  # Cart manager, theme switcher, live search & modals
│   └── style.css               # Complete responsive design system (Light/Dark themes)
├── includes/
│   ├── config.php              # Database singleton, auth guards, CSRF & image security
│   └── icons.php               # Crisp vector SVG icon helper library
├── templates/
│   ├── admin_footer.php        # Admin dashboard footer
│   ├── admin_header.php        # Admin navigation bar and sidebar
│   ├── error_403.php           # 403 Forbidden template
│   ├── error_404.php           # 404 Not Found template
│   ├── footer.php              # Marketing footer
│   └── header.php              # Marketing navigation header
├── uploads/                    # Isolated user asset directory
│   └── .htaccess               # Prevents script execution in uploads
├── about.php                   # Marketing About page
├── contact.php                 # Lead capture form with honeypot security
├── database.sql                # Complete SQL schema & initial demonstration data
├── features.php                # Platform feature tour
├── home.php                    # Public marketing homepage
├── index.php                   # Entry router (serves homepage or storefront)
├── order.php                   # Stock deduction & WhatsApp prefilled message engine
├── pricing.php                 # Platform pricing plans & FAQs
├── privacy.php                 # Privacy policy
├── storefront.php              # Public storefront catalogue with search & cart
└── terms.php                   # Terms of service
```

---

## 📄 License
Commercial proprietary software. All rights reserved.
