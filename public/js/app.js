/**
 * Marrakech Guide — Frontend JavaScript
 */

// ── CSRF helper ──────────────────────────────────────────────
function getCsrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

// ── API helpers ──────────────────────────────────────────────
async function apiPost(url, data) {
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
  const container = document.getElementById('toast-container');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = `toast-msg ${type}`;
  const icon = type === 'success' ? 'fa-circle-check' : type === 'error' ? 'fa-circle-exclamation' : 'fa-circle-info';
  toast.innerHTML = `<i class="fa-solid ${icon}"></i> ${message}`;
  container.appendChild(toast);

  setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateX(20px)'; toast.style.transition = '.3s'; }, 3500);
  setTimeout(() => toast.remove(), 4000);
}

// ── Booking modal ────────────────────────────────────────────
let _bookingPrice = 0;
let _blockedDates = [];

function openBookingModal(serviceId, serviceName, price) {
  _bookingPrice = price;

  document.getElementById('bookingServiceId').value = serviceId;
  document.getElementById('modalServiceName').textContent = serviceName;
  document.getElementById('priceUnit').textContent = '$' + price.toFixed(2);

  updatePriceSummary();

  const modal = document.getElementById('bookingModal');
  if (modal) {
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
    // Reset form
    const form = document.getElementById('bookingForm');
    const success = document.getElementById('bookingSuccess');
    if (form) form.style.display = '';
    if (success) success.style.display = 'none';
    // Set date min to today
    const dateInput = document.getElementById('bDate');
    if (dateInput) dateInput.min = new Date().toISOString().split('T')[0];
  }
}

function closeBookingModal() {
  const modal = document.getElementById('bookingModal');
  if (modal) {
    modal.classList.remove('open');
    document.body.style.overflow = '';
  }
}

// Load blocked dates from API
async function loadBlockedDates() {
  try {
    const today = new Date().toISOString().split('T')[0];
    const future = new Date(Date.now() + 180 * 86400000).toISOString().split('T')[0];
    const appBase = (document.querySelector('meta[name="app-url"]')?.content || '').replace(/\/$/, '');
    const r = await fetch(`${appBase}/api/calendar?from=${today}&to=${future}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    if (!r.ok) return;
    const data = await r.json();
    _blockedDates = data.blocked_list || [];
  } catch (e) {
    // silently fail — just means all dates are available
  }
}

function isDateBlocked(dateStr) {
  return _blockedDates.includes(dateStr);
}

function updatePriceSummary() {
  const people = parseInt(document.getElementById('bPeople')?.value) || 1;
  const total  = _bookingPrice * people;

  const guestEl = document.getElementById('priceGuests');
  const totalEl = document.getElementById('priceTotal');
  if (guestEl) guestEl.textContent = people;
  if (totalEl) totalEl.textContent = '$' + total.toFixed(2);
}

// ── DOM Ready ────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {

  // Set min date for booking input
  const dateInput = document.getElementById('bDate');
  if (dateInput) {
    dateInput.min = new Date().toISOString().split('T')[0];
    dateInput.addEventListener('change', function() {
      if (!this.value) return;
      if (isDateBlocked(this.value)) {
        showToast('This date is unavailable for bookings. Please choose another date.', 'error');
        this.value = '';
        return;
      }
    });
  }

  // People count → update total price
  const peopleInput = document.getElementById('bPeople');
  if (peopleInput) peopleInput.addEventListener('input', updatePriceSummary);

  // Booking form submission
  const bookingForm = document.getElementById('bookingForm');
  if (bookingForm) {
    bookingForm.addEventListener('submit', async function(e) {
      e.preventDefault();

      const btn = document.getElementById('bookSubmitBtn');
      if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing…'; }

      const formData = {
        service_id:      document.getElementById('bookingServiceId')?.value,
        customer_name:   document.getElementById('bName')?.value,
        customer_email:  document.getElementById('bEmail')?.value,
        customer_phone:  document.getElementById('bPhone')?.value,
        booking_date:    document.getElementById('bDate')?.value,
        people:          document.getElementById('bPeople')?.value,
        notes:           document.getElementById('bNotes')?.value,
      };

      // Client-side validation
      if (!formData.booking_date) {
        showToast('Please select a tour date.', 'error');
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-calendar-check"></i> Confirm Booking Request'; }
        return;
      }

      if (isDateBlocked(formData.booking_date)) {
        showToast('This date is not available. Please select another date.', 'error');
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-calendar-check"></i> Confirm Booking Request'; }
        return;
      }

      try {
        // Construct correct API URL respecting base path
        const base = document.querySelector('meta[name="app-url"]')?.content || '';
        const apiUrl = base ? `${base}/api/bookings` : '/api/bookings';

        const result = await apiPost(apiUrl, formData);

        if (result.success) {
          bookingForm.style.display = 'none';
          const successDiv = document.getElementById('bookingSuccess');
          const refEl = document.getElementById('bookingRef');
          if (refEl) refEl.textContent = result.reference || '';
          if (successDiv) successDiv.style.display = 'block';
          showToast('Booking submitted successfully!', 'success');
        } else {
          showToast(result.error || 'Booking failed. Please try again.', 'error');
          if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-calendar-check"></i> Confirm Booking Request'; }
        }
      } catch (err) {
        showToast('Network error. Please try again.', 'error');
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-calendar-check"></i> Confirm Booking Request'; }
      }
    });
  }

  // Load blocked dates if booking form exists
  if (document.getElementById('bookingForm')) {
    loadBlockedDates();
  }

  // Close modal on overlay click
  const overlay = document.getElementById('bookingModal');
  if (overlay) {
    overlay.addEventListener('click', function(e) {
      if (e.target === this) closeBookingModal();
    });
  }

  // Keyboard: Escape closes modal
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeBookingModal();
  });

  // Flash messages: auto-dismiss after 4s
  document.querySelectorAll('.flash-toast').forEach(el => {
    setTimeout(() => { el.style.transition = '.4s'; el.style.opacity = '0'; }, 3500);
    setTimeout(() => el.remove(), 4000);
  });

  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', function(e) {
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  // Mobile bottom nav: highlight active item
  const path = window.location.pathname;
  document.querySelectorAll('.bottom-nav-item').forEach(item => {
    const href = (item.getAttribute('href') || '').replace(/.*\/client-guide/, '');
    if (href && (path.endsWith(href) || (href === '/' && (path === '/' || path.endsWith('/client-guide') || path.endsWith('/client-guide/'))))) {
      item.classList.add('active');
    }
  });

});
