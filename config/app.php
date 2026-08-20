<?php
/**
 * Instyment — Application Configuration
 */

return [
    // Application
    'app_name'    => 'Instyment',
    'app_url'     => '',
    'debug'       => false,

    // Paths
    'base_path'    => dirname(__DIR__),
    'tools_path'   => dirname(__DIR__) . '/tools',
    'storage_path' => dirname(__DIR__) . '/storage',
    'upload_path'  => dirname(__DIR__) . '/storage/uploads',
    'process_path' => dirname(__DIR__) . '/storage/processed',
    'temp_path'    => dirname(__DIR__) . '/storage/temp',
    'db_path'      => dirname(__DIR__) . '/database/instyment.db',
    'python_path'  => dirname(__DIR__) . '/python',

    // File limits
    'max_file_size'    => 50 * 1024 * 1024,  // 50MB
    'max_files'        => 20,
    'allowed_images'   => ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/bmp'],
    'allowed_docs'     => ['application/pdf', 'text/plain', 'text/csv', 'application/json'],
    'blocked_extensions' => ['php', 'phtml', 'php3', 'php4', 'php5', 'phps', 'cgi', 'pl', 'py', 'sh', 'bat', 'exe', 'com', 'dll', 'vbs', 'js', 'jsp', 'asp', 'aspx', 'htaccess'],

    // Cleanup (hours)
    'upload_ttl'    => 2,
    'processed_ttl' => 4,
    'temp_ttl'      => 1,

    // Rate limiting
    'rate_limit_requests' => 60,
    'rate_limit_window'   => 60,  // seconds

    // Python
    'python_bin' => '/usr/bin/python3',

    // Categories
    'categories' => [
        'image' => ['name' => 'Изображения', 'icon' => '🖼️'],
        'pdf'   => ['name' => 'PDF',          'icon' => '📄'],
        'dev'   => ['name' => 'Разработчику', 'icon' => '💻'],
        'text'  => ['name' => 'Текст',        'icon' => '📝'],
        'misc'  => ['name' => 'Разное',       'icon' => '🔧'],
    ],
];
