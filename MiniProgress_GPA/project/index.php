<?php
require_once __DIR__ . '/config.php';

checkSessionTimeout();

$page = $_GET['page'] ?? 'login';

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/AdminController.php';
require_once __DIR__ . '/../controllers/ProfessorController.php';
require_once __DIR__ . '/../controllers/StudentController.php';

$db = getDatabaseConnection();

switch (true) {

    case in_array($page, ['login', 'logout', 'doLogin', 'changePassword', 'doChangePassword']):
        if (in_array($page, ['changePassword', 'doChangePassword']) && !isset($_SESSION['user'])) {
            redirect('login');
        }
        $controller = new AuthController($db);
        break;

    case (str_starts_with($page, 'admin.')):
        requireRole('admin');
        $controller = new AdminController($db);
        break;

    case (str_starts_with($page, 'professor.')):
        requireRole('professor');
        $controller = new ProfessorController($db);
        break;

    case (str_starts_with($page, 'student.')):
        requireRole('student');
        $controller = new StudentController($db);
        break;

    default:
        redirect('login');
        exit;
}

$parts  = explode('.', $page);
$action = $parts[1] ?? $page;

if (!method_exists($controller, $action)) {
    die("❌ Action '$action' not found.");
}

$controller->$action();
