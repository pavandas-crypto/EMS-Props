<?php
/**
 * Registration Management Class
 * Handles event registration operations
 */

class Registration {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Register user for event
     */
    public function register_participant($participant_id, $event_id, $organization, $designation, $tssia_membership_id = null, $registration_status_id = REG_STATUS_PENDING) {
        try {
            // Check if already registered
            $sql = "SELECT registration_id FROM event_registrations 
                    WHERE participant_id = ? AND event_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('ii', $participant_id, $event_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return ['success' => false, 'message' => ERROR_DUPLICATE_REGISTRATION];
            }
            
            // Create registration
            $registration_id = $this->db->insert('event_registrations', [
                'participant_id' => $participant_id,
                'event_id' => $event_id,
                'organization' => $organization,
                'designation' => $designation,
                'tssia_membership_id' => $tssia_membership_id,
                'registration_status_id' => $registration_status_id,
                'attendance_status_id' => ATTENDANCE_NOT_PRESENT,
                'registered_at' => date('Y-m-d H:i:s'),
                'created_by' => $participant_id,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            log_activity('PARTICIPANT_REGISTERED', 'Participant registered for event: ' . $event_id);
            
            return ['success' => true, 'message' => SUCCESS_REGISTRATION, 'registration_id' => $registration_id];
            
        } catch (Exception $e) {
            error_log('Registration Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }
    
    /**
     * Get registration by ID
     */
    public function get_registration($registration_id) {
        try {
            $sql = "SELECT r.*, p.name, p.email, p.phone, e.event_name, s.name as status_name
                    FROM event_registrations r
                    JOIN participants p ON r.participant_id = p.participant_id
                    JOIN events e ON r.event_id = e.event_id
                    LEFT JOIN status_master s ON r.registration_status_id = s.status_id
                    WHERE r.registration_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $registration_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc();
            
        } catch (Exception $e) {
            error_log('Get Registration Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get registrations for event
     */
    public function get_event_registrations($event_id, $offset = 0, $limit = 10) {
        try {
            $sql = "SELECT r.*, p.name, p.email, p.phone, 
                           s.name as status_name, a.name as attendance_name
                    FROM event_registrations r
                    JOIN participants p ON r.participant_id = p.participant_id
                    LEFT JOIN status_master s ON r.registration_status_id = s.status_id
                    LEFT JOIN status_master a ON r.attendance_status_id = a.status_id
                    WHERE r.event_id = ?
                    ORDER BY r.registered_at DESC
                    LIMIT ? OFFSET ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('iii', $event_id, $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $registrations = [];
            while ($row = $result->fetch_assoc()) {
                $registrations[] = $row;
            }
            
            return $registrations;
            
        } catch (Exception $e) {
            error_log('Get Event Registrations Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get participant registrations
     */
    public function get_participant_registrations($participant_id) {
        try {
            $sql = "SELECT r.*, e.event_name, e.start_date_time, s.name as status_name
                    FROM event_registrations r
                    JOIN events e ON r.event_id = e.event_id
                    LEFT JOIN status_master s ON r.registration_status_id = s.status_id
                    WHERE r.participant_id = ?
                    ORDER BY e.start_date_time DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $participant_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $registrations = [];
            while ($row = $result->fetch_assoc()) {
                $registrations[] = $row;
            }
            
            return $registrations;
            
        } catch (Exception $e) {
            error_log('Get Participant Registrations Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update registration status
     */
    public function update_status($registration_id, $status_id) {
        try {
            $this->db->update('event_registrations', 
                ['registration_status_id' => $status_id, 'updated_at' => date('Y-m-d H:i:s')],
                ['registration_id' => $registration_id]
            );
            
            log_activity('REGISTRATION_STATUS_UPDATED', 'Registration status updated: ' . $registration_id);
            
            return ['success' => true, 'message' => 'Status updated successfully'];
            
        } catch (Exception $e) {
            error_log('Update Status Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update status'];
        }
    }
    
    /**
     * Mark attendance
     */
    public function mark_attendance($registration_id, $is_present = true) {
        try {
            $attendance_id = $is_present ? ATTENDANCE_PRESENT : ATTENDANCE_NOT_PRESENT;
            
            $this->db->update('event_registrations', 
                ['attendance_status_id' => $attendance_id, 'verified_at' => date('Y-m-d H:i:s')],
                ['registration_id' => $registration_id]
            );
            
            log_activity('ATTENDANCE_MARKED', 'Attendance marked for registration: ' . $registration_id);
            
            return ['success' => true, 'message' => 'Attendance marked successfully'];
            
        } catch (Exception $e) {
            error_log('Mark Attendance Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to mark attendance'];
        }
    }
    
    /**
     * Get registration count for event
     */
    public function get_registration_count($event_id) {
        try {
            return $this->db->count('event_registrations', ['event_id' => $event_id]);
        } catch (Exception $e) {
            error_log('Registration Count Error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get approved registrations
     */
    public function get_approved_registrations($event_id) {
        try {
            $sql = "SELECT r.*, p.name, p.email, p.phone
                    FROM event_registrations r
                    JOIN participants p ON r.participant_id = p.participant_id
                    WHERE r.event_id = ? AND r.registration_status_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('ii', $event_id, REG_STATUS_APPROVED);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $registrations = [];
            while ($row = $result->fetch_assoc()) {
                $registrations[] = $row;
            }
            
            return $registrations;
            
        } catch (Exception $e) {
            error_log('Get Approved Registrations Error: ' . $e->getMessage());
            return [];
        }
    }
}

?>
