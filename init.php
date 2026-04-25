<?php
/**
 * Application Bootstrap File
 * Include this file to initialize the application
 */

// Database and configuration
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';

// Helper functions and security
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/security.php';

// Core classes
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/Event.php';
require_once __DIR__ . '/classes/Registration.php';
require_once __DIR__ . '/classes/CustomField.php';
require_once __DIR__ . '/classes/Ticket.php';

// Initialize database instance
$database = new Database($conn);

?>
