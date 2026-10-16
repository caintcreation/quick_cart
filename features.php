<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/icons.php';

$activeNav = 'features';
$pageTitle = 'Platform Features — ' . APP_NAME;
$metaDescription = 'Explore all real, working features of QuickCart Studio: product catalogue, live search, WhatsApp order link encoding, stock management, and admin dashboards.';

include __DIR__ . '/templates/header.php';
?>

<section style="padding: 60px 0 40px; border-bottom: 1px solid var(--border-light); background-color: var(--bg-surface);">
  <div class="container text-center" style="max-width: 760px; text-align: center; margin: 0 auto;">
    <span class="eyebrow">WHAT WE DELIVER</span>
    <h1 class="heading-lg" style="margin: 12px 0 16px;">Built for Real Conversions, Not Complexity</h1>
    <p class="subheading" style="margin: 0 auto;">
      Every feature described below is fully implemented and operating with real MySQL transactions and server-side validation.
    </p>
  </div>
</section>

<section style="padding: 70px 0;">
  <div class="container" style="display: flex; flex-direction: column; gap: 64px;">
    <!-- Feature 1 -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 48px; align-items: center;">
      <div>
        <div style="width: 44px; height: 44px; border-radius: 10px; background-color: var(--accent-light); color: var(--accent); display: flex; align-items: center; justify-content: center; margin-bottom: 18px;">
          <?= icon('whatsapp', 'icon') ?>
        </div>
        <h2 style="font-size: 26px; margin-bottom: 14px;">Real WhatsApp Order Message Encoding</h2>
        <p style="color: var(--text-muted); font-size: 15px; line-height: 1.65; margin-bottom: 20px;">
          When a buyer selects product quantities and submits an order, QuickCart encodes all details into a clean, standard <code>wa.me</code> protocol URL with proper URL encoding for spaces, line breaks, prices, and reference IDs.
        </p>
        <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px; font-size: 14px;">
          <li style="display: flex; align-items: center; gap: 10px;">
            <span style="color: var(--color-success);"><?= icon('check', 'icon-sm') ?></span>
            <span>Pre-filled shop greeting & item breakdown</span>
          </li>
          <li style="display: flex; align-items: center; gap: 10px;">
            <span style="color: var(--color-success);"><?= icon('check', 'icon-sm') ?></span>
            <span>Calculated totals with custom shop currency</span>
          </li>
          <li style="display: flex; align-items: center; gap: 10px;">
            <span style="color: var(--color-success);"><?= icon('check', 'icon-sm') ?></span>
            <span>Unique order tracking reference (e.g. QC-XXXXXX)</span>
          </li>
        </ul>
      </div>
      <div class="card" style="padding: 28px; background-color: var(--bg-surface); border: 1px solid var(--border-strong);">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
          <?= icon('whatsapp', 'icon') ?>
          <strong style="font-size: 14px;">WhatsApp Message Preview</strong>
        </div>
        <div style="background-color: var(--bg-subtle); padding: 18px; border-radius: var(--radius-md); font-family: var(--font-mono); font-size: 13px; line-height: 1.6; color: var(--text-main); white-space: pre-wrap;">
Hello QuickCart Demo, I would like to order:
2 x Everyday Street Sneakers (KES 2,500.00)
Total: KES 5,000.00
Order reference: QC-9F2B48A1

Sending this message does not confirm payment or acceptance.</div>
      </div>
    </div>

    <!-- Feature 2 -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 48px; align-items: center;">
      <div class="card" style="padding: 28px; background-color: var(--bg-surface); border: 1px solid var(--border-strong);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
          <strong style="font-size: 14px;">Stock Reservation Transaction</strong>
          <span class="badge badge-success">ACID Compliant</span>
        </div>
        <div style="background-color: var(--bg-subtle); padding: 16px; border-radius: var(--radius-md); font-size: 13px; color: var(--text-muted); line-height: 1.6;">
          <code>UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?</code>
          <p style="margin-top: 10px;">Orders are logged into the database with snapshots of pricing before redirecting to WhatsApp. Rollback ensures stock never drops below zero even during high traffic bursts.</p>
        </div>
      </div>
      <div>
        <div style="width: 44px; height: 44px; border-radius: 10px; background-color: var(--color-success-bg); color: var(--color-success); display: flex; align-items: center; justify-content: center; margin-bottom: 18px;">
          <?= icon('package', 'icon') ?>
        </div>
        <h2 style="font-size: 26px; margin-bottom: 14px;">Database Order Logging & Stock Integrity</h2>
        <p style="color: var(--text-muted); font-size: 15px; line-height: 1.65; margin-bottom: 20px;">
          Unlike basic catalogues that simply redirect without keeping records, QuickCart records the full order transaction in MySQL with a snapshot of item price, prevents overselling with atomic stock deductions, and stores customer information.
        </p>
        <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px; font-size: 14px;">
          <li style="display: flex; align-items: center; gap: 10px;">
            <span style="color: var(--color-success);"><?= icon('check', 'icon-sm') ?></span>
            <span>Separate order items snapshot table</span>
          </li>
          <li style="display: flex; align-items: center; gap: 10px;">
            <span style="color: var(--color-success);"><?= icon('check', 'icon-sm') ?></span>
            <span>Lifecycle statuses: Pending, Confirmed, Processing, Completed, Cancelled</span>
          </li>
          <li style="display: flex; align-items: center; gap: 10px;">
            <span style="color: var(--color-success);"><?= icon('check', 'icon-sm') ?></span>
            <span>Printable order packing slips in admin</span>
          </li>
        </ul>
      </div>
    </div>

    <!-- Feature 3 -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 48px; align-items: center;">
      <div>
        <div style="width: 44px; height: 44px; border-radius: 10px; background-color: var(--color-info-bg); color: var(--color-info); display: flex; align-items: center; justify-content: center; margin-bottom: 18px;">
          <?= icon('store', 'icon') ?>
        </div>
        <h2 style="font-size: 26px; margin-bottom: 14px;">Tenant Isolation & Security</h2>
        <p style="color: var(--text-muted); font-size: 15px; line-height: 1.65; margin-bottom: 20px;">
          Each shop owner accesses only their shop data. Product management, categories, and order lists enforce strict session-derived <code>shop_id</code> scoping on every SQL query.
        </p>
        <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px; font-size: 14px;">
          <li style="display: flex; align-items: center; gap: 10px;">
            <span style="color: var(--color-success);"><?= icon('check', 'icon-sm') ?></span>
            <span>MIME verification & non-executable upload protection</span>
          </li>
          <li style="display: flex; align-items: center; gap: 10px;">
            <span style="color: var(--color-success);"><?= icon('check', 'icon-sm') ?></span>
            <span>Full CSRF token verification across all forms</span>
          </li>
          <li style="display: flex; align-items: center; gap: 10px;">
            <span style="color: var(--color-success);"><?= icon('check', 'icon-sm') ?></span>
            <span>Platform owner role separated from merchant accounts</span>
          </li>
        </ul>
      </div>
      <div class="card" style="padding: 28px; background-color: var(--bg-surface); border: 1px solid var(--border-strong);">
        <span class="eyebrow">SECURITY HIGHLIGHT</span>
        <h3 style="font-size: 18px; margin-bottom: 12px;">Zero Trust Shop Isolation</h3>
        <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6;">
          A merchant cannot modify someone else's product or view another boutique's orders by altering URL query parameters. All write operations verify the authenticated session owner.
        </p>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/templates/footer.php'; ?>
