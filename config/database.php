<?php
/**
 * Database Configuration
 * Connection settings for event management system
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'event_management');
define('DB_PORT', 3306);

// Database connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception('Database Connection Failed: ' . $conn->connect_error);
    }
    
    // Set charset to utf8mb4
    $conn->set_charset('utf8mb4');
    
} catch (Exception $e) {
    error_log('Database Error: ' . $e->getMessage());
    die('Unable to connect to database. Please try again later.');
}

// Return connection
return $conn;
?>
