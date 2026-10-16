<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/icons.php';
require_admin();

$shopId = admin_shop_id();
$error = '';

// Handle Delete Category
if (isset($_GET['action']) && $_GET['action'] === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $deleteId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($deleteId) {
        $delStmt = db()->prepare('DELETE FROM categories WHERE id = ? AND shop_id = ?');
        $delStmt->execute([$deleteId, $shopId]);
        log_audit('category_delete', "Deleted category #{$deleteId}", $shopId);
        set_flash('success', 'Category deleted successfully.');
    }
    redirect(base_url('admin/categories.php'));
}

// Handle Add / Edit Category
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
    $name = trim((string)($_POST['name'] ?? ''));
    $slug = trim((string)($_POST['slug'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $sortOrder = filter_input(INPUT_POST, 'sort_order', FILTER_VALIDATE_INT) ?: 0;

    if ($name === '' || mb_strlen($name) > 100) {
        $error = 'Category name is required (up to 100 characters).';
    } else {
        if ($slug === '') {
            $slug = strtolower(trim(preg_replace('/[^a-z0-9-]+/', '-', $name), '-'));
        } else {
            $slug = strtolower(trim(preg_replace('/[^a-z0-9-]+/', '-', $slug), '-'));
        }

        // Check uniqueness within shop
        $chk = db()->prepare('SELECT id FROM categories WHERE shop_id = ? AND slug = ? AND id != ?');
        $chk->execute([$shopId, $slug, $id]);
        if ($chk->fetch()) {
            $error = 'A category with this slug already exists in your shop.';
        } else {
            if ($id) {
                $upd = db()->prepare('UPDATE categories SET name = ?, slug = ?, description = ?, sort_order = ? WHERE id = ? AND shop_id = ?');
                $upd->execute([$name, $slug, $description, $sortOrder, $id, $shopId]);
                log_audit('category_update', "Updated category {$name}", $shopId);
                set_flash('success', 'Category updated.');
            } else {
                $ins = db()->prepare('INSERT INTO categories (shop_id, name, slug, description, sort_order) VALUES (?, ?, ?, ?, ?)');
                $ins->execute([$shopId, $name, $slug, $description, $sortOrder]);
                log_audit('category_create', "Created category {$name}", $shopId);
                set_flash('success', 'Category created.');
            }
            redirect(base_url('admin/categories.php'));
        }
    }
}

// Fetch categories with product counts
$catStmt = db()->prepare('SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.shop_id = c.shop_id AND (p.category_id = c.id OR LOWER(p.category) = LOWER(c.name))) as product_count FROM categories c WHERE c.shop_id = ? ORDER BY c.sort_order ASC, c.name ASC');
$catStmt->execute([$shopId]);
$categories = $catStmt->fetchAll();

$editCategory = null;
if (isset($_GET['edit'])) {
    $editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    if ($editId) {
        $eStmt = db()->prepare('SELECT * FROM categories WHERE id = ? AND shop_id = ?');
        $eStmt->execute([$editId, $shopId]);
        $editCategory = $eStmt->fetch();
    }
}

$adminNav = 'categories';
$pageTitle = 'Manage Categories — ' . APP_NAME;
include __DIR__ . '/../templates/admin_header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px;">
  <div>
    <span class="eyebrow">STORE ORGANIZATION</span>
    <h1 style="font-size: 26px;">Product Categories (<?= count($categories) ?>)</h1>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger"><?= icon('x', 'icon-sm') ?> <span><?= e($error) ?></span></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1.6fr; gap: 32px; align-items: start;">
  <!-- Category Add/Edit Form -->
  <div class="card" style="padding: 28px;">
    <h2 style="font-size: 18px; margin-bottom: 16px;">
      <?= $editCategory ? 'Edit Category' : 'Create New Category' ?>
    </h2>

    <form method="post" action="<?= base_url('admin/categories.php') ?>">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= (int)($editCategory['id'] ?? 0) ?>">

      <div class="form-group">
        <label class="form-label" for="name">Category Name *</label>
        <input class="form-control" type="text" id="name" name="name" required maxlength="100" value="<?= e($editCategory['name'] ?? '') ?>" placeholder="e.g. Footwear">
      </div>

      <div class="form-group">
        <label class="form-label" for="slug">URL Slug (Optional)</label>
        <input class="form-control" type="text" id="slug" name="slug" maxlength="100" value="<?= e($editCategory['slug'] ?? '') ?>" placeholder="footwear">
      </div>

      <div class="form-group">
        <label class="form-label" for="description">Short Description</label>
        <textarea class="form-control" id="description" name="description" rows="2" placeholder="Brief note on items in this category..."><?= e($editCategory['description'] ?? '') ?></textarea>
      </div>

      <div class="form-group">
        <label class="form-label" for="sort_order">Display Sort Order</label>
        <input class="form-control" type="number" id="sort_order" name="sort_order" value="<?= (int)($editCategory['sort_order'] ?? 0) ?>">
      </div>

      <div style="display: flex; gap: 10px; margin-top: 24px;">
        <button class="btn btn-primary" type="submit">
          <?= $editCategory ? 'Update Category' : 'Create Category' ?>
        </button>
        <?php if ($editCategory): ?>
          <a href="<?= base_url('admin/categories.php') ?>" class="btn btn-secondary">Cancel</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Category List Table -->
  <div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive" style="border: none;">
      <table class="data-table">
        <thead>
          <tr>
            <th>Name & Slug</th>
            <th>Items</th>
            <th>Order</th>
            <th style="text-align: right;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($categories)): ?>
            <tr>
              <td colspan="4" style="text-align: center; padding: 32px; color: var(--text-muted);">
                No categories created yet.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($categories as $cat): ?>
              <tr>
                <td>
                  <strong><?= e($cat['name']) ?></strong>
                  <div style="font-size: 11px; font-family: var(--font-mono); color: var(--text-muted);"><?= e($cat['slug']) ?></div>
                </td>
                <td>
                  <span class="badge badge-neutral"><?= (int)$cat['product_count'] ?> items</span>
                </td>
                <td><?= (int)$cat['sort_order'] ?></td>
                <td style="text-align: right;">
                  <div style="display: inline-flex; gap: 8px;">
                    <a href="<?= base_url('admin/categories.php?edit=' . (int)$cat['id']) ?>" class="btn btn-secondary btn-sm" title="Edit">
                      <?= icon('edit', 'icon-sm') ?>
                    </a>
                    <form method="post" action="<?= base_url('admin/categories.php?action=delete') ?>" onsubmit="return confirm('Delete this category? Products in it will be unassigned.');" style="margin: 0;">
                      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                      <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
                      <button class="btn btn-secondary btn-sm" type="submit" style="color: var(--color-danger);" title="Delete">
                        <?= icon('trash', 'icon-sm') ?>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../templates/admin_footer.php'; ?>
