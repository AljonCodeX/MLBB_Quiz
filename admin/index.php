<?php
require __DIR__ . '/_layout.php';
$admin = require_admin();

$counts = [
    ['Questions', (int)$pdo->query('SELECT COUNT(*) FROM questions')->fetchColumn(), 'questions.php'],
    ['Players & admins', (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(), 'users.php'],
    ['Saved scores', (int)$pdo->query('SELECT COUNT(*) FROM scores')->fetchColumn(), 'scores.php'],
];

admin_header('Dashboard', 'index.php');
?>
<p class="muted">Welcome, <?= e($admin['username']) ?>. Pick something to manage.</p>
<div class="cards">
  <?php foreach ($counts as [$label, $n, $link]): ?>
    <a class="stat" href="<?= e($link) ?>"><span class="num"><?= $n ?></span><span><?= e($label) ?></span></a>
  <?php endforeach; ?>
</div>
<p><a class="btn" href="question_edit.php">+ Add a question</a></p>
<?php admin_footer(); ?>
