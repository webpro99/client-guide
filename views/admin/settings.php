<?php
$adminPage  = 'settings';
$pageIcon   = 'fa-gear';
$pendingCount = BookingModel::pendingCount();
include VIEWS_PATH . '/layout/admin-header.php';
?>

<form method="POST" action="<?= url('admin/settings') ?>">
  <input type="hidden" name="csrf_token" value="<?= e(CSRF::token()) ?>">

  <!-- BUSINESS INFO -->
  <div class="form-panel">
    <div class="form-panel-head">
      <h3><i class="fa-solid fa-building"></i> Business Information</h3>
      <p>Displayed on the public website and emails</p>
    </div>
    <div class="form-grid">
      <div class="field">
        <label><i class="fa-solid fa-signature"></i> Business Name</label>
        <input type="text" name="business_name"
               value="<?= e($settings['business_name'] ?? '') ?>"
               placeholder="Marrakech Guide">
      </div>
      <div class="field">
        <label><i class="fa-solid fa-quote-left"></i> Site Tagline</label>
        <input type="text" name="site_tagline"
               value="<?= e($settings['site_tagline'] ?? '') ?>"
               placeholder="Your Private Tour Guide in Morocco">
      </div>
      <div class="field">
        <label><i class="fa-brands fa-whatsapp"></i> WhatsApp Number</label>
        <input type="text" name="whatsapp"
               value="<?= e($settings['whatsapp'] ?? '') ?>"
               placeholder="+212600000000">
        <small>Include country code. Used for WhatsApp buttons site-wide.</small>
      </div>
      <div class="field">
        <label><i class="fa-solid fa-envelope"></i> Contact Email</label>
        <input type="email" name="email"
               value="<?= e($settings['email'] ?? '') ?>"
               placeholder="tarik@marrakechguide.com">
      </div>
      <div class="field full">
        <label><i class="fa-solid fa-location-dot"></i> Address</label>
        <input type="text" name="address"
               value="<?= e($settings['address'] ?? '') ?>"
               placeholder="Marrakech, Morocco">
      </div>
      <div class="field full">
        <label><i class="fa-solid fa-align-left"></i> Meta Description (SEO)</label>
        <textarea name="meta_description" rows="2"
                  placeholder="Discover Morocco with Tarik, your personal Marrakech guide."><?= e($settings['meta_description'] ?? '') ?></textarea>
        <small>Displayed in Google search results. Keep under 160 characters.</small>
      </div>
    </div>
  </div>

  <!-- PAYMENT -->
  <div class="form-panel">
    <div class="form-panel-head">
      <h3><i class="fa-brands fa-paypal"></i> PayPal Settings</h3>
      <p>Required for online payment processing</p>
    </div>
    <div class="form-grid">
      <div class="field">
        <label><i class="fa-solid fa-toggle-on"></i> PayPal Mode</label>
        <select name="paypal_mode">
          <option value="sandbox" <?= ($settings['paypal_mode'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' ?>>
            Sandbox (Testing)
          </option>
          <option value="live" <?= ($settings['paypal_mode'] ?? '') === 'live' ? 'selected' : '' ?>>
            Live (Production)
          </option>
        </select>
      </div>
      <div class="field">
        <label><i class="fa-solid fa-dollar-sign"></i> Currency</label>
        <select name="currency">
          <?php foreach (['USD', 'EUR', 'GBP', 'MAD'] as $cur): ?>
          <option value="<?= $cur ?>" <?= ($settings['currency'] ?? 'USD') === $cur ? 'selected' : '' ?>><?= $cur ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field full">
        <label><i class="fa-solid fa-key"></i> PayPal Client ID</label>
        <input type="text" name="paypal_client_id"
               value="<?= e($settings['paypal_client_id'] ?? '') ?>"
               placeholder="AXxx…">
        <small>Found in your PayPal Developer Dashboard → My Apps</small>
      </div>
      <div class="field full">
        <label><i class="fa-solid fa-lock"></i> PayPal Secret</label>
        <input type="password" name="paypal_secret"
               value="<?= e($settings['paypal_secret'] ?? '') ?>"
               placeholder="EJxx…">
        <small>Keep this secret — never share publicly</small>
      </div>
    </div>
  </div>

  <!-- SUBMIT -->
  <div style="display:flex;gap:12px">
    <button type="submit" class="btn btn-primary">
      <i class="fa-solid fa-floppy-disk"></i> Save Settings
    </button>
    <a href="<?= url('admin') ?>" class="btn btn-ghost">Cancel</a>
  </div>
</form>

<?php include VIEWS_PATH . '/layout/admin-footer.php'; ?>
