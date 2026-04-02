<?php partial('layout/header', ['pageTitle' => $pageTitle, 'settings' => $settings]) ?>

<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <div class="auth-logo-mark">M</div>
      <div class="auth-logo-name">Marrakech <em>Guide</em></div>
    </div>
    <h1 class="auth-title">Welcome back</h1>
    <p class="auth-sub">Sign in to manage your bookings</p>

    <form method="POST" action="<?= url('login') ?>" class="auth-form">
      <input type="hidden" name="csrf_token" value="<?= e(CSRF::token()) ?>">

      <div class="form-group">
        <label class="form-label" for="email">
          <i class="fa-solid fa-envelope"></i> Email address
        </label>
        <input type="email" id="email" name="email" class="form-control"
               placeholder="you@example.com" required autocomplete="email">
      </div>

      <div class="form-group">
        <label class="form-label" for="password">
          <i class="fa-solid fa-lock"></i> Password
        </label>
        <input type="password" id="password" name="password" class="form-control"
               placeholder="Your password" required autocomplete="current-password">
      </div>

      <button type="submit" class="btn btn-primary btn-full">
        <i class="fa-solid fa-right-to-bracket"></i> Sign In
      </button>
    </form>

    <div class="auth-footer">
      Don't have an account?
      <a href="<?= url('register') ?>">Create one free</a>
    </div>
  </div>
</div>

<?php partial('layout/footer', ['settings' => $settings]) ?>
