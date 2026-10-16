<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/icons.php';

// Storefront tenant lookup
$shop = current_shop();
if (!$shop) {
    http_response_code(404);
    include __DIR__ . '/templates/error_404.php';
    exit;
}

// Check published status unless previewing as admin
$isAdminViewing = is_logged_in() && (is_platform_owner() || admin_shop_id() === (int)$shop['id']);
if (!$shop['published'] && !$isAdminViewing) {
    http_response_code(403);
    echo "<div style='font-family: sans-serif; text-align: center; padding: 60px;'><h2>This shop is currently offline.</h2><p>The merchant has temporarily unpublished this catalogue.</p></div>";
    exit;
}

// Fetch categories for this shop
$catStmt = db()->prepare('SELECT * FROM categories WHERE shop_id = ? ORDER BY sort_order ASC, name ASC');
$catStmt->execute([(int)$shop['id']]);
$categories = $catStmt->fetchAll();

// Fetch active products
$prodStmt = db()->prepare('SELECT * FROM products WHERE shop_id = ? AND active = 1 AND stock > 0 ORDER BY created_at DESC');
$prodStmt->execute([(int)$shop['id']]);
$products = $prodStmt->fetchAll();

$accentColor = !empty($shop['accent_color']) ? $shop['accent_color'] : '#f97316';
$currency = !empty($shop['currency']) ? $shop['currency'] : 'KES';
$whatsapp = preg_replace('/\D+/', '', (string)$shop['whatsapp_number']);
?>
<!doctype html>
<html lang="en" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($shop['name']) ?> — Online Product Catalogue</title>
  <meta name="description" content="<?= e($shop['tagline'] ?: ($shop['name'] . ' online storefront catalogue with fast WhatsApp ordering.')) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('assets/style.css') ?>">
  <style>
    :root {
      --accent: <?= e($accentColor) ?>;
      --accent-hover: <?= e($accentColor) ?>;
    }
  </style>
</head>
<body>

<!-- Storefront Top Bar -->
<header class="site-nav">
  <div class="container nav-inner">
    <a class="brand-logo" href="<?= base_url('index.php?shop=' . urlencode($shop['slug'])) ?>">
      <?php if (!empty($shop['logo_path']) && file_exists(__DIR__ . '/' . $shop['logo_path'])): ?>
        <img src="<?= base_url($shop['logo_path']) ?>" alt="<?= e($shop['name']) ?>" style="height: 38px; border-radius: var(--radius-sm); object-fit: contain;">
      <?php else: ?>
        <div class="brand-mark" style="background: var(--accent);"><?= strtoupper(substr($shop['name'], 0, 1)) ?></div>
      <?php endif; ?>
      <span><?= e($shop['name']) ?></span>
    </a>

    <div class="nav-actions">
      <button class="btn btn-icon theme-toggle-btn" type="button" aria-label="Toggle Theme">
        <?= icon('sun', 'icon-sm') ?>
      </button>

      <?php if (!empty($whatsapp)): ?>
        <a class="btn btn-whatsapp btn-sm" href="https://wa.me/<?= e($whatsapp) ?>" target="_blank" rel="noopener">
          <?= icon('whatsapp', 'icon-sm') ?>
          <span style="display: inline-block;">Chat with Shop</span>
        </a>
      <?php endif; ?>

      <?php if ($isAdminViewing): ?>
        <a class="btn btn-secondary btn-sm" href="<?= base_url('admin/dashboard.php') ?>">
          <?= icon('settings', 'icon-sm') ?>
          <span>Manage Shop</span>
        </a>
      <?php else: ?>
        <a class="btn btn-secondary btn-sm" href="<?= base_url('admin/login.php') ?>" title="Merchant Sign In">
          <?= icon('users', 'icon-sm') ?>
        </a>
      <?php endif; ?>
    </div>
  </div>
</header>

<!-- Store Hero -->
<section style="padding: 56px 0 36px; border-bottom: 1px solid var(--border-light); background: radial-gradient(circle at top right, rgba(0, 0, 0, 0.02), transparent 70%);">
  <div class="container">
    <span class="eyebrow">WELCOME TO OUR STOREFRONT</span>
    <h1 class="heading-lg" style="margin: 10px 0 14px;"><?= e($shop['name']) ?></h1>
    <?php if (!empty($shop['tagline'])): ?>
      <p class="subheading" style="margin-bottom: 20px;"><?= e($shop['tagline']) ?></p>
    <?php endif; ?>

    <?php if (!empty($shop['description'])): ?>
      <p style="color: var(--text-muted); font-size: 14px; max-width: 680px; line-height: 1.6;"><?= nl2br(e($shop['description'])) ?></p>
    <?php endif; ?>
  </div>
</section>

<!-- Catalogue Navigation: Live Search & Category Pills -->
<main class="container" style="padding-top: 40px; padding-bottom: 80px;">
  <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 20px; margin-bottom: 32px;">
    <!-- Category Filter Pills -->
    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
      <button class="btn btn-sm btn-primary category-filter-btn active" data-category-slug="all" type="button">
        All Items (<?= count($products) ?>)
      </button>
      <?php foreach ($categories as $c): ?>
        <button class="btn btn-sm btn-secondary category-filter-btn" data-category-slug="<?= e($c['slug']) ?>" type="button">
          <?= e($c['name']) ?>
        </button>
      <?php endforeach; ?>
    </div>

    <!-- Search input -->
    <div style="position: relative; width: min(320px, 100%);">
      <input id="productSearch" class="form-control" type="search" placeholder="Search catalog by name or tag…" aria-label="Search products" style="padding-left: 36px;">
      <div style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-subtle);">
        <?= icon('search', 'icon-sm') ?>
      </div>
    </div>
  </div>

  <!-- Product Grid -->
  <div class="catalog-grid" id="productGrid">
    <?php foreach ($products as $p): 
      $categorySlug = 'general';
      foreach ($categories as $cat) {
          if ($cat['id'] == $p['category_id'] || strtolower($cat['name']) === strtolower($p['category'])) {
              $categorySlug = $cat['slug'];
              break;
          }
      }
      $searchText = strtolower($p['name'] . ' ' . $p['category'] . ' ' . $p['description']);
    ?>
      <article class="catalog-card" data-category="<?= e($categorySlug) ?>" data-search="<?= e($searchText) ?>">
        <div class="catalog-image-wrap">
          <?php if (!empty($p['image_path']) && file_exists(__DIR__ . '/' . $p['image_path'])): ?>
            <img src="<?= base_url($p['image_path']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
          <?php else: ?>
            <div style="font-size: 44px; font-weight: 800; color: var(--text-subtle);"><?= strtoupper(substr($p['name'], 0, 1)) ?></div>
          <?php endif; ?>

          <?php if ((int)$p['stock'] <= 5): ?>
            <span class="badge badge-warning" style="position: absolute; top: 12px; right: 12px;">Only <?= (int)$p['stock'] ?> Left</span>
          <?php endif; ?>
        </div>

        <div class="catalog-content">
          <div class="catalog-meta">
            <span class="badge badge-neutral" style="font-size: 11px;"><?= e($p['category'] ?: 'General') ?></span>
            <span style="font-size: 12px; color: var(--color-success); font-weight: 600;">In Stock</span>
          </div>

          <h2 class="catalog-title"><?= e($p['name']) ?></h2>
          <p class="catalog-desc"><?= e($p['description'] ?: 'High quality crafted merchandise ready for immediate dispatch.') ?></p>

          <div class="catalog-bottom">
            <div>
              <div style="font-size: 11px; color: var(--text-subtle); text-transform: uppercase;">Price</div>
              <div class="catalog-price"><?= format_currency((float)$p['price'], $currency) ?></div>
            </div>

            <!-- WhatsApp Order Submission Form -->
            <form method="post" action="<?= base_url('order.php?shop=' . urlencode($shop['slug'])) ?>" target="_blank" style="display: flex; align-items: center; gap: 8px;">
              <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">

              <label class="sr-only" for="qty-<?= (int)$p['id'] ?>">Quantity</label>
              <input id="qty-<?= (int)$p['id'] ?>" class="form-control" name="quantity" type="number" min="1" max="<?= (int)$p['stock'] ?>" value="1" style="width: 58px; padding: 6px 8px; text-align: center;" title="Quantity">

              <button class="btn btn-whatsapp btn-sm" type="submit" title="Order via WhatsApp">
                <?= icon('whatsapp', 'icon-sm') ?>
                <span>Order</span>
              </button>
            </form>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

  <!-- Empty Search Feedback State -->
  <div id="productEmptyState" class="empty-state" style="display: none; margin-top: 40px;">
    <div class="empty-state-icon"><?= icon('search', 'icon-lg') ?></div>
    <div class="empty-state-title">No matching products found</div>
    <div class="empty-state-desc">Try clearing your search term or selecting a different category filter.</div>
  </div>

  <?php if (empty($products)): ?>
    <div class="empty-state" style="margin-top: 40px;">
      <div class="empty-state-icon"><?= icon('package', 'icon-lg') ?></div>
      <div class="empty-state-title">Catalogue Coming Soon</div>
      <div class="empty-state-desc">This shop currently has no products published. Check back shortly!</div>
    </div>
  <?php endif; ?>
</main>

<!-- Storefront Footer -->
<footer style="background-color: var(--bg-surface); border-top: 1px solid var(--border-light); padding: 40px 0; font-size: 13px; color: var(--text-muted);">
  <div class="container" style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 20px;">
    <div>
      <strong><?= e($shop['name']) ?></strong> — Direct WhatsApp Shopping.
      <div style="font-size: 12px; color: var(--text-subtle); margin-top: 4px;">
        Prices listed in <?= e($currency) ?>. All orders confirmed directly with the merchant via WhatsApp.
      </div>
    </div>

    <div style="display: flex; align-items: center; gap: 16px;">
      <a href="<?= base_url('home.php') ?>" style="color: var(--text-muted); font-size: 12px;">Powered by QuickCart Studio</a>
    </div>
  </div>
</footer>

<script src="<?= base_url('assets/app.js') ?>"></script>
</body>
</html>
