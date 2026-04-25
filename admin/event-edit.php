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

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
if ($event_id <= 0) {
    header('Location: dashboard.php');
    exit;
}

$event = $eventClass->get_event($event_id);
if (!$event) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

$success_settings = $db->get_row('SELECT * FROM registration_success_settings WHERE event_id = ' . $event_id . ' LIMIT 1');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_name = sanitize($_POST['event_name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $start_date_time = sanitize($_POST['start_date_time'] ?? '');
    $end_date_time = sanitize($_POST['end_date_time'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $event_for = sanitize($_POST['event_for'] ?? 'all');
    $success_title = sanitize($_POST['success_title'] ?? 'Registration Successful 🎉');
    $success_message = sanitize($_POST['success_message'] ?? 'You have successfully registered for the event.');
    $show_approval = isset($_POST['show_approval_notice']) ? 1 : 0;
    $approval_message = sanitize($_POST['approval_message'] ?? 'Your registration is under review. Please wait for approval confirmation via email.');

    if (empty($event_name) || empty($start_date_time) || empty($end_date_time)) {
        $error = 'Event title, start date/time, and end date/time are required.';
    } elseif (strtotime($start_date_time) >= strtotime($end_date_time)) {
        $error = 'End date/time must be later than start date/time.';
    } else {
        $event_data = [
            'event_name' => $event_name,
            'description' => $description,
            'start_date_time' => $start_date_time,
            'end_date_time' => $end_date_time,
            'address' => $address,
            'event_for' => in_array($event_for, ['all', 'tssia_members']) ? $event_for : 'all'
        ];

        if (!empty($_FILES['event_image']['name'])) {
            $validation = validate_file_upload($_FILES['event_image'], ALLOWED_IMAGE_TYPES);
            if (!$validation['valid']) {
                $error = $validation['error'];
            } else {
                $upload = save_uploaded_file($_FILES['event_image']);
                if (!$upload['success']) {
                    $error = $upload['error'];
                } else {
                    $image_id = $db->insert('images', [
                        'url' => 'uploads/' . $upload['filename'],
                        'alt_text' => $event_name,
                        'file_name' => $upload['filename'],
                        'file_type' => $_FILES['event_image']['type'],
                        'file_size' => $_FILES['event_image']['size'],
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => get_current_user_id()
                    ]);
                    $event_data['image_id'] = $image_id;
                }
            }
        }

        if (empty($error)) {
            $updated = $eventClass->update($event_id, $event_data);
            if ($updated['success']) {
                if ($success_settings) {
                    $db->update('registration_success_settings', [
                        'success_title' => $success_title,
                        'success_message' => $success_message,
                        'show_approval_notice' => $show_approval,
                        'approval_message' => $approval_message,
                        'updated_at' => date('Y-m-d H:i:s'),
                        'updated_by' => get_current_user_id()
                    ], ['success_id' => $success_settings['success_id']]);
                } else {
                    $db->insert('registration_success_settings', [
                        'event_id' => $event_id,
                        'success_title' => $success_title,
                        'success_message' => $success_message,
                        'show_approval_notice' => $show_approval,
                        'approval_message' => $approval_message,
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => get_current_user_id()
                    ]);
                }

                $success = 'Event updated successfully.';
                $event = $eventClass->get_event($event_id);
                $success_settings = $db->get_row('SELECT * FROM registration_success_settings WHERE event_id = ' . $event_id . ' LIMIT 1');
            } else {
                $error = $updated['message'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - Admin Panel</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <style>
        .form-grid { display: grid; gap: 18px; }
        .checkbox-group { display: flex; align-items: center; gap: 10px; }
        .help-text { color: #6c757d; font-size: 14px; margin-top: 6px; }
        .event-image-preview { max-width: 300px; border-radius: 8px; border: 1px solid #e6ebf1; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div>
                    <h1>Edit Event</h1>
                    <p>Update the event details and registration success settings.</p>
                </div>
                <div>
                    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                    <a href="logout.php" class="btn btn-secondary">Logout</a>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" class="form-grid">
                <div class="form-group required">
                    <label for="event_name">Event Title</label>
                    <input type="text" id="event_name" name="event_name" value="<?php echo htmlspecialchars($event['event_name']); ?>" required>
                </div>

                <div class="form-group required">
                    <label for="start_date_time">Start Date and Time</label>
                    <input type="datetime-local" id="start_date_time" name="start_date_time" value="<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($event['start_date_time']))); ?>" required>
                </div>

                <div class="form-group required">
                    <label for="end_date_time">End Date and Time</label>
                    <input type="datetime-local" id="end_date_time" name="end_date_time" value="<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($event['end_date_time']))); ?>" required>
                </div>

                <div class="form-group required">
                    <label for="event_for">Visible To</label>
                    <select id="event_for" name="event_for" required>
                        <option value="all"<?php echo ($event['event_for'] === 'all') ? ' selected' : ''; ?>>All Participants</option>
                        <option value="tssia_members"<?php echo ($event['event_for'] === 'tssia_members') ? ' selected' : ''; ?>>TSSIA Members Only</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($event['address']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="event_image">Event Image</label>
                    <input type="file" id="event_image" name="event_image" accept="image/*">
                    <div class="help-text">Upload a new banner image to replace the current one.</div>
                    <?php if (!empty($event['image_url'])): ?>
                        <img src="../<?php echo htmlspecialchars($event['image_url']); ?>" alt="Event Image" class="event-image-preview">
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="success_title">Success Page Title</label>
                    <input type="text" id="success_title" name="success_title" value="<?php echo htmlspecialchars($success_settings['success_title'] ?? 'Registration Successful 🎉'); ?>">
                </div>

                <div class="form-group">
                    <label for="success_message">Success Page Message</label>
                    <textarea id="success_message" name="success_message" rows="4"><?php echo htmlspecialchars($success_settings['success_message'] ?? 'You have successfully registered for the event.'); ?></textarea>
                </div>

                <div class="form-group checkbox-group">
                    <input type="checkbox" id="show_approval_notice" name="show_approval_notice" value="1" <?php echo (!empty($success_settings['show_approval_notice']) ? 'checked' : ''); ?>>
                    <label for="show_approval_notice">Require approval before registration is confirmed</label>
                </div>

                <div class="form-group">
                    <label for="approval_message">Approval Notice Message</label>
                    <textarea id="approval_message" name="approval_message" rows="3"><?php echo htmlspecialchars($success_settings['approval_message'] ?? 'Your registration is under review. Please wait for approval confirmation via email.'); ?></textarea>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary btn-lg btn-block">Update Event</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>