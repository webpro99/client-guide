<?php partial('layout/header', ['pageTitle' => $pageTitle, 'settings' => $settings]) ?>

<!-- ═══════════════════════════════════════════════════════════
     HERO
═══════════════════════════════════════════════════════════ -->
<section class="hero" id="home">
  <div class="hero-pat"></div>
  <div class="hero-inner wrap">
    <div class="hero-content">
      <div class="hero-eyebrow">Private Tour Guide · Morocco</div>
      <h1>Discover Morocco<br>with a <em>Local Expert</em></h1>
      <p class="hero-sub">
        Immersive, handcrafted experiences led by <?= e($settings['business_name'] ?? 'Tarik Belasri') ?>,
        your trusted guide to the magic, culture and hidden wonders of Morocco.
      </p>
      <div class="hero-actions">
        <a href="<?= url('services') ?>" class="btn btn-gold">
          <i class="fa-solid fa-compass"></i> Explore Experiences
        </a>
        <a href="https://wa.me/<?= e(preg_replace('/\D/', '', $settings['whatsapp'] ?? '212600000000')) ?>"
           target="_blank" rel="noopener" class="btn btn-outline">
          <i class="fa-brands fa-whatsapp"></i> Chat on WhatsApp
        </a>
      </div>
    </div>
    <div class="hero-img">
      <div class="hero-frame">
        <img src="https://images.unsplash.com/photo-1539020140153-e479b8c22e70?w=800"
             alt="Marrakech medina with guide">
      </div>
      <div class="hero-stat">
        <div class="num">500+</div>
        <div class="txt">Happy Travellers<br>Guided</div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     FEATURED EXPERIENCES
═══════════════════════════════════════════════════════════ -->
<section class="section services" id="experiences">
  <div class="wrap">
    <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:20px;flex-wrap:wrap;margin-bottom:44px">
      <div>
        <div class="eyebrow">Handpicked Experiences</div>
        <h2 class="sec-title">Unforgettable Morocco<br>Adventures</h2>
        <p class="sec-desc">Each tour is crafted to immerse you in Morocco's authentic beauty.</p>
      </div>
      <a href="<?= url('services') ?>" class="btn btn-ghost">
        View All <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>

    <div class="svc-grid">
      <?php foreach ($featured as $s): ?>
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
          <div class="svc-actions">
            <a href="<?= url('services/' . $s['id']) ?>" class="btn btn-ghost btn-sm">Details</a>
            <button class="btn btn-primary btn-sm" onclick="openBookingModal(<?= (int)$s['id'] ?>, <?= json_encode($s['title']) ?>, <?= (float)$s['price'] ?>)">
              <i class="fa-solid fa-calendar-plus"></i> Book
            </button>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     ABOUT
═══════════════════════════════════════════════════════════ -->
<section class="about section" id="about">
  <div class="about-inner wrap">
    <div class="about-img-wrap">
      <div class="about-frame">
        <img src="https://images.unsplash.com/photo-1534759926459-3e91fad4b8f8?w=800"
             alt="<?= e($settings['business_name'] ?? 'Tarik') ?> — your Morocco guide">
      </div>
      <div class="about-deco"><i class="fa-solid fa-star"></i></div>
    </div>
    <div class="about-content">
      <div class="eyebrow">Your Guide</div>
      <h2 class="about-title">Meet <em><?= e($settings['business_name'] ?? 'Tarik Belasri') ?></em></h2>
      <div class="about-body">
        <p>Born and raised in the heart of Marrakech, I've spent over a decade sharing the authentic stories, hidden corners, and living culture of my homeland with travellers from around the world.</p>
        <p>My philosophy is simple: every journey should feel personal. Whether you're wandering the ancient medina, trekking the Atlas Mountains, or dining in a local family's home — I'm here to make it extraordinary.</p>
      </div>
      <div class="about-stats">
        <div class="about-stat"><div class="n">10+</div><div class="l">Years Experience</div></div>
        <div class="about-stat"><div class="n">500+</div><div class="l">Happy Clients</div></div>
        <div class="about-stat"><div class="n">5★</div><div class="l">Average Rating</div></div>
      </div>
      <a href="<?= url('services') ?>" class="btn btn-gold">
        <i class="fa-solid fa-compass"></i> Explore My Tours
      </a>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     CONTACT / MINI CTA
═══════════════════════════════════════════════════════════ -->
<section class="mini-cta" id="contact">
  <div class="wrap" style="text-align:center">
    <div class="eyebrow" style="justify-content:center;margin-bottom:14px">Let's Plan Together</div>
    <h2 class="sec-title" style="color:#fff;margin-bottom:10px">Ready for Your Morocco<br>Adventure?</h2>
    <p style="color:rgba(255,255,255,.5);margin-bottom:32px;max-width:52ch;margin-left:auto;margin-right:auto;line-height:1.8">
      Whether you have a full itinerary or just a dream, reach out and let's craft something unforgettable.
    </p>
    <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
      <a href="<?= url('services') ?>" class="btn btn-gold">
        <i class="fa-solid fa-compass"></i> Browse Experiences
      </a>
      <a href="https://wa.me/<?= e(preg_replace('/\D/', '', $settings['whatsapp'] ?? '212600000000')) ?>"
         target="_blank" rel="noopener" class="btn btn-wa">
        <i class="fa-brands fa-whatsapp"></i> WhatsApp Me
      </a>
    </div>
    <div style="margin-top:28px;color:rgba(255,255,255,.3);font-size:.85rem">
      <i class="fa-solid fa-envelope" style="margin-right:6px"></i>
      <a href="mailto:<?= e($settings['email'] ?? '') ?>" style="color:inherit">
        <?= e($settings['email'] ?? 'tarik@marrakechguide.com') ?>
      </a>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     BOOKING MODAL
═══════════════════════════════════════════════════════════ -->
<?php include VIEWS_PATH . '/partials/booking-modal.php'; ?>

<?php partial('layout/footer', ['settings' => $settings]) ?>
