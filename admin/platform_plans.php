<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/icons.php';
require_platform_owner();

$error = '';

// Handle Plan Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
    $name = trim((string)($_POST['name'] ?? ''));
    $slug = trim((string)($_POST['slug'] ?? ''));
    $price = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_FLOAT);
    $cycle = trim((string)($_POST['billing_cycle'] ?? 'one-time'));
    $description = trim((string)($_POST['description'] ?? ''));
    $badge = trim((string)($_POST['badge'] ?? ''));
    $isPopular = isset($_POST['is_popular']) ? 1 : 0;
    $sortOrder = filter_input(INPUT_POST, 'sort_order', FILTER_VALIDATE_INT) ?: 0;

    $featuresRaw = trim((string)($_POST['features'] ?? ''));
    $featuresArray = array_values(array_filter(array_map('trim', explode("\n", $featuresRaw))));
    $featuresJson = json_encode($featuresArray);

    if ($name === '' || $price === false) {
        $error = 'Plan name and valid price are required.';
    } else {
        if ($slug === '') {
            $slug = strtolower(trim(preg_replace('/[^a-z0-9-]+/', '-', $name), '-'));
        }

        if ($id) {
            $upd = db()->prepare('UPDATE pricing_plans SET name = ?, slug = ?, price = ?, billing_cycle = ?, description = ?, features_json = ?, badge = ?, is_popular = ?, sort_order = ? WHERE id = ?');
            $upd->execute([$name, $slug, $price, $cycle, $description, $featuresJson, $badge ?: null, $isPopular, $sortOrder, $id]);
            log_audit('plan_update', "Updated plan {$name}");
            set_flash('success', 'Plan package updated.');
        } else {
            $ins = db()->prepare('INSERT INTO pricing_plans (name, slug, price, billing_cycle, description, features_json, badge, is_popular, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $ins->execute([$name, $slug, $price, $cycle, $description, $featuresJson, $badge ?: null, $isPopular, $sortOrder]);
            log_audit('plan_create', "Created plan {$name}");
            set_flash('success', 'New plan package added.');
        }
        redirect(base_url('admin/platform_plans.php'));
    }
}

$plans = db()->query("SELECT * FROM pricing_plans ORDER BY sort_order ASC, id ASC")->fetchAll();

$editPlan = null;
if (isset($_GET['edit'])) {
    $editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    if ($editId) {
        $eStmt = db()->prepare('SELECT * FROM pricing_plans WHERE id = ?');
        $eStmt->execute([$editId]);
        $editPlan = $eStmt->fetch();
    }
}

$adminNav = 'platform_plans';
$pageTitle = 'Manage Pricing Packages — ' . APP_NAME;
include __DIR__ . '/../templates/admin_header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px;">
  <div>
    <span class="eyebrow">PLATFORM SERVICES</span>
    <h1 style="font-size: 26px;">Pricing & Setup Packages</h1>
    <p style="color: var(--text-muted); font-size: 14px;">Edit package names, prices, and bullet inclusions without code changes.</p>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger"><?= icon('x', 'icon-sm') ?> <span><?= e($error) ?></span></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1.6fr; gap: 32px; align-items: start;">
  <!-- Edit / Add Plan Form -->
  <div class="card" style="padding: 28px;">
    <h2 style="font-size: 18px; margin-bottom: 16px;">
      <?= $editPlan ? 'Edit Package' : 'Create Package' ?>
    </h2>

    <form method="post" action="<?= base_url('admin/platform_plans.php') ?>">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= (int)($editPlan['id'] ?? 0) ?>">

      <div class="form-group">
        <label class="form-label" for="name">Package Title *</label>
        <input class="form-control" type="text" id="name" name="name" required value="<?= e($editPlan['name'] ?? '') ?>" placeholder="e.g. Starter Catalogue">
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="price">Price (USD) *</label>
          <input class="form-control" type="number" step="0.01" min="0" id="price" name="price" required value="<?= e((string)($editPlan['price'] ?? '')) ?>" placeholder="49.00">
        </div>

        <div class="form-group">
          <label class="form-label" for="billing_cycle">Billing Period</label>
          <input class="form-control" type="text" id="billing_cycle" name="billing_cycle" value="<?= e($editPlan['billing_cycle'] ?? 'one-time setup') ?>" placeholder="one-time setup, monthly">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="badge">Badge Pill</label>
        <input class="form-control" type="text" id="badge" name="badge" value="<?= e($editPlan['badge'] ?? '') ?>" placeholder="Most Popular, Starter, Turnkey">
      </div>

      <div class="form-group">
        <label class="form-label" for="description">Summary Description</label>
        <textarea class="form-control" id="description" name="description" rows="2"><?= e($editPlan['description'] ?? '') ?></textarea>
      </div>

      <div class="form-group">
        <label class="form-label" for="features">Bullet Points (One item per line)</label>
        <?php
        $featuresText = '';
        if ($editPlan && !empty($editPlan['features_json'])) {
            $fArr = json_decode($editPlan['features_json'], true);
            if (is_array($fArr)) $featuresText = implode("\n", $fArr);
        }
        ?>
        <textarea class="form-control" id="features" name="features" rows="6" placeholder="Branded catalogue&#10;WhatsApp checkout&#10;Admin panel"><?= e($featuresText) ?></textarea>
      </div>

      <div class="form-group">
        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 600;">
          <input type="checkbox" name="is_popular" value="1" <?= (!empty($editPlan['is_popular'])) ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: var(--accent);">
          <span>Highlight as Popular Choice</span>
        </label>
      </div>

      <div style="display: flex; gap: 10px; margin-top: 24px;">
        <button class="btn btn-primary" type="submit"><?= $editPlan ? 'Update Package' : 'Save Package' ?></button>
        <?php if ($editPlan): ?>
          <a href="<?= base_url('admin/platform_plans.php') ?>" class="btn btn-secondary">Cancel</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Plans List -->
  <div style="display: flex; flex-direction: column; gap: 20px;">
    <?php foreach ($plans as $p): ?>
      <div class="card" style="padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
          <div>
            <div style="display: flex; align-items: center; gap: 8px;">
              <h3 style="font-size: 18px; margin: 0;"><?= e($p['name']) ?></h3>
              <?php if (!empty($p['badge'])): ?>
                <span class="badge badge-primary"><?= e($p['badge']) ?></span>
              <?php endif; ?>
            </div>
            <div style="font-size: 13px; color: var(--text-muted); margin-top: 4px;"><?= e($p['description']) ?></div>
          </div>
          <div>
            <span style="font-size: 24px; font-weight: 800; font-family: var(--font-heading);">$<?= number_format((float)$p['price'], 0) ?></span>
            <span style="font-size: 12px; color: var(--text-muted);">/ <?= e($p['billing_cycle']) ?></span>
          </div>
        </div>

        <ul style="list-style: none; display: flex; flex-direction: column; gap: 6px; font-size: 13px; margin: 16px 0; color: var(--text-main);">
          <?php 
          $feats = json_decode($p['features_json'] ?? '[]', true);
          if (is_array($feats)):
            foreach (array_slice($feats, 0, 4) as $ft): ?>
              <li style="display: flex; align-items: center; gap: 8px;">
                <span style="color: var(--color-success);"><?= icon('check', 'icon-sm') ?></span>
                <span><?= e($ft) ?></span>
              </li>
          <?php endforeach; endif; ?>
        </ul>

        <div style="display: flex; justify-content: flex-end; padding-top: 14px; border-top: 1px solid var(--border-light);">
          <a href="<?= base_url('admin/platform_plans.php?edit=' . (int)$p['id']) ?>" class="btn btn-secondary btn-sm">
            Edit Inclusions & Price
          </a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php include __DIR__ . '/../templates/admin_footer.php'; ?>
