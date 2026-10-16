<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/icons.php';
require_admin();

$shopId = admin_shop_id();
$shopStmt = db()->prepare('SELECT * FROM shops WHERE id = ?');
$shopStmt->execute([$shopId]);
$shop = $shopStmt->fetch();

if (!$shop) {
    exit('Shop not found.');
}

// Key metrics
$totalProducts = (int)db()->query("SELECT COUNT(*) FROM products WHERE shop_id = {$shopId}")->fetchColumn();
$activeProducts = (int)db()->query("SELECT COUNT(*) FROM products WHERE shop_id = {$shopId} AND active = 1")->fetchColumn();
$lowStockProducts = (int)db()->query("SELECT COUNT(*) FROM products WHERE shop_id = {$shopId} AND stock <= 5")->fetchColumn();

$totalOrders = (int)db()->query("SELECT COUNT(*) FROM orders WHERE shop_id = {$shopId}")->fetchColumn();
$pendingOrders = (int)db()->query("SELECT COUNT(*) FROM orders WHERE shop_id = {$shopId} AND status = 'pending'")->fetchColumn();
$totalSales = (float)db()->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE shop_id = {$shopId} AND status != 'cancelled'")->fetchColumn();

// Recent 5 orders
$recentOrdersStmt = db()->prepare("SELECT * FROM orders WHERE shop_id = ? ORDER BY id DESC LIMIT 5");
$recentOrdersStmt->execute([$shopId]);
$recentOrders = $recentOrdersStmt->fetchAll();

// Categories count
$totalCategories = (int)db()->query("SELECT COUNT(*) FROM categories WHERE shop_id = {$shopId}")->fetchColumn();

$adminNav = 'dashboard';
$pageTitle = 'Overview — ' . $shop['name'];
include __DIR__ . '/../templates/admin_header.php';
?>

<div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; gap: 16px; margin-bottom: 32px;">
  <div>
    <span class="eyebrow">SHOP OVERVIEW</span>
    <h1 style="font-size: 28px; margin-top: 4px;"><?= e($shop['name']) ?></h1>
    <p style="color: var(--text-muted); font-size: 14px;">Here is what is happening across your products and orders today.</p>
  </div>

  <div style="display: flex; gap: 10px;">
    <a href="<?= base_url('admin/products.php?action=new') ?>" class="btn btn-primary btn-sm">
      <?= icon('plus', 'icon-sm') ?>
      <span>Add Product</span>
    </a>
    <a href="<?= base_url('index.php?shop=' . urlencode($shop['slug'])) ?>" target="_blank" class="btn btn-secondary btn-sm">
      <?= icon('external', 'icon-sm') ?>
      <span>Open Live Shop</span>
    </a>
  </div>
</div>

<!-- Key Stat Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 32px;">
  <div class="card" style="padding: 20px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
      <span style="font-size: 13px; font-weight: 600; color: var(--text-muted);">Total Catalogue Sales</span>
      <div style="color: var(--color-success);"><?= icon('dollar', 'icon') ?></div>
    </div>
    <div style="font-size: 26px; font-weight: 800; font-family: var(--font-heading);">
      <?= format_currency($totalSales, $shop['currency']) ?>
    </div>
    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Excludes cancelled requests</div>
  </div>

  <div class="card" style="padding: 20px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
      <span style="font-size: 13px; font-weight: 600; color: var(--text-muted);">Total Recorded Orders</span>
      <div style="color: var(--accent);"><?= icon('orders', 'icon') ?></div>
    </div>
    <div style="font-size: 26px; font-weight: 800; font-family: var(--font-heading);">
      <?= $totalOrders ?>
    </div>
    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
      <span style="color: var(--accent); font-weight: 700;"><?= $pendingOrders ?> pending</span> review
    </div>
  </div>

  <div class="card" style="padding: 20px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
      <span style="font-size: 13px; font-weight: 600; color: var(--text-muted);">Active Products</span>
      <div style="color: var(--color-info);"><?= icon('package', 'icon') ?></div>
    </div>
    <div style="font-size: 26px; font-weight: 800; font-family: var(--font-heading);">
      <?= $activeProducts ?> <span style="font-size: 15px; color: var(--text-muted); font-weight: normal;">/ <?= $totalProducts ?> total</span>
    </div>
    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
      <?= $lowStockProducts > 0 ? "<span style='color:var(--color-danger); font-weight:600;'>{$lowStockProducts} low stock</span>" : "Stock healthy" ?>
    </div>
  </div>

  <div class="card" style="padding: 20px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
      <span style="font-size: 13px; font-weight: 600; color: var(--text-muted);">Categories</span>
      <div style="color: var(--color-warning);"><?= icon('tag', 'icon') ?></div>
    </div>
    <div style="font-size: 26px; font-weight: 800; font-family: var(--font-heading);">
      <?= $totalCategories ?>
    </div>
    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Organizing your storefront</div>
  </div>
</div>

<!-- Recent Orders Section -->
<div class="card" style="padding: 24px;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="font-size: 18px; margin: 0;">Recent WhatsApp Orders</h2>
    <a href="<?= base_url('admin/orders.php') ?>" class="btn btn-secondary btn-sm">View All Orders</a>
  </div>

  <?php if (empty($recentOrders)): ?>
    <div class="empty-state" style="padding: 32px 20px;">
      <div class="empty-state-icon"><?= icon('orders', 'icon') ?></div>
      <div class="empty-state-title">No orders logged yet</div>
      <div class="empty-state-desc">When visitors select items on your storefront, their order requests will appear here.</div>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Reference</th>
            <th>Date & Time</th>
            <th>Items Summary</th>
            <th>Total</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentOrders as $ro): 
            $items = json_decode($ro['items_json'] ?? '[]', true);
            $itemDesc = '';
            if (is_array($items) && count($items) > 0) {
                $first = $items[0];
                $itemDesc = ($first['quantity'] ?? 1) . 'x ' . ($first['name'] ?? 'Item');
                if (count($items) > 1) {
                    $itemDesc .= ' + ' . (count($items) - 1) . ' more';
                }
            } else {
                $itemDesc = '1 Item';
            }
          ?>
            <tr>
              <td>
                <span style="font-family: var(--font-mono); font-weight: 700; color: var(--accent);">
                  <?= e($ro['reference']) ?>
                </span>
              </td>
              <td><?= date('M j, Y H:i', strtotime($ro['created_at'])) ?></td>
              <td><?= e($itemDesc) ?></td>
              <td><strong><?= format_currency((float)$ro['total'], $ro['currency'] ?? $shop['currency']) ?></strong></td>
              <td>
                <?php
                $statusBadges = [
                    'pending' => 'badge-warning',
                    'confirmed' => 'badge-primary',
                    'processing' => 'badge-neutral',
                    'completed' => 'badge-success',
                    'cancelled' => 'badge-danger',
                ];
                $badgeClass = $statusBadges[$ro['status']] ?? 'badge-neutral';
                ?>
                <span class="badge <?= $badgeClass ?>"><?= ucfirst(e($ro['status'])) ?></span>
              </td>
              <td>
                <a href="<?= base_url('admin/orders.php?view=' . (int)$ro['id']) ?>" class="btn btn-secondary btn-sm">
                  View Details
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
