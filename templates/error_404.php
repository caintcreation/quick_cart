<?php
// templates/error_404.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/icons.php';
$pageTitle = '404 Not Found — ' . APP_NAME;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <link rel="stylesheet" href="<?= base_url('assets/style.css') ?>">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; text-align: center; padding: 24px;">
  <div class="card" style="max-width: 480px; width: 100%; padding: 48px 32px;">
    <div style="color: var(--accent); margin-bottom: 20px;">
      <?= icon('store', 'icon-lg') ?>
    </div>
    <h1 style="font-size: 32px; margin-bottom: 12px;">Shop Or Page Not Found</h1>
    <p style="color: var(--text-muted); margin-bottom: 28px; line-height: 1.6;">
      The storefront or page you are trying to visit is not available or has not been published yet.
    </p>
    <div style="display: flex; gap: 12px; justify-content: center;">
      <a href="<?= base_url('home.php') ?>" class="btn btn-secondary">Platform Home</a>
      <a href="<?= base_url('index.php?shop=demo') ?>" class="btn btn-primary">Browse Demo Shop</a>
    </div>
  </div>
</body>
</html>
