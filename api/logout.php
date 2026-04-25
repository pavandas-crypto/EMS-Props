<?php
/**
 * API: User Logout
 * POST /api/logout.php
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';

init_session();

try {
    if (!is_logged_in()) {
        json_response('error', 'Not logged in', null, 401);
    }
    
    // Initialize classes
    $db = new Database($conn);
    $auth = new Auth($db);
    
    // Logout
    $result = $auth->logout();
    
    json_response('success', $result['message']);
    
} catch (Exception $e) {
    error_log('Logout API Error: ' . $e->getMessage());
    json_response('error', 'An error occurred', null, 500);
}

?>
