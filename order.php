<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/icons.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Orders must be submitted from the storefront catalogue.');
}

// 1. Verify CSRF
verify_csrf();

// 2. Identify target shop
$shop = current_shop();
if (!$shop) {
    http_response_code(404);
    exit('Shop not found.');
}

// 3. Validate product ID and requested quantity
$productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
$quantity  = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 999]]);

if (!$productId || !$quantity) {
    http_response_code(422);
    exit('Please select a valid product and quantity between 1 and 999.');
}

// 4. Query available product details with tenant lock
$stmt = db()->prepare('SELECT id, name, price, stock, active FROM products WHERE id = ? AND shop_id = ? LIMIT 1');
$stmt->execute([$productId, (int)$shop['id']]);
$product = $stmt->fetch();

if (!$product || !$product['active']) {
    http_response_code(404);
    exit('The requested product is currently inactive or unavailable.');
}

if ((int)$product['stock'] < $quantity) {
    http_response_code(422);
    exit("Sorry, only {$product['stock']} units remain available for this item.");
}

// 5. Build order snapshot data
$unitPrice = (float)$product['price'];
$subtotal  = $unitPrice * $quantity;
$reference = 'QC-' . strtoupper(bin2hex(random_bytes(4)));
$currency  = !empty($shop['currency']) ? $shop['currency'] : 'KES';

$itemsPayload = [
    [
        'product_id' => (int)$product['id'],
        'name'       => $product['name'],
        'quantity'   => $quantity,
        'price'      => $unitPrice,
        'subtotal'   => $subtotal,
    ]
];
$itemsJson = json_encode($itemsPayload, JSON_THROW_ON_ERROR);

// 6. Execute atomic stock reservation & order insertion inside a transaction
db()->beginTransaction();
try {
    // Atomic deduction guarding against race conditions
    $reserve = db()->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND shop_id = ? AND stock >= ?');
    $reserve->execute([$quantity, (int)$product['id'], (int)$shop['id'], $quantity]);

    if ($reserve->rowCount() !== 1) {
        db()->rollBack();
        http_response_code(409);
        exit('Item stock changed while your order was processing. Please refresh and try again.');
    }

    // Insert order record
    $insOrder = db()->prepare('INSERT INTO orders (shop_id, items_json, total, currency, reference, status) VALUES (?, ?, ?, ?, ?, ?)');
    $insOrder->execute([(int)$shop['id'], $itemsJson, $subtotal, $currency, $reference, 'pending']);
    $orderId = db()->lastInsertId();

    // Insert order items line snapshot
    $insItem = db()->prepare('INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?, ?)');
    $insItem->execute([(int)$orderId, (int)$product['id'], $product['name'], $quantity, $unitPrice, $subtotal]);

    db()->commit();
} catch (Throwable $e) {
    if (db()->inTransaction()) {
        db()->rollBack();
    }
    http_response_code(500);
    exit('An error occurred while registering your order. Please contact the merchant directly.');
}

// 7. Verify WhatsApp number configuration
$whatsapp = preg_replace('/\D+/', '', (string)$shop['whatsapp_number']);
if ($whatsapp === '') {
    echo "<div style='font-family: sans-serif; text-align: center; padding: 60px;'><h2>Order #{$reference} Recorded</h2><p>This shop has not configured a valid WhatsApp number yet. Please contact the shop via email or phone to complete your order.</p></div>";
    exit;
}

// 8. Generate neatly formatted WhatsApp pre-filled text
$messageLines = [
    "Hello {$shop['name']}, I would like to place an order:",
    "",
    "• {$quantity}x {$product['name']} — {$currency} " . number_format($unitPrice, 2) . " each",
    "",
    "Total Order Amount: {$currency} " . number_format($subtotal, 2),
    "Order Reference: {$reference}",
    "",
    "Please let me know how to proceed with payment and delivery. (Note: sending this message does not guarantee order acceptance)."
];

$prefilledText = implode("\n", $messageLines);
$whatsappUrl = 'https://wa.me/' . $whatsapp . '?text=' . rawurlencode($prefilledText);

// 9. Redirect customer to WhatsApp conversation
redirect($whatsappUrl);
