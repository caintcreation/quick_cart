<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/icons.php';
require_admin();

$shopId = admin_shop_id();
$shopStmt = db()->prepare('SELECT * FROM shops WHERE id = ?');
$shopStmt->execute([$shopId]);
$shop = $shopStmt->fetch();

// Handle Order Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    verify_csrf();
    $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $newStatus = trim((string)($_POST['status'] ?? ''));

    $validStatuses = ['pending', 'confirmed', 'processing', 'completed', 'cancelled'];
    if ($orderId && in_array($newStatus, $validStatuses, true)) {
        $upd = db()->prepare('UPDATE orders SET status = ? WHERE id = ? AND shop_id = ?');
        $upd->execute([$newStatus, $orderId, $shopId]);
        log_audit('order_status_update', "Updated order #{$orderId} status to {$newStatus}", $shopId);
        set_flash('success', "Order status updated to " . ucfirst($newStatus));
    }
    redirect(base_url('admin/orders.php?view=' . $orderId));
}

// Single Order Detail View
if (isset($_GET['view'])) {
    $viewId = filter_input(INPUT_GET, 'view', FILTER_VALIDATE_INT);
    $oStmt = db()->prepare('SELECT * FROM orders WHERE id = ? AND shop_id = ?');
    $oStmt->execute([$viewId, $shopId]);
    $order = $oStmt->fetch();

    if (!$order) {
        set_flash('error', 'Order not found.');
        redirect(base_url('admin/orders.php'));
    }

    // Fetch line items
    $itemStmt = db()->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC');
    $itemStmt->execute([$order['id']]);
    $items = $itemStmt->fetchAll();

    $adminNav = 'orders';
    $pageTitle = 'Order ' . $order['reference'] . ' — ' . APP_NAME;
    include __DIR__ . '/../templates/admin_header.php';
    ?>

    <div style="max-width: 860px; margin: 0 auto;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
          <span class="eyebrow">ORDER DETAILS</span>
          <h1 style="font-size: 26px;">Order Reference: <?= e($order['reference']) ?></h1>
          <span style="font-size: 13px; color: var(--text-muted);">Received on <?= date('F j, Y \a\t H:i', strtotime($order['created_at'])) ?></span>
        </div>
        <div style="display: flex; gap: 10px;">
          <button class="btn btn-secondary btn-sm" onclick="window.print();" type="button">Print Packing Slip</button>
          <a href="<?= base_url('admin/orders.php') ?>" class="btn btn-secondary btn-sm">← Back to Orders</a>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
        <!-- Items breakdown -->
        <div class="card" style="padding: 28px;">
          <h2 style="font-size: 18px; margin-bottom: 16px;">Ordered Items</h2>
          <div class="table-responsive" style="border: none;">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Quantity</th>
                  <th>Unit Price</th>
                  <th style="text-align: right;">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($items)): 
                  $rawItems = json_decode($order['items_json'] ?? '[]', true);
                  foreach ($rawItems as $ri): ?>
                    <tr>
                      <td><strong><?= e($ri['name'] ?? 'Product') ?></strong></td>
                      <td><?= (int)($ri['quantity'] ?? 1) ?></td>
                      <td><?= format_currency((float)($ri['price'] ?? 0), $order['currency'] ?? $shop['currency']) ?></td>
                      <td style="text-align: right;"><strong><?= format_currency((float)($ri['subtotal'] ?? 0), $order['currency'] ?? $shop['currency']) ?></strong></td>
                    </tr>
                <?php endforeach; else: ?>
                  <?php foreach ($items as $it): ?>
                    <tr>
                      <td><strong><?= e($it['product_name']) ?></strong></td>
                      <td><?= (int)$it['quantity'] ?></td>
                      <td><?= format_currency((float)$it['unit_price'], $order['currency'] ?? $shop['currency']) ?></td>
                      <td style="text-align: right;"><strong><?= format_currency((float)$it['subtotal'], $order['currency'] ?? $shop['currency']) ?></strong></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="3" style="text-align: right; padding-top: 20px; font-weight: 700;">Grand Total:</td>
                  <td style="text-align: right; padding-top: 20px; font-size: 18px; font-weight: 800; color: var(--accent);">
                    <?= format_currency((float)$order['total'], $order['currency'] ?? $shop['currency']) ?>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- Order Status and Customer Card -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
          <div class="card" style="padding: 24px;">
            <h3 style="font-size: 16px; margin-bottom: 14px;">Lifecycle Status</h3>
            <form method="post" action="<?= base_url('admin/orders.php') ?>">
              <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="action" value="update_status">
              <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">

              <div class="form-group">
                <select class="form-control" name="status">
                  <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending Review</option>
                  <option value="confirmed" <?= $order['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                  <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing / Packing</option>
                  <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Completed / Delivered</option>
                  <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
              </div>

              <button class="btn btn-primary btn-sm" type="submit" style="width: 100%;">
                Update Status
              </button>
            </form>
          </div>

          <div class="card" style="padding: 24px;">
            <h3 style="font-size: 16px; margin-bottom: 14px;">WhatsApp Communication</h3>
            <p style="font-size: 13px; color: var(--text-muted); line-height: 1.5; margin-bottom: 16px;">
              The buyer initiated an order for reference <strong><?= e($order['reference']) ?></strong>.
            </p>
            <div style="padding: 10px 14px; background-color: var(--bg-subtle); border-radius: var(--radius-sm); font-size: 12px; color: var(--text-subtle);">
              Store WhatsApp: +<?= e($shop['whatsapp_number']) ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php
    include __DIR__ . '/../templates/admin_footer.php';
    exit;
}

// Order List View
$statusFilter = trim($_GET['status'] ?? '');
$search = trim($_GET['q'] ?? '');

$sql = "SELECT * FROM orders WHERE shop_id = ?";
$params = [$shopId];

if ($statusFilter !== '') {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}

if ($search !== '') {
    $sql .= " AND reference LIKE ?";
    $params[] = "%{$search}%";
}

$sql .= " ORDER BY id DESC";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$adminNav = 'orders';
$pageTitle = 'Orders Management — ' . APP_NAME;
include __DIR__ . '/../templates/admin_header.php';
?>

<div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 28px;">
  <div>
    <span class="eyebrow">ORDER MANAGEMENT</span>
    <h1 style="font-size: 26px;">Recorded Storefront Orders (<?= count($orders) ?>)</h1>
  </div>
</div>

<!-- Filters Bar -->
<div class="card" style="padding: 16px 20px; margin-bottom: 24px;">
  <form method="get" action="<?= base_url('admin/orders.php') ?>" style="display: flex; flex-wrap: wrap; gap: 14px; align-items: center;">
    <div style="position: relative; flex: 1; min-width: 220px;">
      <input class="form-control" type="search" name="q" value="<?= e($search) ?>" placeholder="Search by reference QC-…" style="padding-left: 36px;">
      <div style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-subtle);">
        <?= icon('search', 'icon-sm') ?>
      </div>
    </div>

    <select class="form-control" name="status" style="width: auto; min-width: 170px;">
      <option value="">All Statuses</option>
      <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
      <option value="confirmed" <?= $statusFilter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
      <option value="processing" <?= $statusFilter === 'processing' ? 'selected' : '' ?>>Processing</option>
      <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>Completed</option>
      <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
    </select>

    <button class="btn btn-secondary btn-sm" type="submit">Filter</button>
    <?php if ($statusFilter !== '' || $search !== ''): ?>
      <a href="<?= base_url('admin/orders.php') ?>" class="btn btn-secondary btn-sm">Reset</a>
    <?php endif; ?>
  </form>
</div>

<!-- Orders Table -->
<div class="card" style="padding: 0; overflow: hidden;">
  <?php if (empty($orders)): ?>
    <div class="empty-state">
      <div class="empty-state-icon"><?= icon('orders', 'icon-lg') ?></div>
      <div class="empty-state-title">No orders match criteria</div>
      <div class="empty-state-desc">Customer order submissions from the public storefront catalogue will be listed here.</div>
    </div>
  <?php else: ?>
    <div class="table-responsive" style="border: none;">
      <table class="data-table">
        <thead>
          <tr>
            <th>Order Ref</th>
            <th>Date</th>
            <th>Items</th>
            <th>Total Amount</th>
            <th>Status</th>
            <th style="text-align: right;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $ord): 
            $items = json_decode($ord['items_json'] ?? '[]', true);
            $summary = '';
            if (is_array($items) && count($items) > 0) {
                $summary = ($items[0]['quantity'] ?? 1) . 'x ' . ($items[0]['name'] ?? 'Item');
                if (count($items) > 1) {
                    $summary .= ' (+' . (count($items) - 1) . ' more)';
                }
            } else {
                $summary = 'Recorded Order';
            }

            $statusBadges = [
                'pending' => 'badge-warning',
                'confirmed' => 'badge-primary',
                'processing' => 'badge-neutral',
                'completed' => 'badge-success',
                'cancelled' => 'badge-danger',
            ];
          ?>
            <tr>
              <td>
                <a href="<?= base_url('admin/orders.php?view=' . (int)$ord['id']) ?>" style="font-family: var(--font-mono); font-weight: 700; color: var(--accent);">
                  <?= e($ord['reference']) ?>
                </a>
              </td>
              <td><?= date('M j, Y H:i', strtotime($ord['created_at'])) ?></td>
              <td><?= e($summary) ?></td>
              <td><strong><?= format_currency((float)$ord['total'], $ord['currency'] ?? $shop['currency']) ?></strong></td>
              <td>
                <span class="badge <?= $statusBadges[$ord['status']] ?? 'badge-neutral' ?>">
                  <?= ucfirst(e($ord['status'])) ?>
                </span>
              </td>
              <td style="text-align: right;">
                <a href="<?= base_url('admin/orders.php?view=' . (int)$ord['id']) ?>" class="btn btn-secondary btn-sm">
                  View Slip
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../templates/admin_footer.php'; ?>
