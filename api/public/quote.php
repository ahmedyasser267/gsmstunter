<?php
declare(strict_types=1);
require_once __DIR__ . '/../../php/pricing_engine.php';

try {
    $input = read_json_body();
    $config = get_catalog_config(db());
    $result = calculate_offer($config, $input);
    if (!$result['ok']) {
      json_response($result, 422);
    }
    json_response($result);
} catch (Throwable $e) {
    json_throwable($e);
}

