# Event Management System - PHP Backend Documentation

## Overview
This is a comprehensive PHP backend for an event management system with dual-panel functionality:
- **User Panel**: Browse and register for events
- **Admin Panel**: Manage events, registrations, and generate tickets with QR codes

## Project Structure

```
eve/
├── config/
│   ├── database.php       # Database connection configuration
│   └── constants.php      # Application constants and settings
├── classes/
│   ├── Database.php       # Database abstraction layer
│   ├── Auth.php          # Authentication and user management
│   ├── Event.php         # Event CRUD operations
│   ├── Registration.php  # Event registration management
│   ├── CustomField.php   # Custom form fields
│   └── Ticket.php        # Ticket and QR code generation
├── includes/
│   ├── helpers.php       # Utility functions
│   └── security.php      # Security and session management
├── api/
│   ├── login.php         # User login endpoint
│   ├── logout.php        # User logout endpoint
│   ├── register.php      # Event registration endpoint
│   ├── events.php        # Get events list
│   ├── event-details.php # Get single event details
│   ├── verify-qr.php     # QR code verification
│   └── admin/
│       ├── event-create.php      # Create new event
│       ├── registrations.php     # Get event registrations
│       ├── generate-tickets.php  # Generate bulk tickets
│       └── registration-status.php # Update registration status
├── init.php              # Bootstrap file
└── event_management.sql  # Database schema

```

## Installation & Setup

### 1. Database Setup
```bash
# Import the SQL file into your MySQL database
mysql -u root event_management < event_management.sql
```

### 2. Configuration
Update `config/database.php` with your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'event_management');
```

### 3. Permissions
Create the required directories and set permissions:
```bash
mkdir -p uploads logs
chmod 755 uploads logs
```

## API Endpoints

### Public Endpoints

#### 1. Login
- **POST** `/api/login.php`
- **Request Body**:
```json
{
  "email": "admin@mail.com",
  "password": "password123"
}
```
- **Response**:
```json
{
  "status": "success",
  "message": "Login successful",
  "data": {
    "user": {
      "user_id": 1,
      "name": "Admin Name",
      "email": "admin@mail.com",
      "role": "admin"
    }
  }
}
```

#### 2. Logout
- **POST** `/api/logout.php`
- **Response**:
```json
{
  "status": "success",
  "message": "Logged out successfully"
}
```

#### 3. Get Events
- **GET** `/api/events.php?type=upcoming&page=1&limit=10`
- **Parameters**:
  - `type`: 'all', 'upcoming', or 'search'
  - `page`: Page number (default: 1)
  - `limit`: Items per page (default: 10)
  - `q`: Search query (if type='search')

#### 4. Get Event Details
- **GET** `/api/event-details.php?event_id=1`
- **Returns**: Event details with custom fields

#### 5. Register for Event
- **POST** `/api/register.php`
- **Request Body**:
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "1234567890",
  "organization": "XYZ Corp",
  "designation": "Manager",
  "event_id": 1,
  "tssia_membership_id": "TSSIA123",
  "custom_fields": {
    "1": "Veg",
    "2": "5"
  }
}
```

#### 6. Verify QR Code
- **POST** `/api/verify-qr.php` (Requires verifier role)
- **Request Body**:
```json
{
  "qr_code": "QR_PASS_001",
  "event_id": 1
}
```

### Admin Endpoints (Require Admin Role)

#### 1. Create Event
- **POST** `/api/admin/event-create.php`
- **Request Body**:
```json
{
  "event_name": "Tech Conference 2026",
  "description": "Annual technology event",
  "start_date_time": "2026-06-01 10:00:00",
  "end_date_time": "2026-06-01 17:00:00",
  "address": "Mumbai Expo Center",
  "event_for": "all",
  "image_id": 1
}
```

#### 2. Get Registrations
- **GET** `/api/admin/registrations.php?event_id=1&page=1&limit=10`

#### 3. Generate Tickets
- **POST** `/api/admin/generate-tickets.php`
- **Request Body**:
```json
{
  "event_id": 1
}
```

#### 4. Update Registration Status
- **PUT** `/api/admin/registration-status.php`
- **Request Body**:
```json
{
  "registration_id": 1,
  "status_id": 2
}
```

## Core Classes

### Database Class
Provides database abstraction with prepared statements:
```php
$db = new Database($conn);

// Insert
$id = $db->insert('participants', ['name' => 'John', 'email' => 'john@example.com']);

// Update
$db->update('participants', ['name' => 'Jane'], ['participant_id' => 1]);

// Delete
$db->delete('participants', ['participant_id' => 1]);

// Query
$rows = $db->get_results("SELECT * FROM events");
$row = $db->get_row("SELECT * FROM events WHERE event_id = 1");
```

### Auth Class
```php
$auth = new Auth($db);

// Login
$result = $auth->login('user@example.com', 'password');

// Register
$result = $auth->register('John Doe', 'john@example.com', 'Password123', 'admin');

// Change password
$result = $auth->change_password($user_id, 'old_password', 'new_password');
```

### Event Class
```php
$event = new Event($db);

// Create event
$result = $event->create($data);

// Get event
$event_data = $event->get_event($event_id);

// Get all events
$events = $event->get_all_events($offset, $limit);

// Get upcoming events
$upcoming = $event->get_upcoming_events($limit);

// Search events
$results = $event->search_events($search_term, $offset, $limit);
```

### Registration Class
```php
$registration = new Registration($db);

// Register participant
$result = $registration->register_participant($participant_id, $event_id, $org, $designation);

// Get registration
$reg = $registration->get_registration($registration_id);

// Update status
$registration->update_status($registration_id, $status_id);

// Mark attendance
$registration->mark_attendance($registration_id, true);
```

### CustomField Class
```php
$cf = new CustomField($db);

// Create field
$cf->create_field($event_id, $data);

// Get event fields
$fields = $cf->get_event_fields($event_id);

// Validate value
$validation = $cf->validate_value($field_id, $value);

// Save response
$cf->save_response($registration_id, $field_id, $value);
```

### Ticket Class
```php
$ticket = new Ticket($db);

// Generate single ticket
$result = $ticket->generate_ticket($registration_id);

// Generate bulk tickets
$result = $ticket->generate_bulk_tickets($event_id);

// Verify QR code
$result = $ticket->verify_qr_code($qr_code, $event_id);

// Log scan
$ticket->log_scan($event_id, $registration_id);
```

## Security Features

1. **Password Hashing**: Uses bcrypt with cost factor 12
2. **CSRF Protection**: Token generation and validation
3. **Session Management**: Automatic timeout after 1 hour of inactivity
4. **Input Validation**: Email, phone, URL, and custom regex validation
5. **SQL Injection Prevention**: Prepared statements with parameter binding
6. **Rate Limiting**: Login attempt limiting
7. **Activity Logging**: All important actions are logged
8. **Role-Based Access Control**: Admin and verifier roles with access restrictions

## Helper Functions

```php
// Sanitization
sanitize($data);              // Sanitize input
verify_password($pass, $hash); // Verify password
hash_password($password);      // Hash password

// Validation
is_valid_email($email);       // Validate email
is_logged_in();               // Check if user logged in
has_role($role);              // Check user role

// Session Management
require_login();              // Require login
require_admin();              // Require admin role
require_verifier();           // Require verifier role

// Response
json_response($status, $message, $data, $http_code);

// File Operations
validate_file_upload($file, $allowed_types);
save_uploaded_file($file);

// Utility
generate_unique_id();         // Generate unique ID
generate_pass_number();       // Generate ticket number
generate_qr_code_string();    // Generate QR code string
format_datetime($datetime);   // Format datetime
redirect($url, $message);     // Redirect with message
```

## Error Handling

All API endpoints return consistent JSON responses:

**Success Response**:
```json
{
  "status": "success",
  "message": "Operation completed",
  "data": { ... }
}
```

**Error Response**:
```json
{
  "status": "error",
  "message": "Error description"
}
```

HTTP Status Codes:
- 200: OK
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 405: Method Not Allowed
- 429: Too Many Requests
- 500: Server Error

## Environment Variables

Edit `config/constants.php` to configure:
- Session timeout
- Upload file size limits
- Allowed file types
- QR code size
- Custom field types

## Logging

Logs are stored in `logs/activity_YYYY-MM-DD.log` with format:
```
YYYY-MM-DD HH:MM:SS | User: user_id | Action: ACTION_NAME | Details: additional_info
```

## Default Credentials

From the SQL dump:
- **Admin**: admin@mail.com / 123456
- **Verifier**: verifier1@mail.com / 123456

## Next Steps

1. Create front-end pages (user registration, admin dashboard)
2. Implement PDF generation for tickets
3. Add email notification system
4. Create QR code scanning interface
5. Add admin dashboard UI
6. Implement event statistics and reporting

## License

This system is provided as-is for event management purposes.
