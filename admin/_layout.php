<?php
// Shared by every admin page: login check, flash messages and the page frame.
require_once __DIR__ . '/../auth.php';

// Call at the top of every admin page. Sends non-admins to the login page.
function require_admin(): array {
    $user = current_user();
    if (!$user || $user['role'] !== 'admin') {
        header('Location: login.php');
        exit;
    }
    return $user;
}

// Show a message on the next page load. $type is 'ok' or 'error'.
function flash(string $message, string $type = 'ok'): void {
    $_SESSION['flash'] = [$message, $type];
}

function admin_header(string $title, string $active = ''): void {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    $user = current_user();
    $tabs = ['index.php' => 'Dashboard', 'questions.php' => 'Questions', 'users.php' => 'Users', 'scores.php' => 'Scores'];
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= e($title) ?> – MLBB Admin</title>
  <link rel="stylesheet" href="admin.css" />
</head>
<body>
  <header class="bar">
    <div class="bar-in">
      <strong class="brand">MLBB Admin</strong>
      <nav>
        <?php foreach ($tabs as $file => $label): ?>
          <a href="<?= e($file) ?>" class="<?= $active === $file ? 'on' : '' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
        <a href="../index.html" target="_blank" rel="noopener">View quiz</a>
      </nav>
      <form method="post" action="logout.php" class="inline">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
        <span class="muted"><?= e($user['username'] ?? '') ?></span>
        <button class="btn small ghost" type="submit">Log out</button>
      </form>
    </div>
  </header>
  <main class="panel">
    <h1><?= e($title) ?></h1>
    <?php if ($flash): ?><div class="flash <?= e($flash[1]) ?>"><?= e($flash[0]) ?></div><?php endif; ?>
<?php
}

function admin_footer(): void {
    ?>
  </main>
</body>
</html>
<?php
}
