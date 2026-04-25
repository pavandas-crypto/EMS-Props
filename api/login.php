<?php
/**
 * API: User Login
 * POST /api/login.php
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';

init_session();

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response('error', 'Invalid request method', null, 405);
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate input
    if (empty($input['email']) || empty($input['password'])) {
        json_response('error', 'Email and password are required', null, 400);
    }
    
    // Rate limiting
    if (!check_rate_limit('login_' . $input['email'], 5, 300)) {
        json_response('error', 'Too many login attempts. Please try again later.', null, 429);
    }
    
    // Initialize classes
    $db = new Database($conn);
    $auth = new Auth($db);
    
    // Attempt login
    $result = $auth->login($input['email'], $input['password']);
    
    if ($result['success']) {
        json_response('success', $result['message'], ['user' => $result['user']]);
    } else {
        json_response('error', $result['message'], null, 401);
    }
    
} catch (Exception $e) {
    error_log('Login API Error: ' . $e->getMessage());
    json_response('error', 'An error occurred', null, 500);
}

?>
