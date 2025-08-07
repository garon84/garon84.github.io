<?php
require 'db.php';

$code = $_GET['code'] ?? '';
$stmt = $pdo->prepare('SELECT original_url, clicks, expires_at FROM links WHERE short_code = ?');
$stmt->execute([$code]);
$link = $stmt->fetch();

if (!$link) {
    http_response_code(404);
    exit('Link not found');
}

if ($link['expires_at'] && strtotime($link['expires_at']) < time()) {
    http_response_code(410);
    exit('Link expired');
}

$stmt = $pdo->prepare('UPDATE links SET clicks = clicks + 1 WHERE short_code = ?');
$stmt->execute([$code]);

header('Location: ' . $link['original_url'], true, 302);
exit;
