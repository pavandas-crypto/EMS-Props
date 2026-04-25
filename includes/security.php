<?php
/**
 * Security Functions
 */

/**
 * Initialize session
 */
function init_session() {
    session_name(SESSION_NAME);
    session_start();
    
    // Session timeout
    if (isset($_SESSION[SESSION_NAME]['last_activity'])) {
        if (time() - $_SESSION[SESSION_NAME]['last_activity'] > SESSION_TIMEOUT) {
            session_destroy();
            header('Location: /eve/admin/login.php');
            exit;
        }
    }
    
    $_SESSION[SESSION_NAME]['last_activity'] = time();
}

/**
 * Require login
 */
function require_login() {
    if (!is_logged_in()) {
        header('Location: /eve/admin/login.php');
        exit;
    }
}

/**
 * Require admin role
 */
function require_admin() {
    require_login();
    
    if (!has_role(ROLE_ADMIN)) {
        http_response_code(403);
        die('Access Denied');
    }
}

/**
 * Require verifier role
 */
function require_verifier() {
    require_login();
    
    if (!has_role(ROLE_VERIFIER)) {
        http_response_code(403);
        die('Access Denied');
    }
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION[SESSION_NAME]['csrf_token'])) {
        $_SESSION[SESSION_NAME]['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION[SESSION_NAME]['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    return isset($_SESSION[SESSION_NAME]['csrf_token']) && 
           hash_equals($_SESSION[SESSION_NAME]['csrf_token'], $token);
}

/**
 * Sanitize SQL input
 */
function sanitize_sql($conn, $data) {
    if (is_array($data)) {
        return array_map(function($item) use ($conn) {
            return $conn->real_escape_string($item);
        }, $data);
    }
    return $conn->real_escape_string($data);
}

/**
 * Log activity
 */
function log_activity($action, $details = '', $user_id = null) {
    $user_id = $user_id ?? get_current_user_id();
    $log_file = __DIR__ . '/../logs/activity_' . date('Y-m-d') . '.log';
    
    if (!is_dir(dirname($log_file))) {
        mkdir(dirname($log_file), 0755, true);
    }
    
    $log_message = date('Y-m-d H:i:s') . ' | User: ' . $user_id . ' | Action: ' . $action . ' | Details: ' . $details . "\n";
    
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

/**
 * Rate limiting (simple)
 */
function check_rate_limit($identifier, $limit = 5, $window = 60) {
    $key = 'rate_limit_' . $identifier;
    
    if (!isset($_SESSION[SESSION_NAME][$key])) {
        $_SESSION[SESSION_NAME][$key] = [];
    }
    
    $now = time();
    $_SESSION[SESSION_NAME][$key] = array_filter(
        $_SESSION[SESSION_NAME][$key],
        function($time) use ($now, $window) {
            return ($now - $time) < $window;
        }
    );
    
    if (count($_SESSION[SESSION_NAME][$key]) >= $limit) {
        return false;
    }
    
    $_SESSION[SESSION_NAME][$key][] = $now;
    return true;
}

/**
 * Sanitize filename
 */
function sanitize_filename($filename) {
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    return substr($filename, 0, 255);
}

/**
 * Validate password strength
 */
function validate_password_strength($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }
    
    return $errors;
}

?>
