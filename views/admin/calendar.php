<?php
$adminPage  = 'calendar';
$pageIcon   = 'fa-calendar-xmark';
$pendingCount = BookingModel::pendingCount();

// Build calendar data
$firstDay    = new DateTime($from);
$numDays     = (int)date('t', strtotime($from));
$startDow    = (int)$firstDay->format('N'); // 1=Mon … 7=Sun
$monthName   = $firstDay->format('F Y');

// Prev / next month navigation
$prevMonth = (clone $firstDay)->modify('-1 month');
$nextMonth = (clone $firstDay)->modify('+1 month');

include VIEWS_PATH . '/layout/admin-header.php';
?>

<div style="display:grid;grid-template-columns:1fr 320px;gap:22px;align-items:start">

  <!-- CALENDAR -->
  <div class="table-card">
    <div class="table-head">
      <div>
        <h3><i class="fa-solid fa-calendar-xmark"></i> Off-Days Calendar</h3>
        <p>Click any date to block or unblock it for bookings</p>
      </div>
      <div style="display:flex;align-items:center;gap:8px">
        <a href="<?= url('admin/calendar?year=' . $prevMonth->format('Y') . '&month=' . (int)$prevMonth->format('n')) ?>"
           class="btn btn-ghost btn-sm"><i class="fa-solid fa-chevron-left"></i></a>
        <strong style="font-size:.95rem;min-width:120px;text-align:center"><?= $monthName ?></strong>
        <a href="<?= url('admin/calendar?year=' . $nextMonth->format('Y') . '&month=' . (int)$nextMonth->format('n')) ?>"
           class="btn btn-ghost btn-sm"><i class="fa-solid fa-chevron-right"></i></a>
        <a href="<?= url('admin/calendar') ?>" class="btn btn-ghost btn-sm">Today</a>
      </div>
    </div>

    <div style="padding:20px">
      <!-- Legend -->
      <div style="display:flex;gap:16px;margin-bottom:16px;flex-wrap:wrap;font-size:.8rem">
        <span><span style="display:inline-block;width:14px;height:14px;background:var(--card);border:1.5px solid var(--border);border-radius:4px;vertical-align:middle;margin-right:4px"></span> Available</span>
        <span><span style="display:inline-block;width:14px;height:14px;background:#fee2e2;border:1.5px solid #fca5a5;border-radius:4px;vertical-align:middle;margin-right:4px"></span> Blocked</span>
        <span><span style="display:inline-block;width:14px;height:14px;background:rgba(184,132,42,.15);border:1.5px solid rgba(184,132,42,.4);border-radius:4px;vertical-align:middle;margin-right:4px"></span> Today</span>
      </div>

      <!-- Day headers -->
      <div class="cal-grid">
        <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $d): ?>
        <div class="cal-dow"><?= $d ?></div>
        <?php endforeach; ?>

        <!-- Empty cells before first day -->
        <?php for ($i = 1; $i < $startDow; $i++): ?>
        <div class="cal-day cal-empty"></div>
        <?php endfor; ?>

        <!-- Day cells -->
        <?php for ($day = 1; $day <= $numDays; $day++):
          $dateStr  = sprintf('%04d-%02d-%02d', $year, $month, $day);
          $isToday  = $dateStr === date('Y-m-d');
          $isPast   = $dateStr < date('Y-m-d');
          $isBlocked = in_array($dateStr, $blockedList);
          $reason   = $blockedMap[$dateStr] ?? '';
        ?>
        <div class="cal-day <?= $isToday ? 'cal-today' : '' ?> <?= $isBlocked ? 'cal-blocked' : '' ?> <?= $isPast ? 'cal-past' : '' ?>"
             data-date="<?= $dateStr ?>"
             data-blocked="<?= $isBlocked ? '1' : '0' ?>"
             data-reason="<?= e($reason) ?>"
             <?= !$isPast ? 'onclick="calDayClick(this)"' : '' ?>
             title="<?= $isBlocked ? 'Blocked' . ($reason ? ': ' . e($reason) : '') : ($isPast ? 'Past date' : 'Click to block') ?>">
          <span class="cal-day-num"><?= $day ?></span>
          <?php if ($isBlocked): ?>
          <span class="cal-blocked-icon"><i class="fa-solid fa-ban"></i></span>
          <?php endif; ?>
        </div>
        <?php endfor; ?>
      </div>
    </div>
  </div>

  <!-- SIDEBAR: Add Block Form + Blocked List -->
  <div style="display:flex;flex-direction:column;gap:16px">

    <!-- Block Date Form -->
    <div class="form-panel">
      <div class="form-panel-head">
        <h3><i class="fa-solid fa-ban"></i> Block Date(s)</h3>
        <p>Prevent client bookings on specific dates</p>
      </div>
      <form method="POST" action="<?= url('admin/calendar/block') ?>" id="blockForm">
        <input type="hidden" name="csrf_token" value="<?= e(CSRF::token()) ?>">
        <div class="field" style="margin-bottom:12px">
          <label><i class="fa-solid fa-calendar"></i> Date</label>
          <input type="date" name="date" id="blockDateInput"
                 min="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="field" style="margin-bottom:16px">
          <label><i class="fa-solid fa-comment"></i> Reason (optional)</label>
          <input type="text" name="reason" id="blockReasonInput"
                 placeholder="e.g. Public holiday, Personal day">
        </div>
        <button type="submit" class="btn btn-danger btn-full">
          <i class="fa-solid fa-ban"></i> Block This Date
        </button>
      </form>
    </div>

    <!-- Blocked Dates List -->
    <div class="table-card">
      <div class="table-head">
        <div>
          <h3><i class="fa-solid fa-list"></i> Blocked Dates</h3>
          <p><?= count($blockedDates) ?> date<?= count($blockedDates) !== 1 ? 's' : '' ?> blocked this month</p>
        </div>
      </div>
      <?php if (empty($blockedDates)): ?>
      <div style="padding:24px;text-align:center;color:var(--muted);font-size:.88rem">
        <i class="fa-solid fa-calendar-check" style="font-size:1.5rem;margin-bottom:8px;display:block;color:var(--success)"></i>
        All dates available this month
      </div>
      <?php else: ?>
      <div style="max-height:300px;overflow-y:auto">
        <?php foreach ($blockedDates as $bd): ?>
        <div style="display:flex;align-items:center;gap:10px;padding:10px 16px;border-bottom:1px solid var(--border)">
          <div style="flex:1">
            <div style="font-weight:600;font-size:.87rem"><?= date('D, d M Y', strtotime($bd['blocked_date'])) ?></div>
            <?php if ($bd['reason']): ?>
            <div style="font-size:.78rem;color:var(--muted)"><?= e($bd['reason']) ?></div>
            <?php endif; ?>
          </div>
          <form method="POST" action="<?= url('admin/calendar/unblock') ?>"
                onsubmit="return confirm('Unblock this date?')">
            <input type="hidden" name="csrf_token" value="<?= e(CSRF::token()) ?>">
            <input type="hidden" name="date" value="<?= e($bd['blocked_date']) ?>">
            <button type="submit" class="btn btn-ghost btn-sm" title="Unblock">
              <i class="fa-solid fa-unlock"></i>
            </button>
          </form>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<script>
// Calendar day click: open block/unblock form
function calDayClick(el) {
  const date     = el.dataset.date;
  const isBlocked = el.dataset.blocked === '1';
  const reason   = el.dataset.reason || '';

  if (isBlocked) {
    if (!confirm('Unblock ' + date + '?')) return;
    // Submit unblock form via fetch
    const token = document.querySelector('meta[name="csrf-token"]').content;
    fetch('<?= url('admin/calendar/unblock') ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': token },
      body: 'csrf_token=' + encodeURIComponent(token) + '&date=' + encodeURIComponent(date)
    }).then(r => { if (r.ok) location.reload(); });
  } else {
    document.getElementById('blockDateInput').value = date;
    document.getElementById('blockReasonInput').value = reason;
    document.getElementById('blockReasonInput').focus();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
}
</script>

<?php include VIEWS_PATH . '/layout/admin-footer.php'; ?>
