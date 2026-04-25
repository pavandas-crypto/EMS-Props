<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';

init_session();

// Redirect if already logged in
if (is_logged_in()) {
    if (is_admin()) {
        header('Location: ../admin/dashboard.php');
        exit;
    } elseif (is_verifier()) {
        header('Location: ../verifier/scanner.php');
        exit;
    }
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } else {
        // Rate limiting
        if (!check_rate_limit('login_' . $email, 5, 300)) {
            $error = 'Too many login attempts. Please try again later.';
        } else {
            $db = new Database($conn);
            $auth = new Auth($db);

            $result = $auth->login($email, $password);

            if ($result['success']) {
                // Redirect based on role
                if ($result['user']['role'] === ROLE_ADMIN) {
                    header('Location: ../admin/dashboard.php');
                    exit;
                } elseif ($result['user']['role'] === ROLE_VERIFIER) {
                    header('Location: ../verifier/scanner.php');
                    exit;
                } else {
                    $error = 'Invalid user role.';
                }
            } else {
                $error = $result['message'];
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
    <title>Login - Event Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="page-wrapper">
        <div class="container">
            <div class="login-container">
                <div class="login-card">
                    <h1>Login</h1>
                    <p>Access your admin panel or verifier scanner</p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <?php if ($message): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="form-group required">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required autofocus>
                        </div>
                        <div class="form-group required">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
                    </form>

                    <div style="text-align: center; margin-top: 20px;">
                        <a href="index.php">Back to Events</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>