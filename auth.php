<?php
// Shared by api.php, auth_api.php, create_admin.php and the admin pages:
// database connection, login session and a few small helpers.
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,   // JavaScript cannot read the session cookie
        'samesite' => 'Lax',  // not sent with cross-site POSTs
        'secure' => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}

// The logged-in user, read fresh from the database (so a deleted or demoted user loses access at once)
function current_user(): ?array {
    global $pdo;
    static $user = false;
    if ($user !== false) return $user;
    $user = null;
    if (!empty($_SESSION['uid'])) {
        $s = $pdo->prepare('SELECT id, username, role FROM users WHERE id = ?');
        $s->execute([$_SESSION['uid']]);
        $user = $s->fetch() ?: null;
    }
    return $user;
}

// CSRF protection for the admin forms
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) {
        http_response_code(403);
        exit('Invalid or expired form. Go back, refresh the page and try again.');
    }
}

// Escape text for HTML
function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
