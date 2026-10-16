<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/icons.php';

$activeNav = '';
$pageTitle = 'Privacy Policy — ' . APP_NAME;
include __DIR__ . '/templates/header.php';
?>
<section style="padding: 60px 0;">
  <div class="container container-sm">
    <h1 class="heading-lg" style="margin-bottom: 24px;">Privacy Policy</h1>
    <div style="font-size: 15px; line-height: 1.8; color: var(--text-main); display: flex; flex-direction: column; gap: 20px;">
      <p>Last updated: <?= date('F j, Y') ?></p>
      <p>QuickCart Studio ("we", "our", or "platform") respects the privacy of our merchants and their storefront shoppers. This Privacy Policy outlines what information is collected, how it is used, and how it is secured.</p>
      
      <h2 style="font-size: 20px; margin-top: 10px;">1. Information Collected from Merchants</h2>
      <p>When creating a shop administrator account, we collect your business email, password hash, shop name, and official WhatsApp business number. Passwords are cryptographically salted and hashed using modern bcrypt/Argon2 algorithms; plain text passwords are never stored.</p>
      
      <h2 style="font-size: 20px; margin-top: 10px;">2. Information Collected from Storefront Shoppers</h2>
      <p>When a customer submits an order on a published merchant shop, our system records the requested items, total price, and generated order reference number. Customer contact numbers and names are processed solely to generate the WhatsApp conversation and verify inventory. We do not store or process payment card details.</p>
      
      <h2 style="font-size: 20px; margin-top: 10px;">3. WhatsApp and Third-Party Services</h2>
      <p>When submitting an order, the customer is handed off to WhatsApp (owned by Meta Platforms, Inc.) via a pre-filled direct URL. Interactions on WhatsApp are governed by WhatsApp's privacy policy and terms.</p>

      <h2 style="font-size: 20px; margin-top: 10px;">4. Data Security</h2>
      <p>All database transactions utilize prepared PDO queries with strict tenant isolation to prevent unauthorized cross-tenant data access. Uploaded assets are verified using binary MIME analysis and stored in non-executable directories.</p>
    </div>
  </div>
</section>
<?php include __DIR__ . '/templates/footer.php'; ?>
