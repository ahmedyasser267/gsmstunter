<?php
declare(strict_types=1);

/**
 * Smoke tests for commerce-critical helpers — no DB required.
 * Run: php tests/run_critical_tests.php
 */

require_once dirname(__DIR__) . '/php/checkout_catalog.php';
require_once dirname(__DIR__) . '/php/pricing_engine.php';

function test_fail(string $message): void
{
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

function test_pass(string $label): void
{
    fwrite(STDOUT, "OK  {$label}\n");
}

// --- checkout_catalog ---
if (checkout_resolve_product_id(['product_id' => 42]) !== 42) {
    test_fail('checkout_resolve_product_id product_id');
}
test_pass('checkout_resolve_product_id product_id');

if (checkout_resolve_product_id(['id' => '7']) !== 7) {
    test_fail('checkout_resolve_product_id id');
}
test_pass('checkout_resolve_product_id numeric id');

$agg = checkout_aggregate_need([
    ['product_id' => 1, 'quantity' => 2],
    ['product_id' => 1, 'quantity' => 3],
]);
if (($agg[1] ?? 0) !== 5) {
    test_fail('checkout_aggregate_need merge quantities');
}
test_pass('checkout_aggregate_need merge quantities');

try {
    checkout_normalize_payment_method('bitcoin');
    test_fail('checkout_normalize_payment_method must reject unknown');
} catch (RuntimeException $e) {
}
test_pass('checkout_normalize_payment_method rejects unknown');

if (checkout_normalize_payment_method(' IDEAL ') !== 'ideal') {
    test_fail('checkout_normalize_payment_method normalize');
}
test_pass('checkout_normalize_payment_method normalize');

try {
    checkout_validate_cart_lines([['product_id' => 1, 'quantity' => CHECKOUT_MAX_QTY_PER_LINE + 1]]);
    test_fail('checkout_validate_cart_lines qty cap');
} catch (RuntimeException $e) {
}
test_pass('checkout_validate_cart_lines qty cap');

try {
    checkout_validate_cart_lines([]);
    test_fail('checkout_validate_cart_lines empty');
} catch (RuntimeException $e) {
}
test_pass('checkout_validate_cart_lines empty');

// --- pricing_engine calculate_offer (pure math path) ---
$config = [
    'devices' => [
        'phone_a' => ['base_prices' => ['128GB' => 500.0]],
    ],
    'conditions' => [
        'goed' => ['label' => 'Good', 'factor' => 0.9],
    ],
    'defects' => [],
    'cosmetic' => [],
    'risk_rules' => [],
    'bonuses' => [],
    'calculation' => [
        'min_price' => 30.0,
        'global_reduction_percent' => 0.0,
        'rounding' => 'nearest_5',
        'currency' => 'EUR',
    ],
];
$offer = calculate_offer($config, [
    'device_key' => 'phone_a',
    'storage' => '128GB',
    'condition_key' => 'goed',
    'defects' => [],
    'cosmetics' => [],
    'risks' => [],
    'bonuses' => [],
]);
if (!$offer['ok']) {
    test_fail('calculate_offer expected ok');
}
$final = $offer['breakdown']['final_offer'] ?? null;
if ($final !== round(round(500 * 0.9 / 5) * 5, 2)) {
    test_fail('calculate_offer final_offer mismatch got ' . json_encode($final));
}
test_pass('calculate_offer rounding');

fwrite(STDOUT, "\nAll critical smoke tests passed.\n");
