<?php
/**
 * Email Templates
 */

/**
 * Registration Confirmation Email Template
 */
function get_registration_confirmation_email($participant_name, $event_name, $registration_id, $approval_message = null) {
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f5f5f5; }
            .email-content { background: white; padding: 30px; border-radius: 8px; }
            .header { border-bottom: 3px solid #007bff; padding-bottom: 20px; margin-bottom: 20px; }
            .logo { font-size: 24px; font-weight: bold; color: #007bff; }
            .content { margin: 20px 0; }
            .confirmation-box { background: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0; border-radius: 4px; }
            .details-box { background: #f0f0f0; padding: 15px; margin: 20px 0; border-radius: 4px; }
            .details-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #ddd; }
            .details-row:last-child { border-bottom: none; }
            .label { font-weight: bold; color: #666; }
            .value { color: #333; }
            .footer { text-align: center; padding-top: 20px; margin-top: 20px; border-top: 1px solid #ddd; color: #999; font-size: 12px; }
            .button { display: inline-block; background: #007bff; color: white; padding: 12px 20px; text-decoration: none; border-radius: 4px; margin: 20px 0; }
            .approval-notice { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; }
        </style>
    </head>
    <body>
        <div class=\"container\">
            <div class=\"email-content\">
                <div class=\"header\">
                    <div class=\"logo\">📅 Event Management System</div>
                </div>

                <div class=\"content\">
                    <p>Dear <strong>{$participant_name}</strong>,</p>

                    <p>Thank you for registering for <strong>{$event_name}</strong>! We're excited to have you join us.</p>

                    <div class=\"confirmation-box\">
                        <strong style=\"color: #155724;\">✓ Registration Received</strong><br>
                        Your registration has been successfully submitted.
                    </div>

                    <div class=\"details-box\">
                        <div class=\"details-row\">
                            <span class=\"label\">Registration ID:</span>
                            <span class=\"value\" style=\"font-family: monospace; font-weight: bold;\">{$registration_id}</span>
                        </div>
                        <div class=\"details-row\">
                            <span class=\"label\">Event:</span>
                            <span class=\"value\">{$event_name}</span>
                        </div>
                        <div class=\"details-row\">
                            <span class=\"label\">Status:</span>
                            <span class=\"value\">Pending Approval</span>
                        </div>
                    </div>
    ";

    if ($approval_message) {
        $body .= "
                    <div class=\"approval-notice\">
                        <strong style=\"color: #856404;\">⚠ Important Notice</strong><br>
                        {$approval_message}
                    </div>
        ";
    }

    $body .= "
                    <p>We will send you a confirmation email once your registration is approved. Please keep your registration ID handy for future reference.</p>

                    <p><strong>What's next?</strong></p>
                    <ul>
                        <li>Wait for registration approval (usually within 24 hours)</li>
                        <li>You'll receive a ticket via email after approval</li>
                        <li>Download and save your ticket for check-in</li>
                        <li>Arrive early on the event day for smooth check-in</li>
                    </ul>

                    <p>If you have any questions, please don't hesitate to contact us.</p>

                    <p>Best regards,<br>
                    <strong>Event Management Team</strong></p>
                </div>

                <div class=\"footer\">
                    <p>&copy; 2026 Event Management System. All rights reserved.</p>
                    <p>This is an automated email. Please do not reply to this message.</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";

    return $body;
}

/**
 * Registration Approved Email Template
 */
function get_registration_approved_email($participant_name, $event_name, $event_date, $event_location, $pass_number, $qr_code = null) {
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f5f5f5; }
            .email-content { background: white; padding: 30px; border-radius: 8px; }
            .header { border-bottom: 3px solid #28a745; padding-bottom: 20px; margin-bottom: 20px; }
            .logo { font-size: 24px; font-weight: bold; color: #28a745; }
            .content { margin: 20px 0; }
            .approval-box { background: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0; border-radius: 4px; }
            .ticket-box { background: #f0f0f0; padding: 20px; margin: 20px 0; border-radius: 4px; text-align: center; }
            .pass-number { font-size: 20px; font-weight: bold; color: #007bff; font-family: monospace; margin: 10px 0; }
            .event-details { background: #f9f9f9; padding: 15px; margin: 20px 0; border-radius: 4px; }
            .details-row { padding: 10px 0; border-bottom: 1px solid #eee; }
            .details-row:last-child { border-bottom: none; }
            .footer { text-align: center; padding-top: 20px; margin-top: 20px; border-top: 1px solid #ddd; color: #999; font-size: 12px; }
            .button { display: inline-block; background: #28a745; color: white; padding: 12px 20px; text-decoration: none; border-radius: 4px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class=\"container\">
            <div class=\"email-content\">
                <div class=\"header\">
                    <div class=\"logo\">📅 Event Management System</div>
                </div>

                <div class=\"content\">
                    <p>Dear <strong>{$participant_name}</strong>,</p>

                    <p>Great news! Your registration for <strong>{$event_name}</strong> has been approved! 🎉</p>

                    <div class=\"approval-box\">
                        <strong style=\"color: #155724;\">✓ Registration Approved</strong><br>
                        Your registration is confirmed and your ticket is ready.
                    </div>

                    <div class=\"ticket-box\">
                        <p><strong>Your Ticket Number</strong></p>
                        <div class=\"pass-number\">{$pass_number}</div>
                        " . ($qr_code ? "<p style=\"font-size: 12px; color: #666; margin-top: 10px;\">QR Code: {$qr_code}</p>" : "") . "
                    </div>

                    <div class=\"event-details\">
                        <p style=\"margin: 0 0 15px 0; font-weight: bold; font-size: 16px;\">Event Details</p>
                        <div class=\"details-row\"><strong>Event:</strong> {$event_name}</div>
                        <div class=\"details-row\"><strong>Date & Time:</strong> {$event_date}</div>
                        <div class=\"details-row\"><strong>Location:</strong> {$event_location}</div>
                    </div>

                    <p><strong>Important Reminders:</strong></p>
                    <ul>
                        <li>Please arrive 15-30 minutes early for check-in</li>
                        <li>Bring a valid ID for verification</li>
                        <li>Save your ticket number for quick check-in</li>
                        <li>If you have a printable ticket, print it out or have it ready on your phone</li>
                    </ul>

                    <p>Thank you for registering! We look forward to seeing you at the event.</p>

                    <p>If you have any questions, please contact us.</p>

                    <p>Best regards,<br>
                    <strong>Event Management Team</strong></p>
                </div>

                <div class=\"footer\">
                    <p>&copy; 2026 Event Management System. All rights reserved.</p>
                    <p>This is an automated email. Please do not reply to this message.</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";

    return $body;
}

/**
 * Registration Rejected Email Template
 */
function get_registration_rejected_email($participant_name, $event_name, $reason = null) {
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f5f5f5; }
            .email-content { background: white; padding: 30px; border-radius: 8px; }
            .header { border-bottom: 3px solid #dc3545; padding-bottom: 20px; margin-bottom: 20px; }
            .logo { font-size: 24px; font-weight: bold; color: #dc3545; }
            .content { margin: 20px 0; }
            .rejection-box { background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px; }
            .footer { text-align: center; padding-top: 20px; margin-top: 20px; border-top: 1px solid #ddd; color: #999; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class=\"container\">
            <div class=\"email-content\">
                <div class=\"header\">
                    <div class=\"logo\">📅 Event Management System</div>
                </div>

                <div class=\"content\">
                    <p>Dear <strong>{$participant_name}</strong>,</p>

                    <p>Thank you for your interest in <strong>{$event_name}</strong>. Unfortunately, your registration has been declined at this time.</p>

                    <div class=\"rejection-box\">
                        <strong style=\"color: #721c24;\">✕ Registration Not Approved</strong><br>
                        Your registration did not meet the requirements for this event.
                    </div>

                    " . ($reason ? "<p><strong>Reason:</strong> {$reason}</p>" : "") . "

                    <p>If you believe this is an error or would like more information, please contact us directly.</p>

                    <p>We appreciate your understanding and hope you can join us for future events.</p>

                    <p>Best regards,<br>
                    <strong>Event Management Team</strong></p>
                </div>

                <div class=\"footer\">
                    <p>&copy; 2026 Event Management System. All rights reserved.</p>
                    <p>This is an automated email. Please do not reply to this message.</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";

    return $body;
}

/**
 * Event Reminder Email Template
 */
function get_event_reminder_email($participant_name, $event_name, $event_date, $event_location, $ticket_number) {
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f5f5f5; }
            .email-content { background: white; padding: 30px; border-radius: 8px; }
            .header { border-bottom: 3px solid #17a2b8; padding-bottom: 20px; margin-bottom: 20px; }
            .logo { font-size: 24px; font-weight: bold; color: #17a2b8; }
            .content { margin: 20px 0; }
            .reminder-box { background: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; margin: 20px 0; border-radius: 4px; }
            .event-details { background: #f9f9f9; padding: 15px; margin: 20px 0; border-radius: 4px; }
            .details-row { padding: 10px 0; border-bottom: 1px solid #eee; }
            .details-row:last-child { border-bottom: none; }
            .footer { text-align: center; padding-top: 20px; margin-top: 20px; border-top: 1px solid #ddd; color: #999; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class=\"container\">
            <div class=\"email-content\">
                <div class=\"header\">
                    <div class=\"logo\">📅 Event Management System</div>
                </div>

                <div class=\"content\">
                    <p>Dear <strong>{$participant_name}</strong>,</p>

                    <p>This is a friendly reminder that <strong>{$event_name}</strong> is coming up soon!</p>

                    <div class=\"reminder-box\">
                        <strong style=\"color: #0c5460;\">⏰ Event Reminder</strong><br>
                        Don't forget to mark your calendar and prepare for the event.
                    </div>

                    <div class=\"event-details\">
                        <p style=\"margin: 0 0 15px 0; font-weight: bold; font-size: 16px;\">Event Details</p>
                        <div class=\"details-row\"><strong>Event:</strong> {$event_name}</div>
                        <div class=\"details-row\"><strong>Date & Time:</strong> {$event_date}</div>
                        <div class=\"details-row\"><strong>Location:</strong> {$event_location}</div>
                        <div class=\"details-row\"><strong>Your Ticket:</strong> <span style=\"font-family: monospace; font-weight: bold;\">{$ticket_number}</span></div>
                    </div>

                    <p><strong>Before You Go:</strong></p>
                    <ul>
                        <li>Plan your travel and arrive early</li>
                        <li>Bring your ticket and a valid ID</li>
                        <li>Check the event agenda if applicable</li>
                        <li>Prepare any required documents</li>
                    </ul>

                    <p>We're looking forward to seeing you there!</p>

                    <p>Best regards,<br>
                    <strong>Event Management Team</strong></p>
                </div>

                <div class=\"footer\">
                    <p>&copy; 2026 Event Management System. All rights reserved.</p>
                    <p>This is an automated email. Please do not reply to this message.</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";

    return $body;
}

?>
