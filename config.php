<?php
// Database login. These are the XAMPP defaults.
// On a web host, replace them with the values from its control panel.
$db = ['host' => 'localhost', 'name' => 'mlbb_quiz', 'user' => 'root', 'pass' => ''];

try {
    $pdo = new PDO(
        "mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4",
        $db['user'],
        $db['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit(json_encode(['error' => 'Database connection failed']));
}
