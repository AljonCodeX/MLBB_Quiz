<?php
require __DIR__ . '/_layout.php';
$admin = require_admin();

// Delete a question
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['action'] ?? '') === 'delete') {
        $pdo->prepare('DELETE FROM questions WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
        flash('Question deleted.');
    }
    header('Location: questions.php');
    exit;
}

// Search + pagination
$search = trim((string)($_GET['q'] ?? ''));
$per = 20;
$where = '';
$params = [];
if ($search !== '') {
    $where = 'WHERE question LIKE ? OR category LIKE ?';
    $params = ['%' . $search . '%', '%' . $search . '%'];
}
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM questions $where");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$pages = max(1, (int)ceil($total / $per));
$page = min($pages, max(1, (int)($_GET['page'] ?? 1)));
$offset = ($page - 1) * $per;

$stmt = $pdo->prepare("SELECT * FROM questions $where ORDER BY id DESC LIMIT $per OFFSET $offset");
$stmt->execute($params);
$rows = $stmt->fetchAll();

admin_header('Questions', 'questions.php');
?>
<div class="toolbar">
  <form method="get" class="inline">
    <input name="q" value="<?= e($search) ?>" placeholder="Search question or category" />
    <button class="btn small" type="submit">Search</button>
    <?php if ($search !== ''): ?><a class="btn small ghost" href="questions.php">Clear</a><?php endif; ?>
  </form>
  <a class="btn" href="question_edit.php">+ Add question</a>
</div>

<div class="table-wrap">
<table>
  <thead><tr><th>ID</th><th>Image</th><th>Category</th><th>Question</th><th>Correct answer</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r):
      $opts = [$r['option_a'], $r['option_b'], $r['option_c'], $r['option_d']]; ?>
    <tr>
      <td><?= (int)$r['id'] ?></td>
      <td><?php if ($r['image']): ?><img class="thumb" src="../images/<?= e(rawurlencode($r['image'])) ?>" alt="" /><?php endif; ?></td>
      <td><?= e($r['category']) ?></td>
      <td><?= e($r['question']) ?></td>
      <td><?= e($opts[(int)$r['answer']] ?? '?') ?></td>
      <td class="actions">
        <a class="btn small" href="question_edit.php?id=<?= (int)$r['id'] ?>">Edit</a>
        <form method="post" class="inline" onsubmit="return confirm('Delete this question?')">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
          <input type="hidden" name="action" value="delete" />
          <input type="hidden" name="id" value="<?= (int)$r['id'] ?>" />
          <button class="btn small danger" type="submit">Delete</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="6" class="muted">No questions found.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>

<?php if ($pages > 1): ?>
<p class="pager">
  <?php for ($i = 1; $i <= $pages; $i++): ?>
    <a class="<?= $i === $page ? 'on' : '' ?>" href="?q=<?= e(urlencode($search)) ?>&amp;page=<?= $i ?>"><?= $i ?></a>
  <?php endfor; ?>
</p>
<?php endif; ?>
<p class="muted"><?= $total ?> question<?= $total === 1 ? '' : 's' ?></p>
<?php admin_footer(); ?>
