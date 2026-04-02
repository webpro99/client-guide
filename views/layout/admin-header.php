<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle ?? 'Admin — Marrakech Guide') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= url('public/css/admin.css') ?>">
  <meta name="csrf-token" content="<?= e(CSRF::token()) ?>">
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-mark">M</div>
    <div class="title">Marrakech Guide</div>
    <div class="role">Admin Panel</div>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-section">Main</div>
    <a href="<?= url('admin') ?>" class="nav-item <?= ($adminPage ?? '') === 'dashboard' ? 'active' : '' ?>">
      <i class="fa-solid fa-gauge"></i> Dashboard
    </a>
    <a href="<?= url('admin/bookings') ?>" class="nav-item <?= ($adminPage ?? '') === 'bookings' ? 'active' : '' ?>">
      <i class="fa-solid fa-calendar-check"></i> Bookings
      <?php if (($pendingCount ?? 0) > 0): ?>
        <span class="nav-badge"><?= (int)($pendingCount ?? 0) ?></span>
      <?php endif; ?>
    </a>
    <a href="<?= url('admin/calendar') ?>" class="nav-item <?= ($adminPage ?? '') === 'calendar' ? 'active' : '' ?>">
      <i class="fa-solid fa-calendar-xmark"></i> Calendar
    </a>

    <div class="sidebar-section">Catalog</div>
    <a href="<?= url('admin/services') ?>" class="nav-item <?= ($adminPage ?? '') === 'services' ? 'active' : '' ?>">
      <i class="fa-solid fa-compass"></i> Services
    </a>

    <div class="sidebar-section">System</div>
    <a href="<?= url('admin/users') ?>" class="nav-item <?= ($adminPage ?? '') === 'users' ? 'active' : '' ?>">
      <i class="fa-solid fa-users"></i> Users
    </a>
    <a href="<?= url('admin/settings') ?>" class="nav-item <?= ($adminPage ?? '') === 'settings' ? 'active' : '' ?>">
      <i class="fa-solid fa-gear"></i> Settings
    </a>
  </nav>

  <div class="sidebar-footer">
    <a href="<?= url() ?>" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> View Website</a>
    <a href="<?= url('logout') ?>"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
  </div>
</aside>

<!-- MAIN AREA -->
<div class="main">
  <header class="admin-topbar">
    <div class="topbar-title">
      <i class="fa-solid <?= $pageIcon ?? 'fa-gauge' ?>"></i>
      <?= e($pageTitle ?? 'Admin') ?>
    </div>
    <div style="display:flex;align-items:center;gap:12px">
      <span style="font-size:.83rem;color:var(--muted)"><?= e(Auth::user()['name'] ?? 'Admin') ?></span>
      <div class="admin-avatar"><?= strtoupper(substr(Auth::user()['name'] ?? 'A', 0, 1)) ?></div>
    </div>
  </header>

  <!-- Flash messages -->
  <?php foreach (getFlashes() as $f): ?>
  <div class="admin-flash admin-flash-<?= e($f['type']) ?>" id="adminFlash">
    <i class="fa-solid <?= $f['type'] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
    <?= e($f['message']) ?>
    <button onclick="this.parentElement.remove()" style="margin-left:auto;background:none;border:0;cursor:pointer;font-size:.9rem;opacity:.6">&times;</button>
  </div>
  <?php endforeach; ?>

  <div class="page-content">
