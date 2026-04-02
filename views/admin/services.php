<?php
$adminPage  = 'services';
$pageIcon   = 'fa-compass';
$pendingCount = BookingModel::pendingCount();
include VIEWS_PATH . '/layout/admin-header.php';
?>

<div class="table-card">
  <div class="table-head">
    <div>
      <h3><i class="fa-solid fa-compass"></i> All Experiences</h3>
      <p><?= count($services) ?> experience<?= count($services) !== 1 ? 's' : '' ?></p>
    </div>
    <a href="<?= url('admin/services/add') ?>" class="btn btn-primary btn-sm">
      <i class="fa-solid fa-plus"></i> Add New Experience
    </a>
  </div>

  <!-- Filters -->
  <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
    <form method="GET" action="<?= url('admin/services') ?>" class="admin-filter">
      <input type="text" name="search" class="admin-search"
             value="<?= e($search ?? '') ?>" placeholder="Search experiences…">
      <select name="category" class="admin-select" onchange="this.form.submit()">
        <option value="">All categories</option>
        <?php foreach ($categories as $cat): ?>
        <option value="<?= e($cat) ?>" <?= ($category ?? '') === $cat ? 'selected' : '' ?>>
          <?= e($cat) ?>
        </option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-ghost btn-sm"><i class="fa-solid fa-magnifying-glass"></i></button>
      <?php if ($search || $category): ?>
      <a href="<?= url('admin/services') ?>" class="btn btn-ghost btn-sm">
        <i class="fa-solid fa-xmark"></i> Clear
      </a>
      <?php endif; ?>
    </form>
  </div>

  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Experience</th>
          <th>Category</th>
          <th>Price</th>
          <th>Duration</th>
          <th>Rating</th>
          <th>Status</th>
          <th>Order</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($services)): ?>
        <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--muted)">No experiences found</td></tr>
        <?php else: ?>
        <?php foreach ($services as $s): ?>
        <tr>
          <td>
            <div class="td-img-wrap">
              <img src="<?= e($s['image'] ?? '') ?>" class="td-img"
                   onerror="this.style.display='none'" alt="">
              <div>
                <div class="td-name"><?= e($s['title']) ?></div>
                <?php if ($s['badge']): ?>
                <span class="badge badge-gold" style="font-size:.7rem"><?= e($s['badge']) ?></span>
                <?php endif; ?>
              </div>
            </div>
          </td>
          <td><?= e($s['category']) ?></td>
          <td>$<?= number_format($s['price'], 0) ?></td>
          <td><?= e($s['duration'] ?? '—') ?></td>
          <td>
            <?php if ($s['rating']): ?>
            <i class="fa-solid fa-star" style="color:var(--gold)"></i>
            <?= number_format($s['rating'], 1) ?>
            <span style="color:var(--muted);font-size:.8rem">(<?= (int)$s['reviews'] ?>)</span>
            <?php else: ?>
            —
            <?php endif; ?>
          </td>
          <td>
            <span class="badge <?= $s['is_active'] ? 'badge-confirmed' : 'badge-cancelled' ?>">
              <?= $s['is_active'] ? 'Active' : 'Inactive' ?>
            </span>
          </td>
          <td><?= (int)$s['sort_order'] ?></td>
          <td class="actions">
            <a href="<?= url('admin/services/' . $s['id'] . '/edit') ?>"
               class="btn btn-ghost btn-sm" title="Edit">
              <i class="fa-solid fa-pen"></i>
            </a>
            <a href="<?= url('services/' . $s['id']) ?>" target="_blank"
               class="btn btn-ghost btn-sm" title="View on site">
              <i class="fa-solid fa-eye"></i>
            </a>
            <form method="POST" action="<?= url('admin/services/' . $s['id'] . '/delete') ?>"
                  onsubmit="return confirm('Delete \'<?= e(addslashes($s['title'])) ?>\'? This cannot be undone.')"
                  style="display:inline">
              <input type="hidden" name="csrf_token" value="<?= e(CSRF::token()) ?>">
              <button type="submit" class="btn btn-danger btn-sm" title="Delete">
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

<?php include VIEWS_PATH . '/layout/admin-footer.php'; ?>
