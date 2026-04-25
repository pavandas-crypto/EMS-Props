<?php
/**
 * API: Get Verifier Events
 * GET /api/verifier-events.php?verifier_id=1
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../classes/Database.php';

init_session();
require_admin();

try {
    $verifier_id = (int)($_GET['verifier_id'] ?? 0);

    if ($verifier_id <= 0) {
        json_response('error', 'Verifier ID is required', null, 400);
    }

    $db = new Database($conn);
    $assignments = $db->get_results("SELECT event_id FROM verifier_events WHERE verifier_id = $verifier_id");

    json_response('success', 'Assignments retrieved', $assignments);

} catch (Exception $e) {
    error_log('Verifier Events API Error: ' . $e->getMessage());
    json_response('error', 'An error occurred', null, 500);
}

?>