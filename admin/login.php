<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';

init_session();

if (is_logged_in() && has_role(ROLE_ADMIN)) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } else {
        $db = new Database($conn);
        $auth = new Auth($db);
        $result = $auth->login($email, $password);

        if ($result['success']) {
            header('Location: dashboard.php');
            exit;
        }

        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Event Management System</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <style>
        body { background: #f4f7fb; }
        .login-container { max-width: 420px; margin: 80px auto; padding: 32px; background: #ffffff; border-radius: 12px; box-shadow: 0 12px 28px rgba(0,0,0,.08); }
        .login-container h1 { font-size: 26px; margin-bottom: 18px; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 600; }
        .form-group input { width: 100%; padding: 12px 14px; border: 1px solid #d1d9e6; border-radius: 8px; }
        .error-message { margin-bottom: 18px; padding: 12px; background: #fdecea; color: #b00020; border-radius: 8px; }
        .admin-note { margin-top: 18px; color: #55687e; font-size: 14px; }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="container">
            <div class="login-container">
                <h1>Admin Login</h1>
                <?php if ($error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="post" novalidate>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="admin@mail.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Password" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg btn-block">Sign In</button>
                </form>
                <p class="admin-note">Use admin credentials to manage events, registrations, and ticketing.</p>
            </div>
        </div>
    </div>
</body>
</html>
