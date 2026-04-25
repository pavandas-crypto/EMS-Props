<?php
/**
 * API: Admin - Create Event
 * POST /api/admin/event-create.php
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Event.php';

init_session();
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response('error', 'Invalid request method', null, 405);
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required = ['event_name', 'start_date_time', 'end_date_time'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            json_response('error', ucfirst($field) . ' is required', null, 400);
        }
    }
    
    // Prepare data
    $event_data = [
        'event_name' => $input['event_name'],
        'description' => $input['description'] ?? '',
        'start_date_time' => $input['start_date_time'],
        'end_date_time' => $input['end_date_time'],
        'address' => $input['address'] ?? '',
        'event_for' => $input['event_for'] ?? 'all',
        'image_id' => $input['image_id'] ?? null
    ];
    
    // Initialize classes
    $db = new Database($conn);
    $event = new Event($db);
    
    // Create event
    $result = $event->create($event_data);
    
    if ($result['success']) {
        json_response('success', $result['message'], ['event_id' => $result['event_id']]);
    } else {
        json_response('error', $result['message'], null, 400);
    }
    
} catch (Exception $e) {
    error_log('Event Create API Error: ' . $e->getMessage());
    json_response('error', 'An error occurred', null, 500);
}

?>
