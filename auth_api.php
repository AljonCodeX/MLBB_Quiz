<?php
// Player accounts: GET ?action=me, POST ?action=register | login | logout (JSON in, JSON out)
header('Content-Type: application/json');
require __DIR__ . '/auth.php';

function out(array $data, int $code = 200) {
    http_response_code($code);
    exit(json_encode($data));
}

$action = $_GET['action'] ?? '';

if ($action === 'me') {
    out(['user' => current_user()]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(['error' => 'POST required'], 405);
// A cross-site form cannot send JSON, so this also blocks CSRF
if (!str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) out(['error' => 'Bad request'], 415);

$d = json_decode(file_get_contents('php://input'), true) ?: [];
$username = trim((string)($d['username'] ?? ''));
$password = (string)($d['password'] ?? '');

if ($action === 'logout') {
    $_SESSION = [];
    session_regenerate_id(true);
    out(['ok' => true]);
}

if ($action === 'register') {
    if (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $username)) {
        out(['error' => 'Username must be 3-20 letters, numbers or underscores'], 400);
    }
    if (strlen($password) < 8 || strlen($password) > 72) {
        out(['error' => 'Password must be 8-72 characters'], 400);
    }
    try {
        $pdo->prepare('INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)')
            ->execute([$username, password_hash($password, PASSWORD_DEFAULT), 'player']);
    } catch (PDOException $ex) {
        if ($ex->getCode() === '23000') out(['error' => 'That username is already taken'], 409);
        throw $ex;
    }
    $id = (int)$pdo->lastInsertId();
    session_regenerate_id(true);
    $_SESSION['uid'] = $id;
    out(['user' => ['id' => $id, 'username' => $username, 'role' => 'player']]);
}

if ($action === 'login') {
    $s = $pdo->prepare('SELECT id, username, password_hash, role FROM users WHERE username = ?');
    $s->execute([$username]);
    $u = $s->fetch();
    if (!$u || !password_verify($password, $u['password_hash'])) {
        usleep(400000); // small delay slows down password guessing
        out(['error' => 'Wrong username or password'], 401);
    }
    session_regenerate_id(true);
    $_SESSION['uid'] = (int)$u['id'];
    out(['user' => ['id' => (int)$u['id'], 'username' => $u['username'], 'role' => $u['role']]]);
}

out(['error' => 'Unknown action'], 404);
