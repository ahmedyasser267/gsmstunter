<?php
declare(strict_types=1);

/**
 * Vendit E-commerce 2.0 integration configuration.
 * Local folders are used until SFTP credentials are provided.
 */
return [
    'ftp_host' => '',
    'ftp_port' => 21,
    'ftp_username' => '',
    'ftp_password' => '',
    'import_path' => __DIR__ . '/import',
    'export_path' => __DIR__ . '/export',

    'archive_path' => __DIR__ . '/archive',
    'logs_path' => __DIR__ . '/logs',
    'samples_path' => __DIR__ . '/xml_samples',

    'database' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'gsmstunter',
        'user' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],

    'xml' => [
        'encoding' => 'UTF-16LE',
        'order_decimal_places' => 4,
    ],

    'store' => [
        'store_number' => 'GSMSTUNTER-01',
        'order_origin' => 'Website',
    ],

    'sync' => [
        'archive_processed_imports' => true,
        'auto_export_encoding_utf16' => true,
    ],
];
