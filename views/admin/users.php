<?php
$adminPage  = 'users';
$pageIcon   = 'fa-users';
$pendingCount = BookingModel::pendingCount();
include VIEWS_PATH . '/layout/admin-header.php';
?>

<div class="table-card">
  <div class="table-head">
    <div>
      <h3><i class="fa-solid fa-users"></i> All Users</h3>
      <p><?= count($users) ?> registered user<?= count($users) !== 1 ? 's' : '' ?></p>
    </div>
  </div>
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Role</th>
          <th>Joined</th>
          <th>Bookings</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($users)): ?>
        <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--muted)">No users found</td></tr>
        <?php else: ?>
        <?php foreach ($users as $u): ?>
        <?php
        $bookingCount = (int) Database::queryScalar(
            'SELECT COUNT(*) FROM bookings WHERE user_id = ?', [$u['id']]
        );
        ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--gold),#9c6e1a);display:grid;place-items:center;font-family:'Cormorant Garamond',serif;font-size:1rem;font-weight:700;color:#0e0c09;flex-shrink:0">
                <?= strtoupper(substr($u['name'], 0, 1)) ?>
              </div>
              <div class="td-name"><?= e($u['name']) ?></div>
            </div>
          </td>
          <td><?= e($u['email']) ?></td>
          <td><?= e($u['phone'] ?? '—') ?></td>
          <td>
            <?php if ($u['role'] === 'admin'): ?>
            <span class="badge badge-gold"><i class="fa-solid fa-crown"></i> Admin</span>
            <?php else: ?>
            <span class="badge badge-pending">User</span>
            <?php endif; ?>
          </td>
          <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
          <td>
            <?php if ($bookingCount > 0): ?>
            <span class="badge badge-confirmed"><?= $bookingCount ?> booking<?= $bookingCount !== 1 ? 's' : '' ?></span>
            <?php else: ?>
            <span style="color:var(--muted)">None</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include VIEWS_PATH . '/layout/admin-footer.php'; ?>
