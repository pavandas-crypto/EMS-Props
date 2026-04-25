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

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_verifier') {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $assigned_events = $_POST['assigned_events'] ?? [];

        if (empty($name) || empty($email) || empty($password)) {
            $error = 'Name, email, and password are required.';
        } elseif (!is_valid_email($email)) {
            $error = 'Invalid email format.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            // Check if email exists
            $existing = $db->count('users', ['email' => $email]);
            if ($existing > 0) {
                $error = 'Email already exists.';
            } else {
                // Create verifier
                $verifier_id = $db->insert('users', [
                    'name' => $name,
                    'email' => $email,
                    'password' => hash_password($password),
                    'role' => ROLE_VERIFIER,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                // Assign events
                foreach ($assigned_events as $event_id) {
                    $db->insert('verifier_events', [
                        'verifier_id' => $verifier_id,
                        'event_id' => (int)$event_id,
                        'assigned_by' => get_current_user_id(),
                        'assigned_at' => date('Y-m-d H:i:s')
                    ]);
                }

                $message = 'Verifier created successfully.';
            }
        }
    } elseif ($action === 'update_assignments') {
        $verifier_id = (int)($_POST['verifier_id'] ?? 0);
        $assigned_events = $_POST['assigned_events'] ?? [];

        if ($verifier_id > 0) {
            // Remove existing assignments
            $db->delete('verifier_events', ['verifier_id' => $verifier_id]);

            // Add new assignments
            foreach ($assigned_events as $event_id) {
                $db->insert('verifier_events', [
                    'verifier_id' => $verifier_id,
                    'event_id' => (int)$event_id,
                    'assigned_by' => get_current_user_id(),
                    'assigned_at' => date('Y-m-d H:i:s')
                ]);
            }

            $message = 'Verifier assignments updated successfully.';
        }
    }
}

// Get all verifiers
$verifiers = $db->get_results("SELECT * FROM users WHERE role = '" . ROLE_VERIFIER . "' ORDER BY created_at DESC");

// Get all events
$events = $eventClass->get_all_events(0, 100);

// Get assignments for each verifier
$assignments = [];
foreach ($verifiers as $verifier) {
    $assignments[$verifier['user_id']] = $db->get_results("SELECT event_id FROM verifier_events WHERE verifier_id = " . (int)$verifier['user_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Verifiers - Admin Panel</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <style>
        .verifier-table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        .verifier-table th, .verifier-table td { padding: 12px 10px; border: 1px solid #edf0f4; }
        .verifier-table th { background: #f8fafc; }
        .checkbox-group { display: flex; flex-wrap: wrap; gap: 10px; }
        .checkbox-item { display: flex; align-items: center; gap: 5px; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { background: #fff; margin: 10% auto; padding: 20px; border-radius: 8px; width: 90%; max-width: 600px; }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div>
                    <h1>Manage Verifiers</h1>
                    <p>Create verifiers and assign them to events for QR code scanning and attendance verification.</p>
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
                <h2>Create New Verifier</h2>
                <form method="post">
                    <input type="hidden" name="action" value="create_verifier">
                    <div class="form-group required">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group required">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group required">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Assign to Events</label>
                        <div class="checkbox-group">
                            <?php foreach ($events as $event): ?>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="event_<?php echo $event['event_id']; ?>" name="assigned_events[]" value="<?php echo $event['event_id']; ?>">
                                    <label for="event_<?php echo $event['event_id']; ?>"><?php echo htmlspecialchars($event['event_name']); ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Verifier</button>
                </form>
            </div>

            <div class="admin-card" style="margin-top: 24px;">
                <h2>Existing Verifiers</h2>
                <?php if (empty($verifiers)): ?>
                    <div class="alert alert-info">No verifiers have been created yet.</div>
                <?php else: ?>
                    <table class="verifier-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Assigned Events</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($verifiers as $verifier): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($verifier['name']); ?></td>
                                <td><?php echo htmlspecialchars($verifier['email']); ?></td>
                                <td>
                                    <?php
                                    $assigned = $assignments[$verifier['user_id']] ?? [];
                                    $event_names = [];
                                    foreach ($assigned as $assign) {
                                        foreach ($events as $event) {
                                            if ($event['event_id'] == $assign['event_id']) {
                                                $event_names[] = $event['event_name'];
                                                break;
                                            }
                                        }
                                    }
                                    echo htmlspecialchars(implode(', ', $event_names));
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($verifier['created_at']); ?></td>
                                <td>
                                    <button class="btn btn-secondary btn-sm" onclick="editAssignments(<?php echo $verifier['user_id']; ?>, '<?php echo htmlspecialchars($verifier['name']); ?>')">Edit Assignments</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Edit Assignments Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>Edit Assignments for <span id="verifierName"></span></h3>
            <form method="post">
                <input type="hidden" name="action" value="update_assignments">
                <input type="hidden" id="verifierId" name="verifier_id" value="">
                <div class="form-group">
                    <label>Assign to Events</label>
                    <div class="checkbox-group">
                        <?php foreach ($events as $event): ?>
                            <div class="checkbox-item">
                                <input type="checkbox" id="edit_event_<?php echo $event['event_id']; ?>" name="assigned_events[]" value="<?php echo $event['event_id']; ?>">
                                <label for="edit_event_<?php echo $event['event_id']; ?>"><?php echo htmlspecialchars($event['event_name']); ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editAssignments(verifierId, verifierName) {
            document.getElementById('verifierId').value = verifierId;
            document.getElementById('verifierName').textContent = verifierName;
            document.getElementById('editModal').style.display = 'block';

            // Load current assignments
            fetch('../api/verifier-events.php?verifier_id=' + verifierId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const assignedEvents = data.data.map(item => item.event_id);
                        document.querySelectorAll('#editModal input[type="checkbox"]').forEach(checkbox => {
                            const eventId = checkbox.value;
                            checkbox.checked = assignedEvents.includes(parseInt(eventId));
                        });
                    }
                });
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }
    </script>
</body>
</html>