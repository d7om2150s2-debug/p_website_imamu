<?php
// ============================================================
//  api/login.php — Authenticate an existing user
//  POST JSON: { username, password }
//  Returns  : { success: bool, message: string, user?: {id,full_name,username,email} }
// ============================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST')    { echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit; }

require_once __DIR__ . '/../config.php';

// ── Read JSON body ────────────────────────────────────────────
$body     = json_decode(file_get_contents('php://input'), true);
$username = trim($body['username'] ?? '');
$password = $body['password']      ?? '';

// ── Validate ─────────────────────────────────────────────────
if ($username === '' || $password === '') {
    echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
    exit;
}

// ── Lookup user ───────────────────────────────────────────────
try {
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        'SELECT id, full_name, username, email, password FROM users WHERE username = ? LIMIT 1'
    );
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) {
        // User does not exist → prompt to register
        echo json_encode([
            'success'      => false,
            'not_found'    => true,
            'message'      => 'No account found with that username. Please register first.'
        ]);
        exit;
    }

    if (!password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Incorrect password. Please try again.']);
        exit;
    }

    // Success — never return the password hash
    unset($user['password']);
    echo json_encode([
        'success' => true,
        'message' => 'Welcome back, ' . $user['full_name'] . '!',
        'user'    => $user
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error. Please try again later.']);
}
