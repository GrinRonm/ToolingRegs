<?php
/**
 * Cleanup script — removes old uploaded/processed files and old logs
 * Intended to be run via cron, e.g., every hour
 */

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/FileManager.php';
require_once __DIR__ . '/../core/Logger.php';

$config = require __DIR__ . '/../config/app.php';

echo "Starting cleanup task...\n";

// 1. Cleanup files
$filesResult = FileManager::cleanup();
echo "Files deleted: {$filesResult['deleted']}\n";

// 2. Cleanup logs older than 30 days
$logsDeleted = Logger::cleanup(30);
echo "Logs deleted: {$logsDeleted}\n";

// 3. Cleanup old jobs (status failed/completed older than 1 day)
$db = Database::getInstance();
$stmt = $db->prepare("DELETE FROM jobs WHERE (status = 'completed' OR status = 'failed') AND created_at < datetime('now', '-1 day')");
$stmt->execute();
$jobsDeleted = $stmt->rowCount();
echo "Old jobs deleted: {$jobsDeleted}\n";

echo "Cleanup completed successfully.\n";
