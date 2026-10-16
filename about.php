<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/icons.php';

$activeNav = 'about';
$pageTitle = 'About Us — ' . APP_NAME;
$metaDescription = 'Learn why QuickCart Studio was built to bring accessible, high-converting digital storefronts to boutique merchants and social commerce sellers.';

include __DIR__ . '/templates/header.php';
?>

<section style="padding: 60px 0 40px; border-bottom: 1px solid var(--border-light); background-color: var(--bg-surface);">
  <div class="container" style="max-width: 760px; text-align: center; margin: 0 auto;">
    <span class="eyebrow">OUR MISSION</span>
    <h1 class="heading-lg" style="margin: 12px 0 16px;">Direct Commerce for Independent Sellers</h1>
    <p class="subheading" style="margin: 0 auto;">
      We bridge the gap between social media browsing and real sales by removing bloated checkouts and putting customer conversation first.
    </p>
  </div>
</section>

<section style="padding: 70px 0;">
  <div class="container container-sm">
    <div style="font-size: 16px; line-height: 1.8; color: var(--text-main); display: flex; flex-direction: column; gap: 24px;">
      <p>
        Across Instagram, TikTok, and Facebook, millions of small businesses showcase incredible fashion, handmade accessories, electronics, and beauty products every day. Yet when customers want to buy, the experience breaks down.
      </p>

      <p>
        Enterprise e-commerce platforms force small merchants into exorbitant monthly subscriptions, complex inventory plugins, and lengthy checkout screens that demand account passwords and credit card forms. For social commerce shoppers, this friction causes massive cart abandonment.
      </p>

      <div class="card" style="padding: 28px; background-color: var(--bg-surface); border-left: 4px solid var(--accent); margin: 12px 0;">
        <h3 style="font-size: 19px; margin-bottom: 8px;">The WhatsApp Advantage</h3>
        <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6; margin: 0;">
          WhatsApp is the primary communication and payment channel in thousands of growing markets. QuickCart Studio gives merchants a sleek, professional digital storefront that turns product discovery into immediate, pre-filled WhatsApp order conversations.
        </p>
      </div>

      <h2 style="font-size: 24px; margin-top: 16px;">Who We Serve</h2>
      <ul style="list-style: none; display: flex; flex-direction: column; gap: 12px; font-size: 15px;">
        <li style="display: flex; align-items: center; gap: 10px;">
          <span style="color: var(--accent);"><?= icon('check', 'icon-sm') ?></span>
          <span><strong>Clothing & Boutique Stores:</strong> Showcase daily arrivals with instant order buttons.</span>
        </li>
        <li style="display: flex; align-items: center; gap: 10px;">
          <span style="color: var(--accent);"><?= icon('check', 'icon-sm') ?></span>
          <span><strong>Shoe & Accessory Designers:</strong> Highlight curated items with clear currency pricing.</span>
        </li>
        <li style="display: flex; align-items: center; gap: 10px;">
          <span style="color: var(--accent);"><?= icon('check', 'icon-sm') ?></span>
          <span><strong>Beauty, Skincare & Cosmetics:</strong> Let clients reorder favorites in 1 click.</span>
        </li>
        <li style="display: flex; align-items: center; gap: 10px;">
          <span style="color: var(--accent);"><?= icon('check', 'icon-sm') ?></span>
          <span><strong>Electronics & Gadget Retailers:</strong> Provide real-time stock levels and fast dispatch.</span>
        </li>
      </ul>

      <div style="margin-top: 24px; text-align: center;">
        <a href="<?= base_url('contact.php') ?>" class="btn btn-primary btn-lg">
          Talk to Our Shop Launch Team
        </a>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/templates/footer.php'; ?>
