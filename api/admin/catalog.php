<?php
declare(strict_types=1);
require_once __DIR__ . '/../../php/config.php';
require_once __DIR__ . '/../../php/pricing_engine.php';
require_admin();

try {
    $pdo = db();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method === 'GET') {
        json_response(['ok' => true, 'data' => get_catalog_config($pdo)]);
    }

    $data = read_json_body();
    $section = (string)($data['section'] ?? '');

    if ($section === 'calculation') {
        $stmt = $pdo->prepare("UPDATE calculation_settings SET min_price=:m, rounding_rule=:r, currency=:c WHERE id=1");
        $stmt->execute([
          ':m' => (float)($data['min_price'] ?? 30),
          ':r' => (string)($data['rounding'] ?? 'nearest_5'),
          ':c' => (string)($data['currency'] ?? 'EUR'),
        ]);
        json_response(['ok' => true]);
    }

    if ($section === 'conditions') {
        $stmt = $pdo->prepare("UPDATE conditions SET factor=:factor, label=:label WHERE condition_key=:key");
        $stmt->execute([
          ':factor' => (float)($data['factor'] ?? 1),
          ':label' => (string)($data['label'] ?? ''),
          ':key' => (string)($data['key'] ?? ''),
        ]);
        json_response(['ok' => true]);
    }

    if ($section === 'defects') {
        $stmt = $pdo->prepare("UPDATE defects SET deduction=:deduction, label=:label WHERE defect_key=:key");
        $stmt->execute([
          ':deduction' => (float)($data['deduction'] ?? 0),
          ':label' => (string)($data['label'] ?? ''),
          ':key' => (string)($data['key'] ?? ''),
        ]);
        json_response(['ok' => true]);
    }

    json_response(['ok' => false, 'error' => 'Unsupported section'], 422);
} catch (Throwable $e) {
    json_throwable($e, 'Request failed.');
}

