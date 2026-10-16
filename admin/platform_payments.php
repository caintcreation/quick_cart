<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/icons.php';
require_platform_owner();

$error = '';

// Handle Record New Service Payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $shopId = filter_input(INPUT_POST, 'shop_id', FILTER_VALIDATE_INT) ?: null;
    $customerName = trim((string)($_POST['customer_name'] ?? ''));
    $amount = filter_var($_POST['amount'] ?? null, FILTER_VALIDATE_FLOAT);
    $currency = trim((string)($_POST['currency'] ?? 'USD'));
    $method = trim((string)($_POST['payment_method'] ?? 'Bank Transfer'));
    $reference = trim((string)($_POST['reference'] ?? ''));
    $paymentDate = trim((string)($_POST['payment_date'] ?? date('Y-m-d')));
    $notes = trim((string)($_POST['notes'] ?? ''));

    if ($customerName === '' || $amount === false || $amount <= 0) {
        $error = 'Customer name and a valid positive payment amount are required.';
    } else {
        $ins = db()->prepare('INSERT INTO payment_records (shop_id, customer_name, amount, currency, payment_method, reference, payment_date, notes, recorded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $ins->execute([$shopId, $customerName, $amount, $currency, $method, $reference ?: null, $paymentDate, $notes ?: null, (int)$_SESSION['admin_id']]);

        log_audit('payment_record_created', "Recorded {$currency} {$amount} from {$customerName}", $shopId);
        set_flash('success', 'Service payment record saved successfully.');
        redirect(base_url('admin/platform_payments.php'));
    }
}

// Fetch all payment records with shop name
$payments = db()->query("
    SELECT p.*, s.name as shop_name 
    FROM payment_records p 
    LEFT JOIN shops s ON p.shop_id = s.id 
    ORDER BY p.payment_date DESC, p.id DESC
")->fetchAll();

$totalCollected = (float)db()->query("SELECT COALESCE(SUM(amount), 0) FROM payment_records")->fetchColumn();
$shopsList = db()->query("SELECT id, name FROM shops ORDER BY name ASC")->fetchAll();

$adminNav = 'platform_payments';
$pageTitle = 'Service Payments Ledger — ' . APP_NAME;
include __DIR__ . '/../templates/admin_header.php';
?>

<div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 28px;">
  <div>
    <span class="eyebrow">REVENUE & BILLING</span>
    <h1 style="font-size: 26px;">Service Payments Ledger</h1>
    <p style="color: var(--text-muted); font-size: 14px;">Manual bookkeeping for customer shop setup and recurring maintenance fees.</p>
  </div>

  <div class="card" style="padding: 12px 24px;">
    <span style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Total Recorded Revenue:</span>
    <div style="font-size: 22px; font-weight: 800; font-family: var(--font-heading); color: var(--color-success);">
      $<?= number_format($totalCollected, 2) ?>
    </div>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger"><?= icon('x', 'icon-sm') ?> <span><?= e($error) ?></span></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1.6fr; gap: 32px; align-items: start;">
  <!-- Record Payment Form -->
  <div class="card" style="padding: 28px;">
    <h2 style="font-size: 18px; margin-bottom: 16px;">Record Service Payment</h2>

    <form method="post" action="<?= base_url('admin/platform_payments.php') ?>">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

      <div class="form-group">
        <label class="form-label" for="customer_name">Client / Customer Name *</label>
        <input class="form-control" type="text" id="customer_name" name="customer_name" required placeholder="e.g. Alex Morgan">
      </div>

      <div class="form-group">
        <label class="form-label" for="shop_id">Associated Customer Shop</label>
        <select class="form-control" id="shop_id" name="shop_id">
          <option value="">-- Standalone Client / In Setup --</option>
          <?php foreach ($shopsList as $sl): ?>
            <option value="<?= (int)$sl['id'] ?>"><?= e($sl['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="amount">Amount *</label>
          <input class="form-control" type="number" step="0.01" min="1" id="amount" name="amount" required placeholder="149.00">
        </div>

        <div class="form-group">
          <label class="form-label" for="currency">Currency</label>
          <input class="form-control" type="text" id="currency" name="currency" value="USD" placeholder="USD, KES, EUR">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="payment_method">Payment Method</label>
          <select class="form-control" id="payment_method" name="payment_method">
            <option value="Bank Transfer">Bank Transfer / Wire</option>
            <option value="M-Pesa / Mobile Money">M-Pesa / Mobile Money</option>
            <option value="Cash / Cheque">Cash / Cheque</option>
            <option value="PayPal / Stripe Invoice">PayPal / Stripe Invoice</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="payment_date">Date Received</label>
          <input class="form-control" type="date" id="payment_date" name="payment_date" value="<?= date('Y-m-d') ?>">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="reference">Bank / Transaction Reference</label>
        <input class="form-control" type="text" id="reference" name="reference" placeholder="e.g. MPESA-QZ89123 or Wire Ref #">
      </div>

      <div class="form-group">
        <label class="form-label" for="notes">Notes / Scope Covered</label>
        <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="e.g. Business Shop Setup fee + domain linking"></textarea>
      </div>

      <button class="btn btn-primary" type="submit" style="width: 100%;">
        Save Payment Record
      </button>
    </form>
  </div>

  <!-- Payments Ledger Table -->
  <div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive" style="border: none;">
      <table class="data-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Customer & Shop</th>
            <th>Amount</th>
            <th>Method & Ref</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($payments)): ?>
            <tr>
              <td colspan="4" style="text-align: center; padding: 36px; color: var(--text-muted);">
                No service payments recorded yet.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($payments as $pm): ?>
              <tr>
                <td><?= date('M j, Y', strtotime($pm['payment_date'])) ?></td>
                <td>
                  <strong><?= e($pm['customer_name']) ?></strong>
                  <div style="font-size: 12px; color: var(--text-muted);">
                    <?= e($pm['shop_name'] ?: 'Standalone Setup') ?>
                  </div>
                </td>
                <td>
                  <strong style="color: var(--color-success); font-family: var(--font-heading); font-size: 16px;">
                    <?= e($pm['currency']) ?> <?= number_format((float)$pm['amount'], 2) ?>
                  </strong>
                </td>
                <td>
                  <div><?= e($pm['payment_method']) ?></div>
                  <?php if (!empty($pm['reference'])): ?>
                    <div style="font-size: 11px; font-family: var(--font-mono); color: var(--text-muted);">
                      Ref: <?= e($pm['reference']) ?>
                    </div>
                  <?php endif; ?>
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
