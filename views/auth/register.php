<?php partial('layout/header', ['pageTitle' => $pageTitle, 'settings' => $settings]) ?>

<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <div class="auth-logo-mark">M</div>
      <div class="auth-logo-name">Marrakech <em>Guide</em></div>
    </div>
    <h1 class="auth-title">Create your account</h1>
    <p class="auth-sub">Join to track all your Morocco adventures</p>

    <form method="POST" action="<?= url('register') ?>" class="auth-form">
      <input type="hidden" name="csrf_token" value="<?= e(CSRF::token()) ?>">

      <div class="form-group">
        <label class="form-label" for="name">
          <i class="fa-solid fa-user"></i> Full name
        </label>
        <input type="text" id="name" name="name" class="form-control"
               placeholder="Tarik Belasri" required autocomplete="name">
      </div>

      <div class="form-group">
        <label class="form-label" for="email">
          <i class="fa-solid fa-envelope"></i> Email address
        </label>
        <input type="email" id="email" name="email" class="form-control"
               placeholder="you@example.com" required autocomplete="email">
      </div>

      <div class="form-group">
        <label class="form-label" for="phone">
          <i class="fa-solid fa-phone"></i> Phone (optional)
        </label>
        <input type="tel" id="phone" name="phone" class="form-control"
               placeholder="+212 600 000 000" autocomplete="tel">
      </div>

      <div class="form-group">
        <label class="form-label" for="password">
          <i class="fa-solid fa-lock"></i> Password
        </label>
        <input type="password" id="password" name="password" class="form-control"
               placeholder="At least 8 characters" required autocomplete="new-password" minlength="8">
      </div>

      <div class="form-group">
        <label class="form-label" for="password_confirm">
          <i class="fa-solid fa-lock"></i> Confirm password
        </label>
        <input type="password" id="password_confirm" name="password_confirm" class="form-control"
               placeholder="Repeat your password" required autocomplete="new-password">
      </div>

      <button type="submit" class="btn btn-primary btn-full">
        <i class="fa-solid fa-user-plus"></i> Create Account
      </button>
    </form>

    <div class="auth-footer">
      Already have an account?
      <a href="<?= url('login') ?>">Sign in</a>
    </div>
  </div>
</div>

<?php partial('layout/footer', ['settings' => $settings]) ?>
