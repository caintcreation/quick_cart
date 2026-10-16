<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/icons.php';
require_admin();

$shopId = admin_shop_id();
$action = $_GET['action'] ?? 'list';
$error = '';

// Handle Delete Product
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $deleteId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($deleteId) {
        $checkStmt = db()->prepare('SELECT image_path FROM products WHERE id = ? AND shop_id = ?');
        $checkStmt->execute([$deleteId, $shopId]);
        $prod = $checkStmt->fetch();
        if ($prod) {
            // Delete image file if present
            if (!empty($prod['image_path']) && file_exists(__DIR__ . '/../' . $prod['image_path'])) {
                @unlink(__DIR__ . '/../' . $prod['image_path']);
            }
            $delStmt = db()->prepare('DELETE FROM products WHERE id = ? AND shop_id = ?');
            $delStmt->execute([$deleteId, $shopId]);
            log_audit('product_delete', "Deleted product #{$deleteId}", $shopId);
            set_flash('success', 'Product was successfully removed from your catalogue.');
        }
    }
    redirect(base_url('admin/products.php'));
}

// Handle Add or Edit Product Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['new', 'edit'], true)) {
    verify_csrf();

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
    $name = trim((string)($_POST['name'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT) ?: null;
    $categoryName = trim((string)($_POST['category_name'] ?? 'General'));
    $price = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_FLOAT);
    $comparePrice = filter_var($_POST['compare_at_price'] ?? null, FILTER_VALIDATE_FLOAT);
    $stock = filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $active = isset($_POST['active']) ? 1 : 0;

    // Retrieve category name if category_id given
    if ($categoryId) {
        $cStmt = db()->prepare('SELECT name FROM categories WHERE id = ? AND shop_id = ?');
        $cStmt->execute([$categoryId, $shopId]);
        $cRow = $cStmt->fetch();
        if ($cRow) $categoryName = $cRow['name'];
    }

    if ($name === '' || mb_strlen($name) > 160) {
        $error = 'Product name is required (up to 160 characters).';
    } elseif ($price === false || $price < 0) {
        $error = 'Please enter a valid non-negative product price.';
    } elseif ($stock === false) {
        $error = 'Stock must be zero or a positive integer.';
    } else {
        $existingImage = null;
        if ($id) {
            $eStmt = db()->prepare('SELECT image_path FROM products WHERE id = ? AND shop_id = ?');
            $eStmt->execute([$id, $shopId]);
            $existingImage = $eStmt->fetchColumn() ?: null;
        }

        $imagePath = $existingImage;

        // Process image upload if provided
        if (!empty($_FILES['image']['name'])) {
            $uploadRes = handle_image_upload($_FILES['image'], 'products/' . $shopId, 5);
            if (!$uploadRes['success']) {
                $error = $uploadRes['error'];
            } else {
                $imagePath = $uploadRes['path'];
                // Clean up previous image file if replacing
                if ($existingImage && file_exists(__DIR__ . '/../' . $existingImage)) {
                    @unlink(__DIR__ . '/../' . $existingImage);
                }
            }
        }

        if (!$error) {
            if ($id) {
                $updStmt = db()->prepare('UPDATE products SET name = ?, description = ?, price = ?, compare_at_price = ?, category_id = ?, category = ?, stock = ?, active = ?, image_path = ? WHERE id = ? AND shop_id = ?');
                $updStmt->execute([$name, $description, $price, $comparePrice ?: null, $categoryId, $categoryName, $stock, $active, $imagePath, $id, $shopId]);
                log_audit('product_update', "Updated product '{$name}' (#{$id})", $shopId);
                set_flash('success', "Product '{$name}' updated successfully.");
            } else {
                $insStmt = db()->prepare('INSERT INTO products (shop_id, name, description, price, compare_at_price, category_id, category, stock, active, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $insStmt->execute([$shopId, $name, $description, $price, $comparePrice ?: null, $categoryId, $categoryName, $stock, $active, $imagePath]);
                $newId = db()->lastInsertId();
                log_audit('product_create', "Created product '{$name}' (#{$newId})", $shopId);
                set_flash('success', "Product '{$name}' added to catalogue.");
            }
            redirect(base_url('admin/products.php'));
        }
    }
}

// Fetch categories for dropdown
$categories = db()->prepare('SELECT * FROM categories WHERE shop_id = ? ORDER BY sort_order ASC, name ASC');
$categories->execute([$shopId]);
$categoryList = $categories->fetchAll();

// Product Form View (New / Edit)
if (in_array($action, ['new', 'edit'], true)) {
    $editId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
    $product = [
        'id' => 0,
        'name' => '',
        'description' => '',
        'price' => '',
        'compare_at_price' => '',
        'category_id' => '',
        'category' => 'General',
        'stock' => 50,
        'active' => 1,
        'image_path' => null
    ];

    if ($action === 'edit' && $editId) {
        $pStmt = db()->prepare('SELECT * FROM products WHERE id = ? AND shop_id = ?');
        $pStmt->execute([$editId, $shopId]);
        $fetched = $pStmt->fetch();
        if ($fetched) {
            $product = $fetched;
        } else {
            set_flash('error', 'Product not found.');
            redirect(base_url('admin/products.php'));
        }
    }

    $adminNav = 'products';
    $pageTitle = ($action === 'edit' ? 'Edit Product' : 'Add New Product') . ' — ' . APP_NAME;
    include __DIR__ . '/../templates/admin_header.php';
    ?>

    <div style="max-width: 800px; margin: 0 auto;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
          <span class="eyebrow">PRODUCT MANAGEMENT</span>
          <h1 style="font-size: 26px;"><?= $action === 'edit' ? 'Edit Product' : 'Add New Product' ?></h1>
        </div>
        <a href="<?= base_url('admin/products.php') ?>" class="btn btn-secondary btn-sm">← Back to List</a>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= icon('x', 'icon-sm') ?> <span><?= e($error) ?></span></div>
      <?php endif; ?>

      <div class="card" style="padding: 32px;">
        <form method="post" enctype="multipart/form-data" action="<?= base_url('admin/products.php?action=' . $action) ?>">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">

          <div class="form-group">
            <label class="form-label" for="name">Product Title *</label>
            <input class="form-control" type="text" id="name" name="name" required maxlength="160" value="<?= e($product['name']) ?>" placeholder="e.g. Leather Oxford Shoes">
          </div>

          <div class="form-group">
            <label class="form-label" for="description">Product Description</label>
            <textarea class="form-control" id="description" name="description" rows="4" placeholder="Detail materials, sizing, fits, and features..."><?= e($product['description']) ?></textarea>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label" for="price">Selling Price *</label>
              <input class="form-control" type="number" step="0.01" min="0" id="price" name="price" required value="<?= e((string)$product['price']) ?>" placeholder="0.00">
            </div>

            <div class="form-group">
              <label class="form-label" for="stock">Inventory Stock Count *</label>
              <input class="form-control" type="number" step="1" min="0" id="stock" name="stock" required value="<?= (int)$product['stock'] ?>" placeholder="10">
              <span class="form-help">Automatically decreases when customers confirm orders.</span>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label" for="category_id">Category</label>
              <select class="form-control" id="category_id" name="category_id">
                <option value="">General (Default)</option>
                <?php foreach ($categoryList as $cl): ?>
                  <option value="<?= (int)$cl['id'] ?>" <?= ($product['category_id'] == $cl['id'] || $product['category'] === $cl['name']) ? 'selected' : '' ?>>
                    <?= e($cl['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label" for="category_name">Or New Category Name</label>
              <input class="form-control" type="text" id="category_name" name="category_name" value="<?= e($product['category'] ?? '') ?>" placeholder="Type if not in list above">
            </div>
          </div>

          <!-- Product Image Upload -->
          <div class="form-group" style="padding-top: 10px;">
            <label class="form-label" for="image">Product Photography</label>
            <input class="form-control" type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif" data-preview="#imagePreview">
            <span class="form-help">Supported formats: JPG, PNG, WEBP, GIF. Max 5MB. Verified for server security.</span>

            <div style="margin-top: 16px; display: flex; align-items: center; gap: 20px;">
              <div style="width: 100px; height: 100px; border-radius: var(--radius-md); border: 1px solid var(--border-light); overflow: hidden; background-color: var(--bg-subtle); display: flex; align-items: center; justify-content: center;">
                <img id="imagePreview" src="<?= !empty($product['image_path']) ? base_url($product['image_path']) : '' ?>" alt="Preview" style="width:100%; height:100%; object-fit: cover; display: <?= !empty($product['image_path']) ? 'block' : 'none' ?>;">
              </div>
              <?php if (!empty($product['image_path'])): ?>
                <span style="font-size: 13px; color: var(--text-muted);">Current photo saved. Choose a new file to replace it.</span>
              <?php endif; ?>
            </div>
          </div>

          <div class="form-group" style="margin-top: 24px;">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 600;">
              <input type="checkbox" name="active" value="1" <?= $product['active'] ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: var(--accent);">
              <span>Publish in storefront (available for customer orders)</span>
            </label>
          </div>

          <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--border-light);">
            <a href="<?= base_url('admin/products.php') ?>" class="btn btn-secondary">Cancel</a>
            <button class="btn btn-primary" type="submit"><?= $action === 'edit' ? 'Update Product' : 'Save Product' ?></button>
          </div>
        </form>
      </div>
    </div>

    <?php
    include __DIR__ . '/../templates/admin_footer.php';
    exit;
}

// Product List View
$search = trim($_GET['q'] ?? '');
$filterCategory = trim($_GET['cat'] ?? '');

$sql = "SELECT * FROM products WHERE shop_id = ?";
$params = [$shopId];

if ($search !== '') {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($filterCategory !== '') {
    $sql .= " AND category = ?";
    $params[] = $filterCategory;
}

$sql .= " ORDER BY id DESC";
$prodStmt = db()->prepare($sql);
$prodStmt->execute($params);
$products = $prodStmt->fetchAll();

$adminNav = 'products';
$pageTitle = 'Manage Products — ' . APP_NAME;
include __DIR__ . '/../templates/admin_header.php';
?>

<div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 28px;">
  <div>
    <span class="eyebrow">INVENTORY</span>
    <h1 style="font-size: 26px;">Product Catalogue (<?= count($products) ?>)</h1>
  </div>

  <a href="<?= base_url('admin/products.php?action=new') ?>" class="btn btn-primary">
    <?= icon('plus', 'icon-sm') ?>
    <span>Add New Product</span>
  </a>
</div>

<!-- Filters bar -->
<div class="card" style="padding: 16px 20px; margin-bottom: 24px;">
  <form method="get" action="<?= base_url('admin/products.php') ?>" style="display: flex; flex-wrap: wrap; gap: 14px; align-items: center;">
    <div style="position: relative; flex: 1; min-width: 240px;">
      <input class="form-control" type="search" name="q" value="<?= e($search) ?>" placeholder="Filter products by title..." style="padding-left: 36px;">
      <div style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-subtle);">
        <?= icon('search', 'icon-sm') ?>
      </div>
    </div>

    <select class="form-control" name="cat" style="width: auto; min-width: 180px;">
      <option value="">All Categories</option>
      <?php foreach ($categoryList as $cl): ?>
        <option value="<?= e($cl['name']) ?>" <?= $filterCategory === $cl['name'] ? 'selected' : '' ?>><?= e($cl['name']) ?></option>
      <?php endforeach; ?>
    </select>

    <button class="btn btn-secondary btn-sm" type="submit">Filter</button>
    <?php if ($search !== '' || $filterCategory !== ''): ?>
      <a href="<?= base_url('admin/products.php') ?>" class="btn btn-secondary btn-sm">Reset</a>
    <?php endif; ?>
  </form>
</div>

<!-- Products Table -->
<div class="card" style="padding: 0; overflow: hidden;">
  <?php if (empty($products)): ?>
    <div class="empty-state">
      <div class="empty-state-icon"><?= icon('package', 'icon-lg') ?></div>
      <div class="empty-state-title">No products found</div>
      <div class="empty-state-desc">Try clearing your filters or create your first product listing above.</div>
    </div>
  <?php else: ?>
    <div class="table-responsive" style="border: none;">
      <table class="data-table">
        <thead>
          <tr>
            <th style="width: 70px;">Image</th>
            <th>Product Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Status</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p): ?>
            <tr>
              <td>
                <div style="width: 48px; height: 48px; border-radius: var(--radius-sm); background-color: var(--bg-subtle); overflow: hidden; display: flex; align-items: center; justify-content: center;">
                  <?php if (!empty($p['image_path']) && file_exists(__DIR__ . '/../' . $p['image_path'])): ?>
                    <img src="<?= base_url($p['image_path']) ?>" alt="<?= e($p['name']) ?>" style="width:100%; height:100%; object-fit: cover;">
                  <?php else: ?>
                    <span style="font-weight: 800; color: var(--text-subtle);"><?= strtoupper(substr($p['name'], 0, 1)) ?></span>
                  <?php endif; ?>
                </div>
              </td>
              <td>
                <strong style="display: block; font-size: 15px;"><?= e($p['name']) ?></strong>
                <span style="font-size: 12px; color: var(--text-muted); display: block; max-width: 320px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                  <?= e($p['description']) ?>
                </span>
              </td>
              <td><span class="badge badge-neutral"><?= e($p['category']) ?></span></td>
              <td><strong><?= format_currency((float)$p['price'], $shop['currency'] ?? 'KES') ?></strong></td>
              <td>
                <?php if ((int)$p['stock'] <= 5): ?>
                  <span class="badge badge-danger"><?= (int)$p['stock'] ?> Left</span>
                <?php else: ?>
                  <span style="font-weight: 600;"><?= (int)$p['stock'] ?></span>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge <?= $p['active'] ? 'badge-success' : 'badge-neutral' ?>">
                  <?= $p['active'] ? 'Published' : 'Hidden' ?>
                </span>
              </td>
              <td style="text-align: right;">
                <div style="display: inline-flex; gap: 8px;">
                  <a href="<?= base_url('admin/products.php?action=edit&id=' . (int)$p['id']) ?>" class="btn btn-secondary btn-sm" title="Edit">
                    <?= icon('edit', 'icon-sm') ?>
                  </a>

                  <form method="post" action="<?= base_url('admin/products.php?action=delete') ?>" onsubmit="return confirm('Are you sure you want to delete this product?');" style="margin: 0;">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <button class="btn btn-secondary btn-sm" type="submit" style="color: var(--color-danger);" title="Delete">
                      <?= icon('trash', 'icon-sm') ?>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../templates/admin_footer.php'; ?>
