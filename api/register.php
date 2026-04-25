<?php
/**
 * API: Event Registration
 * POST /api/register.php
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Registration.php';
require_once __DIR__ . '/../classes/CustomField.php';

init_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response('error', 'Invalid request method', null, 405);
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required = ['name', 'email', 'phone', 'organization', 'event_id'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            json_response('error', ucfirst($field) . ' is required', null, 400);
        }
    }
    
    // Validate email
    if (!is_valid_email($input['email'])) {
        json_response('error', ERROR_INVALID_EMAIL, null, 400);
    }
    
    // Initialize classes
    $db = new Database($conn);
    $registration = new Registration($db);
    $custom_field = new CustomField($db);
    
    // Check if participant exists or create
    $participant_sql = "SELECT participant_id FROM participants WHERE email = ?";
    $stmt = $db->prepare($participant_sql);
    $stmt->bind_param('s', $input['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $participant = $result->fetch_assoc();
        $participant_id = $participant['participant_id'];
    } else {
        // Create new participant
        $participant_id = $db->insert('participants', [
            'NAME' => $input['name'],
            'email' => $input['email'],
            'phone' => $input['phone'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    // Register for event
    $reg_result = $registration->register_participant(
        $participant_id,
        $input['event_id'],
        $input['organization'],
        $input['designation'] ?? '',
        $input['tssia_membership_id'] ?? null
    );
    
    if (!$reg_result['success']) {
        json_response('error', $reg_result['message'], null, 400);
    }
    
    $registration_id = $reg_result['registration_id'];
    
    // Save custom field responses if provided
    if (isset($input['custom_fields']) && is_array($input['custom_fields'])) {
        foreach ($input['custom_fields'] as $field_id => $value) {
            // Validate field value
            $validation = $custom_field->validate_value($field_id, $value);
            
            if (!$validation['valid']) {
                // Delete created registration
                $db->delete('event_registrations', ['registration_id' => $registration_id]);
                json_response('error', $validation['error'], null, 400);
            }
            
            // Save response
            $custom_field->save_response($registration_id, $field_id, $value);
        }
    }
    
    json_response('success', $reg_result['message'], ['registration_id' => $registration_id]);
    
} catch (Exception $e) {
    error_log('Register API Error: ' . $e->getMessage());
    json_response('error', 'An error occurred', null, 500);
}

?>
