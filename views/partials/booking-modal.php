<div class="modal-overlay" id="bookingModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
  <div class="modal">
    <div class="modal-body">
      <div class="m-head">
        <div>
          <h2 id="modalTitle" class="m-title">Book Your Experience</h2>
          <p id="modalServiceName" class="m-sub"></p>
        </div>
        <button class="modal-close" onclick="closeBookingModal()" aria-label="Close">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <form id="bookingForm" class="booking-form" novalidate>
        <input type="hidden" id="bookingServiceId" name="service_id">

        <div class="fg">
          <div class="f">
            <label for="bName"><i class="fa-solid fa-user"></i> Full Name</label>
            <input type="text" id="bName" name="customer_name" placeholder="Your full name" required>
          </div>
          <div class="f">
            <label for="bEmail"><i class="fa-solid fa-envelope"></i> Email</label>
            <input type="email" id="bEmail" name="customer_email" placeholder="you@example.com" required>
          </div>
          <div class="f">
            <label for="bPhone"><i class="fa-solid fa-phone"></i> Phone / WhatsApp</label>
            <input type="tel" id="bPhone" name="customer_phone" placeholder="+212 600 000 000" required>
          </div>
          <div class="f">
            <label for="bDate"><i class="fa-solid fa-calendar"></i> Tour Date</label>
            <input type="date" id="bDate" name="booking_date" required>
          </div>
          <div class="f">
            <label for="bPeople"><i class="fa-solid fa-users"></i> Number of People</label>
            <input type="number" id="bPeople" name="people" min="1" max="20" value="1" required>
          </div>
          <div class="f fl">
            <label for="bNotes"><i class="fa-solid fa-comment"></i> Special Requests (optional)</label>
            <textarea id="bNotes" name="notes" rows="3" placeholder="Dietary requirements, accessibility needs, special occasions…"></textarea>
          </div>
        </div>

        <div class="price-summary" id="priceSummary">
          <div class="p-row">
            <span id="priceLabel">Price per person</span>
            <span id="priceUnit">$0</span>
          </div>
          <div class="p-row">
            <span>Number of guests</span>
            <span id="priceGuests">1</span>
          </div>
          <div class="p-row tot">
            <span>Total</span>
            <span id="priceTotal">$0</span>
          </div>
        </div>

        <button type="submit" class="btn btn-gold btn-full" id="bookSubmitBtn">
          <i class="fa-solid fa-calendar-check"></i> Confirm Booking Request
        </button>
        <p style="font-size:.78rem;color:var(--muted);text-align:center;margin-top:10px">
          <i class="fa-solid fa-shield-halved"></i>
          No payment required now — we'll confirm your booking and send payment details.
        </p>
      </form>

      <div id="bookingSuccess" style="display:none;text-align:center;padding:20px 0">
        <div style="font-size:3rem;margin-bottom:16px">✅</div>
        <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.6rem;margin-bottom:8px">Booking Submitted!</h3>
        <p style="color:var(--muted);line-height:1.8;margin-bottom:6px">
          Your booking reference is: <strong id="bookingRef" style="color:var(--gold)"></strong>
        </p>
        <p style="color:var(--muted);font-size:.9rem;line-height:1.8;margin-bottom:24px">
          We'll confirm your reservation and send details to your email within a few hours.
        </p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
          <a href="<?= url('bookings') ?>" class="btn btn-primary">
            <i class="fa-solid fa-calendar-check"></i> View My Bookings
          </a>
          <button class="btn btn-ghost" onclick="closeBookingModal()">Close</button>
        </div>
      </div>
    </div>
  </div>
</div>
