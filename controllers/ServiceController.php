<?php
require_once ROOT_PATH . '/models/Service.php';
require_once ROOT_PATH . '/models/Setting.php';

/**
 * ServiceController — browsing experiences (public).
 */
class ServiceController
{
    /**
     * GET /services — list all experiences with filtering.
     */
    public function index(array $params = []): void
    {
        $search   = get('search');
        $category = get('category');

        $services   = ServiceModel::all($search, $category);
        $categories = ServiceModel::categories();
        $settings   = SettingModel::all();

        view('frontend/services', [
            'services'   => $services,
            'categories' => $categories,
            'search'     => $search,
            'category'   => $category,
            'settings'   => $settings,
            'pageTitle'  => 'All Experiences — Marrakech Guide',
        ]);
    }

    /**
     * GET /services/:id — single experience detail page.
     */
    public function show(array $params = []): void
    {
        $id      = (int)($params['id'] ?? 0);
        $service = ServiceModel::find($id);

        if (!$service) {
            http_response_code(404);
            view('errors/404');
            return;
        }

        $related  = ServiceModel::related($id, $service['category']);
        $settings = SettingModel::all();

        view('frontend/service-detail', [
            'service'  => $service,
            'related'  => $related,
            'settings' => $settings,
            'pageTitle'=> e($service['title']) . ' — Marrakech Guide',
        ]);
    }
}
