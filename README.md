# Marrakech Guide — Installation & Setup

A full-stack Morocco tour guide booking platform built with **PHP (MVC)**, **MySQL**, and vanilla JavaScript.

---

## Requirements

| Requirement | Version |
|-------------|---------|
| PHP | 8.1 or newer |
| MySQL / MariaDB | 5.7+ / 10.3+ |
| Apache | 2.4+ with `mod_rewrite` enabled |
| Browser | Modern (Chrome, Firefox, Safari, Edge) |

---

## Quick Start

### 1 — Clone the Repository

```bash
git clone https://github.com/webpro99/client-guide.git
cd client-guide
```

Or download and extract the ZIP archive into your web server's document root.

---

### 2 — Create the Database

Open your MySQL client (phpMyAdmin, MySQL Workbench, or CLI) and run:

```sql
SOURCE /path/to/client-guide/database/schema.sql;
```

Or via CLI:

```bash
mysql -u root -p < database/schema.sql
```

This will:
- Create the `marrakech_guide` database
- Create all tables (`users`, `services`, `bookings`, `payments`, `settings`, `blocked_dates`)
- Insert default settings
- Insert the admin user (see credentials below)
- Insert 10 sample tour experiences
- Insert 5 sample bookings

**Default Admin Credentials:**
| Field    | Value                          |
|----------|-------------------------------|
| Email    | `admin@marrakechguide.com`    |
| Password | `Admin@1234`                  |

> ⚠️ **Change the admin password immediately after first login** via the database or by creating a new admin user.

---

### 3 — Configure the Application

Open `config/config.php` and update the following constants (or set environment variables):

```php
// App URL — no trailing slash
define('APP_URL', 'http://localhost/client-guide');

// Database
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'marrakech_guide');
define('DB_USER', 'root');
define('DB_PASS', 'your_password_here');

// Environment: 'development' or 'production'
define('APP_ENV', 'development');
```

**Using Environment Variables (recommended for production):**

```bash
export APP_URL="https://yourdomain.com"
export APP_ENV="production"
export DB_HOST="127.0.0.1"
export DB_NAME="marrakech_guide"
export DB_USER="dbuser"
export DB_PASS="securepassword"
export PAYPAL_CLIENT_ID="AXxx..."
export PAYPAL_SECRET="EJxx..."
export PAYPAL_MODE="live"
```

---

### 4 — Configure Apache

Ensure `mod_rewrite` is enabled:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

In your Apache VirtualHost, make sure `AllowOverride All` is set:

```apache
<Directory "/var/www/html/client-guide">
    AllowOverride All
</Directory>
```

The included `.htaccess` file routes all requests through `index.php` automatically.

**Example VirtualHost for a subdirectory setup:**
```apache
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /var/www/html
    <Directory "/var/www/html/client-guide">
        AllowOverride All
        Options -Indexes
    </Directory>
</VirtualHost>
```

**Example VirtualHost for a dedicated domain:**
```apache
<VirtualHost *:80>
    ServerName marrakechguide.com
    DocumentRoot /var/www/html/client-guide
    <Directory "/var/www/html/client-guide">
        AllowOverride All
        Options -Indexes
    </Directory>
</VirtualHost>
```

If using a dedicated domain, update `APP_URL` in `config/config.php`:
```php
define('APP_URL', 'https://marrakechguide.com');
```

---

### 5 — Configure PayPal (Optional)

To enable online payments, set up a PayPal Developer account:

1. Go to [developer.paypal.com](https://developer.paypal.com)
2. Create a new App under **My Apps & Credentials**
3. Copy the **Client ID** and **Secret**
4. In the Admin Panel → **Settings**, enter:
   - PayPal Mode: `sandbox` (testing) or `live` (production)
   - PayPal Client ID
   - PayPal Secret

---

## File Structure

```
client-guide/
├── index.php                # ← Application entry point (router)
├── .htaccess                # ← Apache URL rewriting rules
├── config/
│   └── config.php           # ← Database, PayPal, session config + autoloader
├── src/
│   ├── Auth.php             # Session auth, login, logout, role checks
│   ├── CSRF.php             # CSRF token generation & verification
│   ├── Database.php         # PDO singleton with query helpers
│   ├── Router.php           # Lightweight HTTP router
│   └── helpers.php          # Global helper functions (e, url, flash, view…)
├── models/
│   ├── Booking.php          # Bookings data access
│   ├── BlockedDate.php      # Calendar off-days management
│   ├── Payment.php          # PayPal payment records
│   ├── Service.php          # Tour experiences CRUD
│   ├── Setting.php          # Key-value settings store
│   └── User.php             # User accounts
├── controllers/
│   ├── AdminController.php  # Admin dashboard, services, bookings, calendar, settings
│   ├── AuthController.php   # Login, register, logout
│   ├── BookingController.php# User-facing bookings list
│   ├── HomeController.php   # Public homepage
│   ├── PaymentController.php# PayPal checkout flow
│   └── ServiceController.php# Public services list & detail
├── api/
│   ├── auth.php             # REST API: /api/auth/*
│   ├── bookings.php         # REST API: /api/bookings
│   ├── calendar.php         # REST API: /api/calendar
│   ├── payments.php         # REST API: /api/payments/*
│   └── services.php         # REST API: /api/services
├── views/
│   ├── layout/
│   │   ├── header.php       # Frontend page header
│   │   ├── footer.php       # Frontend page footer
│   │   ├── admin-header.php # Admin sidebar + topbar
│   │   └── admin-footer.php # Admin closing tags + JS
│   ├── partials/
│   │   └── booking-modal.php# Reusable booking form modal
│   ├── frontend/
│   │   ├── home.php         # Homepage with hero + featured tours
│   │   ├── services.php     # All experiences with filter
│   │   ├── service-detail.php # Single experience page
│   │   └── bookings.php     # User booking history
│   ├── auth/
│   │   ├── login.php        # Login form
│   │   └── register.php     # Registration form
│   ├── admin/
│   │   ├── dashboard.php    # KPIs + recent bookings
│   │   ├── bookings.php     # Bookings management
│   │   ├── calendar.php     # Off-days calendar management
│   │   ├── services.php     # Services list
│   │   ├── service-form.php # Add/edit service form
│   │   ├── settings.php     # Business settings
│   │   └── users.php        # User management
│   └── errors/
│       └── 404.php          # 404 error page
├── public/
│   ├── css/
│   │   ├── frontend.css     # Frontend styles
│   │   └── admin.css        # Admin panel styles
│   └── js/
│       ├── app.js           # Frontend JavaScript
│       └── admin.js         # Admin panel JavaScript
└── database/
    └── schema.sql           # Full MySQL schema + seed data
```

---

## URL Routes

### Public

| URL | Description |
|-----|-------------|
| `GET /` | Homepage with featured tours |
| `GET /services` | All experiences (with search & category filter) |
| `GET /services/:id` | Single experience detail + booking button |
| `GET /bookings` | Current user's / guest's booking history |

### Authentication

| URL | Description |
|-----|-------------|
| `GET /login` | Login form |
| `POST /login` | Process login |
| `GET /register` | Registration form |
| `POST /register` | Process registration |
| `GET /logout` | Logout and redirect |

### Admin Panel (requires admin role)

| URL | Description |
|-----|-------------|
| `GET /admin` | Dashboard with KPIs and recent bookings |
| `GET /admin/bookings` | All bookings with search & filter |
| `POST /admin/bookings/:id/status` | Update booking status |
| `POST /admin/bookings/:id/delete` | Delete a booking |
| `GET /admin/calendar` | Off-days calendar management |
| `POST /admin/calendar/block` | Block a date |
| `POST /admin/calendar/unblock` | Unblock a date |
| `GET /admin/services` | All experiences management |
| `GET /admin/services/add` | Add new experience form |
| `POST /admin/services/add` | Create new experience |
| `GET /admin/services/:id/edit` | Edit experience form |
| `POST /admin/services/:id/update` | Update experience |
| `POST /admin/services/:id/delete` | Delete experience |
| `GET /admin/settings` | Business settings form |
| `POST /admin/settings` | Save settings |
| `GET /admin/users` | User management |

### REST API

| URL | Method | Description |
|-----|--------|-------------|
| `/api/auth/login` | POST | Login |
| `/api/auth/logout` | POST | Logout |
| `/api/auth/register` | POST | Register |
| `/api/auth/me` | GET | Current user |
| `/api/services` | GET | List services |
| `/api/services/:id` | GET | Single service |
| `/api/services` | POST | Create service (admin) |
| `/api/services/:id` | PUT | Update service (admin) |
| `/api/services/:id` | DELETE | Delete service (admin) |
| `/api/bookings` | POST | Create booking |
| `/api/bookings` | GET | User's bookings |
| `/api/bookings/:id` | GET | Single booking |
| `/api/bookings/:id/status` | POST | Update status (admin) |
| `/api/bookings/:id` | DELETE | Delete booking (admin) |
| `/api/calendar` | GET | Get blocked dates |
| `/api/calendar` | POST | Block a date (admin) |
| `/api/calendar/:date` | DELETE | Unblock a date (admin) |
| `/api/payments/create` | POST | Initiate PayPal payment |
| `/api/payments/capture` | POST | Capture PayPal payment |

---

## Calendar — Off-Days Feature

Admins can block specific dates to prevent client bookings:

1. Navigate to **Admin → Calendar**
2. **Click any date** on the calendar to pre-fill the block form
3. Optionally add a **reason** (e.g. "Public holiday", "Personal day")
4. Click **Block This Date**

Blocked dates appear in red on the calendar. Clients attempting to book on blocked dates will see an error message.

**Via API:**
```bash
# Block a date
curl -X POST /api/calendar \
  -H "Content-Type: application/json" \
  -d '{"date":"2025-12-25","reason":"Christmas Day"}'

# Unblock a date
curl -X DELETE /api/calendar/2025-12-25

# Get all blocked dates
curl /api/calendar?from=2025-01-01&to=2025-12-31
```

---

## Creating Bookings (API)

```bash
curl -X POST /api/bookings \
  -H "Content-Type: application/json" \
  -d '{
    "service_id": 1,
    "customer_name": "Jane Doe",
    "customer_email": "jane@example.com",
    "customer_phone": "+1 555 123 4567",
    "booking_date": "2025-06-15",
    "people": 2,
    "notes": "Vegetarian diet required"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Booking created successfully.",
  "booking": { ... },
  "reference": "MG-20250615-A3F2B"
}
```

---

## Security Notes

- All database queries use **PDO prepared statements** (SQL injection protection)
- All output is escaped with `e()` (XSS protection)
- CSRF tokens protect all state-changing forms
- Passwords hashed with **bcrypt** (cost 12)
- Sessions regenerated on login (session fixation protection)
- HTTP-only, SameSite=Strict cookies in production
- Admin routes protected by `Auth::requireAdmin()`

---

## Troubleshooting

**404 on all pages except the homepage:**
- Ensure `mod_rewrite` is enabled: `a2enmod rewrite`
- Ensure `AllowOverride All` in your VirtualHost config
- Check that `.htaccess` is present in the project root

**Database connection error:**
- Verify DB credentials in `config/config.php`
- Ensure the MySQL service is running
- Run `schema.sql` if tables don't exist

**CSS / images not loading:**
- Verify `APP_URL` matches your actual URL (no trailing slash)
- Ensure the `public/` directory is accessible by the web server

**Admin login not working:**
- Default credentials: `admin@marrakechguide.com` / `Admin@1234`
- Verify the `users` table contains the admin row from `schema.sql`

---

## License

MIT — see LICENSE file for details.
