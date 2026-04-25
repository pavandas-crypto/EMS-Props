<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Event.php';

init_session();
require_admin();

$db = new Database($conn);
$eventClass = new Event($db);
$events = $eventClass->get_all_events(0, 50);
$eventCount = $eventClass->get_event_count();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Event Management System</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <style>
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .admin-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 32px; }
        .admin-card { padding: 20px; background: #fff; border-radius: 12px; border: 1px solid #e6ebf1; }
        .admin-card h3 { margin: 0 0 12px; font-size: 18px; }
        .admin-list { width: 100%; border-collapse: collapse; }
        .admin-list th, .admin-list td { padding: 14px 12px; border-bottom: 1px solid #f0f2f7; }
        .admin-list th { text-align: left; background: #f8fafd; }
        .status-badge { display: inline-flex; padding: 6px 10px; border-radius: 999px; font-size: 12px; color: #fff; }
        .status-badge.pending { background: #f0ad4e; }
        .status-badge.approved { background: #28a745; }
        .small-note { color: #6c757d; font-size: 14px; }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="container">
            <div class="admin-header">
                <div>
                    <h1>Admin Dashboard</h1>
                    <p class="small-note">Manage events, registration forms, approvals, and ticket generation from one place.</p>
                </div>
                <div>
                    <a href="logout.php" class="btn btn-secondary">Logout</a>
                </div>
            </div>

            <div class="admin-actions">
                <div class="admin-card">
                    <h3>Create Event</h3>
                    <p>Create a new event with title, description, dates, location, image, and approval mode.</p>
                    <a href="event-create.php" class="btn btn-primary">Create Event</a>
                </div>
                <div class="admin-card">
                    <h3>Manage Registration Form</h3>
                    <p>Add custom questions and configure success/approval messaging for each event.</p>
                    <a href="event-form.php" class="btn btn-primary">Manage Form</a>
                </div>
                <div class="admin-card">
                    <h3>Approve Participants</h3>
                    <p>Review pending registrations per event and approve participants before ticket generation.</p>
                    <a href="registrations.php" class="btn btn-primary">Manage Registrations</a>
                </div>
                <div class="admin-card">
                    <h3>Generate Tickets</h3>
                    <p>Choose a ticket template and bulk-generate tickets only for approved registrations.</p>
                    <a href="generate-tickets.php" class="btn btn-primary">Generate Tickets</a>
                </div>
            </div>

            <div class="admin-card">
                <h3>Recent Events</h3>
                <p>Total events: <strong><?php echo (int)$eventCount; ?></strong></p>

                <?php if (empty($events)): ?>
                    <div class="alert alert-info">No events have been created yet. Use the Create Event button above.</div>
                <?php else: ?>
                    <table class="admin-list">
                        <thead>
                            <tr>
                                <th>Event Name</th>
                                <th>Start / End</th>
                                <th>Registrations</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($event['event_name']); ?></td>
                                <td><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($event['start_date_time']))); ?> <br> to <br> <?php echo htmlspecialchars(date('M d, Y H:i', strtotime($event['end_date_time']))); ?></td>
                                <td><?php echo (int)$event['total_registrations']; ?></td>
                                <td>
                                    <a href="event-form.php?event_id=<?php echo (int)$event['event_id']; ?>" class="btn btn-secondary btn-sm">Form</a>
                                    <a href="registrations.php?event_id=<?php echo (int)$event['event_id']; ?>" class="btn btn-secondary btn-sm">Registrations</a>
                                    <a href="generate-tickets.php?event_id=<?php echo (int)$event['event_id']; ?>" class="btn btn-secondary btn-sm">Tickets</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
