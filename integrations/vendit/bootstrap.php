<?php
declare(strict_types=1);

/**
 * Bootstrap the Vendit integration module.
 */
require_once __DIR__ . '/src/Autoload.php';

Integrations\Vendit\Autoload::register(__DIR__ . '/src');

use Integrations\Vendit\Container;

/** @return array<string, mixed> */
function vendit_config(): array
{
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/config.php';
        foreach (['import_path', 'export_path', 'archive_path', 'logs_path', 'samples_path'] as $key) {
            if (!empty($config[$key]) && !is_dir($config[$key])) {
                mkdir($config[$key], 0755, true);
            }
        }
    }
    return $config;
}

function vendit_container(): Container
{
    static $container = null;
    if ($container === null) {
        $container = Container::fromConfig(vendit_config());
    }
    return $container;
}
