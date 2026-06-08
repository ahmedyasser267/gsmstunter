<?php
declare(strict_types=1);

namespace App\Services\Vendit;

use RuntimeException;

/**
 * Loads and exposes Vendit module configuration.
 */
final class VenditConfig
{
    private static ?array $config = null;

    public static function all(): array
    {
        if (self::$config === null) {
            $path = dirname(__DIR__, 3) . '/config/vendit.php';
            if (!is_file($path)) {
                throw new RuntimeException('Vendit config not found: ' . $path);
            }
            $loaded = require $path;
            if (!is_array($loaded)) {
                throw new RuntimeException('Vendit config must return an array.');
            }
            self::$config = $loaded;
        }
        return self::$config;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $config = self::all();
        $parts = explode('.', $key);
        $value = $config;
        foreach ($parts as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }
        return $value;
    }

    public static function path(string $name): string
    {
        $paths = self::get('paths', []);
        if (!isset($paths[$name])) {
            throw new RuntimeException('Unknown Vendit path key: ' . $name);
        }
        return (string) $paths[$name];
    }

    public static function reset(): void
    {
        self::$config = null;
    }
}
