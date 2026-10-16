<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/icons.php';
require_platform_owner();

$error = '';

// Handle Create New Shop
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_shop') {
    verify_csrf();

    $name = trim((string)($_POST['name'] ?? ''));
    $slug = trim((string)($_POST['slug'] ?? ''));
    $whatsapp = trim((string)($_POST['whatsapp_number'] ?? ''));
    $currency = trim((string)($_POST['currency'] ?? 'KES'));
    $adminEmail = filter_input(INPUT_POST, 'admin_email', FILTER_VALIDATE_EMAIL);
    $adminPassword = (string)($_POST['admin_password'] ?? '');

    if ($name === '') {
        $error = 'Shop name is required.';
    } elseif ($whatsapp === '') {
        $error = 'WhatsApp contact number is required.';
    } else {
        if ($slug === '') {
            $slug = strtolower(trim(preg_replace('/[^a-z0-9-]+/', '-', $name), '-'));
        } else {
            $slug = strtolower(trim(preg_replace('/[^a-z0-9-]+/', '-', $slug), '-'));
        }

        // Check slug uniqueness
        $chk = db()->prepare('SELECT id FROM shops WHERE slug = ?');
        $chk->execute([$slug]);
        if ($chk->fetch()) {
            $error = 'A shop with slug "' . $slug . '" already exists.';
        } else {
            db()->beginTransaction();
            try {
                $insShop = db()->prepare('INSERT INTO shops (name, slug, whatsapp_number, currency, accent_color, published) VALUES (?, ?, ?, ?, ?, ?)');
                $insShop->execute([$name, $slug, $whatsapp, $currency, '#f97316', 1]);
                $newShopId = (int)db()->lastInsertId();

                // If admin credentials provided, create merchant account
                if ($adminEmail && $adminPassword !== '') {
                    $hash = password_hash($adminPassword, PASSWORD_DEFAULT);
                    $insAdmin = db()->prepare('INSERT INTO admins (shop_id, email, password_hash, role, active) VALUES (?, ?, ?, ?, ?)');
                    $insAdmin->execute([$newShopId, $adminEmail, $hash, 'shop_admin', 1]);
                }

                db()->commit();
                log_audit('platform_create_shop', "Created shop '{$name}' (#{$newShopId})", $newShopId);
                set_flash('success', "New shop '{$name}' provisioned successfully!");
                redirect(base_url('admin/platform_overview.php'));
            } catch (Throwable $e) {
                if (db()->inTransaction()) db()->rollBack();
                $error = 'Failed to provision shop: ' . $e->getMessage();
            }
        }
    }
}

// Handle Shop Status Toggle (Publish / Unpublish)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_published') {
    verify_csrf();
    $toggleShopId = filter_input(INPUT_POST, 'shop_id', FILTER_VALIDATE_INT);
    if ($toggleShopId) {
        $upd = db()->prepare('UPDATE shops SET published = NOT published WHERE id = ?');
        $upd->execute([$toggleShopId]);
        log_audit('platform_toggle_shop', "Toggled publish state for shop #{$toggleShopId}", $toggleShopId);
        set_flash('success', 'Shop publication status updated.');
    }
    redirect(base_url('admin/platform_overview.php'));
}

// Handle Switch / Impersonate Active Shop
if (isset($_GET['manage_shop'])) {
    $manageId = filter_input(INPUT_GET, 'manage_shop', FILTER_VALIDATE_INT);
    if ($manageId) {
        $_SESSION['shop_id'] = $manageId;
        $_SESSION['impersonate_shop_id'] = $manageId;
        set_flash('success', "Switched active context to shop #{$manageId}.");
        redirect(base_url('admin/dashboard.php'));
    }
}

// Fetch all shops with product, order, and admin counts
$shops = db()->query("
    SELECT s.*,
           (SELECT COUNT(*) FROM products p WHERE p.shop_id = s.id) as product_count,
           (SELECT COUNT(*) FROM orders o WHERE o.shop_id = s.id) as order_count,
           (SELECT COALESCE(SUM(total), 0) FROM orders o WHERE o.shop_id = s.id AND o.status != 'cancelled') as total_volume,
           (SELECT email FROM admins a WHERE a.shop_id = s.id AND a.role = 'shop_admin' LIMIT 1) as admin_email
    FROM shops s
    ORDER BY s.id DESC
")->fetchAll();

// System aggregates
$totalShopsCount = count($shops);
$platformTotalOrders = (int)db()->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$platformTotalProducts = (int)db()->query("SELECT COUNT(*) FROM products")->fetchColumn();
$platformTotalEnquiries = (int)db()->query("SELECT COUNT(*) FROM service_enquiries")->fetchColumn();

$adminNav = 'platform_overview';
$pageTitle = 'Platform Owner Overview — ' . APP_NAME;
include __DIR__ . '/../templates/admin_header.php';
?>

<div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 28px;">
  <div>
    <span class="eyebrow">PLATFORM OWNER CONSOLE</span>
    <h1 style="font-size: 26px;">All Tenant Shops (<?= $totalShopsCount ?>)</h1>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger"><?= icon('x', 'icon-sm') ?> <span><?= e($error) ?></span></div>
<?php endif; ?>

<!-- Platform-wide Key Metrics -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 32px;">
  <div class="card" style="padding: 20px;">
    <span style="font-size: 13px; font-weight: 600; color: var(--text-muted);">Active Shops</span>
    <div style="font-size: 28px; font-weight: 800; font-family: var(--font-heading); margin-top: 4px;"><?= $totalShopsCount ?></div>
  </div>

  <div class="card" style="padding: 20px;">
    <span style="font-size: 13px; font-weight: 600; color: var(--text-muted);">System Products</span>
    <div style="font-size: 28px; font-weight: 800; font-family: var(--font-heading); margin-top: 4px;"><?= $platformTotalProducts ?></div>
  </div>

  <div class="card" style="padding: 20px;">
    <span style="font-size: 13px; font-weight: 600; color: var(--text-muted);">System Orders Logged</span>
    <div style="font-size: 28px; font-weight: 800; font-family: var(--font-heading); margin-top: 4px;"><?= $platformTotalOrders ?></div>
  </div>

  <div class="card" style="padding: 20px;">
    <span style="font-size: 13px; font-weight: 600; color: var(--text-muted);">Customer Enquiries</span>
    <div style="font-size: 28px; font-weight: 800; font-family: var(--font-heading); margin-top: 4px;"><?= $platformTotalEnquiries ?></div>
  </div>
</div>

<div style="display: grid; grid-template-columns: 1.8fr 1fr; gap: 32px; align-items: start;">
  <!-- Shops Directory Table -->
  <div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive" style="border: none;">
      <table class="data-table">
        <thead>
          <tr>
            <th>Shop / Slug</th>
            <th>Admin Email</th>
            <th>Products</th>
            <th>Orders</th>
            <th>Status</th>
            <th style="text-align: right;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($shops as $s): ?>
            <tr>
              <td>
                <strong><?= e($s['name']) ?></strong>
                <div style="font-size: 11px; font-family: var(--font-mono); color: var(--accent);">
                  ?shop=<?= e($s['slug']) ?>
                </div>
              </td>
              <td>
                <span style="font-size: 13px;"><?= e($s['admin_email'] ?: 'No merchant admin') ?></span>
              </td>
              <td><?= (int)$s['product_count'] ?></td>
              <td><?= (int)$s['order_count'] ?></td>
              <td>
                <span class="badge <?= $s['published'] ? 'badge-success' : 'badge-neutral' ?>">
                  <?= $s['published'] ? 'Live' : 'Draft' ?>
                </span>
              </td>
              <td style="text-align: right;">
                <div style="display: inline-flex; gap: 8px;">
                  <a href="<?= base_url('admin/platform_overview.php?manage_shop=' . (int)$s['id']) ?>" class="btn btn-secondary btn-sm" title="Switch to this shop dashboard">
                    Manage
                  </a>
                  <a href="<?= base_url('index.php?shop=' . urlencode($s['slug'])) ?>" target="_blank" class="btn btn-secondary btn-sm" title="Open storefront">
                    <?= icon('external', 'icon-sm') ?>
                  </a>
                  <form method="post" action="<?= base_url('admin/platform_overview.php') ?>" style="margin: 0;">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="toggle_published">
                    <input type="hidden" name="shop_id" value="<?= (int)$s['id'] ?>">
                    <button class="btn btn-secondary btn-sm" type="submit" title="<?= $s['published'] ? 'Unpublish' : 'Publish' ?>">
                      <?= $s['published'] ? icon('eye', 'icon-sm') : icon('check', 'icon-sm') ?>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Provision New Customer Shop Form -->
  <div class="card" style="padding: 28px;">
    <h2 style="font-size: 18px; margin-bottom: 6px;">Provision New Shop</h2>
    <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 20px;">Instantly spin up a separate shop for a paying customer.</p>

    <form method="post" action="<?= base_url('admin/platform_overview.php') ?>">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="action" value="create_shop">

      <div class="form-group">
        <label class="form-label" for="new_name">Store Name *</label>
        <input class="form-control" type="text" id="new_name" name="name" required placeholder="e.g. Savannah Glow Beauty">
      </div>

      <div class="form-group">
        <label class="form-label" for="new_slug">Custom Slug (Optional)</label>
        <input class="form-control" type="text" id="new_slug" name="slug" placeholder="savannah-glow">
      </div>

      <div class="form-group">
        <label class="form-label" for="new_whatsapp">Merchant WhatsApp Line *</label>
        <input class="form-control" type="text" id="new_whatsapp" name="whatsapp_number" required placeholder="2547XXXXXXXX">
      </div>

      <div class="form-group">
        <label class="form-label" for="new_currency">Currency Code</label>
        <input class="form-control" type="text" id="new_currency" name="currency" value="KES" placeholder="KES, USD, EUR">
      </div>

      <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border-light);">
        <h4 style="font-size: 14px; margin-bottom: 12px;">Merchant Login Account</h4>

        <div class="form-group">
          <label class="form-label" for="admin_email">Merchant Email</label>
          <input class="form-control" type="email" id="admin_email" name="admin_email" placeholder="merchant@brand.com">
        </div>

        <div class="form-group">
          <label class="form-label" for="admin_password">Initial Password</label>
          <input class="form-control" type="password" id="admin_password" name="admin_password" placeholder="Temporary password">
        </div>
      </div>

      <button class="btn btn-primary" type="submit" style="width: 100%; margin-top: 10px;">
        Provision Storefront
      </button>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../templates/admin_footer.php'; ?>
