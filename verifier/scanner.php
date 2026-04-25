<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Event.php';

init_session();
require_verifier();

$db = new Database($conn);
$eventClass = new Event($db);

// Get verifier's assigned events
$user_id = get_current_user_id();
$assigned_events = $db->get_results("SELECT e.* FROM events e JOIN verifier_events ve ON e.event_id = ve.event_id WHERE ve.verifier_id = $user_id ORDER BY e.event_name");

$message = '';
$scan_result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['qr_code'])) {
    $qr_code = sanitize($_POST['qr_code']);
    $selected_event = (int)($_POST['event_id'] ?? 0);

    if (empty($qr_code)) {
        $message = 'QR code is required.';
    } elseif ($selected_event <= 0) {
        $message = 'Please select an event.';
    } else {
        // Verify the QR code
        $ticket = $db->get_row("SELECT t.*, r.*, e.event_name, e.event_date, e.event_time, e.location FROM tickets t JOIN registrations r ON t.registration_id = r.registration_id JOIN events e ON r.event_id = e.event_id WHERE t.qr_code = ? AND r.event_id = ? AND r.status = 'approved'", [$qr_code, $selected_event]);

        if ($ticket) {
            $scan_result = $ticket;
            // Log the verification
            $db->insert('verification_logs', [
                'ticket_id' => $ticket['ticket_id'],
                'verifier_id' => $user_id,
                'verified_at' => date('Y-m-d H:i:s'),
                'event_id' => $selected_event
            ]);
        } else {
            $message = 'Invalid QR code or ticket not found for this event.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Scanner - Verifier</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <style>
        .scanner-container { max-width: 600px; margin: 0 auto; }
        .scan-result { margin-top: 20px; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .scan-result.success { background: #d4edda; border-color: #c3e6cb; }
        .scan-result.error { background: #f8d7da; border-color: #f5c6cb; }
        .participant-info { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 15px; }
        .info-item { padding: 8px; background: #f8f9fa; border-radius: 4px; }
        .info-label { font-weight: bold; color: #666; font-size: 0.9em; }
        .info-value { margin-top: 2px; }
        .qr-input { font-family: monospace; font-size: 16px; padding: 12px; width: 100%; border: 2px solid #ddd; border-radius: 4px; }
        .qr-input:focus { border-color: #007bff; outline: none; }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div>
                    <h1>QR Code Scanner</h1>
                    <p>Scan QR codes to verify participant attendance.</p>
                </div>
                <div>
                    <a href="../api/logout.php" class="btn btn-secondary">Logout</a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div class="scanner-container">
                <div class="admin-card">
                    <h2>Scan QR Code</h2>
                    <form method="post">
                        <div class="form-group required">
                            <label for="event_id">Select Event</label>
                            <select id="event_id" name="event_id" required>
                                <option value="">Choose an event...</option>
                                <?php foreach ($assigned_events as $event): ?>
                                    <option value="<?php echo $event['event_id']; ?>" <?php echo (isset($_POST['event_id']) && $_POST['event_id'] == $event['event_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($event['event_name']); ?> - <?php echo htmlspecialchars($event['event_date']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group required">
                            <label for="qr_code">QR Code</label>
                            <input type="text" id="qr_code" name="qr_code" class="qr-input" placeholder="Enter or scan QR code" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Verify Ticket</button>
                    </form>
                </div>

                <?php if ($scan_result): ?>
                    <div class="scan-result success">
                        <h3>✓ Verification Successful</h3>
                        <div class="participant-info">
                            <div class="info-item">
                                <div class="info-label">Name</div>
                                <div class="info-value"><?php echo htmlspecialchars($scan_result['first_name'] . ' ' . $scan_result['last_name']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Email</div>
                                <div class="info-value"><?php echo htmlspecialchars($scan_result['email']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Phone</div>
                                <div class="info-value"><?php echo htmlspecialchars($scan_result['phone'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Event</div>
                                <div class="info-value"><?php echo htmlspecialchars($scan_result['event_name']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Date & Time</div>
                                <div class="info-value"><?php echo htmlspecialchars($scan_result['event_date'] . ' ' . $scan_result['event_time']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Location</div>
                                <div class="info-value"><?php echo htmlspecialchars($scan_result['location'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Ticket ID</div>
                                <div class="info-value"><?php echo htmlspecialchars($scan_result['ticket_id']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Verified At</div>
                                <div class="info-value"><?php echo date('Y-m-d H:i:s'); ?></div>
                            </div>
                        </div>
                        <?php if (!empty($scan_result['custom_fields'])): ?>
                            <div style="margin-top: 15px;">
                                <h4>Additional Information</h4>
                                <div class="participant-info">
                                    <?php
                                    $custom_fields = json_decode($scan_result['custom_fields'], true);
                                    if (is_array($custom_fields)) {
                                        foreach ($custom_fields as $field => $value) {
                                            echo '<div class="info-item">';
                                            echo '<div class="info-label">' . htmlspecialchars($field) . '</div>';
                                            echo '<div class="info-value">' . htmlspecialchars($value) . '</div>';
                                            echo '</div>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Auto-focus on QR input
        document.getElementById('qr_code').focus();

        // Clear result after 10 seconds
        <?php if ($scan_result): ?>
            setTimeout(() => {
                window.location.reload();
            }, 10000);
        <?php endif; ?>

        // Handle Enter key to submit form
        document.getElementById('qr_code').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.querySelector('form').submit();
            }
        });
    </script>
</body>
</html>