<?php
// templates/admin_header.php
if (!isset($pageTitle)) $pageTitle = 'Dashboard — ' . APP_NAME;
$user = current_user();
$shopId = admin_shop_id();
$shopStmt = db()->prepare('SELECT * FROM shops WHERE id = ? LIMIT 1');
$shopStmt->execute([$shopId]);
$activeShop = $shopStmt->fetch();

$unreadOrders = 0;
if ($activeShop) {
    $orderCountStmt = db()->prepare("SELECT COUNT(*) FROM orders WHERE shop_id = ? AND status = 'pending'");
    $orderCountStmt->execute([$activeShop['id']]);
    $unreadOrders = (int)$orderCountStmt->fetchColumn();
}
?>
<!doctype html>
<html lang="en" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('assets/style.css') ?>">
</head>
<body>

<div class="admin-layout">
  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div class="sidebar-header">
      <a class="brand-logo" href="<?= base_url('admin/dashboard.php') ?>">
        <div class="brand-mark"><?= icon('cart', 'icon') ?></div>
        <span>QuickCart</span>
      </a>
    </div>

    <div class="sidebar-body">
      <?php if (is_platform_owner()): ?>
        <div class="sidebar-heading">Platform Administration</div>
        <ul class="sidebar-nav" style="margin-bottom: 16px;">
          <li>
            <a class="sidebar-link <?= ($adminNav ?? '') === 'platform_overview' ? 'active' : '' ?>" href="<?= base_url('admin/platform_overview.php') ?>">
              <?= icon('platform', 'icon') ?>
              <span>All Shops & Stats</span>
            </a>
          </li>
          <li>
            <a class="sidebar-link <?= ($adminNav ?? '') === 'platform_plans' ? 'active' : '' ?>" href="<?= base_url('admin/platform_plans.php') ?>">
              <?= icon('dollar', 'icon') ?>
              <span>Pricing Packages</span>
            </a>
          </li>
          <li>
            <a class="sidebar-link <?= ($adminNav ?? '') === 'platform_enquiries' ? 'active' : '' ?>" href="<?= base_url('admin/platform_enquiries.php') ?>">
              <?= icon('mail', 'icon') ?>
              <span>Inquiries</span>
            </a>
          </li>
          <li>
            <a class="sidebar-link <?= ($adminNav ?? '') === 'platform_payments' ? 'active' : '' ?>" href="<?= base_url('admin/platform_payments.php') ?>">
              <?= icon('orders', 'icon') ?>
              <span>Service Payments</span>
            </a>
          </li>
        </ul>
      <?php endif; ?>

      <div class="sidebar-heading">
        <?= $activeShop ? e($activeShop['name']) : 'Shop Management' ?>
      </div>
      <ul class="sidebar-nav">
        <li>
          <a class="sidebar-link <?= ($adminNav ?? '') === 'dashboard' ? 'active' : '' ?>" href="<?= base_url('admin/dashboard.php') ?>">
            <?= icon('store', 'icon') ?>
            <span>Overview</span>
          </a>
        </li>
        <li>
          <a class="sidebar-link <?= ($adminNav ?? '') === 'products' ? 'active' : '' ?>" href="<?= base_url('admin/products.php') ?>">
            <?= icon('package', 'icon') ?>
            <span>Products</span>
          </a>
        </li>
        <li>
          <a class="sidebar-link <?= ($adminNav ?? '') === 'categories' ? 'active' : '' ?>" href="<?= base_url('admin/categories.php') ?>">
            <?= icon('tag', 'icon') ?>
            <span>Categories</span>
          </a>
        </li>
        <li>
          <a class="sidebar-link <?= ($adminNav ?? '') === 'orders' ? 'active' : '' ?>" href="<?= base_url('admin/orders.php') ?>">
            <?= icon('orders', 'icon') ?>
            <span>Orders</span>
            <?php if ($unreadOrders > 0): ?>
              <span class="badge badge-primary" style="margin-left: auto;"><?= $unreadOrders ?></span>
            <?php endif; ?>
          </a>
        </li>
        <li>
          <a class="sidebar-link <?= ($adminNav ?? '') === 'settings' ? 'active' : '' ?>" href="<?= base_url('admin/settings.php') ?>">
            <?= icon('settings', 'icon') ?>
            <span>Shop Settings</span>
          </a>
        </li>
      </ul>
    </div>

    <div class="sidebar-footer">
      <div style="font-size: 13px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
        <strong><?= e($user['email'] ?? 'Admin') ?></strong>
        <div style="color: var(--text-muted); font-size: 11px; text-transform: capitalize;"><?= e(str_replace('_', ' ', $user['role'] ?? 'Admin')) ?></div>
      </div>
      <form method="post" action="<?= base_url('admin/logout.php') ?>" style="margin: 0;">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <button class="btn btn-icon" type="submit" title="Sign Out">
          <?= icon('logout', 'icon-sm') ?>
        </button>
      </form>
    </div>
  </aside>

  <!-- Main Content Wrap -->
  <div class="admin-content">
    <header class="admin-topbar">
      <div style="display: flex; align-items: center; gap: 14px;">
        <button class="btn btn-icon admin-menu-toggle" type="button" aria-label="Toggle menu" style="display: inline-flex;">
          <?= icon('tag', 'icon-sm') ?>
        </button>

        <?php if ($activeShop): ?>
          <a class="btn btn-secondary btn-sm" href="<?= base_url('index.php?shop=' . urlencode($activeShop['slug'])) ?>" target="_blank">
            <?= icon('external', 'icon-sm') ?>
            <span>View Storefront</span>
          </a>
        <?php endif; ?>
      </div>

      <div style="display: flex; align-items: center; gap: 12px;">
        <button class="btn btn-icon theme-toggle-btn" type="button" title="Toggle Light/Dark Theme">
          <?= icon('sun', 'icon-sm') ?>
        </button>
      </div>
    </header>

    <main class="admin-main">
      <?php if ($flash = get_flash()): ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : e($flash['type']) ?>">
          <?= icon($flash['type'] === 'error' ? 'x' : 'check', 'icon-sm') ?>
          <span><?= e($flash['message']) ?></span>
          <button type="button" class="alert-dismiss btn-icon btn-sm" style="margin-left: auto; background: none; border: none; cursor: pointer;">
            <?= icon('x', 'icon-sm') ?>
          </button>
        </div>
      <?php endif; ?>
