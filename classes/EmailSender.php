<?php
/**
 * Email Sender Class
 * Handles email sending functionality
 */

class EmailSender {
    private $from_email = 'noreply@eventmanagement.local';
    private $from_name = 'Event Management System';
    
    public function __construct($from_email = null, $from_name = null) {
        if ($from_email) {
            $this->from_email = $from_email;
        }
        if ($from_name) {
            $this->from_name = $from_name;
        }
    }
    
    /**
     * Send email
     */
    public function send($to_email, $subject, $message, $is_html = true) {
        try {
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: " . ($is_html ? "text/html" : "text/plain") . "; charset=UTF-8\r\n";
            $headers .= "From: {$this->from_name} <{$this->from_email}>\r\n";
            $headers .= "Reply-To: {$this->from_email}\r\n";
            
            // In production, use a proper mail library like PHPMailer
            // For now, using PHP's mail function
            if (function_exists('mail')) {
                $result = mail($to_email, $subject, $message, $headers);
                
                if ($result) {
                    error_log('Email sent successfully to: ' . $to_email);
                    return ['success' => true, 'message' => 'Email sent successfully'];
                } else {
                    error_log('Failed to send email to: ' . $to_email);
                    return ['success' => false, 'message' => 'Failed to send email'];
                }
            } else {
                error_log('Mail function not available');
                return ['success' => false, 'message' => 'Mail function not available'];
            }
            
        } catch (Exception $e) {
            error_log('Email sending error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Email sending error'];
        }
    }
    
    /**
     * Send registration confirmation
     */
    public function send_registration_confirmation($to_email, $participant_name, $event_name, $registration_id, $approval_message = null) {
        require_once __DIR__ . '/email-templates.php';
        
        $message = get_registration_confirmation_email($participant_name, $event_name, $registration_id, $approval_message);
        $subject = "Event Registration Confirmation - {$event_name}";
        
        return $this->send($to_email, $subject, $message);
    }
    
    /**
     * Send registration approved
     */
    public function send_registration_approved($to_email, $participant_name, $event_name, $event_date, $event_location, $pass_number, $qr_code = null) {
        require_once __DIR__ . '/email-templates.php';
        
        $message = get_registration_approved_email($participant_name, $event_name, $event_date, $event_location, $pass_number, $qr_code);
        $subject = "Registration Approved - {$event_name}";
        
        return $this->send($to_email, $subject, $message);
    }
    
    /**
     * Send registration rejected
     */
    public function send_registration_rejected($to_email, $participant_name, $event_name, $reason = null) {
        require_once __DIR__ . '/email-templates.php';
        
        $message = get_registration_rejected_email($participant_name, $event_name, $reason);
        $subject = "Registration Status - {$event_name}";
        
        return $this->send($to_email, $subject, $message);
    }
    
    /**
     * Send event reminder
     */
    public function send_event_reminder($to_email, $participant_name, $event_name, $event_date, $event_location, $ticket_number) {
        require_once __DIR__ . '/email-templates.php';
        
        $message = get_event_reminder_email($participant_name, $event_name, $event_date, $event_location, $ticket_number);
        $subject = "Reminder: {$event_name} is coming up!";
        
        return $this->send($to_email, $subject, $message);
    }
}

?>
