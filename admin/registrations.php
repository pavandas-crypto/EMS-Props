<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Event.php';
require_once __DIR__ . '/../classes/Registration.php';

init_session();
require_admin();

$db = new Database($conn);
$eventClass = new Event($db);
$registrationClass = new Registration($db);

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$events = $eventClass->get_all_events(0, 50);
$message = '';
$error = '';

// Ensure rejected status exists in the status master table
try {
    $db->query("INSERT INTO status_master (status_id, TYPE, NAME, description, created_at) SELECT 5, 'registration', 'rejected', 'Registration rejected by admin', NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM status_master WHERE status_id = 5)");
} catch (Exception $e) {
    // ignore if status already exists or query fails
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $registration_id = (int)($_POST['registration_id'] ?? 0);
    if ($registration_id > 0) {
        if ($_POST['action'] === 'approve') {
            $result = $registrationClass->update_status($registration_id, REG_STATUS_APPROVED);
            if ($result['success']) {
                $message = 'Registration approved successfully.';
            } else {
                $error = $result['message'];
            }
        } elseif ($_POST['action'] === 'reject') {
            $result = $registrationClass->update_status($registration_id, REG_STATUS_REJECTED);
            if ($result['success']) {
                $message = 'Registration rejected successfully.';
            } else {
                $error = $result['message'];
            }
        }
    }
}

$registrations = [];
if ($event_id > 0) {
    $registrations = $registrationClass->get_event_registrations($event_id, 0, 100);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Registrations - Admin Panel</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <style>
        .select-group { margin-bottom: 20px; display: flex; gap: 14px; align-items: center; flex-wrap: wrap; }
        .registration-table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        .registration-table th, .registration-table td { padding: 12px 10px; border: 1px solid #edf0f4; }
        .registration-table th { background: #f8fafc; }
        .button-group { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .status-badge { display: inline-flex; padding: 6px 10px; border-radius: 999px; font-size: 12px; color: #fff; }
        .status-badge.approved { background: #28a745; }
        .status-badge.rejected { background: #dc3545; }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div>
                    <h1>Registration Approval</h1>
                    <p>Select an event and approve pending participant registrations.</p>
                </div>
                <div>
                    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                    <a href="logout.php" class="btn btn-secondary">Logout</a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="get" class="select-group">
                <label for="event_id">Choose Event</label>
                <select id="event_id" name="event_id" onchange="this.form.submit()">
                    <option value="">-- Select Event --</option>
                    <?php foreach ($events as $event): ?>
                        <option value="<?php echo (int)$event['event_id']; ?>"<?php echo $event_id === (int)$event['event_id'] ? ' selected' : ''; ?>><?php echo htmlspecialchars($event['event_name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($event_id > 0): ?>
                    <a href="generate-tickets.php?event_id=<?php echo $event_id; ?>" class="btn btn-secondary">Go to Ticket Generation</a>
                <?php endif; ?>
            </form>

            <?php if ($event_id === 0): ?>
                <div class="alert alert-info">Choose an event to review registrations.</div>
            <?php elseif (empty($registrations)): ?>
                <div class="alert alert-info">No registrations found for this event yet.</div>
            <?php else: ?>
                <table class="registration-table">
                    <thead>
                        <tr>
                            <th>Participant</th>
                            <th>Email / Phone</th>
                            <th>Organization</th>
                            <th>Designation</th>
                            <th>Status</th>
                            <th>Registered At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrations as $registration): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($registration['name']); ?></td>
                                <td><?php echo htmlspecialchars($registration['email'] . ' / ' . $registration['phone']); ?></td>
                                <td><?php echo htmlspecialchars($registration['organization']); ?></td>
                                <td><?php echo htmlspecialchars($registration['designation']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($registration['status_name'] ?? 'unknown')); ?></td>
                                <td><?php echo htmlspecialchars($registration['registered_at']); ?></td>
                                <td>
                                    <div class="button-group">
                                        <?php if ($registration['registration_status_id'] === REG_STATUS_PENDING): ?>
                                            <form method="post" style="display:inline-block; margin:0;">
                                                <input type="hidden" name="action" value="approve">
                                                <input type="hidden" name="registration_id" value="<?php echo (int)$registration['registration_id']; ?>">
                                                <button type="submit" class="btn btn-primary btn-sm">Approve</button>
                                            </form>
                                            <form method="post" style="display:inline-block; margin:0;">
                                                <input type="hidden" name="action" value="reject">
                                                <input type="hidden" name="registration_id" value="<?php echo (int)$registration['registration_id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                            </form>
                                        <?php elseif ($registration['registration_status_id'] === REG_STATUS_APPROVED): ?>
                                            <span class="status-badge approved">Approved</span>
                                            <form method="post" style="display:inline-block; margin:0;">
                                                <input type="hidden" name="action" value="reject">
                                                <input type="hidden" name="registration_id" value="<?php echo (int)$registration['registration_id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                            </form>
                                        <?php elseif ($registration['registration_status_id'] === REG_STATUS_REJECTED): ?>
                                            <span class="status-badge rejected">Rejected</span>
                                            <form method="post" style="display:inline-block; margin:0;">
                                                <input type="hidden" name="action" value="approve">
                                                <input type="hidden" name="registration_id" value="<?php echo (int)$registration['registration_id']; ?>">
                                                <button type="submit" class="btn btn-primary btn-sm">Approve</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="small-meta"><?php echo htmlspecialchars(ucfirst($registration['status_name'] ?? 'Unknown')); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
