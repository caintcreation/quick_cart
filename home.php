<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/icons.php';

$activeNav = 'home';
$pageTitle = APP_NAME . ' — Frictionless WhatsApp E-Commerce Storefront Builder';
$metaDescription = 'Create clean, high-converting online shops for small retail brands, fashion boutiques, and Instagram sellers with instant 1-tap WhatsApp checkout.';

// Fetch live showcase demo shop and published pricing plans
$demoShop = db()->query("SELECT * FROM shops WHERE slug = 'demo' LIMIT 1")->fetch();
$plans = db()->query("SELECT * FROM pricing_plans WHERE active = 1 ORDER BY sort_order ASC")->fetchAll();
$demoProducts = [];
if ($demoShop) {
    $pStmt = db()->prepare("SELECT * FROM products WHERE shop_id = ? AND active = 1 ORDER BY id DESC LIMIT 4");
    $pStmt->execute([$demoShop['id']]);
    $demoProducts = $pStmt->fetchAll();
}

include __DIR__ . '/templates/header.php';
?>

<!-- Hero Section -->
<section style="padding: 90px 0 70px; border-bottom: 1px solid var(--border-light); background: radial-gradient(circle at top right, rgba(249, 115, 22, 0.08), transparent 60%);">
  <div class="container" style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 48px; align-items: center;">
    <div>
      <span class="eyebrow"><?= icon('cart', 'icon-sm') ?> WhatsApp-First E-Commerce</span>
      <h1 class="heading-lg" style="margin: 12px 0 20px;">
        Turn Your Product Catalogue Into Instant <span style="color: var(--accent);">WhatsApp Orders</span>.
      </h1>
      <p class="subheading" style="margin-bottom: 32px;">
        Empower fashion boutiques, electronics retailers, artisan makers, and social sellers with high-converting online storefronts. No complex apps, no customer passwords, and zero friction checkout.
      </p>

      <div style="display: flex; flex-wrap: wrap; gap: 14px; margin-bottom: 32px;">
        <a href="<?= base_url('index.php?shop=demo') ?>" target="_blank" class="btn btn-primary btn-lg">
          <?= icon('external', 'icon') ?>
          <span>Explore Live Storefront</span>
        </a>
        <a href="<?= base_url('pricing.php') ?>" class="btn btn-secondary btn-lg">
          <?= icon('tag', 'icon') ?>
          <span>See Setup Packages</span>
        </a>
      </div>

      <div style="display: flex; align-items: center; gap: 24px; font-size: 13px; color: var(--text-muted);">
        <div style="display: flex; align-items: center; gap: 8px;">
          <?= icon('check', 'icon-sm') ?>
          <span>Pre-filled WhatsApp message</span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
          <?= icon('check', 'icon-sm') ?>
          <span>Independent Merchant Dashboard</span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
          <?= icon('check', 'icon-sm') ?>
          <span>Zero builder subscriptions</span>
        </div>
      </div>
    </div>

    <!-- Storefront Mockup Preview Card -->
    <div>
      <div class="card" style="padding: 24px; border: 2px solid var(--border-strong); border-radius: var(--radius-xl); box-shadow: var(--shadow-xl); background-color: var(--bg-surface);">
        <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 16px; border-bottom: 1px solid var(--border-light); margin-bottom: 20px;">
          <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 12px; height: 12px; border-radius: 50%; background-color: #ef4444;"></div>
            <div style="width: 12px; height: 12px; border-radius: 50%; background-color: #f59e0b;"></div>
            <div style="width: 12px; height: 12px; border-radius: 50%; background-color: #10b981;"></div>
          </div>
          <span class="badge badge-neutral" style="font-family: var(--font-mono); font-size: 11px;">
            quickcart.local/demo
          </span>
          <span class="badge badge-success">Online</span>
        </div>

        <div style="background-color: var(--bg-subtle); padding: 18px; border-radius: var(--radius-md); margin-bottom: 18px;">
          <span class="eyebrow" style="font-size: 10px;">Storefront Experience</span>
          <h3 style="font-size: 18px; margin-bottom: 4px;"><?= e($demoShop['name'] ?? 'Boutique Store') ?></h3>
          <p style="font-size: 12px; color: var(--text-muted);"><?= e($demoShop['tagline'] ?? 'Good products. Fast WhatsApp ordering.') ?></p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px;">
          <?php foreach (array_slice($demoProducts, 0, 2) as $dp): ?>
            <div style="border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 12px; background-color: var(--bg-surface);">
              <div style="height: 90px; background-color: var(--bg-subtle); border-radius: var(--radius-sm); margin-bottom: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                <?php if (!empty($dp['image_path'])): ?>
                  <img src="<?= base_url($dp['image_path']) ?>" alt="<?= e($dp['name']) ?>" style="width:100%; height:100%; object-fit: cover;">
                <?php else: ?>
                  <span style="font-size: 28px; font-weight: 800; color: var(--text-subtle);"><?= strtoupper(substr($dp['name'], 0, 1)) ?></span>
                <?php endif; ?>
              </div>
              <div style="font-size: 12px; font-weight: 700; margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= e($dp['name']) ?></div>
              <div style="display: flex; justify-content: space-between; align-items: center; font-size: 11px;">
                <span style="font-weight: 800; color: var(--accent);"><?= e($demoShop['currency'] ?? 'KES') ?> <?= number_format((float)$dp['price'], 2) ?></span>
                <span class="badge badge-primary" style="padding: 2px 6px; font-size: 10px;">1-Tap Order</span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div style="padding: 12px 16px; background-color: #25d36615; border: 1px dashed #25D366; border-radius: var(--radius-md); display: flex; align-items: center; gap: 12px;">
          <div style="color: #25D366;"><?= icon('whatsapp', 'icon') ?></div>
          <div style="font-size: 12px; color: var(--text-main);">
            <strong>Pre-formatted message generated automatically:</strong> item name, quantity, price breakdown & order reference number.
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Why QuickCart Studio -->
<section style="padding: 80px 0; background-color: var(--bg-surface);">
  <div class="container">
    <div style="text-align: center; max-width: 680px; margin: 0 auto 60px;">
      <span class="eyebrow">BUILT FOR REAL CONVERSATIONAL COMMERCE</span>
      <h2 class="heading-md" style="margin-bottom: 16px;">Why Traditional E-Commerce Stalls Small Businesses</h2>
      <p style="color: var(--text-muted); font-size: 16px;">
        In social and local commerce, buyers don't want 6-step checkouts, credit card forms, or forgotten passwords. They want to see what is in stock and order immediately on WhatsApp.
      </p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 32px;">
      <div class="card card-hover">
        <div style="width: 48px; height: 48px; border-radius: 12px; background-color: var(--accent-light); color: var(--accent); display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
          <?= icon('whatsapp', 'icon') ?>
        </div>
        <h3 style="font-size: 20px; margin-bottom: 12px;">Zero Friction WhatsApp Ordering</h3>
        <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6;">
          Customers tap order and immediately jump into your shop's official WhatsApp chat with the product name, quantity, total price, and reference number ready to send.
        </p>
      </div>

      <div class="card card-hover">
        <div style="width: 48px; height: 48px; border-radius: 12px; background-color: var(--color-success-bg); color: var(--color-success); display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
          <?= icon('store', 'icon') ?>
        </div>
        <h3 style="font-size: 20px; margin-bottom: 12px;">Multi-Tenant Architecture</h3>
        <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6;">
          One installation manages dozens of distinct, brand-isolated customer shops. Each merchant gets their own private dashboard, product inventory, and customer orders.
        </p>
      </div>

      <div class="card card-hover">
        <div style="width: 48px; height: 48px; border-radius: 12px; background-color: var(--color-info-bg); color: var(--color-info); display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
          <?= icon('package', 'icon') ?>
        </div>
        <h3 style="font-size: 20px; margin-bottom: 12px;">Live Inventory & Order Records</h3>
        <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6;">
          Orders are logged with individual line item snapshots and transaction-safe stock subtraction before the customer hits WhatsApp, preventing overselling.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- How It Works Section -->
<section style="padding: 80px 0; border-top: 1px solid var(--border-light); border-bottom: 1px solid var(--border-light);">
  <div class="container">
    <div style="text-align: center; max-width: 640px; margin: 0 auto 54px;">
      <span class="eyebrow">SIMPLE 3-STEP WORKFLOW</span>
      <h2 class="heading-md">How QuickCart Studio Works</h2>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 32px; counter-reset: steps;">
      <div class="card" style="position: relative;">
        <span class="badge badge-primary" style="margin-bottom: 16px;">Step 1</span>
        <h3 style="font-size: 18px; margin-bottom: 10px;">We Set Up Your Shop</h3>
        <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6;">
          Your custom shop URL is created (e.g. <code>quickcart.local/?shop=yourstore</code>), customized with your logo, brand colors, currency, and WhatsApp number.
        </p>
      </div>

      <div class="card" style="position: relative;">
        <span class="badge badge-primary" style="margin-bottom: 16px;">Step 2</span>
        <h3 style="font-size: 18px; margin-bottom: 10px;">Manage Products & Categories</h3>
        <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6;">
          Log into your private merchant dashboard. Upload product photos, set prices, define categories, and manage stock with immediate updates.
        </p>
      </div>

      <div class="card" style="position: relative;">
        <span class="badge badge-primary" style="margin-bottom: 16px;">Step 3</span>
        <h3 style="font-size: 18px; margin-bottom: 10px;">Receive Ready Orders On WhatsApp</h3>
        <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6;">
          Share your shop link on Instagram, TikTok, Facebook, or your WhatsApp status. Customers browse, choose quantities, and send complete order messages directly to your chat.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- Pricing Summary on Homepage -->
<section style="padding: 80px 0; background-color: var(--bg-surface);">
  <div class="container">
    <div style="text-align: center; max-width: 600px; margin: 0 auto 48px;">
      <span class="eyebrow">TRANSPARENT SERVICE TIERS</span>
      <h2 class="heading-md" style="margin-bottom: 12px;">Affordable Shop Packages</h2>
      <p style="color: var(--text-muted); font-size: 15px;">
        Choose the level of setup and support that matches your business stage. Fully editable in platform settings.
      </p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 28px;">
      <?php foreach ($plans as $plan): ?>
        <div class="card card-hover" style="display: flex; flex-direction: column; <?= $plan['is_popular'] ? 'border: 2px solid var(--accent);' : '' ?>">
          <?php if (!empty($plan['badge'])): ?>
            <div style="margin-bottom: 12px;">
              <span class="badge <?= $plan['is_popular'] ? 'badge-primary' : 'badge-neutral' ?>">
                <?= e($plan['badge']) ?>
              </span>
            </div>
          <?php endif; ?>

          <h3 style="font-size: 22px; margin-bottom: 6px;"><?= e($plan['name']) ?></h3>
          <p style="color: var(--text-muted); font-size: 13px; line-height: 1.5; min-height: 40px; margin-bottom: 18px;"><?= e($plan['description']) ?></p>

          <div style="margin-bottom: 24px;">
            <span style="font-size: 36px; font-weight: 800; font-family: var(--font-heading);">$<?= number_format((float)$plan['price'], 0) ?></span>
            <span style="color: var(--text-muted); font-size: 13px;"> / <?= e($plan['billing_cycle']) ?></span>
          </div>

          <ul style="list-style: none; display: flex; flex-direction: column; gap: 12px; margin-bottom: 32px; flex: 1;">
            <?php 
            $features = json_decode($plan['features_json'] ?? '[]', true);
            if (is_array($features)):
              foreach ($features as $f): ?>
                <li style="display: flex; align-items: flex-start; gap: 10px; font-size: 13px; color: var(--text-main);">
                  <span style="color: var(--accent); margin-top: 2px;"><?= icon('check', 'icon-sm') ?></span>
                  <span><?= e($f) ?></span>
                </li>
            <?php endforeach; endif; ?>
          </ul>

          <a href="<?= base_url('contact.php?plan=' . urlencode($plan['slug'])) ?>" class="btn <?= $plan['is_popular'] ? 'btn-primary' : 'btn-secondary' ?>" style="width: 100%;">
            Request <?= e($plan['name']) ?>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Call to Action Banner -->
<section style="padding: 70px 0; border-top: 1px solid var(--border-light); background: linear-gradient(135deg, var(--brand-navy) 0%, var(--brand-navy-light) 100%); color: #fff;">
  <div class="container" style="text-align: center; max-width: 680px;">
    <h2 style="font-size: clamp(24px, 3.5vw, 36px); color: #fff; margin-bottom: 16px;">Ready to Launch Your High-Converting Shop?</h2>
    <p style="color: #cbd5e1; font-size: 16px; margin-bottom: 32px; line-height: 1.6;">
      See how your products look in a live, real-time catalogue with 1-tap WhatsApp checkout.
    </p>
    <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
      <a href="<?= base_url('index.php?shop=demo') ?>" target="_blank" class="btn btn-primary btn-lg">
        <?= icon('store', 'icon') ?>
        <span>View Live Demo Shop</span>
      </a>
      <a href="<?= base_url('contact.php') ?>" class="btn btn-secondary btn-lg" style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); color: #fff;">
        <?= icon('mail', 'icon') ?>
        <span>Get in Touch With Us</span>
      </a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/templates/footer.php'; ?>
