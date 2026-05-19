<?php
// ============================================================
//  api/feedback.php — Save and retrieve feedback
//  POST { name, email, topic, message, user_id? } → save
//  GET                                             → fetch all
// ============================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_once __DIR__ . '/../config.php';

// ── GET: return all feedback ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $pdo  = getDB();
        $stmt = $pdo->query(
            'SELECT name, topic, message, created_at FROM feedback ORDER BY created_at DESC LIMIT 50'
        );
        echo json_encode(['success' => true, 'feedback' => $stmt->fetchAll()]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Could not load feedback.']);
    }
    exit;
}

// ── POST: save new feedback ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body    = json_decode(file_get_contents('php://input'), true);
    $name    = trim($body['name']    ?? '');
    $email   = trim($body['email']   ?? '');
    $topic   = trim($body['topic']   ?? '');
    $message = trim($body['message'] ?? '');
    $userId  = isset($body['user_id']) ? (int)$body['user_id'] : null;

    $errors = [];
    if ($name    === '') $errors[] = 'Name is required.';
    if ($email   === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if ($topic   === '') $errors[] = 'Please select a topic.';
    if ($message === '') $errors[] = 'Feedback message is required.';

    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
        exit;
    }

    try {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            'INSERT INTO feedback (user_id, name, email, topic, message) VALUES (?,?,?,?,?)'
        );
        $stmt->execute([$userId, $name, $email, $topic, $message]);
        echo json_encode(['success' => true, 'message' => 'Thank you for your feedback!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Could not save feedback. Please try again.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Method not allowed']);
