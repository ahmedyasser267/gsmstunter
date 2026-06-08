<?php
declare(strict_types=1);
require_once __DIR__ . '/../../php/pricing_engine.php';

try {
    $config = get_catalog_config(db());
    json_response(['ok' => true, 'data' => $config]);
} catch (Throwable $e) {
    json_throwable($e);
}

