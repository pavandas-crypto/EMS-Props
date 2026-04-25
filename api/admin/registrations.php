<?php
/**
 * API: Admin - Get Registrations
 * GET /api/admin/registrations.php?event_id=1&page=1
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Registration.php';

init_session();
require_admin();

try {
    if (empty($_GET['event_id'])) {
        json_response('error', 'Event ID is required', null, 400);
    }
    
    $event_id = (int)$_GET['event_id'];
    $page = max(1, $_GET['page'] ?? 1);
    $limit = min(50, $_GET['limit'] ?? 10);
    $offset = ($page - 1) * $limit;
    
    // Initialize classes
    $db = new Database($conn);
    $registration = new Registration($db);
    
    // Get registrations
    $registrations = $registration->get_event_registrations($event_id, $offset, $limit);
    $total = $registration->get_registration_count($event_id);
    
    $pagination = paginate($total, $limit, $page);
    
    json_response('success', 'Registrations retrieved', [
        'registrations' => $registrations,
        'pagination' => $pagination
    ]);
    
} catch (Exception $e) {
    error_log('Registrations API Error: ' . $e->getMessage());
    json_response('error', 'An error occurred', null, 500);
}

?>
