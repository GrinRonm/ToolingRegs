<?php
/**
 * Logger — Logs user actions to SQLite
 */

class Logger
{
    /**
     * Log a user action
     */
    public static function log(array $data): void
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                INSERT INTO logs (ip, user_agent, session_id, tool_id, action, file_name, file_type, file_size, result, duration_ms, error, job_id)
                VALUES (:ip, :user_agent, :session_id, :tool_id, :action, :file_name, :file_type, :file_size, :result, :duration_ms, :error, :job_id)
            ");

            $stmt->execute([
                ':ip'          => self::getClientIp(),
                ':user_agent'  => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
                ':session_id'  => session_id() ?: null,
                ':tool_id'     => $data['tool_id'] ?? null,
                ':action'      => $data['action'] ?? null,
                ':file_name'   => $data['file_name'] ?? null,
                ':file_type'   => $data['file_type'] ?? null,
                ':file_size'   => $data['file_size'] ?? null,
                ':result'      => $data['result'] ?? null,
                ':duration_ms' => $data['duration_ms'] ?? null,
                ':error'       => $data['error'] ?? null,
                ':job_id'      => $data['job_id'] ?? null,
            ]);
        } catch (Exception $e) {
            error_log('Logger error: ' . $e->getMessage());
        }
    }

    /**
     * Record tool usage stat
     */
    public static function recordStat(string $toolId): void
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                INSERT INTO stats (tool_id, date, count) VALUES (:tool_id, date('now'), 1)
                ON CONFLICT(tool_id, date) DO UPDATE SET count = count + 1
            ");
            $stmt->execute([':tool_id' => $toolId]);
        } catch (Exception $e) {
            error_log('Stats error: ' . $e->getMessage());
        }
    }

    /**
     * Get recent logs
     */
    public static function getRecent(int $limit = 100): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM logs ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get client IP address
     */
    private static function getClientIp(): string
    {
        $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = explode(',', $_SERVER[$key])[0];
                return trim($ip);
            }
        }
        return '0.0.0.0';
    }

    /**
     * Cleanup old logs
     */
    public static function cleanup(int $days = 30): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM logs WHERE created_at < datetime('now', :days)");
        $stmt->execute([':days' => "-{$days} days"]);
        return $stmt->rowCount();
    }
}
