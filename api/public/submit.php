<?php
declare(strict_types=1);
require_once __DIR__ . '/../../php/pricing_engine.php';

try {
    $pdo = db();
    $input = read_json_body();
    $config = get_catalog_config($pdo);
    $calc = calculate_offer($config, $input);
    if (!$calc['ok']) {
        json_response($calc, 422);
    }

    $ref = 'Q' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    $stmt = $pdo->prepare("
      INSERT INTO quotes (
        quote_reference, customer_name, customer_email, customer_phone,
        device_key, storage_label, condition_key,
        selected_defects_json, selected_cosmetics_json, selected_risks_json, selected_bonuses_json,
        base_price, condition_factor, defects_total, cosmetics_total, bonuses_total, final_offer, manual_review_required
      ) VALUES (
        :ref, :name, :email, :phone,
        :device_key, :storage, :condition_key,
        :defects, :cosmetics, :risks, :bonuses,
        :base_price, :condition_factor, :defects_total, :cosmetics_total, :bonuses_total, :final_offer, :manual_review
      )
    ");
    $stmt->execute([
        ':ref' => $ref,
        ':name' => $input['customer_name'] ?? null,
        ':email' => $input['customer_email'] ?? null,
        ':phone' => $input['customer_phone'] ?? null,
        ':device_key' => $input['device_key'] ?? '',
        ':storage' => $input['storage'] ?? '',
        ':condition_key' => $input['condition_key'] ?? '',
        ':defects' => json_encode(array_values($input['defects'] ?? [])),
        ':cosmetics' => json_encode(array_values($input['cosmetics'] ?? [])),
        ':risks' => json_encode(array_values($input['risks'] ?? [])),
        ':bonuses' => json_encode(array_values($input['bonuses'] ?? [])),
        ':base_price' => $calc['breakdown']['base_price'],
        ':condition_factor' => $calc['breakdown']['condition_factor'],
        ':defects_total' => $calc['breakdown']['defects_total'],
        ':cosmetics_total' => $calc['breakdown']['cosmetics_total'],
        ':bonuses_total' => $calc['breakdown']['bonuses_total'],
        ':final_offer' => $calc['breakdown']['final_offer'],
        ':manual_review' => $calc['breakdown']['manual_review_required'] ? 1 : 0,
    ]);

    json_response([
      'ok' => true,
      'quote_reference' => $ref,
      'breakdown' => $calc['breakdown']
    ]);
} catch (Throwable $e) {
    json_throwable($e);
}

