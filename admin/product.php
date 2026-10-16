<?php
require __DIR__ . '/../includes/config.php';
require_admin();

$shopId = admin_shop_id();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$product = ['name' => '', 'description' => '', 'price' => '', 'category' => 'General', 'stock' => 0, 'active' => 1];
$error = '';

if ($id) {
    $stmt = db()->prepare('SELECT name, description, price, category, stock, active FROM products WHERE id = ? AND shop_id = ?');
    $stmt->execute([$id, $shopId]);
    $product = $stmt->fetch() ?: $product;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)($_POST['id'] ?? 0);
    $name = trim((string)($_POST['name'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $category = trim((string)($_POST['category'] ?? 'General'));
    $price = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_FLOAT);
    $stock = filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $active = isset($_POST['active']) ? 1 : 0;

    if ($name === '' || mb_strlen($name) > 160) $error = 'Enter a product name up to 160 characters.';
    elseif ($price === false || $price < 0) $error = 'Enter a valid non-negative price.';
    elseif ($stock === false) $error = 'Stock must be zero or greater.';
    elseif ($category === '' || mb_strlen($category) > 100) $error = 'Enter a category up to 100 characters.';
    else {
        if ($id) {
            
        } else {
            $stmt = db()->prepare('INSERT INTO products (shop_id, name, description, price, category, stock, active) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$shopId, $name, $description, $price, $category, $stock, $active]);
        }
        redirect('dashboard.php');
    }
    $product = compact('name', 'description', 'price', 'category', 'stock', 'active');
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= $id ? 'Edit' : 'Add' ?> product</title><link rel="stylesheet" href="../assets/style.css"><style>.dash{max-width:760px;margin:40px auto;padding:0 20px}.panel{background:#fff;border:1px solid var(--line);border-radius:10px;padding:24px}.field{margin:16px 0}.field label{display:block;font-size:13px;font-weight:600;margin-bottom:7px}.field input,.field textarea{width:100%;padding:11px;border:1px solid var(--line);border-radius:6px;font:inherit}.field textarea{min-height:120px;resize:vertical}.row{display:grid;grid-template-columns:1fr 1fr;gap:16px}.error{color:#b91c1c;font-size:13px}@media(max-width:560px){.row{grid-template-columns:1fr}}</style></head><body><header class="topbar"><a class="brand" href="dashboard.php">QuickCart Admin</a><a class="admin-link" href="dashboard.php">Back to dashboard</a></header><main class="dash"><span class="eyebrow">PRODUCT MANAGEMENT</span><h1><?= $id ? 'Edit product' : 'Add product' ?></h1><div class="panel"><?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= $id ?>"><div class="field"><label for="name">Product name</label><input id="name" name="name" value="<?= e((string)$product['name']) ?>" maxlength="160" required></div><div class="field"><label for="description">Description</label><textarea id="description" name="description"><?= e((string)$product['description']) ?></textarea></div><div class="row"><div class="field"><label for="price">Price</label><input id="price" name="price" type="number" min="0" step="0.01" value="<?= e((string)$product['price']) ?>" required></div><div class="field"><label for="stock">Stock</label><input id="stock" name="stock" type="number" min="0" step="1" value="<?= e((string)$product['stock']) ?>" required></div></div><div class="field"><label for="category">Category</label><input id="category" name="category" value="<?= e((string)$product['category']) ?>" maxlength="100" required></div><div class="field"><label><input type="checkbox" name="active" <?= $product['active'] ? 'checked' : '' ?>> Published in storefront</label></div><button class="button" type="submit">Save product</button></form></div></main></body></html>
