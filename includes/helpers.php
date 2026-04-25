<?php
/**
 * Helper Functions
 */

/**
 * Sanitize input
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash password
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate unique ID
 */
function generate_unique_id() {
    return bin2hex(random_bytes(8));
}

/**
 * Generate pass number
 */
function generate_pass_number() {
    return 'PASS' . strtoupper(bin2hex(random_bytes(4)));
}

/**
 * Generate QR code string
 */
function generate_qr_code_string($pass_id, $event_id) {
    return 'QR_' . $event_id . '_' . $pass_id . '_' . time();
}

/**
 * Send JSON response
 */
function json_response($status, $message, $data = null, $http_code = 200) {
    header('Content-Type: application/json');
    http_response_code($http_code);
    
    $response = [
        'status' => $status,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit;
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION[SESSION_NAME]['user_id']);
}

/**
 * Check user role
 */
function has_role($required_role) {
    if (!is_logged_in()) {
        return false;
    }
    return $_SESSION[SESSION_NAME]['role'] === $required_role;
}

/**
 * Get current user ID
 */
function get_current_user_id() {
    return $_SESSION[SESSION_NAME]['user_id'] ?? null;
}

/**
 * Get current user role
 */
function get_current_user_role() {
    return $_SESSION[SESSION_NAME]['role'] ?? null;
}

/**
 * Format datetime
 */
function format_datetime($datetime, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($datetime));
}

/**
 * Format date
 */
function format_date($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Redirect with message
 */
function redirect($url, $message = null, $type = 'info') {
    if ($message) {
        $_SESSION['flash_message'] = ['message' => $message, 'type' => $type];
    }
    header('Location: ' . $url);
    exit;
}

/**
 * Get flash message
 */
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Validate file upload
 */
function validate_file_upload($file, $allowed_types, $max_size = MAX_UPLOAD_SIZE) {
    if (!isset($file['tmp_name']) || !$file['tmp_name']) {
        return ['valid' => false, 'error' => 'No file uploaded'];
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'Upload error: ' . $file['error']];
    }
    
    if ($file['size'] > $max_size) {
        return ['valid' => false, 'error' => 'File size exceeds limit'];
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        return ['valid' => false, 'error' => 'Invalid file type'];
    }
    
    return ['valid' => true];
}

/**
 * Save uploaded file
 */
function save_uploaded_file($file, $directory = UPLOAD_DIR) {
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
    
    $filename = uniqid() . '_' . basename($file['name']);
    $filepath = $directory . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'path' => $filepath];
    }
    
    return ['success' => false, 'error' => 'Failed to save file'];
}

/**
 * Paginate results
 */
function paginate($total_items, $items_per_page = 10, $current_page = 1) {
    $total_pages = ceil($total_items / $items_per_page);
    $offset = ($current_page - 1) * $items_per_page;
    
    return [
        'total_items' => $total_items,
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'items_per_page' => $items_per_page,
        'offset' => $offset,
        'has_prev' => $current_page > 1,
        'has_next' => $current_page < $total_pages
    ];
}

/**
 * Validate JSON
 */
function is_valid_json($string) {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

/**
 * Check rate limit
 */
function check_rate_limit($key, $max_attempts = 5, $window_seconds = 300) {
    $now = time();
    $window_start = $now - $window_seconds;

    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [];
    }

    // Clean old entries
    $_SESSION['rate_limit'][$key] = array_filter(
        $_SESSION['rate_limit'][$key] ?? [],
        function($timestamp) use ($window_start) {
            return $timestamp > $window_start;
        }
    );

    // Check if under limit
    if (count($_SESSION['rate_limit'][$key] ?? []) < $max_attempts) {
        $_SESSION['rate_limit'][$key][] = $now;
        return true;
    }

    return false;
}

?>
