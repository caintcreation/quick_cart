<?php
// templates/header.php
if (!isset($pageTitle)) $pageTitle = APP_NAME . ' — Fast WhatsApp E-Commerce Storefront Builder';
?>
<!doctype html>
<html lang="en" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($metaDescription ?? 'Turn your product catalogue into direct, high-converting WhatsApp orders without complex apps or recurring builder fees.') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('assets/style.css') ?>">
</head>
<body>
<nav class="site-nav">
  <div class="container nav-inner">
    <a class="brand-logo" href="<?= base_url('home.php') ?>">
      <div class="brand-mark"><?= icon('cart', 'icon') ?></div>
      <span>QuickCart<span style="color:var(--accent);">.</span></span>
    </a>

    <ul class="nav-links">
      <li><a class="nav-link <?= ($activeNav ?? '') === 'home' ? 'active' : '' ?>" href="<?= base_url('home.php') ?>">Home</a></li>
      <li><a class="nav-link <?= ($activeNav ?? '') === 'features' ? 'active' : '' ?>" href="<?= base_url('features.php') ?>">Features</a></li>
      <li><a class="nav-link <?= ($activeNav ?? '') === 'pricing' ? 'active' : '' ?>" href="<?= base_url('pricing.php') ?>">Pricing</a></li>
      <li><a class="nav-link <?= ($activeNav ?? '') === 'about' ? 'active' : '' ?>" href="<?= base_url('about.php') ?>">About</a></li>
      <li><a class="nav-link <?= ($activeNav ?? '') === 'contact' ? 'active' : '' ?>" href="<?= base_url('contact.php') ?>">Contact</a></li>
    </ul>

    <div class="nav-actions">
      <button class="btn btn-icon theme-toggle-btn" type="button" aria-label="Toggle theme">
        <?= icon('sun', 'icon') ?>
      </button>

      <a class="btn btn-secondary btn-sm" href="<?= base_url('index.php?shop=demo') ?>" target="_blank">
        <?= icon('store', 'icon-sm') ?>
        <span>Live Demo</span>
      </a>

      <?php if (is_logged_in()): ?>
        <a class="btn btn-primary btn-sm" href="<?= base_url('admin/dashboard.php') ?>">
          <?= icon('platform', 'icon-sm') ?>
          <span>Dashboard</span>
        </a>
      <?php else: ?>
        <a class="btn btn-dark btn-sm" href="<?= base_url('admin/login.php') ?>">
          Sign In
        </a>
      <?php endif; ?>

      <button class="mobile-menu-btn" type="button" aria-label="Toggle Menu">
        <?= icon('tag', 'icon') ?>
      </button>
    </div>
  </div>
</nav>
