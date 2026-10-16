<?php
require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $adminId = $_SESSION['admin_id'] ?? null;
    if ($adminId) {
        log_audit('logout', "Admin logged out");
    }
}

// Clear session securely
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}
session_destroy();

redirect(base_url('admin/login.php'));
