<?php
require_once __DIR__ . '/../project/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$db          = getDatabaseConnection();
$professorId = $_SESSION['user']['id'];
$action      = $_GET['action'] ?? '';

require_once __DIR__ . '/../models/Assignment.php';
require_once __DIR__ . '/../models/Grade.php';

$assignmentModel = new Assignment($db);
$gradeModel      = new Grade($db);

if ($action === 'students') {
    $assignmentId = (int)($_GET['assignment_id'] ?? 0);

    if (!$assignmentModel->isAssignedTo($assignmentId, $professorId)) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }

    $sheet = $gradeModel->getGradeSheet($assignmentId);
    echo json_encode($sheet);
    exit;
}

if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data         = json_decode(file_get_contents('php://input'), true);
    $assignmentId = (int)($data['assignment_id'] ?? 0);
    $grades       = $data['grades'] ?? [];

    if (!$assignmentModel->isAssignedTo($assignmentId, $professorId)) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }

    foreach ($grades as $entry) {
        $studentId = (int)($entry['student_id'] ?? 0);
        $score     = (float)($entry['score'] ?? 0);
        if ($studentId > 0 && $score >= 0 && $score <= 20) {
            $gradeModel->save($studentId, $assignmentId, $score);
        }
    }

    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Unknown action']);
