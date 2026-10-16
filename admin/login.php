<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/icons.php';

$error = '';
$emailInput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = (string)($_POST['password'] ?? '');
    $emailInput = (string)($_POST['email'] ?? '');

    if (!$email || empty($password)) {
        $error = 'Please provide both your registered email address and password.';
    } else {
        $stmt = db()->prepare('SELECT * FROM admins WHERE email = ? AND active = 1 LIMIT 1');
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            // Regenerate session ID to prevent session fixation attacks
            session_regenerate_id(true);

            $_SESSION['admin_id'] = (int)$admin['id'];
            $_SESSION['shop_id'] = (int)($admin['shop_id'] ?? 1);
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['is_platform_owner'] = ($admin['role'] === 'platform_owner');

            log_audit('login_success', "Admin {$admin['email']} logged in successfully", (int)$admin['shop_id']);

            if ($admin['role'] === 'platform_owner') {
                redirect(base_url('admin/platform_overview.php'));
            } else {
                redirect(base_url('admin/dashboard.php'));
            }
        } else {
            $error = 'Invalid email or password. Please verify your credentials and try again.';
            log_audit('login_failed', "Failed login attempt for {$emailInput}");
        }
    }
}
?>
<!doctype html>
<html lang="en" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Merchant Sign In — QuickCart Studio</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('assets/style.css') ?>">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 24px; background: radial-gradient(circle at top, rgba(249, 115, 22, 0.05), transparent 70%);">

<div class="card" style="max-width: 440px; width: 100%; padding: 40px 32px; box-shadow: var(--shadow-xl); border: 1px solid var(--border-strong);">
  <div style="text-align: center; margin-bottom: 28px;">
    <a class="brand-logo" href="<?= base_url('home.php') ?>" style="justify-content: center; margin-bottom: 16px;">
      <div class="brand-mark"><?= icon('cart', 'icon') ?></div>
      <span>QuickCart<span style="color:var(--accent);">.</span></span>
    </a>
    <h1 style="font-size: 24px; margin-bottom: 8px;">Merchant Sign In</h1>
    <p style="font-size: 13px; color: var(--text-muted);">Manage your catalogue, stock, and orders</p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger" style="margin-bottom: 20px;">
      <?= icon('x', 'icon-sm') ?>
      <span><?= e($error) ?></span>
    </div>
  <?php endif; ?>

  <form method="post" action="<?= base_url('admin/login.php') ?>">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

    <div class="form-group">
      <label class="form-label" for="email">Account Email</label>
      <input class="form-control" type="email" id="email" name="email" required autocomplete="username" value="<?= e($emailInput) ?>" placeholder="owner@store.com">
    </div>

    <div class="form-group">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 7px;">
        <label class="form-label" for="password" style="margin-bottom: 0;">Password</label>
        <button class="password-toggle-btn" type="button" data-target="password" style="background: none; border: none; font-size: 12px; color: var(--accent); cursor: pointer;">
          Toggle Visibility
        </button>
      </div>
      <input class="form-control" type="password" id="password" name="password" required autocomplete="current-password" placeholder="••••••••">
    </div>

    <button class="btn btn-primary btn-lg" type="submit" style="width: 100%; margin-top: 10px;">
      <?= icon('logout', 'icon-sm') ?>
      <span>Sign In to Dashboard</span>
    </button>
  </form>

  <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-light); text-align: center; font-size: 13px; color: var(--text-muted);">
    <a href="<?= base_url('home.php') ?>" style="color: var(--accent); font-weight: 600;">← Return to Platform Home</a>
  </div>
</div>

<script src="<?= base_url('assets/app.js') ?>"></script>
</body>
</html>
