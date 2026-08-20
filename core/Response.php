<?php
/**
 * Response — Unified JSON and HTTP response helpers
 */

class Response
{
    /**
     * Send JSON success response
     */
    public static function json(array $data = [], int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge(['success' => true], $data), JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send JSON error response
     */
    public static function error(string $message, int $code = 400): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send file download response
     */
    public static function download(string $filePath, string $downloadName): void
    {
        if (!file_exists($filePath)) {
            self::error('Файл не найден', 404);
        }

        $mime = mime_content_type($filePath) ?: 'application/octet-stream';
        $size = filesize($filePath);
        
        $encodedName = rawurlencode($downloadName);
        $asciiName = preg_replace('/[^\x20-\x7E]/', '', $downloadName) ?: 'download';

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $asciiName . '"; filename*=UTF-8\'\'' . $encodedName);
        header('Content-Length: ' . $size);
        header('Cache-Control: no-cache, must-revalidate');

        readfile($filePath);
        exit;
    }

    /**
     * Send 404 page
     */
    public static function notFound(): void
    {
        http_response_code(404);
        echo '<h1>404 — Не найдено</h1>';
        exit;
    }
}
