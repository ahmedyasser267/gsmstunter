<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

function get_catalog_config(PDO $pdo): array
{
    $devices = [];
    $dRows = $pdo->query("SELECT id, device_key, name FROM devices WHERE active = 1 ORDER BY id DESC")->fetchAll();
    foreach ($dRows as $row) {
        $devices[$row['device_key']] = [
            'name' => $row['name'],
            'base_prices' => []
        ];
    }

    $spRows = $pdo->query("
      SELECT d.device_key, dsp.storage_label, dsp.base_price
      FROM device_storage_prices dsp
      JOIN devices d ON d.id = dsp.device_id
      WHERE d.active = 1
      ORDER BY d.id DESC, CAST(dsp.storage_label AS UNSIGNED)
    ")->fetchAll();
    foreach ($spRows as $row) {
        if (isset($devices[$row['device_key']])) {
            $devices[$row['device_key']]['base_prices'][$row['storage_label']] = (float)$row['base_price'];
        }
    }

    $conditions = [];
    foreach ($pdo->query("SELECT condition_key, label, factor FROM conditions")->fetchAll() as $r) {
        $conditions[$r['condition_key']] = ['label' => $r['label'], 'factor' => (float)$r['factor']];
    }

    $defects = [];
    foreach ($pdo->query("SELECT defect_key, label, deduction FROM defects")->fetchAll() as $r) {
        $defects[$r['defect_key']] = ['label' => $r['label'], 'deduction' => (float)$r['deduction']];
    }

    $cosmetics = [];
    foreach ($pdo->query("SELECT cosmetic_key, label, deduction FROM cosmetics")->fetchAll() as $r) {
        $cosmetics[$r['cosmetic_key']] = ['label' => $r['label'], 'deduction' => (float)$r['deduction']];
    }

    $riskRules = [];
    foreach ($pdo->query("SELECT risk_key, label, action_type FROM risk_rules")->fetchAll() as $r) {
        $riskRules[$r['risk_key']] = ['label' => $r['label'], 'action' => $r['action_type']];
    }

    $bonuses = [];
    foreach ($pdo->query("SELECT bonus_key, label, value FROM bonuses")->fetchAll() as $r) {
        $bonuses[$r['bonus_key']] = ['label' => $r['label'], 'value' => (float)$r['value']];
    }

    $calc = $pdo->query("SELECT min_price, global_reduction_percent, rounding_rule, currency FROM calculation_settings ORDER BY id ASC LIMIT 1")->fetch();
    $calculation = [
        'min_price' => (float)($calc['min_price'] ?? 30),
        'global_reduction_percent' => (float)($calc['global_reduction_percent'] ?? 0),
        'rounding' => $calc['rounding_rule'] ?? 'nearest_5',
        'currency' => $calc['currency'] ?? 'EUR',
    ];

    return [
        'devices' => $devices,
        'conditions' => $conditions,
        'defects' => $defects,
        'cosmetic' => $cosmetics,
        'risk_rules' => $riskRules,
        'bonuses' => $bonuses,
        'calculation' => $calculation
    ];
}

function round_by_rule(float $value, string $rule): float
{
    if ($rule === 'nearest_5') {
        return round($value / 5) * 5;
    }
    return round($value);
}

function calculate_offer(array $config, array $input): array
{
    $deviceKey = (string)($input['device_key'] ?? '');
    $storage = (string)($input['storage'] ?? '');
    $conditionKey = (string)($input['condition_key'] ?? 'goed');
    $defects = array_values(array_unique(array_filter($input['defects'] ?? [])));
    $cosmetics = array_values(array_unique(array_filter($input['cosmetics'] ?? [])));
    $risks = array_values(array_unique(array_filter($input['risks'] ?? [])));
    $bonuses = array_values(array_unique(array_filter($input['bonuses'] ?? [])));

    if (!isset($config['devices'][$deviceKey])) {
        return ['ok' => false, 'error' => 'Unknown device_key'];
    }
    if (!isset($config['devices'][$deviceKey]['base_prices'][$storage])) {
        return ['ok' => false, 'error' => 'Unknown storage for selected device'];
    }
    if (!isset($config['conditions'][$conditionKey])) {
        return ['ok' => false, 'error' => 'Unknown condition_key'];
    }

    $basePrice = (float)$config['devices'][$deviceKey]['base_prices'][$storage];
    $conditionFactor = (float)$config['conditions'][$conditionKey]['factor'];
    $afterCondition = $basePrice * $conditionFactor;

    $defectsTotal = 0.0;
    foreach ($defects as $k) {
        $defectsTotal += (float)($config['defects'][$k]['deduction'] ?? 0);
    }

    $cosmeticsTotal = 0.0;
    foreach ($cosmetics as $k) {
        $cosmeticsTotal += (float)($config['cosmetic'][$k]['deduction'] ?? 0);
    }

    $bonusesTotal = 0.0;
    foreach ($bonuses as $k) {
        $bonusesTotal += (float)($config['bonuses'][$k]['value'] ?? 0);
    }

    $manualReview = false;
    foreach ($risks as $k) {
        if (($config['risk_rules'][$k]['action'] ?? '') === 'manual_review') {
            $manualReview = true;
            break;
        }
    }

    $subtotal = $afterCondition - $defectsTotal - $cosmeticsTotal + $bonusesTotal;
    $globalReduction = max(0.0, min(100.0, (float)($config['calculation']['global_reduction_percent'] ?? 0)));
    if ($globalReduction > 0) {
        $subtotal *= (1 - ($globalReduction / 100));
    }
    $rounded = round_by_rule($subtotal, (string)$config['calculation']['rounding']);
    $final = max((float)$config['calculation']['min_price'], $rounded);

    return [
        'ok' => true,
        'breakdown' => [
            'base_price' => $basePrice,
            'condition_factor' => $conditionFactor,
            'after_condition' => round($afterCondition, 2),
            'defects_total' => round($defectsTotal, 2),
            'cosmetics_total' => round($cosmeticsTotal, 2),
            'bonuses_total' => round($bonusesTotal, 2),
            'global_reduction_percent' => round($globalReduction, 2),
            'final_offer' => round($final, 2),
            'currency' => $config['calculation']['currency'],
            'manual_review_required' => $manualReview
        ]
    ];
}

