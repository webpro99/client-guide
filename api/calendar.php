<?php
/**
 * API: /api/calendar
 *
 * GET    /api/calendar            → list all blocked dates
 * GET    /api/calendar?from=&to=  → blocked dates in range
 * POST   /api/calendar            → block a date (admin)
 * DELETE /api/calendar/:date      → unblock a date (admin)
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/models/BlockedDate.php';

header('Content-Type: application/json; charset=utf-8');

$httpMethod = method();
$uri        = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Match /api/calendar/:date
preg_match('#/api/calendar/?([0-9]{4}-[0-9]{2}-[0-9]{2})?#', $uri, $m);
$dateParam = $m[1] ?? null;

switch (true) {

    // GET /api/calendar — list blocked dates
    case ($httpMethod === 'GET'):
        $from = get('from') ?: date('Y-m-01');
        $to   = get('to')   ?: date('Y-m-d', strtotime('+12 months'));
        $dates = BlockedDateModel::inRange($from, $to);
        jsonSuccess([
            'blocked_dates' => $dates,
            'blocked_list'  => array_column($dates, 'blocked_date'),
            'total'         => count($dates),
        ]);
        break;

    // POST /api/calendar — block a date (admin only)
    case ($httpMethod === 'POST'):
        Auth::requireAdmin();
        $data   = jsonBody();
        $date   = trim($data['date'] ?? '');
        $reason = trim($data['reason'] ?? '');

        if (!$date) {
            jsonError('date is required (Y-m-d format).', 422);
        }

        $d = DateTime::createFromFormat('Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date) {
            jsonError('Invalid date format. Use Y-m-d.', 422);
        }

        BlockedDateModel::block($date, $reason);
        jsonSuccess(['date' => $date, 'reason' => $reason], 'Date blocked successfully.');
        break;

    // DELETE /api/calendar/:date — unblock a date (admin only)
    case ($httpMethod === 'DELETE' && $dateParam):
        Auth::requireAdmin();
        $removed = BlockedDateModel::unblock($dateParam);
        if (!$removed) {
            jsonError('Date was not blocked.', 404);
        }
        jsonSuccess(['date' => $dateParam], 'Date unblocked successfully.');
        break;

    default:
        jsonError('Method not allowed.', 405);
}
