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

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim((string)($_POST['name'] ?? ''));
    $tagline = trim((string)($_POST['tagline'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $whatsappNumber = trim((string)($_POST['whatsapp_number'] ?? ''));
    $currency = trim((string)($_POST['currency'] ?? 'KES'));
    $accentColor = trim((string)($_POST['accent_color'] ?? '#f97316'));
    $published = isset($_POST['published']) ? 1 : 0;

    if ($name === '' || mb_strlen($name) > 120) {
        $error = 'Shop name is required (up to 120 characters).';
    } elseif ($whatsappNumber === '') {
        $error = 'WhatsApp phone number is required in international format (e.g. 254700000000).';
    } elseif (!preg_match('/^#[a-fA-F0-9]{6}$/', $accentColor)) {
        $error = 'Accent color must be a valid 6-character hex code (e.g. #f97316).';
    } else {
        $logoPath = $shop['logo_path'];

        // Handle Logo Upload
        if (!empty($_FILES['logo']['name'])) {
            $uploadRes = handle_image_upload($_FILES['logo'], 'logos/' . $shopId, 2);
            if (!$uploadRes['success']) {
                $error = $uploadRes['error'];
            } else {
                $logoPath = $uploadRes['path'];
                if (!empty($shop['logo_path']) && file_exists(__DIR__ . '/../' . $shop['logo_path'])) {
                    @unlink(__DIR__ . '/../' . $shop['logo_path']);
                }
            }
        }

        if (!$error) {
            $upd = db()->prepare('UPDATE shops SET name = ?, tagline = ?, description = ?, whatsapp_number = ?, currency = ?, accent_color = ?, published = ?, logo_path = ? WHERE id = ?');
            $upd->execute([
                $name,
                $tagline ?: null,
                $description ?: null,
                $whatsappNumber,
                $currency,
                $accentColor,
                $published,
                $logoPath,
                $shopId
            ]);

            log_audit('shop_settings_update', "Updated settings for shop '{$name}'", $shopId);
            set_flash('success', 'Shop settings and branding updated successfully.');
            redirect(base_url('admin/settings.php'));
        }
    }
}

$adminNav = 'settings';
$pageTitle = 'Shop Settings — ' . APP_NAME;
include __DIR__ . '/../templates/admin_header.php';
?>

<div style="max-width: 800px; margin: 0 auto;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px;">
    <div>
      <span class="eyebrow">CUSTOMIZATION & BRANDING</span>
      <h1 style="font-size: 26px;">Shop Settings</h1>
      <p style="color: var(--text-muted); font-size: 14px;">Configure your storefront appearance, WhatsApp line, and store currency.</p>
    </div>

    <a href="<?= base_url('index.php?shop=' . urlencode($shop['slug'])) ?>" target="_blank" class="btn btn-secondary btn-sm">
      <?= icon('external', 'icon-sm') ?>
      <span>Preview Storefront</span>
    </a>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= icon('x', 'icon-sm') ?> <span><?= e($error) ?></span></div>
  <?php endif; ?>

  <div class="card" style="padding: 32px;">
    <form method="post" enctype="multipart/form-data" action="<?= base_url('admin/settings.php') ?>">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="name">Shop Name *</label>
          <input class="form-control" type="text" id="name" name="name" required maxlength="120" value="<?= e($shop['name']) ?>">
        </div>

        <div class="form-group">
          <label class="form-label" for="slug">Public Shop Slug</label>
          <input class="form-control" type="text" id="slug" value="<?= e($shop['slug']) ?>" disabled style="background-color: var(--bg-subtle);">
          <span class="form-help">Storefront URL: <code>?shop=<?= e($shop['slug']) ?></code></span>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="tagline">Tagline / Header Motto</label>
        <input class="form-control" type="text" id="tagline" name="tagline" maxlength="255" value="<?= e($shop['tagline'] ?? '') ?>" placeholder="e.g. Curated Luxury & Everyday Essentials">
      </div>

      <div class="form-group">
        <label class="form-label" for="description">About Store Description</label>
        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Displayed to shoppers below your header banner..."><?= e($shop['description'] ?? '') ?></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="whatsapp_number">Official WhatsApp Number (Country Code + Digits) *</label>
          <input class="form-control" type="text" id="whatsapp_number" name="whatsapp_number" required value="<?= e($shop['whatsapp_number']) ?>" placeholder="254700000000">
          <span class="form-help">Do not include +, spaces, or hyphens. Example: 254712345678 or 14155552671.</span>
        </div>

        <div class="form-group">
          <label class="form-label" for="currency">Store Currency Code *</label>
          <input class="form-control" type="text" id="currency" name="currency" required maxlength="8" value="<?= e($shop['currency']) ?>" placeholder="KES, USD, EUR, NGN">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="accent_color">Brand Accent Color (Hex)</label>
          <div style="display: flex; gap: 12px; align-items: center;">
            <input type="color" id="accent_color_picker" value="<?= e($shop['accent_color'] ?: '#f97316') ?>" onchange="document.getElementById('accent_color').value = this.value;" style="width: 44px; height: 42px; padding: 2px; border: 1px solid var(--border-light); border-radius: var(--radius-sm); cursor: pointer;">
            <input class="form-control" type="text" id="accent_color" name="accent_color" maxlength="7" value="<?= e($shop['accent_color'] ?: '#f97316') ?>" onchange="document.getElementById('accent_color_picker').value = this.value;">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="logo">Shop Logo</label>
          <input class="form-control" type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/webp">
          <span class="form-help">Recommended transparent PNG or WEBP (Max 2MB).</span>
        </div>
      </div>

      <?php if (!empty($shop['logo_path'])): ?>
        <div style="margin: 10px 0 20px; display: flex; align-items: center; gap: 14px;">
          <img src="<?= base_url($shop['logo_path']) ?>" alt="Logo" style="height: 48px; border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 4px; background: #fff;">
          <span style="font-size: 13px; color: var(--text-muted);">Current store logo uploaded.</span>
        </div>
      <?php endif; ?>

      <div class="form-group" style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-light);">
        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 600;">
          <input type="checkbox" name="published" value="1" <?= $shop['published'] ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: var(--accent);">
          <span>Catalogue is Published (visible to the public)</span>
        </label>
        <span class="form-help">Unchecking this temporarily puts the shop in offline mode.</span>
      </div>

      <div style="display: flex; justify-content: flex-end; margin-top: 32px;">
        <button class="btn btn-primary btn-lg" type="submit">Save Shop Settings</button>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../templates/admin_footer.php'; ?>
