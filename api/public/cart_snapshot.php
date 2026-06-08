<?php
declare(strict_types=1);
require_once __DIR__ . '/../../php/config.php';
require_once __DIR__ . '/../../php/checkout_catalog.php';

try {
    $data = read_json_body();
    $items = $data['items'] ?? [];
    if (!is_array($items) || count($items) === 0) {
        json_response(['ok' => false, 'error' => 'Cart is empty'], 422);
    }

    checkout_validate_cart_lines($items);

    try {
        $currency = checkout_normalize_currency((string)($data['currency'] ?? CHECKOUT_CURRENCY_DEFAULT));
    } catch (RuntimeException $e) {
        json_response(['ok' => false, 'error' => $e->getMessage()], 422);
    }

    $needByPid = checkout_aggregate_need($items);
    if ($needByPid === []) {
        json_response(['ok' => false, 'error' => 'Invalid cart contents'], 422);
    }

    $pdo = db();
    $catalog = checkout_load_visible_products($pdo, $needByPid);
    $priced = checkout_authoritative_lines($items, $catalog);

    $emailRaw = isset($data['customer_email']) ? trim((string)$data['customer_email']) : '';
    $customerEmail = null;
    if ($emailRaw !== '') {
        if (strlen($emailRaw) > 180 || !filter_var($emailRaw, FILTER_VALIDATE_EMAIL)) {
            json_response(['ok' => false, 'error' => 'Ongeldig e-mailadres voor snapshot'], 422);
        }
        $customerEmail = strtolower($emailRaw);
    }

    $ref = 'CRT-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(2)));
    $stmt = $pdo->prepare(
        '
      INSERT INTO cart_snapshots (snapshot_reference, customer_email, items_json, subtotal, currency)
      VALUES (:ref, :email, :items, :subtotal, :currency)
    '
    );
    $stmt->execute([
      ':ref' => $ref,
      ':email' => $customerEmail,
      ':items' => json_encode($priced['lines'], JSON_UNESCAPED_UNICODE),
      ':subtotal' => $priced['subtotal'],
      ':currency' => $currency,
    ]);
    json_response(['ok' => true, 'snapshot_reference' => $ref, 'subtotal' => $priced['subtotal']]);
} catch (RuntimeException $e) {
    json_response(['ok' => false, 'error' => $e->getMessage()], 422);
} catch (Throwable $e) {
    json_throwable($e);
}
