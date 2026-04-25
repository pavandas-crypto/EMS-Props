<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Event.php';
require_once __DIR__ . '/../classes/Ticket.php';

init_session();
require_admin();

$db = new Database($conn);
$eventClass = new Event($db);
$ticketClass = new Ticket($db);

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$events = $eventClass->get_all_events(0, 50);
$templates = $db->get_results('SELECT * FROM ticket_templates ORDER BY template_id DESC');
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'create_template') {
        $template_name = sanitize($_POST['template_name'] ?? '');
        $layout_json = trim($_POST['layout_json'] ?? '');

        if (empty($template_name) || empty($layout_json)) {
            $error = 'Template name and layout JSON are required.';
        } elseif (!is_valid_json($layout_json)) {
            $error = 'Template layout must be valid JSON.';
        } else {
            $db->insert('ticket_templates', [
                'template_name' => $template_name,
                'layout_json' => $layout_json,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => get_current_user_id()
            ]);
            header('Location: generate-tickets.php?event_id=' . $event_id . '&template_created=1');
            exit;
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'generate_tickets') {
        $event_id = (int)($_POST['event_id'] ?? 0);
        $template_id = (int)($_POST['template_id'] ?? 1);

        if ($event_id <= 0) {
            $error = 'Please select an event to generate tickets.';
        } else {
            $result = $ticketClass->generate_bulk_tickets($event_id, $template_id);
            if ($result['success']) {
                $message = sprintf('Generated %d tickets. %d failed.', $result['generated'], $result['failed']);
            } else {
                $error = $result['message'];
            }
        }
    }
}

if (!empty($event_id)) {
    $event = $eventClass->get_event($event_id);
} else {
    $event = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Tickets - Admin Panel</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <style>
        .grid-two { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .template-table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        .template-table th, .template-table td { padding: 12px 10px; border: 1px solid #edf0f4; }
        .template-table th { background: #f8fafc; }
        .code-block { background: #f8f9fc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; font-family: monospace; font-size: 14px; white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div>
                    <h1>Bulk Ticket Generation</h1>
                    <p>Generate tickets for approved participants only. Select a ticket template and run bulk generation.</p>
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

            <div class="admin-card">
                <h2>Ticket Generation</h2>
                <form method="post">
                    <input type="hidden" name="action" value="generate_tickets">
                    <div class="form-group required">
                        <label for="event_id">Event</label>
                        <select id="event_id" name="event_id" required>
                            <option value="">-- Select Event --</option>
                            <?php foreach ($events as $item): ?>
                                <option value="<?php echo (int)$item['event_id']; ?>"<?php echo $event_id === (int)$item['event_id'] ? ' selected' : ''; ?>><?php echo htmlspecialchars($item['event_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group required">
                        <label for="template_id">Ticket Template</label>
                        <select id="template_id" name="template_id" required>
                            <?php foreach ($templates as $template): ?>
                                <option value="<?php echo (int)$template['template_id']; ?>"><?php echo htmlspecialchars($template['template_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Generate Bulk Tickets</button>
                </form>
            </div>

            <div class="grid-two" style="margin-top: 28px;">
                <div class="admin-card">
                    <h2>Create Ticket Template</h2>
                    <p>Use JSON to configure the template structure. Stored templates can be selected when generating tickets.</p>
                    <form method="post">
                        <input type="hidden" name="action" value="create_template">
                        <div class="form-group required">
                            <label for="template_name">Template Name</label>
                            <input type="text" id="template_name" name="template_name" required>
                        </div>
                        <div class="form-group required">
                            <label for="layout_json">Layout JSON</label>
                            <textarea id="layout_json" name="layout_json" rows="8" required>{
    "title": "Event Pass",
    "fields": [
        "event_name",
        "participant_name",
        "organization",
        "pass_number",
        "qr_code"
    ]
}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Template</button>
                    </form>
                </div>

                <div class="admin-card">
                    <h2>Available Templates</h2>
                    <?php if (empty($templates)): ?>
                        <div class="alert alert-info">No ticket templates created yet.</div>
                    <?php else: ?>
                        <table class="template-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Created At</th>
                                    <th>Layout</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($templates as $template): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($template['template_name']); ?></td>
                                        <td><?php echo htmlspecialchars($template['created_at']); ?></td>
                                        <td><div class="code-block"><?php echo htmlspecialchars($template['layout_json'] ?? ''); ?></div></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
