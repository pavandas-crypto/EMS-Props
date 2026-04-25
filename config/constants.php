<?php
/**
 * Application Constants
 */

// Application Settings
define('APP_NAME', 'Event Management System');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/eve');

// Session Settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('SESSION_NAME', 'EVENTSYSTEM');

// File Upload Settings
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Ticket Generation
define('QR_CODE_SIZE', 300);
define('TICKET_PAGE_SIZE', 'A4');

// User Roles
define('ROLE_ADMIN', 'admin');
define('ROLE_VERIFIER', 'verifier');

// Registration Statuses
define('REG_STATUS_PENDING', 1);
define('REG_STATUS_APPROVED', 2);

// Attendance Statuses
define('ATTENDANCE_NOT_PRESENT', 3);
define('ATTENDANCE_PRESENT', 4);

// Custom Field Types
define('CUSTOM_FIELD_TYPES', [
    'text' => 'Text Input',
    'textarea' => 'Text Area',
    'number' => 'Number',
    'email' => 'Email',
    'phone' => 'Phone',
    'dropdown' => 'Dropdown',
    'radio' => 'Radio Button',
    'checkbox' => 'Checkbox',
    'date' => 'Date',
    'time' => 'Time',
    'file' => 'File Upload',
    'url' => 'URL'
]);

// Error Messages
define('ERROR_INVALID_EMAIL', 'Invalid email format');
define('ERROR_INVALID_PASSWORD', 'Password must be at least 8 characters');
define('ERROR_USER_EXISTS', 'User already exists');
define('ERROR_INVALID_CREDENTIALS', 'Invalid email or password');
define('ERROR_UNAUTHORIZED', 'Unauthorized access');
define('ERROR_INVALID_EVENT', 'Invalid event');
define('ERROR_DUPLICATE_REGISTRATION', 'You are already registered for this event');

// Success Messages
define('SUCCESS_LOGIN', 'Login successful');
define('SUCCESS_LOGOUT', 'Logged out successfully');
define('SUCCESS_REGISTRATION', 'Registration successful');
define('SUCCESS_EVENT_CREATED', 'Event created successfully');
define('SUCCESS_TICKET_GENERATED', 'Tickets generated successfully');

?>
