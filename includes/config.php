<?php
declare(strict_types=1);
ini_set('session.use_strict_mode', '1');
session_start();

// Local XAMPP defaults. For production, set these through environment variables.
const DB_HOST = '127.0.0.1';
const DB_NAME = 'quickcart';
const DB_USER = 'root';
const DB_PASS = '';

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    if (!extension_loaded('pdo_mysql')) {
        throw new RuntimeException(
            'The PHP PDO MySQL driver is not enabled. Enable "extension=pdo_mysql" in the php.ini used by Apache, then restart Apache in XAMPP.'
        );
    }
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void {
    $token = (string)($_POST['csrf_token'] ?? '');
    if (!hash_equals((string)($_SESSION['csrf_token'] ?? ''), $token)) {
        http_response_code(419);
        exit('Your form session expired. Go back, refresh the page, and try again.');
    }
}

function redirect(string $location): never {
    header('Location: ' . $location);
    exit;
}

function current_shop(): array {
    $slug = preg_replace('/[^a-z0-9-]/', '', strtolower($_GET['shop'] ?? 'demo'));
    $stmt = db()->prepare('SELECT * FROM shops WHERE slug = ? AND published = 1 LIMIT 1');
    $stmt->execute([$slug]);
    $shop = $stmt->fetch();
    if (!$shop) {
        http_response_code(404);
        exit('Shop not found.');
    }
    return $shop;
}

function require_admin(): void {
    if (empty($_SESSION['admin_id']) || empty($_SESSION['shop_id'])) {
        redirect('login.php');
    }
}

function admin_shop_id(): int {
    require_admin();
    return (int)$_SESSION['shop_id'];
}
