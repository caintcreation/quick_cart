<?php
// templates/error_403.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/icons.php';
$pageTitle = '403 Forbidden — ' . APP_NAME;
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
    <div style="color: var(--color-danger); margin-bottom: 20px;">
      <?= icon('x', 'icon-lg') ?>
    </div>
    <h1 style="font-size: 28px; margin-bottom: 12px;">Access Restricted</h1>
    <p style="color: var(--text-muted); margin-bottom: 28px; line-height: 1.6;">
      You do not have administrative permission to view this platform area. Platform owner privileges are required.
    </p>
    <a href="<?= base_url('admin/dashboard.php') ?>" class="btn btn-primary">Return to Shop Dashboard</a>
  </div>
</body>
</html>
