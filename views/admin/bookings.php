<?php
$adminPage  = 'bookings';
$pageIcon   = 'fa-calendar-check';
$pendingCount = $pending ?? 0;
include VIEWS_PATH . '/layout/admin-header.php';
?>

<!-- STATS BAR -->
<div class="kpi-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px">
  <div class="kpi" style="--kpi-c:#2563eb;--kpi-bg:rgba(37,99,235,.1)">
    <div class="kpi-icon"><i class="fa-solid fa-calendar-check"></i></div>
    <div class="kpi-val"><?= (int)($total ?? 0) ?></div>
    <div class="kpi-label">Total Bookings</div>
  </div>
  <div class="kpi" style="--kpi-c:#d97706;--kpi-bg:rgba(217,119,6,.1)">
    <div class="kpi-icon"><i class="fa-solid fa-clock"></i></div>
    <div class="kpi-val"><?= (int)($pending ?? 0) ?></div>
    <div class="kpi-label">Pending</div>
  </div>
  <div class="kpi" style="--kpi-c:#16a34a;--kpi-bg:rgba(22,163,74,.1)">
    <div class="kpi-icon"><i class="fa-solid fa-dollar-sign"></i></div>
    <div class="kpi-val">$<?= number_format($revenue ?? 0, 0) ?></div>
    <div class="kpi-label">Revenue (Paid)</div>
  </div>
</div>

<!-- FILTERS -->
<div class="table-card">
  <div class="table-head">
    <div>
      <h3><i class="fa-solid fa-calendar-check"></i> All Bookings</h3>
      <p><?= count($bookings) ?> booking<?= count($bookings) !== 1 ? 's' : '' ?> found</p>
    </div>
  </div>
  <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
    <form method="GET" action="<?= url('admin/bookings') ?>" class="admin-filter">
      <input type="text" name="search" class="admin-search"
             value="<?= e($search ?? '') ?>"
             placeholder="Search by name, email, reference or experience…">
      <select name="status" class="admin-select" onchange="this.form.submit()">
        <option value="">All statuses</option>
        <option value="pending"   <?= ($status ?? '') === 'pending'    ? 'selected' : '' ?>>Pending</option>
        <option value="confirmed" <?= ($status ?? '') === 'confirmed'  ? 'selected' : '' ?>>Confirmed</option>
        <option value="cancelled" <?= ($status ?? '') === 'cancelled'  ? 'selected' : '' ?>>Cancelled</option>
      </select>
      <button type="submit" class="btn btn-ghost btn-sm"><i class="fa-solid fa-magnifying-glass"></i></button>
      <?php if ($search || $status): ?>
      <a href="<?= url('admin/bookings') ?>" class="btn btn-ghost btn-sm">
        <i class="fa-solid fa-xmark"></i> Clear
      </a>
      <?php endif; ?>
    </form>
  </div>
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Reference</th>
          <th>Customer</th>
          <th>Experience</th>
          <th>Tour Date</th>
          <th>Guests</th>
          <th>Total</th>
          <th>Status</th>
          <th>Payment</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($bookings)): ?>
        <tr><td colspan="9" style="text-align:center;padding:40px;color:var(--muted)">No bookings found</td></tr>
        <?php else: ?>
        <?php foreach ($bookings as $b): ?>
        <tr>
          <td><code style="font-size:.78rem;background:var(--bg);padding:3px 6px;border-radius:4px"><?= e($b['reference']) ?></code></td>
          <td>
            <div class="td-name"><?= e($b['customer_name']) ?></div>
            <div class="td-sub"><?= e($b['customer_email']) ?></div>
            <div class="td-sub"><?= e($b['customer_phone']) ?></div>
          </td>
          <td><?= e($b['service_title']) ?></td>
          <td><?= date('d M Y', strtotime($b['booking_date'])) ?></td>
          <td><?= (int)$b['people'] ?></td>
          <td><strong>$<?= number_format($b['total_price'], 2) ?></strong></td>
          <td>
            <span class="badge badge-<?= e($b['status']) ?>"><?= ucfirst(e($b['status'])) ?></span>
          </td>
          <td>
            <?php $pc = $b['payment_status'] === 'paid' ? 'confirmed' : ($b['payment_status'] === 'refunded' ? 'cancelled' : 'pending'); ?>
            <span class="badge badge-<?= $pc ?>"><?= ucfirst(e($b['payment_status'])) ?></span>
          </td>
          <td class="actions">
            <button class="btn btn-ghost btn-sm"
                    onclick="openStatusModal(<?= (int)$b['id'] ?>, '<?= e($b['status']) ?>', '<?= e(addslashes($b['admin_notes'] ?? '')) ?>')">
              <i class="fa-solid fa-pen"></i>
            </button>
            <form method="POST" action="<?= url('admin/bookings/' . $b['id'] . '/delete') ?>"
                  onsubmit="return confirm('Delete this booking?')" style="display:inline">
              <input type="hidden" name="csrf_token" value="<?= e(CSRF::token()) ?>">
              <button type="submit" class="btn btn-danger btn-sm">
                <i class="fa-solid fa-trash"></i>
              </button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- STATUS UPDATE MODAL -->
<div id="statusModal" style="display:none;position:fixed;inset:0;z-index:500;background:rgba(14,12,9,.7);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:20px">
  <div style="background:var(--card);border-radius:var(--r);width:min(480px,100%);padding:28px;box-shadow:var(--shadow-md)">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
      <h3 style="font-size:1rem;font-weight:700;display:flex;align-items:center;gap:8px">
        <i class="fa-solid fa-pen" style="color:var(--gold)"></i> Update Booking Status
      </h3>
      <button onclick="closeStatusModal()" style="background:var(--bg);border:0;width:32px;height:32px;border-radius:7px;cursor:pointer;font-size:.95rem;color:var(--muted)">×</button>
    </div>
    <form id="statusForm" method="POST">
      <input type="hidden" name="csrf_token" value="<?= e(CSRF::token()) ?>">
      <div class="field" style="margin-bottom:14px">
        <label style="font-size:.79rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.05em">
          <i class="fa-solid fa-circle-dot" style="color:var(--gold)"></i> Booking Status
        </label>
        <select name="status" id="statusSelect">
          <option value="pending">Pending</option>
          <option value="confirmed">Confirmed</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
      <div class="field" style="margin-bottom:20px">
        <label style="font-size:.79rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.05em">
          <i class="fa-solid fa-comment" style="color:var(--gold)"></i> Admin Notes (visible to client)
        </label>
        <textarea name="admin_notes" id="adminNotes" rows="3" placeholder="Add notes for the client…"></textarea>
      </div>
      <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary btn-sm">
          <i class="fa-solid fa-check"></i> Save Changes
        </button>
        <button type="button" onclick="closeStatusModal()" class="btn btn-ghost btn-sm">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function openStatusModal(id, status, notes) {
  const modal = document.getElementById('statusModal');
  document.getElementById('statusForm').action = '<?= url('admin/bookings/') ?>' + id + '/status';
  document.getElementById('statusSelect').value = status;
  document.getElementById('adminNotes').value = notes;
  modal.style.display = 'flex';
}
function closeStatusModal() {
  document.getElementById('statusModal').style.display = 'none';
}
document.getElementById('statusModal').addEventListener('click', function(e) {
  if (e.target === this) closeStatusModal();
});
</script>

<?php include VIEWS_PATH . '/layout/admin-footer.php'; ?>
