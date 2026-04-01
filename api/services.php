<?php
/**
 * API: /api/services
 *
 * GET  /api/services           → list services (supports ?search= &category=)
 * GET  /api/services/:id       → single service
 * POST /api/services           → create service (admin)
 * PUT  /api/services/:id       → update service (admin)
 * DELETE /api/services/:id     → delete service (admin)
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/models/Service.php';

header('Content-Type: application/json; charset=utf-8');

$httpMethod = method();
$uri        = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Extract optional :id from the end of the URI
preg_match('#/api/services/?(\d+)?#', $uri, $m);
$id = isset($m[1]) ? (int)$m[1] : null;

switch ($httpMethod) {

    // -------------------------------------------------------------------
    case 'GET':
        if ($id !== null) {
            $service = ServiceModel::find($id);
            if (!$service) {
                jsonError('Service not found.', 404);
            }
            jsonSuccess(['service' => $service]);
        }

        $services   = ServiceModel::all(get('search'), get('category'));
        $categories = ServiceModel::categories();
        jsonSuccess([
            'services'   => $services,
            'categories' => $categories,
            'total'      => count($services),
        ]);
        break;

    // -------------------------------------------------------------------
    case 'POST':
        Auth::requireAdmin();     // 401/redirect if not admin
        CSRF::verify(true);

        $data = jsonBody();
        if (empty($data['title']) || empty($data['price'])) {
            jsonError('title and price are required.', 422);
        }

        $newId = ServiceModel::create($data);
        $service = ServiceModel::find($newId);
        jsonSuccess(['service' => $service], 'Service created.', 201);
        break;

    // -------------------------------------------------------------------
    case 'PUT':
        Auth::requireAdmin();
        CSRF::verify(true);

        if (!$id) jsonError('Service ID required.', 400);

        $data = jsonBody();
        ServiceModel::update($id, $data);
        $service = ServiceModel::find($id);
        jsonSuccess(['service' => $service], 'Service updated.');
        break;

    // -------------------------------------------------------------------
    case 'DELETE':
        Auth::requireAdmin();
        CSRF::verify(true);

        if (!$id) jsonError('Service ID required.', 400);
        ServiceModel::delete($id);
        jsonSuccess([], 'Service deleted.');
        break;

    default:
        jsonError('Method not allowed.', 405);
}

// Override jsonSuccess to support custom status codes in this file
function jsonSuccess(mixed $data = [], string $message = 'OK', int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['success' => true, 'message' => $message], (array)$data));
    exit;
}
