<?php
require __DIR__ . '/../includes/config.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = (string)($_POST['password'] ?? '');
    $stmt = db()->prepare('SELECT id, shop_id, password_hash FROM admins WHERE email = ? AND active = 1 LIMIT 1');
    $stmt->execute([$email ?: '']);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int)$admin['id'];
        $_SESSION['shop_id'] = (int)$admin['shop_id'];
        header('Location: dashboard.php'); exit;
    }
    $error = 'Invalid email or password.';
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Shop Admin Login</title><link rel="stylesheet" href="../assets/style.css"><style>.login{max-width:420px;margin:80px auto;background:white;border:1px solid var(--line);padding:28px;border-radius:12px}.login label{display:block;font-size:13px;margin:16px 0 6px}.login input{width:100%;padding:12px;border:1px solid var(--line);border-radius:6px;font:inherit}.login button{border:0;margin-top:20px;cursor:pointer}.error{color:#b91c1c;font-size:13px}</style></head><body><main class="login"><span class="eyebrow">SHOP MANAGEMENT</span><h2>Admin sign in</h2><?php if($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label for="email">Email</label><input id="email" type="email" name="email" required autocomplete="username"><label for="password">Password</label><input id="password" type="password" name="password" required autocomplete="current-password"><button class="button" type="submit">Sign in</button></form><p style="font-size:12px;color:#71717a;margin-top:20px">Create your first admin using the setup instructions in README.md.</p></main></body></html>
