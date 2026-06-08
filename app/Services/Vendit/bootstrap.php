<?php
declare(strict_types=1);

/**
 * Vendit module bootstrap — load once from CLI scripts or admin pages.
 */
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/VenditConfig.php';
require_once __DIR__ . '/VenditLogger.php';
require_once __DIR__ . '/VenditXmlParser.php';
require_once __DIR__ . '/VenditCustomerImporter.php';
require_once __DIR__ . '/VenditOrderExporter.php';
require_once __DIR__ . '/VenditCheckoutBridge.php';

use App\Services\Vendit\Database;
use App\Services\Vendit\VenditConfig;
use App\Services\Vendit\VenditLogger;

/**
 * Ensure Vendit storage directories exist.
 */
function vendit_bootstrap(): array
{
    $config = VenditConfig::all();
    foreach ($config['paths'] as $path) {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
    return $config;
}

/**
 * Build default Vendit service stack.
 *
 * @return array{db: Database, config: array, logger: VenditLogger}
 */
function vendit_services(): array
{
    $config = vendit_bootstrap();
    $db = Database::fromConfig($config);
    $logger = new VenditLogger($db, $config['paths']['logs_folder']);

    return [
        'db' => $db,
        'config' => $config,
        'logger' => $logger,
    ];
}
