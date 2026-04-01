<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle ?? APP_NAME) ?></title>
  <meta name="description" content="<?= e($settings['meta_description'] ?? 'Discover Morocco with Tarik, your personal Marrakech guide.') ?>">

  <!-- Fonts & Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- App CSS -->
  <link rel="stylesheet" href="<?= url('public/css/frontend.css') ?>">

  <!-- CSRF meta tag for AJAX requests -->
  <meta name="csrf-token" content="<?= e(CSRF::token()) ?>">
</head>
<body>

<!-- Flash messages (auto-dismiss after 4s) -->
<?php foreach (getFlashes() as $f): ?>
<div class="flash-toast flash-<?= e($f['type']) ?>" role="alert">
  <?= e($f['message']) ?>
</div>
<?php endforeach; ?>

<!-- ── TOP NAVIGATION ── -->
<header class="topbar">
  <div class="topbar-inner">
    <a href="<?= url() ?>" class="logo">
      <img src="<?= url('public/img/logo.png') ?>" alt="Marrakech Guide" onerror="this.style.display='none'">
      <span>Marrakech <em>Guide</em></span>
    </a>

    <nav class="topnav" aria-label="Main navigation">
      <a href="<?= url('#experiences') ?>">Experiences</a>
      <a href="<?= url('#about') ?>">About</a>
      <a href="<?= url('#contact') ?>">Contact</a>
    </nav>

    <div class="topbar-cta">
      <?php if (Auth::check()): ?>
        <a href="<?= url('bookings') ?>" class="btn btn-ghost btn-sm">
          <i class="fa-solid fa-calendar-check"></i> My Bookings
        </a>
        <?php if (Auth::isAdmin()): ?>
        <a href="<?= url('admin') ?>" class="btn btn-ghost btn-sm">
          <i class="fa-solid fa-gauge"></i> Admin
        </a>
        <?php endif; ?>
        <a href="<?= url('logout') ?>" class="btn btn-ghost btn-sm">
          <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
      <?php else: ?>
        <a href="<?= url('login') ?>" class="btn btn-ghost btn-sm">Login</a>
        <a href="<?= url('register') ?>" class="btn btn-primary btn-sm">Sign Up</a>
      <?php endif; ?>
      <a href="https://wa.me/<?= e(preg_replace('/\D/', '', $settings['whatsapp'] ?? '212600000000')) ?>"
         target="_blank" rel="noopener" class="btn btn-wa">
        <i class="fa-brands fa-whatsapp"></i> WhatsApp
      </a>
    </div>
  </div>
</header>
