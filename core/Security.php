<?php
/**
 * Security — CSRF protection, Rate Limiting, Input validation
 */

class Security
{
    /**
     * Initialize session and CSRF token
     */
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    /**
     * Get CSRF token
     */
    public static function getToken(): string
    {
        return $_SESSION['csrf_token'] ?? '';
    }

    /**
     * Generate CSRF hidden input field
     */
    public static function csrfField(): string
    {
        return '<input type="hidden" name="_token" value="' . htmlspecialchars(self::getToken()) . '">';
    }

    /**
     * Verify CSRF token from POST request or header
     */
    public static function verifyCsrf(): bool
    {
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return hash_equals(self::getToken(), $token);
    }

    /**
     * Check rate limit for current IP
     */
    public static function checkRateLimit(): bool
    {
        $config = require __DIR__ . '/../config/app.php';
        $ip = self::getIp();
        $window = $config['rate_limit_window'];
        $maxRequests = $config['rate_limit_requests'];

        try {
            $db = Database::getInstance();

            // Clean old entries
            $db->exec("DELETE FROM logs WHERE action = 'rate_check' AND created_at < datetime('now', '-{$window} seconds')");

            // Count requests
            $stmt = $db->prepare("
                SELECT COUNT(*) as cnt FROM logs
                WHERE ip = :ip AND created_at > datetime('now', :window)
            ");
            $stmt->execute([
                ':ip'     => $ip,
                ':window' => "-{$window} seconds",
            ]);
            $count = (int) $stmt->fetchColumn();

            return $count < $maxRequests;
        } catch (Exception $e) {
            // If rate limiting fails, allow request
            return true;
        }
    }

    /**
     * Sanitize string for output
     */
    public static function escape(string $str): string
    {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize filename
     */
    public static function sanitizeFilename(string $name): string
    {
        $name = preg_replace('/[^\w\.\-]/', '_', $name);
        $name = preg_replace('/\.{2,}/', '.', $name);
        return substr($name, 0, 200);
    }

    /**
     * Validate that the request is an AJAX/API request
     */
    public static function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Get client IP
     */
    private static function getIp(): string
    {
        $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                return trim(explode(',', $_SERVER[$key])[0]);
            }
        }
        return '0.0.0.0';
    }
}
