<?php
header('Content-Type: application/json');
require 'config.php';

// Save a score (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true) ?: [];
    $name = mb_substr(trim((string)($d['name'] ?? '')), 0, 20);
    $score = (int)($d['score'] ?? -1);
    $total = (int)($d['total'] ?? 0);

    // shortcut: the score is sent by the browser, so a determined player can fake it; upgrade by checking answers on the server
    if ($name === '' || $total < 1 || $total > 50 || $score < 0 || $score > $total) {
        http_response_code(400);
        exit(json_encode(['error' => 'Invalid score']));
    }
    $pdo->prepare('INSERT INTO scores (name, score, total) VALUES (?, ?, ?)')->execute([$name, $score, $total]);
    exit(json_encode(['ok' => true]));
}

// 10 random questions (GET ?action=questions)
if (($_GET['action'] ?? '') === 'questions') {
    $rows = $pdo->query('SELECT category, question, option_a, option_b, option_c, option_d, answer FROM questions ORDER BY RAND() LIMIT 10')->fetchAll();
    exit(json_encode(array_map(fn($r) => [
        'category' => $r['category'],
        'question' => $r['question'],
        'options' => [$r['option_a'], $r['option_b'], $r['option_c'], $r['option_d']],
        'answer' => (int)$r['answer'],
    ], $rows)));
}

// Top 10 leaderboard (GET ?action=scores)
exit(json_encode($pdo->query('SELECT name, score, total FROM scores ORDER BY score DESC, id ASC LIMIT 10')->fetchAll()));
