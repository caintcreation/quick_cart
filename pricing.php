<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/icons.php';

$activeNav = 'pricing';
$pageTitle = 'Setup Packages & Pricing — ' . APP_NAME;
$metaDescription = 'Explore transparent setup packages for custom WhatsApp catalogues and managed online shops. No hidden transaction cuts or surprise monthly costs.';

$plans = db()->query("SELECT * FROM pricing_plans WHERE active = 1 ORDER BY sort_order ASC")->fetchAll();

include __DIR__ . '/templates/header.php';
?>

<section style="padding: 60px 0 40px; border-bottom: 1px solid var(--border-light); background-color: var(--bg-surface);">
  <div class="container text-center" style="max-width: 760px; text-align: center; margin: 0 auto;">
    <span class="eyebrow">TRANSPARENT VALUE</span>
    <h1 class="heading-lg" style="margin: 12px 0 16px;">Predictable Pricing For Real Growth</h1>
    <p class="subheading" style="margin: 0 auto;">
      We charge an honest setup fee and optional managed support. You keep 100% of your sales profits and deal directly with your customers over WhatsApp.
    </p>
  </div>
</section>

<section id="packages" style="padding: 70px 0;">
  <div class="container">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 32px; margin-bottom: 60px;">
      <?php foreach ($plans as $plan): ?>
        <div class="card card-hover" style="display: flex; flex-direction: column; <?= $plan['is_popular'] ? 'border: 2px solid var(--accent); position: relative;' : '' ?>">
          <?php if (!empty($plan['badge'])): ?>
            <div style="margin-bottom: 12px;">
              <span class="badge <?= $plan['is_popular'] ? 'badge-primary' : 'badge-neutral' ?>">
                <?= e($plan['badge']) ?>
              </span>
            </div>
          <?php endif; ?>

          <h2 style="font-size: 24px; margin-bottom: 8px;"><?= e($plan['name']) ?></h2>
          <p style="color: var(--text-muted); font-size: 14px; line-height: 1.5; min-height: 44px; margin-bottom: 24px;"><?= e($plan['description']) ?></p>

          <div style="margin-bottom: 28px; padding-bottom: 20px; border-bottom: 1px solid var(--border-light);">
            <span style="font-size: 42px; font-weight: 800; font-family: var(--font-heading);">$<?= number_format((float)$plan['price'], 0) ?></span>
            <span style="color: var(--text-muted); font-size: 14px;"> / <?= e($plan['billing_cycle']) ?></span>
          </div>

          <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-subtle); margin-bottom: 14px;">
            Package Inclusions
          </div>

          <ul style="list-style: none; display: flex; flex-direction: column; gap: 14px; margin-bottom: 36px; flex: 1;">
            <?php 
            $features = json_decode($plan['features_json'] ?? '[]', true);
            if (is_array($features)):
              foreach ($features as $f): ?>
                <li style="display: flex; align-items: flex-start; gap: 10px; font-size: 14px; color: var(--text-main);">
                  <span style="color: var(--accent); margin-top: 2px;"><?= icon('check', 'icon-sm') ?></span>
                  <span><?= e($f) ?></span>
                </li>
            <?php endforeach; endif; ?>
          </ul>

          <a href="<?= base_url('contact.php?plan=' . urlencode($plan['slug'])) ?>" class="btn <?= $plan['is_popular'] ? 'btn-primary' : 'btn-secondary' ?> btn-lg" style="width: 100%;">
            Choose <?= e($plan['name']) ?>
          </a>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- FAQ Section -->
    <div style="max-width: 800px; margin: 0 auto; padding-top: 40px; border-top: 1px solid var(--border-light);">
      <h3 class="heading-md" style="text-align: center; margin-bottom: 40px;">Frequently Asked Questions</h3>

      <div style="display: flex; flex-direction: column; gap: 20px;">
        <div class="card" style="padding: 24px;">
          <h4 style="font-size: 17px; margin-bottom: 8px;">Do you take a percentage cut of my orders?</h4>
          <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6;">
            No. QuickCart Studio never charges transaction fees. Customers order directly to your WhatsApp number and you collect payment via your preferred local method (such as M-Pesa, bank transfer, cash on delivery, or card invoice).
          </p>
        </div>

        <div class="card" style="padding: 24px;">
          <h4 style="font-size: 17px; margin-bottom: 8px;">Can I use my own custom domain?</h4>
          <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6;">
            Yes. With the Business and Managed Shop packages, we can connect your shop to your own custom domain name (e.g. <code>mystore.com</code>) or configure clean subdomains.
          </p>
        </div>

        <div class="card" style="padding: 24px;">
          <h4 style="font-size: 17px; margin-bottom: 8px;">Can I manage my catalogue from a mobile phone?</h4>
          <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6;">
            Yes. The merchant admin dashboard is designed mobile-first. You can snap product photos with your phone camera, upload them directly, change prices, and mark items out of stock on the go.
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/templates/footer.php'; ?>
