<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/icons.php';
require_platform_owner();

// Handle Status Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    verify_csrf();
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $status = trim((string)($_POST['status'] ?? 'new'));

    if ($id && in_array($status, ['new', 'contacted', 'closed'], true)) {
        $upd = db()->prepare('UPDATE service_enquiries SET status = ? WHERE id = ?');
        $upd->execute([$status, $id]);
        log_audit('enquiry_status_update', "Updated enquiry #{$id} status to {$status}");
        set_flash('success', 'Inquiry status updated.');
    }
    redirect(base_url('admin/platform_enquiries.php'));
}

$enquiries = db()->query("SELECT * FROM service_enquiries ORDER BY id DESC")->fetchAll();

$adminNav = 'platform_enquiries';
$pageTitle = 'Customer Inquiries — ' . APP_NAME;
include __DIR__ . '/../templates/admin_header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px;">
  <div>
    <span class="eyebrow">LEAD MANAGEMENT</span>
    <h1 style="font-size: 26px;">Customer Inquiries (<?= count($enquiries) ?>)</h1>
    <p style="color: var(--text-muted); font-size: 14px;">Messages and shop setup inquiries received from the public website contact form.</p>
  </div>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
  <?php if (empty($enquiries)): ?>
    <div class="empty-state">
      <div class="empty-state-icon"><?= icon('mail', 'icon-lg') ?></div>
      <div class="empty-state-title">No inquiries received yet</div>
      <div class="empty-state-desc">Submissions from your public contact page will arrive here in real-time.</div>
    </div>
  <?php else: ?>
    <div class="table-responsive" style="border: none;">
      <table class="data-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Name & Contact</th>
            <th>Shop / Business</th>
            <th>Package</th>
            <th>Message</th>
            <th>Status</th>
            <th style="text-align: right;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($enquiries as $eq): 
            $statusBadges = [
                'new' => 'badge-primary',
                'contacted' => 'badge-warning',
                'closed' => 'badge-neutral'
            ];
          ?>
            <tr>
              <td><?= date('M j, Y', strtotime($eq['created_at'])) ?></td>
              <td>
                <strong><?= e($eq['name']) ?></strong>
                <div style="font-size: 12px; color: var(--text-muted);"><?= e($eq['email']) ?></div>
                <?php if (!empty($eq['phone'])): ?>
                  <div style="font-size: 11px; color: var(--accent);">
                    <a href="https://wa.me/<?= preg_replace('/\D+/', '', $eq['phone']) ?>" target="_blank" style="text-decoration: underline;">
                      <?= e($eq['phone']) ?>
                    </a>
                  </div>
                <?php endif; ?>
              </td>
              <td><?= e($eq['business_name'] ?: 'Not specified') ?></td>
              <td><span class="badge badge-neutral"><?= e($eq['plan_interest'] ?: 'General') ?></span></td>
              <td style="max-width: 280px; font-size: 13px; line-height: 1.5;">
                <?= nl2br(e($eq['message'])) ?>
              </td>
              <td>
                <span class="badge <?= $statusBadges[$eq['status']] ?? 'badge-neutral' ?>">
                  <?= ucfirst(e($eq['status'])) ?>
                </span>
              </td>
              <td style="text-align: right;">
                <form method="post" action="<?= base_url('admin/platform_enquiries.php') ?>" style="display: inline-flex; align-items: center; gap: 6px;">
                  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="update_status">
                  <input type="hidden" name="id" value="<?= (int)$eq['id'] ?>">
                  <select name="status" class="form-control" style="padding: 4px 8px; font-size: 12px; width: auto;" onchange="this.form.submit()">
                    <option value="new" <?= $eq['status'] === 'new' ? 'selected' : '' ?>>New</option>
                    <option value="contacted" <?= $eq['status'] === 'contacted' ? 'selected' : '' ?>>Contacted</option>
                    <option value="closed" <?= $eq['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                  </select>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../templates/admin_footer.php'; ?>
