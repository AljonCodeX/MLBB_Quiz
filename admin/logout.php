<?php
require __DIR__ . '/../auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $_SESSION = [];
    session_regenerate_id(true);
}
header('Location: login.php');
exit;
