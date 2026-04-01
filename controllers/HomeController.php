<?php
require_once ROOT_PATH . '/models/Service.php';
require_once ROOT_PATH . '/models/Setting.php';

/**
 * HomeController — serves the public-facing homepage.
 */
class HomeController
{
    public function index(array $params = []): void
    {
        // Load a few featured services for the homepage cards
        $services = ServiceModel::all();
        $featured = array_slice($services, 0, 4);
        $settings = SettingModel::all();

        view('frontend/home', [
            'featured'  => $featured,
            'settings'  => $settings,
            'pageTitle' => 'Tarik Belasri — Your Private Tour Guide in Morocco',
        ]);
    }
}
