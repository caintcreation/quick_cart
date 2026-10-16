<?php
require __DIR__ . '/includes/config.php';
$shop = current_shop();
$stmt = db()->prepare('SELECT * FROM products WHERE shop_id = ? AND active = 1 AND stock > 0 ORDER BY created_at DESC');
$stmt->execute([$shop['id']]);
$products = $stmt->fetchAll();
$whatsapp = preg_replace('/\D+/', '', $shop['whatsapp_number']);
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= e($shop['name']) ?></title><link rel="stylesheet" href="assets/style.css"><style>:root{--accent:<?= e($shop['accent_color']) ?>}</style></head><body>
<header class="topbar"><a class="brand" href="index.php?shop=<?= e($shop['slug']) ?>"><?= e($shop['name']) ?></a><a class="admin-link" href="admin/login.php">Shop admin</a></header>
<main class="container"><section class="hero"><span class="eyebrow">WELCOME TO OUR STORE</span><h1><?= e($shop['name']) ?></h1><p><?= e($shop['tagline']) ?></p><a class="button" href="#products">Browse products <span aria-hidden="true">↓</span></a></section>
<section id="products"><div class="section-head"><div><span class="eyebrow">THE COLLECTION</span><h2>Find your next favourite</h2></div><input id="search" class="search" type="search" placeholder="Search products…" aria-label="Search products"></div>
<div class="products" id="productGrid">
<?php foreach ($products as $p): ?>
<article class="product-card" data-search="<?= e(strtolower($p['name'] . ' ' . $p['category'] . ' ' . $p['description'])) ?>"><div class="product-image"><?php if ($p['image_path']): ?><img src="<?= e($p['image_path']) ?>" alt="<?= e($p['name']) ?>" loading="lazy"><?php else: ?><span><?= e(strtoupper(substr($p['name'],0,1))) ?></span><?php endif; ?></div><div class="product-info"><span class="category"><?= e($p['category']) ?></span><h3><?= e($p['name']) ?></h3><p><?= e($p['description']) ?></p><div class="product-bottom"><strong><?= e($shop['currency']) ?> <?= number_format((float)$p['price'], 2) ?></strong><form method="post" action="order.php?shop=<?= e($shop['slug']) ?>" target="_blank"><input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>"><label class="sr-only" for="quantity-<?= (int)$p['id'] ?>">Quantity</label><input id="quantity-<?= (int)$p['id'] ?>" name="quantity" type="number" min="1" max="<?= (int)$p['stock'] ?>" value="1" style="width:48px;padding:8px;border:1px solid var(--line);border-radius:5px"><button class="order-link" type="submit">Order ↗</button></form></div></div></article>
<?php endforeach; ?>
</div><p id="emptyState" class="empty" hidden>No products match your search.</p></section>
<footer><span>© <?= date('Y') ?> <?= e($shop['name']) ?></span><span>Simple shopping, direct conversation.</span></footer></main><script src="assets/app.js"></script></body></html>
