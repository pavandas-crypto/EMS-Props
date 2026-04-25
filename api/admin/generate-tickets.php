<?php
/**
 * API: Admin - Generate Tickets
 * POST /api/admin/generate-tickets.php
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Ticket.php';

init_session();
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response('error', 'Invalid request method', null, 405);
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['event_id'])) {
        json_response('error', 'Event ID is required', null, 400);
    }
    
    // Initialize classes
    $db = new Database($conn);
    $ticket = new Ticket($db);
    
    // Generate bulk tickets
    $result = $ticket->generate_bulk_tickets($input['event_id']);
    
    if ($result['success']) {
        json_response('success', $result['message'], [
            'generated' => $result['generated'],
            'failed' => $result['failed']
        ]);
    } else {
        json_response('error', $result['message'], null, 400);
    }
    
} catch (Exception $e) {
    error_log('Generate Tickets API Error: ' . $e->getMessage());
    json_response('error', 'An error occurred', null, 500);
}

?>
