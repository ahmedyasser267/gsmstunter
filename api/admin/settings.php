<?php
declare(strict_types=1);
require_once __DIR__ . '/../../php/config.php';
require_admin();

try {
    $pdo = db();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method === 'GET') {
        $trade = $pdo->query("SELECT * FROM trade_settings ORDER BY id ASC LIMIT 1")->fetch();
        $calc = $pdo->query("SELECT * FROM calculation_settings ORDER BY id ASC LIMIT 1")->fetch();
        json_response(['ok' => true, 'trade' => $trade, 'calculation' => $calc]);
    }

    $data = read_json_body();
    $section = (string)($data['section'] ?? '');

    if ($section === 'trade') {
        $stmt = $pdo->prepare("
          UPDATE trade_settings
          SET trade_bonus_percent=:p, exchange_bonus_value=:v, min_trade_price=:m
          WHERE id=1
        ");
        $stmt->execute([
          ':p' => (float)($data['trade_bonus_percent'] ?? 0),
          ':v' => (float)($data['exchange_bonus_value'] ?? 0),
          ':m' => (float)($data['min_trade_price'] ?? 20),
        ]);
        json_response(['ok' => true]);
    }

    if ($section === 'calculation') {
        $stmt = $pdo->prepare("
          UPDATE calculation_settings
          SET min_price=:m, global_reduction_percent=:g, rounding_rule=:r, currency=:c
          WHERE id=1
        ");
        $stmt->execute([
          ':m' => (float)($data['min_price'] ?? 30),
          ':g' => (float)($data['global_reduction_percent'] ?? 0),
          ':r' => (string)($data['rounding_rule'] ?? 'nearest_5'),
          ':c' => (string)($data['currency'] ?? 'EUR'),
        ]);
        json_response(['ok' => true]);
    }

    json_response(['ok' => false, 'error' => 'Unsupported section'], 422);
} catch (Throwable $e) {
    json_throwable($e, 'Request failed.');
}

