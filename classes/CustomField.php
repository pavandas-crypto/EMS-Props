<?php
/**
 * Custom Field Management Class
 * Handles custom form field creation and management
 */

class CustomField {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Create custom field
     */
    public function create_field($event_id, $data) {
        try {
            $data['event_id'] = $event_id;
            $data['created_by'] = get_current_user_id();
            $data['created_at'] = date('Y-m-d H:i:s');
            
            $field_id = $this->db->insert('custom_fields', $data);
            
            log_activity('CUSTOM_FIELD_CREATED', 'Custom field created for event: ' . $event_id);
            
            return ['success' => true, 'message' => 'Field created successfully', 'field_id' => $field_id];
            
        } catch (Exception $e) {
            error_log('Create Field Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create field'];
        }
    }
    
    /**
     * Get fields for event
     */
    public function get_event_fields($event_id) {
        try {
            $sql = "SELECT * FROM custom_fields 
                    WHERE event_id = ?
                    ORDER BY sort_order ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $event_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $fields = [];
            while ($row = $result->fetch_assoc()) {
                // Parse JSON options if applicable
                if ($row['options_json'] && is_valid_json($row['options_json'])) {
                    $row['options'] = json_decode($row['options_json'], true);
                }
                $fields[] = $row;
            }
            
            return $fields;
            
        } catch (Exception $e) {
            error_log('Get Event Fields Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get field by ID
     */
    public function get_field($field_id) {
        try {
            $sql = "SELECT * FROM custom_fields WHERE custom_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $field_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc();
            
        } catch (Exception $e) {
            error_log('Get Field Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update field
     */
    public function update_field($field_id, $data) {
        try {
            $data['updated_by'] = get_current_user_id();
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            $affected = $this->db->update('custom_fields', $data, ['custom_id' => $field_id]);
            
            if ($affected > 0) {
                log_activity('CUSTOM_FIELD_UPDATED', 'Custom field updated: ID ' . $field_id);
                return ['success' => true, 'message' => 'Field updated successfully'];
            } else {
                return ['success' => false, 'message' => 'No changes made'];
            }
            
        } catch (Exception $e) {
            error_log('Update Field Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update field'];
        }
    }
    
    /**
     * Delete field
     */
    public function delete_field($field_id) {
        try {
            $affected = $this->db->delete('custom_fields', ['custom_id' => $field_id]);
            
            if ($affected > 0) {
                // Also delete responses for this field
                $this->db->delete('custom_field_responses', ['custom_id' => $field_id]);
                
                log_activity('CUSTOM_FIELD_DELETED', 'Custom field deleted: ID ' . $field_id);
                return ['success' => true, 'message' => 'Field deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Field not found'];
            }
            
        } catch (Exception $e) {
            error_log('Delete Field Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete field'];
        }
    }
    
    /**
     * Save field response
     */
    public function save_response($registration_id, $field_id, $value) {
        try {
            $response_id = $this->db->insert('custom_field_responses', [
                'registration_id' => $registration_id,
                'custom_id' => $field_id,
                'VALUE' => $value,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            return ['success' => true, 'response_id' => $response_id];
            
        } catch (Exception $e) {
            error_log('Save Response Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to save response'];
        }
    }
    
    /**
     * Get responses for registration
     */
    public function get_registration_responses($registration_id) {
        try {
            $sql = "SELECT cfr.*, cf.field_name, cf.field_type
                    FROM custom_field_responses cfr
                    JOIN custom_fields cf ON cfr.custom_id = cf.custom_id
                    WHERE cfr.registration_id = ?
                    ORDER BY cf.sort_order ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $registration_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $responses = [];
            while ($row = $result->fetch_assoc()) {
                $responses[] = $row;
            }
            
            return $responses;
            
        } catch (Exception $e) {
            error_log('Get Responses Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Validate field value
     */
    public function validate_value($field_id, $value) {
        try {
            $field = $this->get_field($field_id);
            
            if (!$field) {
                return ['valid' => false, 'error' => 'Field not found'];
            }
            
            // Check required
            if ($field['required'] && empty($value)) {
                return ['valid' => false, 'error' => $field['field_name'] . ' is required'];
            }
            
            // Type validation
            switch ($field['field_type']) {
                case 'email':
                    if ($value && !is_valid_email($value)) {
                        return ['valid' => false, 'error' => 'Invalid email format'];
                    }
                    break;
                    
                case 'phone':
                    if ($value && !preg_match('/^\d{10,}$/', preg_replace('/[^\d]/', '', $value))) {
                        return ['valid' => false, 'error' => 'Invalid phone number'];
                    }
                    break;
                    
                case 'number':
                    if ($value && !is_numeric($value)) {
                        return ['valid' => false, 'error' => 'Must be a number'];
                    }
                    break;
                    
                case 'url':
                    if ($value && !filter_var($value, FILTER_VALIDATE_URL)) {
                        return ['valid' => false, 'error' => 'Invalid URL'];
                    }
                    break;
            }
            
            // Regex validation
            if ($field['validation_regex'] && $value && !preg_match($field['validation_regex'], $value)) {
                return ['valid' => false, 'error' => 'Invalid format'];
            }
            
            return ['valid' => true];
            
        } catch (Exception $e) {
            error_log('Validate Value Error: ' . $e->getMessage());
            return ['valid' => false, 'error' => 'Validation error'];
        }
    }
}

?>
