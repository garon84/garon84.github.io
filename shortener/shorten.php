<?php
require 'db.php';
require_once 'phpqrcode/qrlib.php'; // ensure library installed

$url         = filter_input(INPUT_POST, 'url', FILTER_VALIDATE_URL);
$custom_code = $_POST['custom_code'] ?? '';
$expires_at  = $_POST['expires_at'] ?? null;

if (!$url) {
    die('Invalid URL');
}

$allowedSchemes = ['http', 'https'];
$parsed = parse_url($url);
if (!in_array($parsed['scheme'] ?? '', $allowedSchemes, true)) {
    die('Unsupported protocol');
}
$ip = gethostbyname($parsed['host']);
if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
    die('Disallowed host');
}

// rate limiting
$clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$limit    = 10;
$stmt     = $pdo->prepare('SELECT count, last_reset FROM rate_limits WHERE ip = ?');
$stmt->execute([$clientIp]);
$row = $stmt->fetch();
if (!$row || strtotime($row['last_reset']) < time() - 60) {
    $stmt = $pdo->prepare('REPLACE INTO rate_limits (ip, count, last_reset) VALUES (?, 1, NOW())');
    $stmt->execute([$clientIp]);
} else {
    if ($row['count'] >= $limit) {
        die('Too many requests');
    }
    $stmt = $pdo->prepare('UPDATE rate_limits SET count = count + 1 WHERE ip = ?');
    $stmt->execute([$clientIp]);
}

if ($custom_code) {
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $custom_code)) {
        die('Invalid custom code');
    }
    $stmt = $pdo->prepare('SELECT id FROM links WHERE short_code = ?');
    $stmt->execute([$custom_code]);
    if ($stmt->fetch()) {
        die('Code already taken');
    }
    $shortCode = $custom_code;
} else {
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    do {
        $shortCode = substr(str_shuffle($chars), 0, 6);
        $stmt = $pdo->prepare('SELECT id FROM links WHERE short_code = ?');
        $stmt->execute([$shortCode]);
    } while ($stmt->fetch());
}

$stmt = $pdo->prepare('INSERT INTO links (short_code, original_url, expires_at) VALUES (?, ?, ?)');
$stmt->execute([$shortCode, $url, $expires_at]);

$shortUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/' . $shortCode;
if (!is_dir('qrcodes')) {
    mkdir('qrcodes');
}
QRcode::png($shortUrl, 'qrcodes/' . $shortCode . '.png', QR_ECLEVEL_L, 4);

echo 'Short URL: <a href="' . htmlspecialchars($shortUrl, ENT_QUOTES) . '">' . $shortUrl . "</a><br>";
echo '<img src="qrcodes/' . $shortCode . '.png" alt="QR code">';
