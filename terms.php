<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/icons.php';

$activeNav = '';
$pageTitle = 'Terms of Service — ' . APP_NAME;
include __DIR__ . '/templates/header.php';
?>
<section style="padding: 60px 0;">
  <div class="container container-sm">
    <h1 class="heading-lg" style="margin-bottom: 24px;">Terms of Service</h1>
    <div style="font-size: 15px; line-height: 1.8; color: var(--text-main); display: flex; flex-direction: column; gap: 20px;">
      <p>Last updated: <?= date('F j, Y') ?></p>
      <p>Welcome to QuickCart Studio. By using our website, services, and storefront builder software, you agree to comply with and be bound by the following terms.</p>
      
      <h2 style="font-size: 20px; margin-top: 10px;">1. Service Description</h2>
      <p>QuickCart Studio provides an e-commerce catalogue builder and order orchestration software that connects customers with merchants over WhatsApp. We facilitate product discovery and structured order generation; we do not act as an escrow agent, payment processor, or retail reseller of merchant merchandise.</p>
      
      <h2 style="font-size: 20px; margin-top: 10px;">2. Merchant Responsibilities</h2>
      <p>Merchants are solely responsible for the authenticity, legality, pricing, quality, and fulfillment of the goods listed in their catalogues. Merchants must not list prohibited, illicit, or counterfeit goods.</p>
      
      <h2 style="font-size: 20px; margin-top: 10px;">3. Order Fulfillment & Payments</h2>
      <p>Generating a WhatsApp message or submitting an order on a QuickCart storefront does not guarantee order acceptance or payment completion. Merchants and customers arrange payment and delivery terms directly.</p>

      <h2 style="font-size: 20px; margin-top: 10px;">4. Service Fees</h2>
      <p>Shop setup fees and agreed ongoing maintenance packages are billed per the chosen pricing package. Setup fees are non-refundable once the shop environment and initial catalogue setup have commenced.</p>
    </div>
  </div>
</section>
<?php include __DIR__ . '/templates/footer.php'; ?>
