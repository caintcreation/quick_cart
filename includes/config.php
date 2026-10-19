<?php
declare(strict_types=1);

/**
 * QuickCart Studio - Central Configuration & Helper Services
 * High-performance, clean, modular PHP 8 architecture.
 */

// Strict session settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    // If running under HTTPS, enable secure cookies
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// Optional .env loader for easy production deployment
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            [$k, $v] = explode('=', $line, 2);
            $k = trim($k);
            $v = trim($v, " \t\n\r\0\x0B\"'");
            putenv("{$k}={$v}");
            $_ENV[$k] = $v;
        }
    }
}

// Database Credentials (override with env if present)
define('DB_HOST', getenv('QC_DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('QC_DB_NAME') ?: 'quickcart');
define('DB_USER', getenv('QC_DB_USER') ?: 'root');
define('DB_PASS', getenv('QC_DB_PASS') !== false ? getenv('QC_DB_PASS') : '');
define('DB_PORT', getenv('QC_DB_PORT') ?: '3306');

// Platform default settings
define('APP_NAME', 'QuickCart Studio');
define('APP_TAGLINE', 'Turn WhatsApp into your highest-converting online storefront');
define('APP_SUPPORT_EMAIL', 'hello@quickcartstudio.com');
define('APP_SUPPORT_WHATSAPP', '254700000000');

/**
 * Returns singleton PDO connection instance
 */
function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if (!extension_loaded('pdo_mysql')) {
        throw new RuntimeException(
            'The PHP PDO MySQL driver is not enabled. Enable "extension=pdo_mysql" in php.ini, then restart Apache.'
        );
    }

    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    return $pdo;
}

/**
 * Safe HTML escaping helper
 */
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * CSRF Protection
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void {
    $token = (string)($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!hash_equals((string)($_SESSION['csrf_token'] ?? ''), $token)) {
        http_response_code(419);
        exit('Your form session expired or was invalid. Please go back, refresh the page, and try again.');
    }
}

/**
 * Safe HTTP Redirect
 */
function redirect(string $location): never {
    header('Location: ' . $location);
    exit;
}

/**
 * Get base URL for absolute asset referencing
 */
function base_url(string $path = ''): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Compute project root directory relative to web root
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    // Look for /quickcart-studio or root
    $base = rtrim(str_replace('\\', '/', dirname($script)), '/');
    if (str_contains($base, '/admin')) {
        $base = dirname($base);
    }
    $base = rtrim($base, '/');
    return $protocol . $host . ($base ? $base : '') . '/' . ltrim($path, '/');
}

/**
 * Currency formatter
 */
function format_currency(float $amount, string $currency = 'KES'): string {
    return $currency . ' ' . number_format($amount, 2);
}

/**
 * Storefront: fetch current shop by ?shop=slug or fallback to demo
 */
function current_shop(): ?array {
    static $shop = null;
    if ($shop !== null) return $shop;

    $slug = trim(preg_replace('/[^a-z0-9-]/', '', strtolower($_GET['shop'] ?? 'demo')));
    if ($slug === '') $slug = 'demo';

    $stmt = db()->prepare('SELECT * FROM shops WHERE slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    $row = $stmt->fetch();

    if ($row) {
        $shop = $row;
        return $shop;
    }
    return null;
}

/**
 * Authentication & Authorization helpers
 */
function is_logged_in(): bool {
    return !empty($_SESSION['admin_id']);
}

function current_user(): ?array {
    if (!is_logged_in()) return null;
    static $user = null;
    if ($user !== null) return $user;

    $stmt = db()->prepare('SELECT a.*, s.name as shop_name, s.slug as shop_slug FROM admins a LEFT JOIN shops s ON a.shop_id = s.id WHERE a.id = ? AND a.active = 1 LIMIT 1');
    $stmt->execute([(int)$_SESSION['admin_id']]);
    $user = $stmt->fetch() ?: null;
    return $user;
}

function is_platform_owner(): bool {
    $user = current_user();
    return $user && ($user['role'] === 'platform_owner' || (!empty($_SESSION['is_platform_owner']) && $_SESSION['is_platform_owner'] === true));
}

function require_admin(): void {
    if (!is_logged_in()) {
        redirect(base_url('admin/login.php'));
    }
}

function require_platform_owner(): void {
    require_admin();
    if (!is_platform_owner()) {
        http_response_code(403);
        include __DIR__ . '/../templates/error_403.php';
        exit;
    }
}

function admin_shop_id(): int {
    require_admin();
    $user = current_user();
    if (!$user) {
        redirect(base_url('admin/login.php'));
    }

    // Platform owner may be inspecting a specific shop via session or default
    if (is_platform_owner() && !empty($_SESSION['impersonate_shop_id'])) {
        return (int)$_SESSION['impersonate_shop_id'];
    }

    if (!empty($_SESSION['shop_id'])) {
        return (int)$_SESSION['shop_id'];
    }

    if (!empty($user['shop_id'])) {
        return (int)$user['shop_id'];
    }

    // Fallback if platform owner has no explicit shop
    $firstShop = db()->query("SELECT id FROM shops ORDER BY id ASC LIMIT 1")->fetchColumn();
    return (int)$firstShop;
}

/**
 * Audit Logger
 */
function log_audit(string $action, string $details = '', ?int $shopId = null): void {
    try {
        $adminId = $_SESSION['admin_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmt = db()->prepare('INSERT INTO audit_logs (admin_id, shop_id, action, details, ip_address) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$adminId ? (int)$adminId : null, $shopId, $action, $details, $ip]);
    } catch (Throwable) {
        // Suppress audit failure to avoid blocking primary business operations
    }
}

/**
 * Flash notifications helper
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Secure file upload validator and processor
 */
function handle_image_upload(array $file, string $subfolder = 'products', int $maxSizeMb = 5): array {
    if (empty($file['name'])) {
        return ['success' => false, 'error' => 'No file was uploaded.'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form.',
            UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
        ];
        return ['success' => false, 'error' => $errors[$file['error']] ?? 'Upload error encountered.'];
    }

    if ($file['size'] > ($maxSizeMb * 1024 * 1024)) {
        return ['success' => false, 'error' => "Image size exceeds limit of {$maxSizeMb}MB."];
    }

    // Inspect real mime type using finfo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    if (!array_key_exists($mime, $allowedMimes)) {
        return ['success' => false, 'error' => 'Invalid image format. Only JPG, PNG, WEBP, and GIF are allowed.'];
    }

    // Verify image integrity with getimagesize
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return ['success' => false, 'error' => 'Uploaded file is corrupt or not a valid image.'];
    }

    $ext = $allowedMimes[$mime];
    $uploadDir = __DIR__ . '/../uploads/' . trim($subfolder, '/');
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }

    // Generate non-guessable secure filename
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $targetPath = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => false, 'error' => 'Failed to save the uploaded image to the server storage.'];
    }

    // Store relative public path
    $publicPath = 'uploads/' . trim($subfolder, '/') . '/' . $filename;
    return ['success' => true, 'path' => $publicPath, 'filename' => $filename];
}

