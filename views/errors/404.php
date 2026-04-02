<?php $settings = $settings ?? SettingModel::all(); ?>
<?php partial('layout/header', ['pageTitle' => 'Page Not Found', 'settings' => $settings]) ?>

<div style="min-height:70vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px 32px;text-align:center">
  <div style="font-family:'Cormorant Garamond',serif;font-size:clamp(5rem,15vw,9rem);font-weight:700;color:var(--gold);line-height:1">404</div>
  <h1 style="font-family:'Cormorant Garamond',serif;font-size:clamp(1.5rem,3vw,2.4rem);font-weight:700;margin-bottom:12px">Page Not Found</h1>
  <p style="color:var(--muted);max-width:42ch;line-height:1.8;margin-bottom:32px">The page you're looking for doesn't exist, or may have been moved. Let's get you back on track.</p>
  <div style="display:flex;gap:14px;flex-wrap:wrap;justify-content:center">
    <a href="<?= url() ?>" class="btn btn-primary">
      <i class="fa-solid fa-house"></i> Back to Home
    </a>
    <a href="<?= url('services') ?>" class="btn btn-ghost">
      <i class="fa-solid fa-compass"></i> Browse Experiences
    </a>
  </div>
</div>

<?php partial('layout/footer', ['settings' => $settings]) ?>
