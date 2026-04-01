<?php
require_once ROOT_PATH . '/models/Service.php';
require_once ROOT_PATH . '/models/Booking.php';
require_once ROOT_PATH . '/models/User.php';
require_once ROOT_PATH . '/models/Setting.php';

/**
 * AdminController — all admin dashboard pages.
 * Every method calls Auth::requireAdmin() first.
 */
class AdminController
{
    // ------------------------------------------------------------------
    // Dashboard
    // ------------------------------------------------------------------

    public function dashboard(array $params = []): void
    {
        Auth::requireAdmin();

        $kpis = [
            'total_services' => (int) Database::queryScalar('SELECT COUNT(*) FROM services'),
            'total_bookings' => BookingModel::totalCount(),
            'pending'        => BookingModel::pendingCount(),
            'revenue'        => BookingModel::totalRevenue(),
        ];

        view('admin/dashboard', [
            'pageTitle'     => 'Dashboard — Admin',
            'kpis'          => $kpis,
            'recentBookings'=> BookingModel::recent(5),
            'services'      => ServiceModel::allForAdmin(),
            'settings'      => SettingModel::all(),
        ]);
    }

    // ------------------------------------------------------------------
    // Services
    // ------------------------------------------------------------------

    public function services(array $params = []): void
    {
        Auth::requireAdmin();

        $search   = get('search');
        $category = get('category');
        $services = $search || $category
            ? ServiceModel::all($search, $category)
            : ServiceModel::allForAdmin();

        view('admin/services', [
            'pageTitle'  => 'Services — Admin',
            'services'   => $services,
            'categories' => ServiceModel::categories(),
            'search'     => $search,
            'category'   => $category,
            'settings'   => SettingModel::all(),
        ]);
    }

    public function addServiceForm(array $params = []): void
    {
        Auth::requireAdmin();
        view('admin/service-form', [
            'pageTitle' => 'Add Service — Admin',
            'service'   => null,
            'settings'  => SettingModel::all(),
        ]);
    }

    public function editServiceForm(array $params = []): void
    {
        Auth::requireAdmin();
        $id      = (int)($params['id'] ?? 0);
        $service = ServiceModel::find($id);

        if (!$service) {
            flash('error', 'Service not found.');
            redirect('/admin/services');
        }

        view('admin/service-form', [
            'pageTitle' => 'Edit Service — Admin',
            'service'   => $service,
            'settings'  => SettingModel::all(),
        ]);
    }

    public function createService(array $params = []): void
    {
        Auth::requireAdmin();
        CSRF::verify();

        $data = $this->collectServiceForm();
        $errors = $this->validateService($data);

        if ($errors) {
            flash('error', implode('<br>', $errors));
            redirect('/admin/services/add');
        }

        // Parse textarea list fields into JSON arrays
        $data['included']     = $this->parseListField(post('included'));
        $data['not_included'] = $this->parseListField(post('not_included'));
        $data['highlights']   = $this->parseHighlights(post('highlights'));

        $id = ServiceModel::create($data);
        flash('success', 'Service created successfully.');
        redirect('/admin/services/' . $id . '/edit');
    }

    public function updateService(array $params = []): void
    {
        Auth::requireAdmin();
        CSRF::verify();

        $id      = (int)($params['id'] ?? 0);
        $service = ServiceModel::find($id);

        if (!$service) {
            flash('error', 'Service not found.');
            redirect('/admin/services');
        }

        $data = $this->collectServiceForm();
        $errors = $this->validateService($data);

        if ($errors) {
            flash('error', implode('<br>', $errors));
            redirect('/admin/services/' . $id . '/edit');
        }

        $data['included']     = $this->parseListField(post('included'));
        $data['not_included'] = $this->parseListField(post('not_included'));
        $data['highlights']   = $this->parseHighlights(post('highlights'));
        $data['is_active']    = isset($_POST['is_active']) ? 1 : 0;

        ServiceModel::update($id, $data);
        flash('success', 'Service updated successfully.');
        redirect('/admin/services/' . $id . '/edit');
    }

    public function deleteService(array $params = []): void
    {
        Auth::requireAdmin();
        CSRF::verify();

        $id = (int)($params['id'] ?? 0);

        // Check if bookings reference this service
        $count = (int) Database::queryScalar(
            "SELECT COUNT(*) FROM bookings WHERE service_id = ?", [$id]
        );
        if ($count > 0) {
            flash('error', "Cannot delete: {$count} booking(s) reference this service.");
            redirect('/admin/services');
        }

        ServiceModel::delete($id);
        flash('success', 'Service deleted.');
        redirect('/admin/services');
    }

    // ------------------------------------------------------------------
    // Bookings
    // ------------------------------------------------------------------

    public function bookings(array $params = []): void
    {
        Auth::requireAdmin();

        $search   = get('search');
        $status   = get('status');
        $bookings = BookingModel::allForAdmin($search, $status);

        view('admin/bookings', [
            'pageTitle' => 'Bookings — Admin',
            'bookings'  => $bookings,
            'search'    => $search,
            'status'    => $status,
            'settings'  => SettingModel::all(),
            'pending'   => BookingModel::pendingCount(),
            'total'     => BookingModel::totalCount(),
            'revenue'   => BookingModel::totalRevenue(),
        ]);
    }

    public function updateBookingStatus(array $params = []): void
    {
        Auth::requireAdmin();
        CSRF::verify();

        $id         = (int)($params['id'] ?? 0);
        $status     = post('status');
        $adminNotes = post('admin_notes');

        $booking = BookingModel::find($id);
        if (!$booking) {
            if (isAjax()) {
                jsonError('Booking not found.', 404);
            }
            flash('error', 'Booking not found.');
            redirect('/admin/bookings');
        }

        BookingModel::updateStatus($id, $status, $adminNotes);

        if (isAjax()) {
            jsonSuccess(['status' => $status], 'Booking status updated.');
        }

        flash('success', 'Booking status updated to ' . ucfirst($status) . '.');
        redirect('/admin/bookings');
    }

    public function deleteBooking(array $params = []): void
    {
        Auth::requireAdmin();
        CSRF::verify();

        $id = (int)($params['id'] ?? 0);
        BookingModel::delete($id);

        if (isAjax()) {
            jsonSuccess([], 'Booking deleted.');
        }

        flash('success', 'Booking deleted.');
        redirect('/admin/bookings');
    }

    // ------------------------------------------------------------------
    // Users
    // ------------------------------------------------------------------

    public function users(array $params = []): void
    {
        Auth::requireAdmin();

        view('admin/users', [
            'pageTitle' => 'Users — Admin',
            'users'     => UserModel::all(),
            'settings'  => SettingModel::all(),
        ]);
    }

    // ------------------------------------------------------------------
    // Settings
    // ------------------------------------------------------------------

    public function settings(array $params = []): void
    {
        Auth::requireAdmin();
        view('admin/settings', [
            'pageTitle' => 'Settings — Admin',
            'settings'  => SettingModel::all(),
        ]);
    }

    public function saveSettings(array $params = []): void
    {
        Auth::requireAdmin();
        CSRF::verify();

        $keys = [
            'business_name','whatsapp','email','address','currency',
            'paypal_mode','paypal_client_id','paypal_secret','site_tagline','meta_description',
        ];

        $data = [];
        foreach ($keys as $key) {
            $data[$key] = post($key);
        }

        SettingModel::setMany($data);
        flash('success', 'Settings saved successfully.');
        redirect('/admin/settings');
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function collectServiceForm(): array
    {
        return [
            'title'         => post('title'),
            'tagline'       => post('tagline'),
            'category'      => post('category'),
            'fa_icon'       => post('fa_icon'),
            'price'         => post('price'),
            'duration'      => post('duration'),
            'location'      => post('location'),
            'difficulty'    => post('difficulty'),
            'language'      => post('language'),
            'group_type'    => post('group_type'),
            'max_people'    => post('max_people'),
            'rating'        => post('rating'),
            'reviews'       => post('reviews'),
            'image'         => post('image'),
            'description'   => post('description'),
            'long_desc'     => post('long_desc'),
            'meeting_point' => post('meeting_point'),
            'cancel_policy' => post('cancel_policy'),
            'badge'         => post('badge'),
            'what_to_bring' => post('what_to_bring'),
            'min_age'       => post('min_age'),
            'sort_order'    => post('sort_order'),
        ];
    }

    private function validateService(array $data): array
    {
        $errors = [];
        if (empty($data['title']))   $errors[] = 'Title is required.';
        if (!is_numeric($data['price']) || $data['price'] < 0)
            $errors[] = 'Price must be a positive number.';
        if (empty($data['category'])) $errors[] = 'Category is required.';
        if (empty($data['image']))    $errors[] = 'Image URL is required.';
        if (empty($data['description'])) $errors[] = 'Short description is required.';
        return $errors;
    }

    /**
     * Convert a textarea list (one item per line) to a JSON-ready array.
     */
    private function parseListField(string $raw): array
    {
        return array_values(array_filter(
            array_map('trim', explode("\n", $raw))
        ));
    }

    /**
     * Parse highlights textarea (format: "icon|text" per line).
     */
    private function parseHighlights(string $raw): array
    {
        $result = [];
        foreach (explode("\n", $raw) as $line) {
            $line = trim($line);
            if (!$line) continue;
            if (str_contains($line, '|')) {
                [$icon, $text] = explode('|', $line, 2);
                $result[] = ['icon' => trim($icon), 'text' => trim($text)];
            } else {
                $result[] = ['icon' => 'fa-check', 'text' => $line];
            }
        }
        return $result;
    }
}
