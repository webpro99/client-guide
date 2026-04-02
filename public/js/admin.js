/**
 * Marrakech Guide — Admin Panel JavaScript
 */

// ── CSRF helper ──────────────────────────────────────────────
function getCsrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

// ── API helpers ──────────────────────────────────────────────
async function apiPost(url, data = {}) {
  const r = await fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': getCsrfToken(),
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: JSON.stringify(data),
  });
  return r.json();
}

// ── Toast notifications ──────────────────────────────────────
function showToast(message, type = 'success') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    container.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  const icons = { success: 'fa-circle-check', error: 'fa-circle-exclamation', info: 'fa-circle-info' };
  toast.innerHTML = `<i class="fa-solid ${icons[type] || 'fa-circle-info'}"></i> ${message}`;
  toast.classList.add('show');
  container.appendChild(toast);

  setTimeout(() => { toast.classList.remove('show'); }, 3500);
  setTimeout(() => toast.remove(), 4000);
}

// ── DOM Ready ────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {

  // Auto-dismiss admin flash messages after 5 seconds
  document.querySelectorAll('.admin-flash').forEach(el => {
    setTimeout(() => {
      el.style.transition = '.5s';
      el.style.opacity = '0';
      el.style.maxHeight = '0';
      el.style.overflow = 'hidden';
      el.style.padding = '0';
    }, 5000);
    setTimeout(() => el.remove(), 5600);
  });

  // Image URL preview: if an image URL input changes, show a preview
  const imageInputs = document.querySelectorAll('input[name="image"]');
  imageInputs.forEach(input => {
    input.addEventListener('change', function() {
      const preview = document.getElementById('imagePreview');
      if (preview && this.value) {
        preview.src = this.value;
        preview.style.display = 'block';
      }
    });
  });

  // Confirm on delete forms
  document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', function(e) {
      if (!confirm(this.dataset.confirm || 'Are you sure?')) e.preventDefault();
    });
  });

});
