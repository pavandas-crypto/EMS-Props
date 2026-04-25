<?php
/**
 * Ticket and QR Code Generation Class
 * Handles ticket creation and QR code generation
 */

class Ticket {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Generate ticket for registration
     */
    public function generate_ticket($registration_id, $template_id = 1) {
        try {
            // Check if ticket already exists
            $sql = "SELECT pass_id FROM passes WHERE registration_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $registration_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $pass = $result->fetch_assoc();
                return ['success' => true, 'pass_id' => $pass['pass_id'], 'message' => 'Ticket already exists'];
            }
            
            // Generate pass number
            $pass_number = generate_pass_number();
            
            // Create pass
            $pass_id = $this->db->insert('passes', [
                'registration_id' => $registration_id,
                'template_id' => $template_id,
                'pass_number' => $pass_number,
                'created_by' => get_current_user_id(),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Generate QR code
            $qr_code = generate_qr_code_string($pass_id, $registration_id);
            
            $qr_id = $this->db->insert('qr_codes', [
                'pass_id' => $pass_id,
                'qr_code' => $qr_code,
                'created_by' => get_current_user_id(),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            log_activity('TICKET_GENERATED', 'Ticket generated for registration: ' . $registration_id);
            
            return ['success' => true, 'pass_id' => $pass_id, 'qr_id' => $qr_id, 'message' => SUCCESS_TICKET_GENERATED];
            
        } catch (Exception $e) {
            error_log('Generate Ticket Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to generate ticket'];
        }
    }
    
    /**
     * Generate tickets for all approved registrations
     */
    public function generate_bulk_tickets($event_id, $template_id = 1) {
        try {
            // Get all approved registrations without tickets
            $sql = "SELECT r.registration_id FROM event_registrations r
                    LEFT JOIN passes p ON r.registration_id = p.registration_id
                    WHERE r.event_id = ? AND r.registration_status_id = ? AND p.pass_id IS NULL";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('ii', $event_id, REG_STATUS_APPROVED);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $generated_count = 0;
            $failed_count = 0;
            
            while ($row = $result->fetch_assoc()) {
                $ticket_result = $this->generate_ticket($row['registration_id'], $template_id);
                
                if ($ticket_result['success']) {
                    $generated_count++;
                } else {
                    $failed_count++;
                }
            }
            
            log_activity('BULK_TICKETS_GENERATED', 'Bulk tickets generated for event: ' . $event_id . 
                         ' (' . $generated_count . ' generated, ' . $failed_count . ' failed)');
            
            return [
                'success' => true,
                'message' => 'Tickets generated successfully',
                'generated' => $generated_count,
                'failed' => $failed_count
            ];
            
        } catch (Exception $e) {
            error_log('Bulk Generate Tickets Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to generate tickets'];
        }
    }
    
    /**
     * Get ticket details
     */
    public function get_ticket($pass_id) {
        try {
            $sql = "SELECT p.*, q.qr_code, r.*, pr.name, pr.email, pr.phone, e.event_name
                    FROM passes p
                    LEFT JOIN qr_codes q ON p.pass_id = q.pass_id
                    LEFT JOIN event_registrations r ON p.registration_id = r.registration_id
                    LEFT JOIN participants pr ON r.participant_id = pr.participant_id
                    LEFT JOIN events e ON r.event_id = e.event_id
                    WHERE p.pass_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $pass_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc();
            
        } catch (Exception $e) {
            error_log('Get Ticket Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get registration ticket
     */
    public function get_registration_ticket($registration_id) {
        try {
            $sql = "SELECT p.*, q.qr_code FROM passes p
                    LEFT JOIN qr_codes q ON p.pass_id = q.pass_id
                    WHERE p.registration_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $registration_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc();
            
        } catch (Exception $e) {
            error_log('Get Registration Ticket Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all tickets for event
     */
    public function get_event_tickets($event_id, $offset = 0, $limit = 10) {
        try {
            $sql = "SELECT p.*, q.qr_code, r.*, pr.name, pr.email
                    FROM passes p
                    LEFT JOIN qr_codes q ON p.pass_id = q.pass_id
                    LEFT JOIN event_registrations r ON p.registration_id = r.registration_id
                    LEFT JOIN participants pr ON r.participant_id = pr.participant_id
                    WHERE r.event_id = ?
                    ORDER BY p.created_at DESC
                    LIMIT ? OFFSET ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('iii', $event_id, $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $tickets = [];
            while ($row = $result->fetch_assoc()) {
                $tickets[] = $row;
            }
            
            return $tickets;
            
        } catch (Exception $e) {
            error_log('Get Event Tickets Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Verify QR code
     */
    public function verify_qr_code($qr_code, $event_id) {
        try {
            $sql = "SELECT p.*, q.qr_id, r.registration_id, r.event_id, pr.name
                    FROM qr_codes q
                    JOIN passes p ON q.pass_id = p.pass_id
                    JOIN event_registrations r ON p.registration_id = r.registration_id
                    JOIN participants pr ON r.participant_id = pr.participant_id
                    WHERE q.qr_code = ? AND r.event_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('si', $qr_code, $event_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return ['valid' => false, 'message' => 'Invalid QR code'];
            }
            
            $ticket = $result->fetch_assoc();
            
            // Log scan
            $this->log_scan($event_id, $ticket['registration_id']);
            
            return ['valid' => true, 'data' => $ticket];
            
        } catch (Exception $e) {
            error_log('Verify QR Code Error: ' . $e->getMessage());
            return ['valid' => false, 'message' => 'Verification failed'];
        }
    }
    
    /**
     * Log QR code scan
     */
    public function log_scan($event_id, $registration_id, $scanned_by = null) {
        try {
            $scanned_by = $scanned_by ?? get_current_user_id();
            
            $this->db->insert('scan_logs', [
                'event_id' => $event_id,
                'registration_id' => $registration_id,
                'scanned_by' => $scanned_by,
                'scanned_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            error_log('Log Scan Error: ' . $e->getMessage());
            return ['success' => false];
        }
    }
    
    /**
     * Get scan logs for event
     */
    public function get_scan_logs($event_id, $offset = 0, $limit = 10) {
        try {
            $sql = "SELECT sl.*, pr.name, pr.email, u.name as scanned_by_name
                    FROM scan_logs sl
                    LEFT JOIN event_registrations r ON sl.registration_id = r.registration_id
                    LEFT JOIN participants pr ON r.participant_id = pr.participant_id
                    LEFT JOIN users u ON sl.scanned_by = u.user_id
                    WHERE sl.event_id = ?
                    ORDER BY sl.scanned_at DESC
                    LIMIT ? OFFSET ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('iii', $event_id, $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $logs = [];
            while ($row = $result->fetch_assoc()) {
                $logs[] = $row;
            }
            
            return $logs;
            
        } catch (Exception $e) {
            error_log('Get Scan Logs Error: ' . $e->getMessage());
            return [];
        }
    }
}

?>
