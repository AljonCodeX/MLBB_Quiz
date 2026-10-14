<?php
// Run this ONCE in your browser to create the first admin account, then DELETE this file.
require __DIR__ . '/auth.php';

$adminExists = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn() > 0;
$error = '';
$done = false;

if (!$adminExists && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $username)) {
        $error = 'Username must be 3-20 letters, numbers or underscores.';
    } elseif (strlen($password) < 8 || strlen($password) > 72) {
        $error = 'Password must be 8-72 characters.';
    } else {
        try {
            $pdo->prepare('INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)')
                ->execute([$username, password_hash($password, PASSWORD_DEFAULT), 'admin']);
            $done = true;
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') $error = 'That username already exists. Pick another, or promote it in phpMyAdmin (users table, role = admin).';
            else throw $ex;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Create admin</title>
  <link rel="stylesheet" href="admin/admin.css" />
</head>
<body class="center">
  <main class="panel narrow">
    <h1>Create admin account</h1>
    <?php if ($done): ?>
      <div class="flash ok">Admin created. <strong>Now delete create_admin.php from your server.</strong></div>
      <p><a class="btn" href="admin/login.php">Go to admin login</a></p>
    <?php elseif ($adminExists): ?>
      <div class="flash error">An admin account already exists. Delete this file (create_admin.php) from your server.</div>
    <?php else: ?>
      <?php if ($error): ?><div class="flash error"><?= e($error) ?></div><?php endif; ?>
      <form method="post" class="stack">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
        <label>Username <input name="username" maxlength="20" required autocomplete="username" /></label>
        <label>Password <input name="password" type="password" maxlength="72" required autocomplete="new-password" /></label>
        <p class="muted">Password: at least 8 characters.</p>
        <button class="btn" type="submit">Create admin</button>
      </form>
    <?php endif; ?>
  </main>
</body>
</html>
