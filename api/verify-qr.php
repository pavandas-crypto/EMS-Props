<?php
/**
 * API: Verify QR Code
 * POST /api/verify-qr.php
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Ticket.php';

init_session();
require_verifier(); // Only verifiers can scan

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response('error', 'Invalid request method', null, 405);
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['qr_code']) || empty($input['event_id'])) {
        json_response('error', 'QR code and event ID are required', null, 400);
    }
    
    // Initialize classes
    $db = new Database($conn);
    $ticket = new Ticket($db);
    
    // Verify QR code
    $result = $ticket->verify_qr_code($input['qr_code'], $input['event_id']);
    
    if ($result['valid']) {
        // Mark attendance
        $ticket->mark_attendance($result['data']['registration_id']);
        
        json_response('success', 'QR code verified', $result['data']);
    } else {
        json_response('error', $result['message'], null, 400);
    }
    
} catch (Exception $e) {
    error_log('Verify QR API Error: ' . $e->getMessage());
    json_response('error', 'An error occurred', null, 500);
}

?>
