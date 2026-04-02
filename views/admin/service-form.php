<?php
$adminPage  = 'services';
$pageIcon   = 'fa-compass';
$isEdit     = !empty($service);
$pendingCount = BookingModel::pendingCount();
$pageTitle  = $isEdit ? 'Edit: ' . e($service['title']) : 'Add New Experience';

// Decode JSON fields
$highlights  = $isEdit ? decodeJson($service['highlights'] ?? '') : [];
$included    = $isEdit ? decodeJson($service['included'] ?? '') : [];
$notIncluded = $isEdit ? decodeJson($service['not_included'] ?? '') : [];

// Reconstruct textarea formats
$hlText  = implode("\n", array_map(fn($h) => ($h['icon'] ?? 'fa-check') . '|' . ($h['text'] ?? ''), $highlights));
$incText = implode("\n", $included);
$notText = implode("\n", $notIncluded);

$action = $isEdit
  ? url('admin/services/' . $service['id'] . '/update')
  : url('admin/services/add');

include VIEWS_PATH . '/layout/admin-header.php';
?>

<form method="POST" action="<?= $action ?>">
  <input type="hidden" name="csrf_token" value="<?= e(CSRF::token()) ?>">

  <!-- BASIC INFO -->
  <div class="form-panel">
    <div class="form-panel-head">
      <h3><i class="fa-solid fa-circle-info"></i> Basic Information</h3>
      <p>Core details about this experience</p>
    </div>
    <div class="form-grid">
      <div class="field full">
        <label><i class="fa-solid fa-heading"></i> Title <span style="color:#dc2626">*</span></label>
        <input type="text" name="title" value="<?= e($service['title'] ?? '') ?>"
               placeholder="e.g. Medina Walking Tour" required>
      </div>
      <div class="field full">
        <label><i class="fa-solid fa-quote-left"></i> Tagline</label>
        <input type="text" name="tagline" value="<?= e($service['tagline'] ?? '') ?>"
               placeholder="A short compelling description (1 line)">
      </div>
      <div class="field">
        <label><i class="fa-solid fa-tag"></i> Category <span style="color:#dc2626">*</span></label>
        <select name="category" required>
          <?php foreach (['Cultural','Adventure','Food & Culture','Wellness','Nature','Private'] as $cat): ?>
          <option value="<?= $cat ?>" <?= ($service['category'] ?? '') === $cat ? 'selected' : '' ?>>
            <?= $cat ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label><i class="fa-solid fa-icons"></i> Font Awesome Icon</label>
        <input type="text" name="fa_icon" value="<?= e($service['fa_icon'] ?? 'fa-compass') ?>"
               placeholder="fa-compass">
        <small>E.g. fa-mosque, fa-mountain, fa-utensils</small>
      </div>
      <div class="field">
        <label><i class="fa-solid fa-certificate"></i> Badge Label</label>
        <input type="text" name="badge" value="<?= e($service['badge'] ?? '') ?>"
               placeholder="Best Seller, Most Popular, New…">
      </div>
      <div class="field">
        <label><i class="fa-solid fa-sort-numeric-up"></i> Sort Order</label>
        <input type="number" name="sort_order" value="<?= (int)($service['sort_order'] ?? 0) ?>"
               min="0" placeholder="0">
        <small>Lower number appears first</small>
      </div>
    </div>
  </div>

  <!-- PRICING & LOGISTICS -->
  <div class="form-panel">
    <div class="form-panel-head">
      <h3><i class="fa-solid fa-dollar-sign"></i> Pricing & Logistics</h3>
    </div>
    <div class="form-grid">
      <div class="field">
        <label><i class="fa-solid fa-dollar-sign"></i> Price per Person (USD) <span style="color:#dc2626">*</span></label>
        <input type="number" name="price" value="<?= e($service['price'] ?? '') ?>"
               min="0" step="0.01" placeholder="45.00" required>
      </div>
      <div class="field">
        <label><i class="fa-solid fa-clock"></i> Duration</label>
        <input type="text" name="duration" value="<?= e($service['duration'] ?? '') ?>"
               placeholder="3 hours, Full day (8h)…">
      </div>
      <div class="field">
        <label><i class="fa-solid fa-location-dot"></i> Location</label>
        <input type="text" name="location" value="<?= e($service['location'] ?? '') ?>"
               placeholder="Marrakech Medina">
      </div>
      <div class="field">
        <label><i class="fa-solid fa-gauge"></i> Difficulty</label>
        <select name="difficulty">
          <?php foreach (['Easy','Moderate','Challenging','Flexible'] as $d): ?>
          <option value="<?= $d ?>" <?= ($service['difficulty'] ?? 'Easy') === $d ? 'selected' : '' ?>><?= $d ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label><i class="fa-solid fa-language"></i> Languages</label>
        <input type="text" name="language" value="<?= e($service['language'] ?? 'English, French') ?>"
               placeholder="English, French, Arabic">
      </div>
      <div class="field">
        <label><i class="fa-solid fa-users"></i> Max People</label>
        <input type="number" name="max_people" value="<?= (int)($service['max_people'] ?? 12) ?>"
               min="1" max="99">
      </div>
      <div class="field">
        <label><i class="fa-solid fa-star"></i> Rating (0–5)</label>
        <input type="number" name="rating" value="<?= e($service['rating'] ?? '5.00') ?>"
               min="0" max="5" step="0.01" placeholder="4.90">
      </div>
      <div class="field">
        <label><i class="fa-solid fa-comments"></i> Number of Reviews</label>
        <input type="number" name="reviews" value="<?= (int)($service['reviews'] ?? 0) ?>"
               min="0" placeholder="0">
      </div>
      <div class="field">
        <label><i class="fa-solid fa-child"></i> Minimum Age</label>
        <input type="text" name="min_age" value="<?= e($service['min_age'] ?? '') ?>"
               placeholder="8 years+, All ages">
      </div>

      <!-- Tour Type -->
      <div class="field full">
        <label><i class="fa-solid fa-users"></i> Tour Type</label>
        <div class="mode-cards">
          <label class="mode-card">
            <input type="radio" name="group_type" value="group" <?= ($service['group_type'] ?? 'group') === 'group' ? 'checked' : '' ?>>
            <div>
              <strong>Group Tour</strong>
              <small>Multiple guests share the experience</small>
            </div>
          </label>
          <label class="mode-card">
            <input type="radio" name="group_type" value="private" <?= ($service['group_type'] ?? '') === 'private' ? 'checked' : '' ?>>
            <div>
              <strong>Private Tour</strong>
              <small>Exclusive for the booking group</small>
            </div>
          </label>
        </div>
      </div>

      <!-- Active toggle (edit only) -->
      <?php if ($isEdit): ?>
      <div class="field full" style="display:flex;align-items:center;gap:12px">
        <input type="checkbox" name="is_active" id="isActive" value="1"
               <?= ($service['is_active'] ?? 1) ? 'checked' : '' ?>
               style="width:auto;accent-color:var(--gold)">
        <label for="isActive" style="text-transform:none;font-size:.9rem;cursor:pointer">
          <i class="fa-solid fa-eye" style="color:var(--gold)"></i>
          Active — visible on the public website
        </label>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- CONTENT -->
  <div class="form-panel">
    <div class="form-panel-head">
      <h3><i class="fa-solid fa-align-left"></i> Descriptions</h3>
    </div>
    <div class="form-grid">
      <div class="field full">
        <label><i class="fa-solid fa-image"></i> Image URL <span style="color:#dc2626">*</span></label>
        <input type="url" name="image" value="<?= e($service['image'] ?? '') ?>"
               placeholder="https://images.unsplash.com/…" required>
        <small>Use a high-quality landscape image URL</small>
      </div>
      <div class="field full">
        <label><i class="fa-solid fa-align-left"></i> Short Description <span style="color:#dc2626">*</span></label>
        <textarea name="description" rows="2" placeholder="One-sentence overview…" required><?= e($service['description'] ?? '') ?></textarea>
      </div>
      <div class="field full">
        <label><i class="fa-solid fa-file-lines"></i> Full Description</label>
        <textarea name="long_desc" rows="8" placeholder="Detailed multi-paragraph description. Use blank lines to separate paragraphs."><?= e($service['long_desc'] ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <!-- DETAILS -->
  <div class="form-panel">
    <div class="form-panel-head">
      <h3><i class="fa-solid fa-list-check"></i> Tour Details</h3>
    </div>
    <div class="form-grid">
      <div class="field full">
        <label><i class="fa-solid fa-star"></i> Highlights</label>
        <textarea name="highlights" rows="5" placeholder="icon|Text — one per line&#10;fa-mosque|Visit historic mosques&#10;fa-shopping-bag|Navigate the souks"><?= e($hlText) ?></textarea>
        <small>Format: <code>icon-class|Highlight text</code> — one per line</small>
      </div>
      <div class="field">
        <label><i class="fa-solid fa-circle-check" style="color:var(--success)"></i> What's Included</label>
        <textarea name="included" rows="5" placeholder="One item per line&#10;Professional guide&#10;Bottled water"><?= e($incText) ?></textarea>
        <small>One item per line</small>
      </div>
      <div class="field">
        <label><i class="fa-solid fa-circle-xmark" style="color:var(--danger)"></i> Not Included</label>
        <textarea name="not_included" rows="5" placeholder="One item per line&#10;Meals&#10;Personal expenses"><?= e($notText) ?></textarea>
        <small>One item per line</small>
      </div>
      <div class="field full">
        <label><i class="fa-solid fa-location-dot"></i> Meeting Point</label>
        <input type="text" name="meeting_point" value="<?= e($service['meeting_point'] ?? '') ?>"
               placeholder="Jemaa el-Fna Square, near the Café de France">
      </div>
      <div class="field">
        <label><i class="fa-solid fa-shield-halved"></i> Cancellation Policy</label>
        <textarea name="cancel_policy" rows="2" placeholder="Free cancellation up to 24 hours before."><?= e($service['cancel_policy'] ?? '') ?></textarea>
      </div>
      <div class="field">
        <label><i class="fa-solid fa-bag-shopping"></i> What to Bring</label>
        <input type="text" name="what_to_bring" value="<?= e($service['what_to_bring'] ?? '') ?>"
               placeholder="Comfortable shoes, sunscreen, hat">
      </div>
    </div>
  </div>

  <!-- SUBMIT -->
  <div style="display:flex;gap:12px;align-items:center">
    <button type="submit" class="btn btn-primary">
      <i class="fa-solid <?= $isEdit ? 'fa-floppy-disk' : 'fa-plus' ?>"></i>
      <?= $isEdit ? 'Save Changes' : 'Create Experience' ?>
    </button>
    <a href="<?= url('admin/services') ?>" class="btn btn-ghost">Cancel</a>
    <?php if ($isEdit): ?>
    <a href="<?= url('services/' . $service['id']) ?>" target="_blank" class="btn btn-ghost btn-sm">
      <i class="fa-solid fa-eye"></i> Preview
    </a>
    <?php endif; ?>
  </div>
</form>

<?php include VIEWS_PATH . '/layout/admin-footer.php'; ?>
