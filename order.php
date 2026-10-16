<?php
require __DIR__ . '/includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Orders must be submitted from the storefront.');
}

$shop = current_shop();
$productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
$quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 99]]);
if (!$productId || !$quantity) {
    http_response_code(422);
    exit('Choose a valid product quantity.');
}

$stmt = db()->prepare('SELECT id, name, price, stock FROM products WHERE id = ? AND shop_id = ? AND active = 1 LIMIT 1');
$stmt->execute([$productId, (int)$shop['id']]);
$product = $stmt->fetch();
if (!$product || (int)$product['stock'] < $quantity) {
    http_response_code(422);
    exit('That product is unavailable or does not have enough stock.');
}

$reference = 'QC-' . strtoupper(bin2hex(random_bytes(5)));
$total = (float)$product['price'] * $quantity;
$items = json_encode([['product_id' => (int)$product['id'], 'name' => $product['name'], 'quantity' => $quantity, 'price' => (float)$product['price']]], JSON_THROW_ON_ERROR);

db()->beginTransaction();
try {
    $reserve = db()->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND shop_id = ? AND active = 1 AND stock >= ?');
    $reserve->execute([$quantity, (int)$product['id'], (int)$shop['id'], $quantity]);
    if ($reserve->rowCount() !== 1) {
        db()->rollBack();
        exit('That product sold out while you were ordering. Please try again.');
    }
    $insert = db()->prepare('INSERT INTO orders (shop_id, items_json, total, reference) VALUES (?, ?, ?, ?)');
    $insert->execute([(int)$shop['id'], $items, $total, $reference]);
    db()->commit();
} catch (Throwable $exception) {
    db()->rollBack();
    throw $exception;
}

$message = "Hello {$shop['name']}, I would like to order:\n{$quantity} x {$product['name']} ({$shop['currency']} " .
    number_format((float)$product['price'], 2) . ")\nTotal: {$shop['currency']} " . number_format($total, 2) .
    "\nOrder reference: {$reference}\n\nSending this message does not confirm payment or acceptance.";
$whatsapp = preg_replace('/\D+/', '', (string)$shop['whatsapp_number']);
if ($whatsapp === '') {
    exit('This shop has not configured WhatsApp ordering yet.');
}
header('Location: https://wa.me/' . $whatsapp . '?text=' . rawurlencode($message));
exit;
