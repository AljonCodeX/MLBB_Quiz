<?php
require __DIR__ . '/_layout.php';
$admin = require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $uid = (int)($_POST['id'] ?? 0);
    $role = ($_POST['role'] ?? '') === 'admin' ? 'admin' : 'player';
    $password = (string)($_POST['password'] ?? '');

    if ($action === 'create') {
        $username = trim((string)($_POST['username'] ?? ''));
        if (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $username)) {
            flash('Username must be 3-20 letters, numbers or underscores.', 'error');
        } elseif (strlen($password) < 8 || strlen($password) > 72) {
            flash('Password must be 8-72 characters.', 'error');
        } else {
            try {
                $pdo->prepare('INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)')
                    ->execute([$username, password_hash($password, PASSWORD_DEFAULT), $role]);
                flash("User $username created.");
            } catch (PDOException $ex) {
                if ($ex->getCode() === '23000') flash('That username is already taken.', 'error');
                else throw $ex;
            }
        }
    } elseif ($action === 'update') {
        if ($uid === (int)$admin['id'] && $role !== 'admin') {
            flash('You cannot remove your own admin role.', 'error');
        } elseif ($password !== '' && (strlen($password) < 8 || strlen($password) > 72)) {
            flash('New password must be 8-72 characters.', 'error');
        } else {
            $pdo->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$role, $uid]);
            if ($password !== '') {
                $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($password, PASSWORD_DEFAULT), $uid]);
            }
            flash('User updated.');
        }
    } elseif ($action === 'delete') {
        if ($uid === (int)$admin['id']) {
            flash('You cannot delete your own account.', 'error');
        } else {
            $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$uid]);
            flash('User deleted. Their saved scores stay on the leaderboard.');
        }
    }
    header('Location: users.php');
    exit;
}

$users = $pdo->query('SELECT u.id, u.username, u.role, u.created_at, (SELECT COUNT(*) FROM scores s WHERE s.user_id = u.id) AS plays FROM users u ORDER BY u.id')->fetchAll();

admin_header('Users', 'users.php');
?>
<h2>Add a user</h2>
<form method="post" class="toolbar">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
  <input type="hidden" name="action" value="create" />
  <input name="username" maxlength="20" placeholder="Username" required autocomplete="off" />
  <input name="password" type="password" maxlength="72" placeholder="Password (8+ characters)" required autocomplete="new-password" />
  <select name="role"><option value="player">Player</option><option value="admin">Admin</option></select>
  <button class="btn" type="submit">Add user</button>
</form>

<h2>All users</h2>
<div class="table-wrap">
<table>
  <thead><tr><th>ID</th><th>Username</th><th>Role</th><th>New password</th><th>Saved scores</th><th>Joined</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($users as $u): $fid = 'u' . (int)$u['id']; ?>
    <tr>
      <td><?= (int)$u['id'] ?></td>
      <td><?= e($u['username']) ?><?= (int)$u['id'] === (int)$admin['id'] ? ' <span class="muted">(you)</span>' : '' ?></td>
      <td>
        <select name="role" form="<?= $fid ?>">
          <option value="player" <?= $u['role'] === 'player' ? 'selected' : '' ?>>Player</option>
          <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
      </td>
      <td><input type="password" name="password" form="<?= $fid ?>" maxlength="72" placeholder="leave blank to keep" autocomplete="new-password" /></td>
      <td><?= (int)$u['plays'] ?></td>
      <td><?= e($u['created_at']) ?></td>
      <td class="actions">
        <form method="post" id="<?= $fid ?>" class="inline">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
          <input type="hidden" name="action" value="update" />
          <input type="hidden" name="id" value="<?= (int)$u['id'] ?>" />
          <button class="btn small" type="submit">Save</button>
        </form>
        <?php if ((int)$u['id'] !== (int)$admin['id']): ?>
        <form method="post" class="inline" onsubmit="return confirm('Delete this user?')">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
          <input type="hidden" name="action" value="delete" />
          <input type="hidden" name="id" value="<?= (int)$u['id'] ?>" />
          <button class="btn small danger" type="submit">Delete</button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php admin_footer(); ?>
