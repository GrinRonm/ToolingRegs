<?php
/**
 * Database — SQLite PDO Singleton
 */

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../config/app.php';
            $dbPath = $config['db_path'];
            $dbDir  = dirname($dbPath);

            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }

            self::$instance = new PDO('sqlite:' . $dbPath);
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::$instance->exec('PRAGMA journal_mode=WAL');
            self::$instance->exec('PRAGMA foreign_keys=ON');

            self::migrate();
        }

        return self::$instance;
    }

    private static function migrate(): void
    {
        $db = self::$instance;

        $db->exec("
            CREATE TABLE IF NOT EXISTS logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                created_at DATETIME DEFAULT (datetime('now','localtime')),
                ip TEXT,
                user_agent TEXT,
                session_id TEXT,
                tool_id TEXT,
                action TEXT,
                file_name TEXT,
                file_type TEXT,
                file_size INTEGER,
                result TEXT,
                duration_ms INTEGER,
                error TEXT,
                job_id TEXT
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS jobs (
                id TEXT PRIMARY KEY,
                tool_id TEXT NOT NULL,
                status TEXT DEFAULT 'queued',
                input_data TEXT,
                progress INTEGER DEFAULT 0,
                result TEXT,
                error TEXT,
                created_at DATETIME DEFAULT (datetime('now','localtime')),
                updated_at DATETIME DEFAULT (datetime('now','localtime')),
                completed_at DATETIME
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS stats (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tool_id TEXT NOT NULL,
                date DATE DEFAULT (date('now')),
                count INTEGER DEFAULT 1,
                UNIQUE(tool_id, date)
            )
        ");

        $db->exec("CREATE INDEX IF NOT EXISTS idx_logs_created ON logs(created_at)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_logs_tool ON logs(tool_id)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_jobs_status ON jobs(status)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_stats_tool ON stats(tool_id)");
    }
}
