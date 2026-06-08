<?php
declare(strict_types=1);

/**
 * Vendit E-commerce 2.0 integration configuration.
 * FTP/SFTP credentials are placeholders until provided by Vendit/VMSII.
 */
return [
    'database' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'gsmstunter',
        'user' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],

    'ftp' => [
        'host' => '',           // TODO: Vendit FTP host
        'port' => 21,           // TODO: 21 for FTP, 22 for SFTP
        'username' => '',       // TODO: FTP username
        'password' => '',       // TODO: FTP password
        'passive' => true,
        'use_sftp' => false,    // Set true when SFTP credentials are available
        'remote_import_folder' => '/import',
        'remote_export_folder' => '/export',
    ],

    'paths' => [
        'base' => dirname(__DIR__) . '/storage/vendit',
        'import_folder' => dirname(__DIR__) . '/storage/vendit/import',
        'export_folder' => dirname(__DIR__) . '/storage/vendit/export',
        'archive_folder' => dirname(__DIR__) . '/storage/vendit/archive',
        'logs_folder' => dirname(__DIR__) . '/storage/vendit/logs',
        'samples_folder' => dirname(__DIR__) . '/storage/vendit/samples',
    ],

    'xml' => [
        'encoding' => 'UTF-16LE',
        'decimal_places_orders' => 4,
        'decimal_places_stock' => 2,
    ],

    'store' => [
        'store_number' => '',   // Dealer number (StoreNumber in OrderImport)
        'default_office_id' => null,
        'order_origin' => 'Website',
    ],

    'sync' => [
        'archive_processed_files' => true,
        'max_log_rows_admin' => 100,
        'auto_export_on_checkout' => true,
    ],
];
