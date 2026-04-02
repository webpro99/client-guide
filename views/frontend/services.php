<?php partial('layout/header', ['pageTitle' => $pageTitle, 'settings' => $settings]) ?>

<div style="padding-top:68px">

  <!-- PAGE HEADER -->
  <div class="services-header">
    <div class="wrap">
      <div class="eyebrow">All Experiences</div>
      <h1 class="sec-title">Morocco Adventures</h1>
      <p class="sec-desc">Discover our full range of handcrafted tours and experiences.</p>
    </div>
  </div>

  <!-- FILTERS -->
  <div class="services-filter-bar">
    <div class="wrap">
      <form method="GET" action="<?= url('services') ?>" class="services-filter-form" id="filterForm">
        <input type="text" name="search" class="filter-search"
               value="<?= e($search) ?>" placeholder="Search experiences…"
               oninput="document.getElementById('filterForm').submit()">

        <div class="filter-cats">
          <a href="<?= url('services') ?>"
             class="cat-pill <?= !$category ? 'active' : '' ?>">All</a>
          <?php foreach ($categories as $cat): ?>
          <a href="<?= url('services?category=' . urlencode($cat)) ?>"
             class="cat-pill <?= $category === $cat ? 'active' : '' ?>">
            <i class="fa-solid <?= getCategoryIcon($cat) ?>"></i>
            <?= e($cat) ?>
          </a>
          <?php endforeach; ?>
        </div>
      </form>
    </div>
  </div>

  <!-- SERVICES GRID -->
  <section class="section" style="padding-top:40px">
    <div class="wrap">
      <?php if (empty($services)): ?>
      <div style="text-align:center;padding:80px 0">
        <div style="font-size:3rem;margin-bottom:14px;opacity:.3">🔍</div>
        <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.6rem;margin-bottom:8px">No experiences found</h3>
        <p style="color:var(--muted);margin-bottom:20px">Try adjusting your search or browse all categories.</p>
        <a href="<?= url('services') ?>" class="btn btn-ghost">Clear Filters</a>
      </div>
      <?php else: ?>
      <div class="svc-grid">
        <?php foreach ($services as $s): ?>
        <article class="svc-card">
          <?php if ($s['badge']): ?>
          <span class="svc-badge"><?= e($s['badge']) ?></span>
          <?php endif; ?>
          <div class="svc-card-img">
            <img src="<?= e($s['image'] ?? '') ?>" alt="<?= e($s['title']) ?>"
                 onerror="this.src='https://images.unsplash.com/photo-1539020140153-e479b8c22e70?w=600'">
          </div>
          <div class="svc-card-body">
            <div class="svc-icon">
              <i class="fa-solid <?= e($s['fa_icon'] ?? 'fa-compass') ?>"></i>
            </div>
            <div class="svc-cat"><?= e($s['category']) ?></div>
            <h3><?= e($s['title']) ?></h3>
            <p><?= e($s['description']) ?></p>
            <div class="svc-meta">
              <span><i class="fa-solid fa-dollar-sign"></i> From $<?= number_format($s['price']) ?></span>
              <?php if ($s['duration']): ?>
              <span><i class="fa-solid fa-clock"></i> <?= e($s['duration']) ?></span>
              <?php endif; ?>
              <?php if ($s['rating']): ?>
              <span><i class="fa-solid fa-star"></i> <?= number_format($s['rating'], 1) ?> (<?= (int)$s['reviews'] ?>)</span>
              <?php endif; ?>
            </div>
            <?php if ($s['location']): ?>
            <div style="font-size:.8rem;color:var(--muted);margin-bottom:12px">
              <i class="fa-solid fa-location-dot"></i> <?= e($s['location']) ?>
            </div>
            <?php endif; ?>
            <div class="svc-actions">
              <a href="<?= url('services/' . $s['id']) ?>" class="btn btn-ghost btn-sm">Details</a>
              <button class="btn btn-primary btn-sm"
                      onclick="openBookingModal(<?= (int)$s['id'] ?>, <?= json_encode($s['title']) ?>, <?= (float)$s['price'] ?>)">
                <i class="fa-solid fa-calendar-plus"></i> Book
              </button>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
      <div style="margin-top:16px;color:var(--muted);font-size:.85rem">
        <?= count($services) ?> experience<?= count($services) !== 1 ? 's' : '' ?> found
      </div>
      <?php endif; ?>
    </div>
  </section>

</div>

<?php include VIEWS_PATH . '/partials/booking-modal.php'; ?>

<?php partial('layout/footer', ['settings' => $settings]) ?>
