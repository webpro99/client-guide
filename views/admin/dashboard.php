<?php
$adminPage  = 'dashboard';
$pageIcon   = 'fa-gauge';
$pendingCount = $kpis['pending'] ?? 0;
include VIEWS_PATH . '/layout/admin-header.php';
?>

<!-- KPI CARDS -->
<div class="kpi-grid">
  <div class="kpi" style="--kpi-c:#b8842a;--kpi-bg:rgba(184,132,42,.12)">
    <div class="kpi-icon"><i class="fa-solid fa-compass"></i></div>
    <div class="kpi-val"><?= (int)($kpis['total_services'] ?? 0) ?></div>
    <div class="kpi-label">Total Experiences</div>
  </div>
  <div class="kpi" style="--kpi-c:#2563eb;--kpi-bg:rgba(37,99,235,.1)">
    <div class="kpi-icon"><i class="fa-solid fa-calendar-check"></i></div>
    <div class="kpi-val"><?= (int)($kpis['total_bookings'] ?? 0) ?></div>
    <div class="kpi-label">Total Bookings</div>
  </div>
  <div class="kpi" style="--kpi-c:#d97706;--kpi-bg:rgba(217,119,6,.1)">
    <div class="kpi-icon"><i class="fa-solid fa-clock"></i></div>
    <div class="kpi-val"><?= (int)($kpis['pending'] ?? 0) ?></div>
    <div class="kpi-label">Pending Bookings</div>
  </div>
  <div class="kpi" style="--kpi-c:#16a34a;--kpi-bg:rgba(22,163,74,.1)">
    <div class="kpi-icon"><i class="fa-solid fa-dollar-sign"></i></div>
    <div class="kpi-val">$<?= number_format($kpis['revenue'] ?? 0, 0) ?></div>
    <div class="kpi-label">Total Revenue (Paid)</div>
  </div>
</div>

<!-- RECENT BOOKINGS -->
<div class="table-card">
  <div class="table-head">
    <div>
      <h3><i class="fa-solid fa-clock-rotate-left"></i> Recent Bookings</h3>
      <p>Latest 5 booking requests</p>
    </div>
    <a href="<?= url('admin/bookings') ?>" class="btn btn-ghost btn-sm">
      View All <i class="fa-solid fa-arrow-right"></i>
    </a>
  </div>
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Reference</th>
          <th>Customer</th>
          <th>Experience</th>
          <th>Date</th>
          <th>Status</th>
          <th>Payment</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($recentBookings)): ?>
        <tr><td colspan="7" style="text-align:center;padding:32px;color:var(--muted)">No bookings yet</td></tr>
        <?php else: ?>
        <?php foreach ($recentBookings as $b): ?>
        <tr>
          <td><code style="font-size:.8rem;background:var(--bg);padding:3px 7px;border-radius:5px"><?= e($b['reference']) ?></code></td>
          <td>
            <div class="td-name"><?= e($b['customer_name']) ?></div>
            <div class="td-sub"><?= e($b['customer_email']) ?></div>
          </td>
          <td><?= e($b['service_title']) ?></td>
          <td><?= date('d M Y', strtotime($b['booking_date'])) ?></td>
          <td><span class="badge badge-<?= e($b['status']) ?>"><?= ucfirst(e($b['status'])) ?></span></td>
          <td>
            <?php $pc = $b['payment_status'] === 'paid' ? 'confirmed' : ($b['payment_status'] === 'refunded' ? 'cancelled' : 'pending'); ?>
            <span class="badge badge-<?= $pc ?>"><?= ucfirst(e($b['payment_status'])) ?></span>
          </td>
          <td><strong>$<?= number_format($b['total_price'], 2) ?></strong></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- QUICK SERVICES -->
<div class="table-card">
  <div class="table-head">
    <div>
      <h3><i class="fa-solid fa-compass"></i> Experiences</h3>
      <p>All active tour experiences</p>
    </div>
    <a href="<?= url('admin/services/add') ?>" class="btn btn-primary btn-sm">
      <i class="fa-solid fa-plus"></i> Add New
    </a>
  </div>
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Experience</th>
          <th>Category</th>
          <th>Price</th>
          <th>Rating</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (array_slice($services, 0, 8) as $s): ?>
        <tr>
          <td>
            <div class="td-img-wrap">
              <img src="<?= e($s['image'] ?? '') ?>" class="td-img"
                   onerror="this.style.display='none'" alt="">
              <div>
                <div class="td-name"><?= e($s['title']) ?></div>
                <?php if ($s['tagline']): ?>
                <div class="td-sub"><?= e(substr($s['tagline'], 0, 60)) ?>…</div>
                <?php endif; ?>
              </div>
            </div>
          </td>
          <td><?= e($s['category']) ?></td>
          <td>$<?= number_format($s['price'], 0) ?></td>
          <td>
            <?php if ($s['rating']): ?>
            <i class="fa-solid fa-star" style="color:var(--gold)"></i> <?= number_format($s['rating'], 1) ?>
            <span style="color:var(--muted);font-size:.8rem">(<?= (int)$s['reviews'] ?>)</span>
            <?php endif; ?>
          </td>
          <td><span class="badge <?= $s['is_active'] ? 'badge-confirmed' : 'badge-cancelled' ?>"><?= $s['is_active'] ? 'Active' : 'Inactive' ?></span></td>
          <td class="actions">
            <a href="<?= url('admin/services/' . $s['id'] . '/edit') ?>" class="btn btn-ghost btn-sm">
              <i class="fa-solid fa-pen"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include VIEWS_PATH . '/layout/admin-footer.php'; ?>
