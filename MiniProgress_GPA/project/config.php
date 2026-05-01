<?php
// Database config
define('DB_HOST', 'localhost');
define('DB_NAME', 'gpa_system');
define('DB_USER', 'root');
define('DB_PASS', '');

require_once __DIR__ . '/i18n.php';

// PDO Connection
function getDatabaseConnection() {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        ensureAppSchema($pdo);
    }
    return $pdo;
}

function ensureAppSchema($pdo) {
    try {
        // semesters table
        $pdo->exec("CREATE TABLE IF NOT EXISTS semesters (
            id int(11) NOT NULL AUTO_INCREMENT,
            label varchar(100) NOT NULL,
            year varchar(20) NOT NULL,
            is_active tinyint(1) NOT NULL DEFAULT 0,
            created_at timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        // Add semester_id to courses if missing
        $cols = $pdo->query("SHOW COLUMNS FROM courses")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('semester_id', $cols)) {
            // Insert a default semester first
            $pdo->exec("INSERT INTO semesters (label, year, is_active) VALUES ('S1','2024-2025',1)");
            $semId = $pdo->lastInsertId();
            if (!$semId) {
                $row = $pdo->query("SELECT id FROM semesters WHERE is_active=1 LIMIT 1")->fetch();
                $semId = $row ? $row['id'] : 1;
            }
            $pdo->exec("ALTER TABLE courses ADD COLUMN semester_id int(11) NOT NULL DEFAULT $semId");
        }

        // enrollments table
        $pdo->exec("CREATE TABLE IF NOT EXISTS enrollments (
            id int(11) NOT NULL AUTO_INCREMENT,
            student_id int(11) NOT NULL,
            semester_id int(11) NOT NULL,
            created_at timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (id),
            UNIQUE KEY uq_enrollment (student_id, semester_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        // Ensure at least one active semester
        $cnt = $pdo->query("SELECT COUNT(*) FROM semesters")->fetchColumn();
        if (!$cnt) {
            $pdo->exec("INSERT INTO semesters (label, year, is_active) VALUES ('S1','2024-2025',1)");
        }

        // Ensure score column is nullable (for "no grade yet")
        $pdo->exec("ALTER TABLE grades MODIFY score decimal(5,2) DEFAULT NULL");
    } catch (PDOException $e) {
        // silently fail if tables aren't ready yet
    }
}

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helpers
function redirect($page, $extra = '') {
    $lang = currentLang();
    header("Location: index.php?page=$page&lang=$lang$extra");
    exit();
}

function requireRole($role) {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== $role) {
        redirect('login');
    }
}

function sanitize($value) {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

// Flash messages
function flash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function renderFlash() {
    $flash = getFlash();
    if (!$flash) return;
    $class = $flash['type'] === 'error' ? 'danger' : ($flash['type'] === 'warning' ? 'warning' : 'success');
    echo '<div class="alert alert-' . $class . ' alert-dismissible fade show" role="alert">'
        . htmlspecialchars($flash['msg'])
        . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
}

// Session timeout (30 min)
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > 1800) {
            session_destroy();
            redirect('login');
        }
    }
    $_SESSION['last_activity'] = time();
}
