<?php
// templates/footer.php
?>
<footer style="background-color: var(--bg-surface); border-top: 1px solid var(--border-light); padding: 60px 0 32px; margin-top: auto;">
  <div class="container">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 40px; margin-bottom: 48px;">
      <div>
        <a class="brand-logo" href="<?= base_url('home.php') ?>" style="margin-bottom: 16px;">
          <div class="brand-mark"><?= icon('cart', 'icon') ?></div>
          <span>QuickCart<span style="color:var(--accent);">.</span></span>
        </a>
        <p style="color: var(--text-muted); font-size: 14px; max-width: 300px; line-height: 1.6; margin-bottom: 20px;">
          Empowering small retailers, fashion boutiques, and social commerce merchants with frictionless WhatsApp ordering.
        </p>
        <div style="display: flex; gap: 10px;">
          <a class="btn btn-icon" href="https://wa.me/<?= e(APP_SUPPORT_WHATSAPP) ?>" target="_blank" aria-label="Support WhatsApp">
            <?= icon('whatsapp', 'icon-sm') ?>
          </a>
          <a class="btn btn-icon" href="mailto:<?= e(APP_SUPPORT_EMAIL) ?>" aria-label="Support Email">
            <?= icon('mail', 'icon-sm') ?>
          </a>
        </div>
      </div>

      <div>
        <h4 style="font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 16px;">Navigation</h4>
        <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px; font-size: 14px; color: var(--text-muted);">
          <li><a href="<?= base_url('home.php') ?>" style="transition: color var(--transition-fast);">Home</a></li>
          <li><a href="<?= base_url('features.php') ?>">Features & Capabilities</a></li>
          <li><a href="<?= base_url('pricing.php') ?>">Transparent Pricing</a></li>
          <li><a href="<?= base_url('about.php') ?>">About the Platform</a></li>
          <li><a href="<?= base_url('contact.php') ?>">Contact Merchant Ops</a></li>
        </ul>
      </div>

      <div>
        <h4 style="font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 16px;">Live Demonstrations</h4>
        <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px; font-size: 14px; color: var(--text-muted);">
          <li><a href="<?= base_url('index.php?shop=demo') ?>" target="_blank">Demo Storefront <?= icon('external', 'icon-sm') ?></a></li>
          <li><a href="<?= base_url('admin/login.php') ?>">Merchant Admin Login</a></li>
          <li><a href="<?= base_url('pricing.php#packages') ?>">View Package Inclusions</a></li>
        </ul>
      </div>

      <div>
        <h4 style="font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 16px;">Legal & Security</h4>
        <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px; font-size: 14px; color: var(--text-muted);">
          <li><a href="<?= base_url('privacy.php') ?>">Privacy Policy</a></li>
          <li><a href="<?= base_url('terms.php') ?>">Terms of Service</a></li>
          <li><span style="font-size: 13px; color: var(--text-subtle);">Direct server isolation & CSRF secure</span></li>
        </ul>
      </div>
    </div>

    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px; padding-top: 24px; border-top: 1px solid var(--border-light); font-size: 13px; color: var(--text-muted);">
      <div>
        © <?= date('Y') ?> <?= e(APP_NAME) ?>. All rights reserved. Built for independent merchants worldwide.
      </div>
      <div>
        Runs with clean native PHP & MySQL. Local development powered by XAMPP.
      </div>
    </div>
  </div>
</footer>

<script src="<?= base_url('assets/app.js') ?>"></script>
</body>
</html>
