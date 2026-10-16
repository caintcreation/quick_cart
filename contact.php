<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/icons.php';

$activeNav = 'contact';
$pageTitle = 'Contact Us — ' . APP_NAME;
$metaDescription = 'Send an inquiry to launch your custom WhatsApp storefront or discuss custom managed requirements.';

$selectedPlan = trim($_GET['plan'] ?? '');
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    
    // Honeypot check for spam protection
    $botCheck = trim($_POST['website_url_honeypot'] ?? '');
    if ($botCheck !== '') {
        // Silently exit spam bots
        redirect(base_url('contact.php?submitted=1'));
    }

    $name = trim((string)($_POST['name'] ?? ''));
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $phone = trim((string)($_POST['phone'] ?? ''));
    $businessName = trim((string)($_POST['business_name'] ?? ''));
    $planInterest = trim((string)($_POST['plan_interest'] ?? ''));
    $message = trim((string)($_POST['message'] ?? ''));

    if ($name === '' || mb_strlen($name) > 120) {
        $error = 'Please enter your full name (up to 120 characters).';
    } elseif (!$email || mb_strlen((string)$email) > 190) {
        $error = 'Please provide a valid business email address.';
    } elseif ($message === '' || mb_strlen($message) > 3000) {
        $error = 'Please include a message describing your shop needs (up to 3000 characters).';
    } else {
        $stmt = db()->prepare('INSERT INTO service_enquiries (name, email, phone, business_name, plan_interest, message) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$name, $email, $phone ?: null, $businessName ?: null, $planInterest ?: null, $message]);
        
        log_audit('new_enquiry', "Contact enquiry from {$name} ({$email})");
        $success = true;
    }
}

include __DIR__ . '/templates/header.php';
?>

<section style="padding: 60px 0 40px; border-bottom: 1px solid var(--border-light); background-color: var(--bg-surface);">
  <div class="container text-center" style="max-width: 760px; text-align: center; margin: 0 auto;">
    <span class="eyebrow">GET STARTED TODAY</span>
    <h1 class="heading-lg" style="margin: 12px 0 16px;">Let's Launch Your Storefront</h1>
    <p class="subheading" style="margin: 0 auto;">
      Have questions about our setup packages, catalogue migration, or WhatsApp integration? Send us a message below or chat with our team directly.
    </p>
  </div>
</section>

<section style="padding: 70px 0;">
  <div class="container" style="max-width: 1040px; display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 48px;">
    <!-- Contact Form -->
    <div class="card" style="padding: 36px;">
      <?php if ($success): ?>
        <div class="alert alert-success">
          <?= icon('check', 'icon') ?>
          <div>
            <strong>Message Received!</strong> Thank you for contacting QuickCart Studio. Our onboarding team has logged your inquiry and will reach out to your email shortly.
          </div>
        </div>
      <?php else: ?>
        <?php if ($error): ?>
          <div class="alert alert-danger">
            <?= icon('x', 'icon') ?>
            <span><?= e($error) ?></span>
          </div>
        <?php endif; ?>

        <form method="post" action="<?= base_url('contact.php') ?>">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <!-- Honeypot anti-spam -->
          <div style="display: none;" aria-hidden="true">
            <input type="text" name="website_url_honeypot" tabindex="-1" autocomplete="off">
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label" for="name">Your Name *</label>
              <input class="form-control" type="text" id="name" name="name" required maxlength="120" value="<?= e($_POST['name'] ?? '') ?>" placeholder="e.g. Jane Doe">
            </div>

            <div class="form-group">
              <label class="form-label" for="email">Business Email *</label>
              <input class="form-control" type="email" id="email" name="email" required maxlength="190" value="<?= e($_POST['email'] ?? '') ?>" placeholder="jane@boutique.com">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label" for="phone">WhatsApp or Phone Number</label>
              <input class="form-control" type="tel" id="phone" name="phone" maxlength="40" value="<?= e($_POST['phone'] ?? '') ?>" placeholder="+254 700 000 000">
            </div>

            <div class="form-group">
              <label class="form-label" for="business_name">Shop / Business Name</label>
              <input class="form-control" type="text" id="business_name" name="business_name" maxlength="140" value="<?= e($_POST['business_name'] ?? '') ?>" placeholder="e.g. Nairobi Chic Apparel">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="plan_interest">Interested Package</label>
            <select class="form-control" id="plan_interest" name="plan_interest">
              <option value="General Inquiry">General Inquiry / Custom Setup</option>
              <option value="Basic Catalogue ($49)" <?= ($selectedPlan === 'basic-catalogue') ? 'selected' : '' ?>>Basic Catalogue ($49 one-time)</option>
              <option value="Business Shop ($149)" <?= ($selectedPlan === 'business-shop') ? 'selected' : '' ?>>Business Shop ($149 one-time)</option>
              <option value="Managed Shop ($299)" <?= ($selectedPlan === 'managed-shop') ? 'selected' : '' ?>>Managed Shop ($299 + $29/mo)</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="message">How can we help your business? *</label>
            <textarea class="form-control" id="message" name="message" required rows="4" placeholder="Tell us about the products you sell, your current ordering process, or any questions..."><?= e($_POST['message'] ?? '') ?></textarea>
          </div>

          <button class="btn btn-primary btn-lg" type="submit" style="width: 100%;">
            <?= icon('mail', 'icon') ?>
            <span>Submit Inquiry</span>
          </button>
        </form>
      <?php endif; ?>
    </div>

    <!-- Contact Info Cards -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
      <div class="card" style="padding: 28px;">
        <div style="width: 40px; height: 40px; border-radius: 10px; background-color: #25d36620; color: #25D366; display: flex; align-items: center; justify-content: center; margin-bottom: 14px;">
          <?= icon('whatsapp', 'icon') ?>
        </div>
        <h3 style="font-size: 18px; margin-bottom: 8px;">Direct WhatsApp Consultation</h3>
        <p style="color: var(--text-muted); font-size: 14px; line-height: 1.5; margin-bottom: 16px;">
          Want an immediate response? Chat with our shop architects directly on WhatsApp.
        </p>
        <a href="https://wa.me/<?= e(APP_SUPPORT_WHATSAPP) ?>?text=<?= rawurlencode('Hello QuickCart Studio, I would like to enquire about launching an online shop.') ?>" target="_blank" class="btn btn-whatsapp" style="width: 100%;">
          <?= icon('whatsapp', 'icon-sm') ?>
          <span>Chat on WhatsApp</span>
        </a>
      </div>

      <div class="card" style="padding: 28px;">
        <div style="width: 40px; height: 40px; border-radius: 10px; background-color: var(--color-info-bg); color: var(--color-info); display: flex; align-items: center; justify-content: center; margin-bottom: 14px;">
          <?= icon('mail', 'icon') ?>
        </div>
        <h3 style="font-size: 18px; margin-bottom: 8px;">Email Operations</h3>
        <p style="color: var(--text-muted); font-size: 14px; line-height: 1.5; margin-bottom: 12px;">
          For invoice requests, custom contract terms, or merchant onboarding assistance:
        </p>
        <a href="mailto:<?= e(APP_SUPPORT_EMAIL) ?>" style="font-size: 14px; font-weight: 700; color: var(--accent);">
          <?= e(APP_SUPPORT_EMAIL) ?>
        </a>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/templates/footer.php'; ?>
