<?php
header('Content-Type: application/json');
require __DIR__ . '/auth.php';

// Save a score (POST). Only logged-in players can save, and the name comes from their account.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
        http_response_code(415);
        exit(json_encode(['error' => 'Bad request']));
    }
    $user = current_user();
    if (!$user) {
        http_response_code(401);
        exit(json_encode(['error' => 'Please log in to save your score']));
    }

    $d = json_decode(file_get_contents('php://input'), true) ?: [];
    $score = (int)($d['score'] ?? -1);
    $total = (int)($d['total'] ?? 0);

    // shortcut: the score is sent by the browser, so a determined player can fake it; upgrade by checking answers on the server
    if ($total < 1 || $total > 50 || $score < 0 || $score > $total) {
        http_response_code(400);
        exit(json_encode(['error' => 'Invalid score']));
    }
    $pdo->prepare('INSERT INTO scores (user_id, name, score, total) VALUES (?, ?, ?, ?)')
        ->execute([$user['id'], $user['username'], $score, $total]);
    exit(json_encode(['ok' => true]));
}

// 10 random questions (GET ?action=questions)
if (($_GET['action'] ?? '') === 'questions') {
    $rows = $pdo->query('SELECT category, question, option_a, option_b, option_c, option_d, answer, image FROM questions ORDER BY RAND() LIMIT 10')->fetchAll();
    exit(json_encode(array_map(fn($r) => [
        'category' => $r['category'],
        'question' => $r['question'],
        'options' => [$r['option_a'], $r['option_b'], $r['option_c'], $r['option_d']],
        'answer' => (int)$r['answer'],
        'image' => $r['image'] ?: null,
    ], $rows)));
}

// Top 10 leaderboard (GET ?action=scores)
exit(json_encode($pdo->query('SELECT name, score, total FROM scores ORDER BY score DESC, id ASC LIMIT 10')->fetchAll()));
