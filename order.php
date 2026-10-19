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

// 3. Customer contact / delivery details
$customerName    = trim((string)($_POST['customer_name'] ?? ''));
$customerPhone   = trim((string)($_POST['customer_phone'] ?? ''));
$customerEmail   = filter_input(INPUT_POST, 'customer_email', FILTER_VALIDATE_EMAIL) ?: null;
$customerAddress = trim((string)($_POST['customer_address'] ?? ''));
$customerNotes   = trim((string)($_POST['customer_notes'] ?? ''));

// 4. Parse order items (Multi-item cart JSON or single item fallback)
$itemsToProcess = [];

if (!empty($_POST['cart_items'])) {
    $rawCart = json_decode((string)$_POST['cart_items'], true);
    if (is_array($rawCart) && !empty($rawCart)) {
        foreach ($rawCart as $entry) {
            $pId = filter_var($entry['product_id'] ?? null, FILTER_VALIDATE_INT);
            $qty = filter_var($entry['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 999]]);
            if ($pId && $qty) {
                $itemsToProcess[] = ['product_id' => $pId, 'quantity' => $qty];
            }
        }
    }
}

// Single item direct order fallback
if (empty($itemsToProcess)) {
    $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
    $quantity  = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 999]]);
    if ($productId && $quantity) {
        $itemsToProcess[] = ['product_id' => $productId, 'quantity' => $quantity];
    }
}

if (empty($itemsToProcess)) {
    http_response_code(422);
    exit('Please select at least one valid product and quantity.');
}

// 5. Query and validate every item with tenant scoping
$currency = !empty($shop['currency']) ? $shop['currency'] : 'KES';
$reference = 'QC-' . strtoupper(bin2hex(random_bytes(4)));
$grandTotal = 0.0;
$validatedItems = [];

foreach ($itemsToProcess as $req) {
    $stmt = db()->prepare('SELECT id, name, price, stock, active FROM products WHERE id = ? AND shop_id = ? LIMIT 1');
    $stmt->execute([$req['product_id'], (int)$shop['id']]);
    $prod = $stmt->fetch();

    if (!$prod || !$prod['active']) {
        http_response_code(404);
        exit('One or more requested products are currently inactive or unavailable.');
    }

    if ((int)$prod['stock'] < $req['quantity']) {
        http_response_code(422);
        exit("Sorry, only {$prod['stock']} units remain available for '{$prod['name']}'. Please adjust your quantity.");
    }

    $unitPrice = (float)$prod['price'];
    $subtotal = $unitPrice * $req['quantity'];
    $grandTotal += $subtotal;

    $validatedItems[] = [
        'product_id' => (int)$prod['id'],
        'name'       => $prod['name'],
        'quantity'   => $req['quantity'],
        'unit_price' => $unitPrice,
        'subtotal'   => $subtotal,
    ];
}

$itemsJson = json_encode($validatedItems, JSON_THROW_ON_ERROR);

// 6. Execute atomic stock reservation & order insertion inside a transaction
db()->beginTransaction();
try {
    foreach ($validatedItems as $item) {
        $reserve = db()->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND shop_id = ? AND stock >= ?');
        $reserve->execute([$item['quantity'], $item['product_id'], (int)$shop['id'], $item['quantity']]);

        if ($reserve->rowCount() !== 1) {
            db()->rollBack();
            http_response_code(409);
            exit("Stock changed for '{$item['name']}' while processing. Please refresh and try again.");
        }
    }

    // Insert order record
    $insOrder = db()->prepare('
        INSERT INTO orders (shop_id, customer_name, customer_phone, customer_email, customer_address, customer_notes, items_json, total, currency, reference, status, payment_status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $insOrder->execute([
        (int)$shop['id'],
        $customerName ?: null,
        $customerPhone ?: null,
        $customerEmail,
        $customerAddress ?: null,
        $customerNotes ?: null,
        $itemsJson,
        $grandTotal,
        $currency,
        $reference,
        'pending',
        'unpaid'
    ]);
    $orderId = (int)db()->lastInsertId();

    // Insert order items line snapshot
    $insItem = db()->prepare('INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?, ?)');
    foreach ($validatedItems as $item) {
        $insItem->execute([(int)$orderId, $item['product_id'], $item['name'], $item['quantity'], $item['unit_price'], $item['subtotal']]);
    }

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
$itemCount = count($validatedItems);
$messageLines = [];
$messageLines[] = "👋 Hello {$shop['name']}, I would like to place an order:";
$messageLines[] = "";
$messageLines[] = "🛒 *ORDER SUMMARY* (Ref: *{$reference}*)";
$messageLines[] = "──────────────────────────";

foreach ($validatedItems as $item) {
    $messageLines[] = "• {$item['quantity']}x {$item['name']} — {$currency} " . number_format($item['unit_price'], 2) . " (Subtotal: {$currency} " . number_format($item['subtotal'], 2) . ")";
}

$messageLines[] = "──────────────────────────";
$messageLines[] = "*Total Amount:* {$currency} " . number_format($grandTotal, 2);
$messageLines[] = "";

if (!empty($customerName) || !empty($customerPhone) || !empty($customerAddress) || !empty($customerNotes)) {
    $messageLines[] = "📋 *CUSTOMER DETAILS*";
    if (!empty($customerName)) {
        $messageLines[] = "• *Name:* {$customerName}";
    }
    if (!empty($customerPhone)) {
        $messageLines[] = "• *Phone:* {$customerPhone}";
    }
    if (!empty($customerAddress)) {
        $messageLines[] = "• *Delivery Address:* {$customerAddress}";
    }
    if (!empty($customerNotes)) {
        $messageLines[] = "• *Notes / Requests:* {$customerNotes}";
    }
    $messageLines[] = "";
}

$messageLines[] = "Please confirm availability and dispatch. Thank you!";

$prefilledText = implode("\n", $messageLines);
$whatsappUrl = 'https://wa.me/' . $whatsapp . '?text=' . rawurlencode($prefilledText);

// 9. Redirect customer to WhatsApp conversation
redirect($whatsappUrl);
