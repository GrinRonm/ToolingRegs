<?php
/**
 * FileManager — Secure file upload, storage, and cleanup
 */

class FileManager
{
    /**
     * Handle file upload with security checks
     *
     * @return array{success: bool, file_id: string, path: string, original_name: string, mime: string, size: int, error?: string}
     */
    public static function upload(array $file, array $allowedMimes = []): array
    {
        $config = require __DIR__ . '/../config/app.php';

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => self::uploadErrorMessage($file['error'])];
        }

        // Check file size
        if ($file['size'] > $config['max_file_size']) {
            $maxMB = round($config['max_file_size'] / 1024 / 1024);
            return ['success' => false, 'error' => "Файл слишком большой. Максимум: {$maxMB}MB"];
        }

        // Check MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if (!empty($allowedMimes) && !in_array($mime, $allowedMimes)) {
            return ['success' => false, 'error' => 'Неподдерживаемый тип файла: ' . $mime];
        }

        // Check extension against blocklist
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $config['blocked_extensions'])) {
            return ['success' => false, 'error' => 'Запрещённый тип файла'];
        }

        // Generate unique filename
        $fileId = self::generateId();
        $safeExt = self::getSafeExtension($mime, $ext);
        $filename = $fileId . '.' . $safeExt;

        // Ensure upload directory exists
        $uploadPath = $config['upload_path'];
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $destPath = $uploadPath . '/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['success' => false, 'error' => 'Не удалось сохранить файл'];
        }

        return [
            'success'       => true,
            'file_id'       => $fileId,
            'path'          => $destPath,
            'filename'      => $filename,
            'original_name' => basename($file['name']),
            'mime'          => $mime,
            'size'          => $file['size'],
            'extension'     => $safeExt,
        ];
    }

    /**
     * Handle multiple file uploads
     */
    public static function uploadMultiple(array $files, array $allowedMimes = []): array
    {
        $config = require __DIR__ . '/../config/app.php';
        $results = [];

        // Normalize files array
        $fileList = self::normalizeFiles($files);

        if (count($fileList) > $config['max_files']) {
            return [['success' => false, 'error' => "Максимум {$config['max_files']} файлов"]];
        }

        foreach ($fileList as $file) {
            $results[] = self::upload($file, $allowedMimes);
        }

        return $results;
    }

    /**
     * Save processed file to processed directory
     */
    public static function saveProcessed(string $content, string $extension, string $originalName = ''): array
    {
        $config = require __DIR__ . '/../config/app.php';
        $processPath = $config['process_path'];

        if (!is_dir($processPath)) {
            mkdir($processPath, 0755, true);
        }

        $fileId = self::generateId();
        $filename = $fileId . '.' . $extension;
        $destPath = $processPath . '/' . $filename;

        file_put_contents($destPath, $content);

        return [
            'file_id'  => $fileId,
            'path'     => $destPath,
            'filename' => $filename,
            'size'     => filesize($destPath),
        ];
    }

    /**
     * Copy file to processed directory
     */
    public static function copyToProcessed(string $sourcePath, string $extension): array
    {
        $config = require __DIR__ . '/../config/app.php';
        $processPath = $config['process_path'];

        if (!is_dir($processPath)) {
            mkdir($processPath, 0755, true);
        }

        $fileId = self::generateId();
        $filename = $fileId . '.' . $extension;
        $destPath = $processPath . '/' . $filename;

        copy($sourcePath, $destPath);

        return [
            'file_id'  => $fileId,
            'path'     => $destPath,
            'filename' => $filename,
            'size'     => filesize($destPath),
        ];
    }

    /**
     * Get processed file path for download
     */
    public static function getProcessedPath(string $filename): ?string
    {
        $config = require __DIR__ . '/../config/app.php';
        $path = $config['process_path'] . '/' . basename($filename);

        // Prevent path traversal
        $realPath = realpath($path);
        $realProcessPath = realpath($config['process_path']);

        if ($realPath && $realProcessPath && strpos($realPath, $realProcessPath) === 0 && is_file($realPath)) {
            return $realPath;
        }

        return null;
    }

    /**
     * Cleanup old files
     */
    public static function cleanup(): array
    {
        $config = require __DIR__ . '/../config/app.php';
        $deleted = 0;

        $dirs = [
            $config['upload_path']  => $config['upload_ttl'],
            $config['process_path'] => $config['processed_ttl'],
            $config['temp_path']    => $config['temp_ttl'],
        ];

        foreach ($dirs as $dir => $ttlHours) {
            if (!is_dir($dir)) continue;
            $cutoff = time() - ($ttlHours * 3600);

            foreach (new DirectoryIterator($dir) as $file) {
                if ($file->isDot() || $file->isDir()) continue;
                if ($file->getMTime() < $cutoff) {
                    unlink($file->getPathname());
                    $deleted++;
                }
            }
        }

        return ['deleted' => $deleted];
    }

    /**
     * Generate unique file ID
     */
    public static function generateId(): string
    {
        return bin2hex(random_bytes(8)) . '_' . time();
    }

    /**
     * Get safe file extension based on MIME type
     */
    private static function getSafeExtension(string $mime, string $originalExt): string
    {
        $mimeMap = [
            'image/jpeg'      => 'jpg',
            'image/png'       => 'png',
            'image/webp'      => 'webp',
            'image/gif'       => 'gif',
            'image/bmp'       => 'bmp',
            'application/pdf' => 'pdf',
            'text/plain'      => 'txt',
            'text/csv'        => 'csv',
            'application/json'=> 'json',
        ];

        return $mimeMap[$mime] ?? preg_replace('/[^a-z0-9]/', '', $originalExt) ?: 'bin';
    }

    /**
     * Normalize PHP's weird multi-file upload array structure
     */
    private static function normalizeFiles(array $files): array
    {
        $normalized = [];

        if (isset($files['name']) && is_array($files['name'])) {
            // Multiple files uploaded under same input name
            for ($i = 0; $i < count($files['name']); $i++) {
                $normalized[] = [
                    'name'     => $files['name'][$i],
                    'type'     => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i],
                ];
            }
        } else {
            $normalized[] = $files;
        }

        return $normalized;
    }

    /**
     * Map PHP upload error code to message
     */
    private static function uploadErrorMessage(int $code): string
    {
        $messages = [
            UPLOAD_ERR_INI_SIZE   => 'Файл слишком большой (лимит сервера)',
            UPLOAD_ERR_FORM_SIZE  => 'Файл слишком большой',
            UPLOAD_ERR_PARTIAL    => 'Файл загружен частично',
            UPLOAD_ERR_NO_FILE    => 'Файл не загружен',
            UPLOAD_ERR_NO_TMP_DIR => 'Ошибка сервера',
            UPLOAD_ERR_CANT_WRITE => 'Ошибка записи',
            UPLOAD_ERR_EXTENSION  => 'Загрузка заблокирована',
        ];

        return $messages[$code] ?? 'Неизвестная ошибка загрузки';
    }
}
