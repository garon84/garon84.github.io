<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>URL Shortener</title>
</head>
<body>
<form action="shorten.php" method="post">
    <input type="url" name="url" placeholder="https://example.com" required>
    <input type="text" name="custom_code" placeholder="Custom code (optional)">
    <input type="datetime-local" name="expires_at" placeholder="Expiry (optional)">
    <button type="submit">Shorten</button>
</form>
</body>
</html>
