<?php
require __DIR__ . '/../auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $s = $pdo->prepare('SELECT id, password_hash, role FROM users WHERE username = ?');
    $s->execute([trim((string)($_POST['username'] ?? ''))]);
    $u = $s->fetch();
    if ($u && password_verify((string)($_POST['password'] ?? ''), $u['password_hash']) && $u['role'] === 'admin') {
        session_regenerate_id(true);
        $_SESSION['uid'] = (int)$u['id'];
        unset($_SESSION['csrf']);
        header('Location: index.php');
        exit;
    }
    usleep(400000); // small delay slows down password guessing
    $error = 'Wrong username or password, or this account is not an admin.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin login</title>
  <link rel="stylesheet" href="admin.css" />
</head>
<body class="center">
  <main class="panel narrow">
    <h1>Admin login</h1>
    <?php if ($error): ?><div class="flash error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="stack">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
      <label>Username <input name="username" maxlength="20" required autocomplete="username" autofocus /></label>
      <label>Password <input name="password" type="password" maxlength="72" required autocomplete="current-password" /></label>
      <button class="btn" type="submit">Log in</button>
    </form>
    <p class="muted"><a href="../index.html">Back to the quiz</a></p>
  </main>
</body>
</html>
