<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Event.php';
require_once __DIR__ . '/../classes/CustomField.php';

init_session();
require_admin();

$db = new Database($conn);
$eventClass = new Event($db);
$customFieldClass = new CustomField($db);

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

$message = '';
$error = '';

$success_settings = $db->get_row('SELECT * FROM registration_success_settings WHERE event_id = ' . $event_id . ' LIMIT 1');
$field_types = CUSTOM_FIELD_TYPES;
$custom_fields = $customFieldClass->get_event_fields($event_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_settings') {
        $success_title = sanitize($_POST['success_title'] ?? 'Registration Successful 🎉');
        $success_message = sanitize($_POST['success_message'] ?? 'You have successfully registered for the event.');
        $show_approval_notice = isset($_POST['show_approval_notice']) ? 1 : 0;
        $approval_message = sanitize($_POST['approval_message'] ?? 'Your registration is under review. Please wait for approval confirmation via email.');

        if ($success_settings) {
            $db->update('registration_success_settings', [
                'success_title' => $success_title,
                'success_message' => $success_message,
                'show_approval_notice' => $show_approval_notice,
                'approval_message' => $approval_message,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => get_current_user_id()
            ], ['success_id' => $success_settings['success_id']]);
        } else {
            $db->insert('registration_success_settings', [
                'event_id' => $event_id,
                'success_title' => $success_title,
                'success_message' => $success_message,
                'show_approval_notice' => $show_approval_notice,
                'approval_message' => $approval_message,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => get_current_user_id()
            ]);
        }

        $success_settings = $db->get_row('SELECT * FROM registration_success_settings WHERE event_id = ' . $event_id . ' LIMIT 1');
        $message = 'Success page settings have been saved.';

    } elseif ($action === 'create_field') {
        $field_name = sanitize($_POST['field_name'] ?? '');
        $field_type = sanitize($_POST['field_type'] ?? 'text');
        $placeholder = sanitize($_POST['placeholder'] ?? '');
        $options = sanitize($_POST['options'] ?? '');
        $validation_regex = sanitize($_POST['validation_regex'] ?? '');
        $required = isset($_POST['required']) ? 1 : 0;
        $sort_order = (int)($_POST['sort_order'] ?? 1);

        if (empty($field_name)) {
            $error = 'Field name is required.';
        } else {
            $options_json = null;
            if (in_array($field_type, ['dropdown', 'radio', 'checkbox']) && trim($options) !== '') {
                $options_array = array_filter(array_map('trim', explode('\n', $options)));
                $options_json = json_encode(array_values($options_array));
            }

            $create_result = $customFieldClass->create_field($event_id, [
                'field_name' => $field_name,
                'field_type' => $field_type,
                'placeholder' => $placeholder,
                'options_json' => $options_json,
                'validation_regex' => $validation_regex,
                'required' => $required,
                'sort_order' => $sort_order
            ]);

            if (!$create_result['success']) {
                $error = $create_result['message'];
            } else {
                header('Location: event-form.php?event_id=' . $event_id . '&created=1');
                exit;
            }
        }
    }
}

if (isset($_GET['delete_field'])) {
    $field_id = (int)$_GET['delete_field'];
    if ($field_id > 0) {
        $delete_result = $customFieldClass->delete_field($field_id);
        if ($delete_result['success']) {
            header('Location: event-form.php?event_id=' . $event_id . '&deleted=1');
            exit;
        }
        $error = $delete_result['message'];
    }
}

$custom_fields = $customFieldClass->get_event_fields($event_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Event Form - Admin Panel</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <style>
        .grid-two { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .field-table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        .field-table th, .field-table td { padding: 12px 10px; border: 1px solid #eaecef; }
        .small-meta { font-size: 13px; color: #6c757d; }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div>
                    <h1>Manage Form for “<?php echo htmlspecialchars($event['event_name']); ?>”</h1>
                    <p>Customize the registration experience and control the approval or auto-confirmation workflow.</p>
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

            <div class="grid-two">
                <div class="admin-card">
                    <h2>Success / Approval Settings</h2>
                    <form method="post">
                        <input type="hidden" name="action" value="save_settings">
                        <div class="form-group">
                            <label for="success_title">Success Title</label>
                            <input type="text" id="success_title" name="success_title" value="<?php echo htmlspecialchars($success_settings['success_title'] ?? 'Registration Successful 🎉'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="success_message">Success Message</label>
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
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </form>
                </div>

                <div class="admin-card">
                    <h2>Add Custom Registration Field</h2>
                    <form method="post">
                        <input type="hidden" name="action" value="create_field">
                        <div class="form-group required">
                            <label for="field_name">Field Label</label>
                            <input type="text" id="field_name" name="field_name" required>
                        </div>
                        <div class="form-group required">
                            <label for="field_type">Field Type</label>
                            <select id="field_type" name="field_type" required>
                                <?php foreach ($field_types as $type => $label): ?>
                                    <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="placeholder">Placeholder / Help Text</label>
                            <input type="text" id="placeholder" name="placeholder">
                        </div>
                        <div class="form-group">
                            <label for="options">Options (for dropdown, radio, checkbox)</label>
                            <textarea id="options" name="options" rows="3" placeholder="One option per line"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="validation_regex">Validation Pattern (optional)</label>
                            <input type="text" id="validation_regex" name="validation_regex" placeholder="/^[0-9]{2,}$/">
                        </div>
                        <div class="form-group checkbox-group">
                            <input type="checkbox" id="required" name="required" value="1">
                            <label for="required">Required field</label>
                        </div>
                        <div class="form-group">
                            <label for="sort_order">Sort Order</label>
                            <input type="number" id="sort_order" name="sort_order" value="1" min="1">
                        </div>
                        <button type="submit" class="btn btn-primary">Add Field</button>
                    </form>
                </div>
            </div>

            <div class="admin-card" style="margin-top: 24px;">
                <h2>Existing Custom Fields</h2>
                <?php if (empty($custom_fields)): ?>
                    <div class="alert alert-info">No custom fields have been added for this event yet.</div>
                <?php else: ?>
                    <table class="field-table">
                        <thead>
                            <tr>
                                <th>Label</th>
                                <th>Type</th>
                                <th>Required</th>
                                <th>Order</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($custom_fields as $field): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($field['field_name']); ?><div class="small-meta"><?php echo htmlspecialchars($field['placeholder']); ?></div></td>
                                <td><?php echo htmlspecialchars($field_types[$field['field_type']] ?? $field['field_type']); ?></td>
                                <td><?php echo $field['required'] ? 'Yes' : 'No'; ?></td>
                                <td><?php echo (int)$field['sort_order']; ?></td>
                                <td>
                                    <a href="event-form.php?event_id=<?php echo $event_id; ?>&delete_field=<?php echo (int)$field['custom_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remove this field?');">Delete</a>
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
