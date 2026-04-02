<?php
$highlights   = decodeJson($service['highlights'] ?? '');
$included     = decodeJson($service['included'] ?? '');
$notIncluded  = decodeJson($service['not_included'] ?? '');
?>
<?php partial('layout/header', ['pageTitle' => $pageTitle, 'settings' => $settings]) ?>

<div style="padding-top:68px">

  <!-- HERO IMAGE -->
  <div class="service-detail-hero">
    <img src="<?= e($service['image'] ?? '') ?>" alt="<?= e($service['title']) ?>"
         onerror="this.src='https://images.unsplash.com/photo-1539020140153-e479b8c22e70?w=1200'">
    <div class="sdh-overlay">
      <div class="wrap">
        <?php if ($service['badge']): ?>
        <span class="svc-badge" style="margin-bottom:12px"><?= e($service['badge']) ?></span>
        <?php endif; ?>
        <div class="svc-cat" style="color:var(--gold2);margin-bottom:6px"><?= e($service['category']) ?></div>
        <h1 style="font-family:'Cormorant Garamond',serif;font-size:clamp(2rem,5vw,3.5rem);font-weight:700;color:#fff;line-height:1.1;margin-bottom:10px">
          <?= e($service['title']) ?>
        </h1>
        <?php if ($service['tagline']): ?>
        <p style="color:rgba(255,255,255,.7);font-size:1.05rem;max-width:55ch"><?= e($service['tagline']) ?></p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- QUICK META BAR -->
  <div class="sdh-meta-bar">
    <div class="wrap">
      <div class="sdh-meta-grid">
        <div class="sdh-meta-item">
          <i class="fa-solid fa-dollar-sign"></i>
          <div><strong>$<?= number_format($service['price'], 0) ?></strong><span>per person</span></div>
        </div>
        <?php if ($service['duration']): ?>
        <div class="sdh-meta-item">
          <i class="fa-solid fa-clock"></i>
          <div><strong><?= e($service['duration']) ?></strong><span>duration</span></div>
        </div>
        <?php endif; ?>
        <?php if ($service['difficulty']): ?>
        <div class="sdh-meta-item">
          <i class="fa-solid fa-gauge"></i>
          <div><strong><?= e($service['difficulty']) ?></strong><span>difficulty</span></div>
        </div>
        <?php endif; ?>
        <?php if ($service['language']): ?>
        <div class="sdh-meta-item">
          <i class="fa-solid fa-language"></i>
          <div><strong><?= e($service['language']) ?></strong><span>languages</span></div>
        </div>
        <?php endif; ?>
        <?php if ($service['group_type']): ?>
        <div class="sdh-meta-item">
          <i class="fa-solid fa-users"></i>
          <div><strong><?= ucfirst(e($service['group_type'])) ?></strong><span>tour type</span></div>
        </div>
        <?php endif; ?>
        <?php if ($service['rating']): ?>
        <div class="sdh-meta-item">
          <i class="fa-solid fa-star" style="color:var(--gold)"></i>
          <div>
            <strong><?= number_format($service['rating'], 1) ?></strong>
            <span><?= (int)$service['reviews'] ?> reviews</span>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- MAIN CONTENT + SIDEBAR -->
  <div class="wrap service-layout" style="padding-top:48px;padding-bottom:80px">

    <!-- LEFT: Description & Details -->
    <div class="service-main">

      <?php if ($service['long_desc']): ?>
      <div class="sdh-section">
        <h2 class="sdh-h2">About This Experience</h2>
        <div class="sdh-longdesc">
          <?php foreach (explode("\n\n", $service['long_desc']) as $para): ?>
          <?php if (trim($para)): ?>
          <p><?= e(trim($para)) ?></p>
          <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if (!empty($highlights)): ?>
      <div class="sdh-section">
        <h2 class="sdh-h2">Highlights</h2>
        <div class="service-highlights">
          <?php foreach ($highlights as $h): ?>
          <div class="highlight-item">
            <div class="hi-icon"><i class="fa-solid <?= e($h['icon'] ?? 'fa-check') ?>"></i></div>
            <span><?= e($h['text'] ?? '') ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if (!empty($included) || !empty($notIncluded)): ?>
      <div class="sdh-section">
        <div class="included-grid">
          <?php if (!empty($included)): ?>
          <div>
            <h3 class="inc-title"><i class="fa-solid fa-circle-check" style="color:var(--sage)"></i> What's Included</h3>
            <ul class="inc-list inc-yes">
              <?php foreach ($included as $item): ?>
              <li><?= e($item) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
          <?php endif; ?>
          <?php if (!empty($notIncluded)): ?>
          <div>
            <h3 class="inc-title"><i class="fa-solid fa-circle-xmark" style="color:var(--terra)"></i> Not Included</h3>
            <ul class="inc-list inc-no">
              <?php foreach ($notIncluded as $item): ?>
              <li><?= e($item) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($service['meeting_point']): ?>
      <div class="sdh-section">
        <h2 class="sdh-h2"><i class="fa-solid fa-location-dot" style="color:var(--gold)"></i> Meeting Point</h2>
        <p style="color:var(--muted);line-height:1.8"><?= e($service['meeting_point']) ?></p>
      </div>
      <?php endif; ?>

      <?php if ($service['cancel_policy']): ?>
      <div class="sdh-section">
        <h2 class="sdh-h2"><i class="fa-solid fa-shield-halved" style="color:var(--gold)"></i> Cancellation Policy</h2>
        <p style="color:var(--muted);line-height:1.8"><?= e($service['cancel_policy']) ?></p>
      </div>
      <?php endif; ?>

      <?php if ($service['what_to_bring']): ?>
      <div class="sdh-section">
        <h2 class="sdh-h2"><i class="fa-solid fa-bag-shopping" style="color:var(--gold)"></i> What to Bring</h2>
        <p style="color:var(--muted);line-height:1.8"><?= e($service['what_to_bring']) ?></p>
      </div>
      <?php endif; ?>

      <?php if ($service['min_age']): ?>
      <div class="sdh-section">
        <h2 class="sdh-h2"><i class="fa-solid fa-child" style="color:var(--gold)"></i> Minimum Age</h2>
        <p style="color:var(--muted)"><?= e($service['min_age']) ?></p>
      </div>
      <?php endif; ?>
    </div>

    <!-- RIGHT: Booking Sidebar -->
    <aside class="service-sidebar">
      <div class="booking-sidebar-card">
        <div class="bsc-price">
          <span class="bsc-from">from</span>
          <span class="bsc-amount">$<?= number_format($service['price'], 0) ?></span>
          <span class="bsc-per">/ person</span>
        </div>
        <?php if ($service['rating']): ?>
        <div class="bsc-rating">
          <?php for ($i = 1; $i <= 5; $i++): ?>
          <i class="fa-solid fa-star" style="color:<?= $i <= round($service['rating']) ? 'var(--gold)' : '#ddd' ?>"></i>
          <?php endfor; ?>
          <span><?= number_format($service['rating'], 1) ?> (<?= (int)$service['reviews'] ?> reviews)</span>
        </div>
        <?php endif; ?>
        <button class="btn btn-gold btn-full"
                onclick="openBookingModal(<?= (int)$service['id'] ?>, <?= json_encode($service['title']) ?>, <?= (float)$service['price'] ?>)">
          <i class="fa-solid fa-calendar-plus"></i> Book This Tour
        </button>
        <a href="https://wa.me/<?= e(preg_replace('/\D/', '', $settings['whatsapp'] ?? '212600000000')) ?>"
           target="_blank" rel="noopener" class="btn btn-wa btn-full" style="margin-top:8px">
          <i class="fa-brands fa-whatsapp"></i> Ask on WhatsApp
        </a>
        <div class="bsc-trust">
          <div><i class="fa-solid fa-shield-halved"></i> Free cancellation</div>
          <div><i class="fa-solid fa-headset"></i> 24/7 support</div>
          <div><i class="fa-solid fa-certificate"></i> Local expert guide</div>
        </div>
      </div>
    </aside>
  </div>

  <!-- RELATED EXPERIENCES -->
  <?php if (!empty($related)): ?>
  <section class="section" style="background:var(--sand);padding-top:64px">
    <div class="wrap">
      <div class="eyebrow">You May Also Like</div>
      <h2 class="sec-title" style="margin-bottom:32px">Similar Experiences</h2>
      <div class="svc-grid" style="grid-template-columns:repeat(auto-fill,minmax(270px,1fr))">
        <?php foreach ($related as $r): ?>
        <article class="svc-card">
          <div class="svc-card-img">
            <img src="<?= e($r['image'] ?? '') ?>" alt="<?= e($r['title']) ?>"
                 onerror="this.src='https://images.unsplash.com/photo-1539020140153-e479b8c22e70?w=600'">
          </div>
          <div class="svc-card-body">
            <div class="svc-icon">
              <i class="fa-solid <?= e($r['fa_icon'] ?? 'fa-compass') ?>"></i>
            </div>
            <h3><?= e($r['title']) ?></h3>
            <p><?= e($r['description']) ?></p>
            <div class="svc-meta">
              <span><i class="fa-solid fa-dollar-sign"></i> $<?= number_format($r['price']) ?></span>
            </div>
            <div class="svc-actions">
              <a href="<?= url('services/' . $r['id']) ?>" class="btn btn-ghost btn-sm">View Details</a>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

</div>

<?php include VIEWS_PATH . '/partials/booking-modal.php'; ?>

<?php partial('layout/footer', ['settings' => $settings]) ?>
