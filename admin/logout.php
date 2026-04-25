<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';

init_session();

if (is_logged_in()) {
    $db = new Database($conn);
    $auth = new Auth($db);
    $auth->logout();
}

header('Location: login.php');
exit;
