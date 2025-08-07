# PHP URL Shortener

Simple URL shortening service with optional custom codes, expiration dates, rate limiting, and QR code generation.

## Setup
1. Import `schema.sql` into your MariaDB database.
2. Adjust database credentials in `db.php`.
3. Install [phpqrcode](https://sourceforge.net/projects/phpqrcode/) or via Composer.
4. Deploy the files on a PHP-enabled server (e.g., Plesk).
5. Configure web server rewrite to route `/code` to `redirect.php`:
   ```
   RewriteEngine On
   RewriteRule ^([A-Za-z0-9_-]+)$ redirect.php?code=$1 [L]
   ```

## Tables
See `schema.sql` for table definitions of `links` and `rate_limits`.
