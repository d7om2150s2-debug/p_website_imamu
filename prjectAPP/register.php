<?php
// ============================================================
//  api/register.php — Register a new user
//  POST JSON: { full_name, username, email, password, gender, nationality }
//  Returns  : { success: bool, message: string }
// ============================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST')    { echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit; }

require_once __DIR__ . '/../config.php';

// ── Read JSON body ────────────────────────────────────────────
$body = json_decode(file_get_contents('php://input'), true);

$fullName    = trim($body['full_name']    ?? '');
$username    = trim($body['username']     ?? '');
$email       = trim($body['email']        ?? '');
$password    = $body['password']          ?? '';
$gender      = $body['gender']            ?? 'prefer_not';
$nationality = trim($body['nationality']  ?? '');

// ── Server-side validation ────────────────────────────────────
$errors = [];

if ($fullName === '')    $errors[] = 'Full name is required.';
if ($username === '')    $errors[] = 'Username is required.';
elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username))
                         $errors[] = 'Username must be 3–30 characters (letters, numbers, underscores).';
if ($email === '')       $errors[] = 'Email is required.';
elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
                         $errors[] = 'Invalid email address.';
if ($password === '')    $errors[] = 'Password is required.';
elseif (strlen($password) < 8)
                         $errors[] = 'Password must be at least 8 characters.';
if (!in_array($gender, ['male','female','prefer_not'], true))
                         $errors[] = 'Invalid gender value.';

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// ── Insert into DB ────────────────────────────────────────────
try {
    $pdo = getDB();

    // Check duplicate username
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username already taken. Please choose another.']);
        exit;
    }

    // Check duplicate email
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'An account with this email already exists. Please sign in.']);
        exit;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare(
        'INSERT INTO users (full_name, username, email, password, gender, nationality)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$fullName, $username, $email, $hash, $gender, $nationality]);

    echo json_encode(['success' => true, 'message' => 'Registration successful! You can now sign in.']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error. Please try again later.']);
}
