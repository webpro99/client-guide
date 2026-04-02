<?php partial('layout/header', ['pageTitle' => $pageTitle, 'settings' => $settings]) ?>

<div style="padding-top:68px;min-height:80vh">
  <div class="wrap" style="padding-top:48px;padding-bottom:80px">

    <div class="eyebrow">Your Journey</div>
    <h1 class="sec-title" style="margin-bottom:8px">My Bookings</h1>
    <p class="sec-desc" style="margin-bottom:36px">Track and manage all your Morocco experience bookings.</p>

    <?php if (empty($bookings)): ?>
    <div style="text-align:center;padding:60px 20px;background:var(--card);border-radius:var(--r);border:1px solid var(--bdr)">
      <div style="font-size:3.5rem;margin-bottom:16px">🧳</div>
      <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.7rem;margin-bottom:8px">No bookings yet</h3>
      <p style="color:var(--muted);line-height:1.8;margin-bottom:24px">
        Your adventure awaits. Browse our experiences and book your first tour!
      </p>
      <a href="<?= url('services') ?>" class="btn btn-gold">
        <i class="fa-solid fa-compass"></i> Browse Experiences
      </a>
      <?php if (!Auth::check()): ?>
      <p style="margin-top:16px;color:var(--muted);font-size:.88rem">
        Have an existing booking?
        <a href="<?= url('login') ?>" style="color:var(--gold)">Login to view it</a>
      </p>
      <?php endif; ?>
    </div>

    <?php else: ?>
    <div class="bookings-list">
      <?php foreach ($bookings as $b): ?>
      <div class="booking-card">
        <?php if (!empty($b['image'])): ?>
        <div class="bc-img">
          <img src="<?= e($b['image']) ?>" alt="<?= e($b['service_title']) ?>"
               onerror="this.style.display='none'">
        </div>
        <?php endif; ?>
        <div class="bc-body">
          <div class="bc-top">
            <div>
              <div class="bc-ref"><?= e($b['reference']) ?></div>
              <h3 class="bc-title"><?= e($b['service_title']) ?></h3>
            </div>
            <div class="bc-badges">
              <span class="badge-status badge-status-<?= e($b['status']) ?>">
                <?= ucfirst(e($b['status'])) ?>
              </span>
              <span class="badge-status badge-status-<?= e($b['payment_status']) ?>">
                <?= ucfirst(e($b['payment_status'])) ?>
              </span>
            </div>
          </div>
          <div class="bc-meta">
            <span><i class="fa-solid fa-calendar"></i> <?= date('D, d M Y', strtotime($b['booking_date'])) ?></span>
            <span><i class="fa-solid fa-users"></i> <?= (int)$b['people'] ?> guest<?= $b['people'] != 1 ? 's' : '' ?></span>
            <span><i class="fa-solid fa-dollar-sign"></i> $<?= number_format($b['total_price'], 2) ?> total</span>
          </div>
          <?php if ($b['notes']): ?>
          <div style="font-size:.85rem;color:var(--muted);margin-top:8px;padding:10px 14px;background:var(--sand);border-radius:var(--rsm)">
            <i class="fa-solid fa-comment" style="margin-right:6px"></i><?= e($b['notes']) ?>
          </div>
          <?php endif; ?>
          <?php if ($b['admin_notes']): ?>
          <div style="font-size:.85rem;color:var(--sage);margin-top:8px;padding:10px 14px;background:#f0faf4;border:1px solid #c6f6d5;border-radius:var(--rsm)">
            <i class="fa-solid fa-comment-dots" style="margin-right:6px"></i>
            <strong>Guide note:</strong> <?= e($b['admin_notes']) ?>
          </div>
          <?php endif; ?>
          <div class="bc-footer">
            <span style="font-size:.8rem;color:var(--muted)">
              Booked <?= date('d M Y', strtotime($b['created_at'])) ?>
            </span>
            <?php if ($b['payment_status'] === 'unpaid' && $b['status'] !== 'cancelled'): ?>
            <a href="https://wa.me/<?= e(preg_replace('/\D/', '', $settings['whatsapp'] ?? '212600000000')) ?>"
               target="_blank" rel="noopener" class="btn btn-wa btn-sm">
              <i class="fa-brands fa-whatsapp"></i> Pay via WhatsApp
            </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div style="margin-top:24px;text-align:center">
      <a href="<?= url('services') ?>" class="btn btn-ghost">
        <i class="fa-solid fa-compass"></i> Book Another Experience
      </a>
    </div>
    <?php endif; ?>

  </div>
</div>

<?php partial('layout/footer', ['settings' => $settings]) ?>
