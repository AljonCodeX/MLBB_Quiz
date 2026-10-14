<?php
require __DIR__ . '/_layout.php';
$admin = require_admin();

const UPLOAD_DIR = __DIR__ . '/../images/';
const MAX_UPLOAD = 2 * 1024 * 1024; // 2 MB

// Save an uploaded picture into images/ under a random name. Returns the file name, or null.
function handle_upload(array $file, array &$errors): ?string {
    if ($file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) { $errors[] = 'The image upload failed. Try a smaller file.'; return null; }
    if ($file['size'] > MAX_UPLOAD) { $errors[] = 'The image must be 2 MB or smaller.'; return null; }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'][$mime] ?? null;
    if (!$ext) { $errors[] = 'The image must be a JPG, PNG, WEBP or GIF.'; return null; }
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $name)) { $errors[] = 'Could not save the image into the images folder.'; return null; }
    return $name;
}

// Load the question being edited, or start with an empty one
$id = (int)($_GET['id'] ?? 0);
$row = ['category' => '', 'question' => '', 'option_a' => '', 'option_b' => '', 'option_c' => '', 'option_d' => '', 'answer' => 0, 'image' => ''];
if ($id) {
    $s = $pdo->prepare('SELECT * FROM questions WHERE id = ?');
    $s->execute([$id]);
    $found = $s->fetch();
    if (!$found) {
        flash('Question not found.', 'error');
        header('Location: questions.php');
        exit;
    }
    $row = $found;
    $row['image'] = (string)$row['image'];
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $limits = ['category' => 50, 'question' => 255, 'option_a' => 100, 'option_b' => 100, 'option_c' => 100, 'option_d' => 100];
    foreach ($limits as $field => $max) {
        $row[$field] = trim((string)($_POST[$field] ?? ''));
        if ($row[$field] === '') $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
        elseif (mb_strlen($row[$field]) > $max) $errors[] = ucfirst(str_replace('_', ' ', $field)) . " must be $max characters or fewer.";
    }
    $row['answer'] = (int)($_POST['answer'] ?? -1);
    if ($row['answer'] < 0 || $row['answer'] > 3) $errors[] = 'Choose which option is the correct answer.';

    // The quiz finds the right option by its text, so the four options must all be different
    $opts = [$row['option_a'], $row['option_b'], $row['option_c'], $row['option_d']];
    if (count(array_unique(array_map('mb_strtolower', $opts))) < 4) $errors[] = 'The four options must all be different.';

    // Picture: an uploaded file wins, otherwise use the typed file name from the images folder
    $row['image'] = trim((string)($_POST['image'] ?? ''));
    if ($row['image'] !== '' && (!preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{0,250}\.(jpe?g|png|gif|webp)$/i', $row['image']) || str_contains($row['image'], '..'))) {
        $errors[] = 'Image file name can only use letters, numbers, dot, dash and underscore, and must end in .jpg, .png, .gif or .webp.';
    }
    if (isset($_FILES['upload'])) {
        $uploaded = handle_upload($_FILES['upload'], $errors);
        if ($uploaded !== null) $row['image'] = $uploaded;
    }

    if (!$errors) {
        $values = [$row['category'], $row['question'], $row['option_a'], $row['option_b'], $row['option_c'], $row['option_d'], $row['answer'], $row['image'] === '' ? null : $row['image']];
        if ($id) {
            $pdo->prepare('UPDATE questions SET category=?, question=?, option_a=?, option_b=?, option_c=?, option_d=?, answer=?, image=? WHERE id=?')
                ->execute([...$values, $id]);
            flash('Question updated.');
        } else {
            $pdo->prepare('INSERT INTO questions (category, question, option_a, option_b, option_c, option_d, answer, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute($values);
            flash('Question added.');
        }
        header('Location: questions.php');
        exit;
    }
}

$categories = $pdo->query('SELECT DISTINCT category FROM questions ORDER BY category')->fetchAll(PDO::FETCH_COLUMN);

admin_header($id ? 'Edit question' : 'Add question', 'questions.php');
?>
<?php if ($errors): ?>
  <div class="flash error"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="stack">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />

  <label>Category
    <input name="category" maxlength="50" list="cats" value="<?= e($row['category']) ?>" required />
    <datalist id="cats"><?php foreach ($categories as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?></datalist>
  </label>

  <label>Question
    <input name="question" maxlength="255" value="<?= e($row['question']) ?>" required />
  </label>

  <fieldset>
    <legend>Options (select the correct one)</legend>
    <?php foreach (['a', 'b', 'c', 'd'] as $i => $letter): ?>
      <label class="opt">
        <input type="radio" name="answer" value="<?= $i ?>" <?= (int)$row['answer'] === $i ? 'checked' : '' ?> />
        <span class="letter"><?= strtoupper($letter) ?></span>
        <input name="option_<?= $letter ?>" maxlength="100" value="<?= e($row['option_' . $letter]) ?>" required />
      </label>
    <?php endforeach; ?>
  </fieldset>

  <fieldset>
    <legend>Picture (optional)</legend>
    <?php if ($row['image'] !== ''): ?>
      <p><img class="preview" src="../images/<?= e(rawurlencode($row['image'])) ?>" alt="" onerror="this.style.display='none'" /></p>
    <?php endif; ?>
    <label>Upload a new picture (JPG, PNG, WEBP or GIF, max 2 MB)
      <input type="file" name="upload" accept="image/jpeg,image/png,image/webp,image/gif" />
    </label>
    <label>Or a file name already in the images folder
      <input name="image" maxlength="255" placeholder="layla.png" value="<?= e($row['image']) ?>" />
    </label>
    <p class="muted">Clear the file name to remove the picture from this question. The file itself stays in the images folder.</p>
  </fieldset>

  <div class="toolbar">
    <button class="btn" type="submit"><?= $id ? 'Save changes' : 'Add question' ?></button>
    <a class="btn ghost" href="questions.php">Cancel</a>
  </div>
</form>
<?php admin_footer(); ?>
