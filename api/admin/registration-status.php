<?php
/**
 * API: Admin - Update Registration Status
 * PUT /api/admin/registration-status.php
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

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    json_response('error', 'Invalid request method', null, 405);
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['registration_id']) || empty($input['status_id'])) {
        json_response('error', 'Registration ID and status ID are required', null, 400);
    }
    
    // Initialize classes
    $db = new Database($conn);
    $registration = new Registration($db);
    
    // Update status
    $result = $registration->update_status($input['registration_id'], $input['status_id']);
    
    json_response('success', $result['message']);
    
} catch (Exception $e) {
    error_log('Update Status API Error: ' . $e->getMessage());
    json_response('error', 'An error occurred', null, 500);
}

?>
