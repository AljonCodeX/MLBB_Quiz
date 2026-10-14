<?php
require __DIR__ . '/_layout.php';
$admin = require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['action'] ?? '') === 'delete') {
        $pdo->prepare('DELETE FROM scores WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
        flash('Score deleted.');
    }
    header('Location: scores.php');
    exit;
}

// Latest 100 scores. "name" is what was shown on the leaderboard when the score was saved.
$rows = $pdo->query('SELECT s.id, s.name, s.score, s.total, s.created_at, u.username FROM scores s LEFT JOIN users u ON u.id = s.user_id ORDER BY s.id DESC LIMIT 100')->fetchAll();

admin_header('Scores', 'scores.php');
?>
<p class="muted">Showing the latest 100 saved scores.</p>
<div class="table-wrap">
<table>
  <thead><tr><th>ID</th><th>Name</th><th>Account</th><th>Score</th><th>Date</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= (int)$r['id'] ?></td>
      <td><?= e($r['name']) ?></td>
      <td><?= $r['username'] !== null ? e($r['username']) : '<span class="muted">none / deleted</span>' ?></td>
      <td><?= (int)$r['score'] ?>/<?= (int)$r['total'] ?></td>
      <td><?= e($r['created_at']) ?></td>
      <td class="actions">
        <form method="post" class="inline" onsubmit="return confirm('Delete this score?')">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
          <input type="hidden" name="action" value="delete" />
          <input type="hidden" name="id" value="<?= (int)$r['id'] ?>" />
          <button class="btn small danger" type="submit">Delete</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="6" class="muted">No scores yet.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>
<?php admin_footer(); ?>
