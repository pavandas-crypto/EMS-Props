<?php
/**
 * API: Get Event Details
 * GET /api/event-details.php?event_id=1
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Event.php';
require_once __DIR__ . '/../classes/CustomField.php';

try {
    if (empty($_GET['event_id'])) {
        json_response('error', 'Event ID is required', null, 400);
    }
    
    $event_id = (int)$_GET['event_id'];
    
    // Initialize classes
    $db = new Database($conn);
    $event = new Event($db);
    $custom_field = new CustomField($db);
    
    // Get event details
    $event_data = $event->get_event($event_id);
    
    if (!$event_data) {
        json_response('error', ERROR_INVALID_EVENT, null, 404);
    }
    
    // Get custom fields for event
    $fields = $custom_field->get_event_fields($event_id);
    
    json_response('success', 'Event details retrieved', [
        'event' => $event_data,
        'custom_fields' => $fields
    ]);
    
} catch (Exception $e) {
    error_log('Event Details API Error: ' . $e->getMessage());
    json_response('error', 'An error occurred', null, 500);
}

?>
