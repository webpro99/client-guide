<!-- ── FOOTER ── -->
<footer class="site-footer">
  <div class="footer-inner">
    <div class="footer-brand">
      <div class="footer-logo">Marrakech <em>Guide</em></div>
      <p>Your personal Morocco experience, crafted with passion and local expertise.</p>
      <div class="footer-social">
        <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
        <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook"></i></a>
        <a href="#" aria-label="TripAdvisor"><i class="fa-brands fa-tripadvisor"></i></a>
        <a href="https://wa.me/<?= e(preg_replace('/\D/', '', $settings['whatsapp'] ?? '212600000000')) ?>"
           target="_blank" rel="noopener" aria-label="WhatsApp">
          <i class="fa-brands fa-whatsapp"></i>
        </a>
      </div>
    </div>

    <div class="footer-links">
      <h4>Experiences</h4>
      <ul>
        <li><a href="<?= url('services?category=Cultural') ?>">Cultural Tours</a></li>
        <li><a href="<?= url('services?category=Adventure') ?>">Adventure</a></li>
        <li><a href="<?= url('services?category=Food+%26+Culture') ?>">Food & Culture</a></li>
        <li><a href="<?= url('services?category=Wellness') ?>">Wellness</a></li>
        <li><a href="<?= url('services?category=Nature') ?>">Nature</a></li>
      </ul>
    </div>

    <div class="footer-links">
      <h4>Company</h4>
      <ul>
        <li><a href="<?= url('#about') ?>">About Tarik</a></li>
        <li><a href="<?= url('#contact') ?>">Contact</a></li>
        <?php if (!Auth::check()): ?>
        <li><a href="<?= url('login') ?>">Login</a></li>
        <li><a href="<?= url('register') ?>">Create Account</a></li>
        <?php else: ?>
        <li><a href="<?= url('bookings') ?>">My Bookings</a></li>
        <li><a href="<?= url('logout') ?>">Logout</a></li>
        <?php endif; ?>
      </ul>
    </div>

    <div class="footer-contact">
      <h4>Contact</h4>
      <ul>
        <li><i class="fa-solid fa-phone"></i> <?= e($settings['whatsapp'] ?? '+212600000000') ?></li>
        <li><i class="fa-solid fa-envelope"></i> <?= e($settings['email'] ?? 'tarik@marrakechguide.com') ?></li>
        <li><i class="fa-solid fa-location-dot"></i> <?= e($settings['address'] ?? 'Marrakech, Morocco') ?></li>
      </ul>
    </div>
  </div>

  <div class="footer-bottom">
    <p>&copy; <?= date('Y') ?> <?= e($settings['business_name'] ?? 'Marrakech Guide') ?>. All rights reserved.</p>
  </div>
</footer>

<!-- Mobile bottom nav -->
<nav class="bottom-nav" aria-label="Mobile navigation">
  <a href="<?= url() ?>" class="bottom-nav-item <?= ($_SERVER['REQUEST_URI'] === '/' || str_ends_with($_SERVER['REQUEST_URI'], '/')) ? 'active' : '' ?>">
    <i class="fa-solid fa-house"></i><span>Home</span>
  </a>
  <a href="<?= url('bookings') ?>" class="bottom-nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/bookings') ? 'active' : '' ?>">
    <i class="fa-solid fa-calendar-check"></i><span>Bookings</span>
  </a>
  <a href="https://wa.me/<?= e(preg_replace('/\D/', '', $settings['whatsapp'] ?? '212600000000')) ?>"
     target="_blank" rel="noopener" class="bottom-nav-item">
    <i class="fa-brands fa-whatsapp"></i><span>Contact</span>
  </a>
</nav>

<!-- Toast container for AJAX feedback -->
<div id="toast-container" aria-live="polite"></div>

<!-- App JS -->
<script src="<?= url('public/js/app.js') ?>"></script>

</body>
</html>
