<?php
declare(strict_types=1);
require_once __DIR__ . '/../../php/pricing_engine.php';

try {
    $pdo = db();
    $input = read_json_body();
    $config = get_catalog_config($pdo);
    $sell = calculate_offer($config, $input);
    if (!$sell['ok']) {
        json_response($sell, 422);
    }

    $trade = $pdo->query("SELECT * FROM trade_settings ORDER BY id ASC LIMIT 1")->fetch();
    $bonusPercent = (float)($trade['trade_bonus_percent'] ?? 0);
    $exchangeBonus = (float)($trade['exchange_bonus_value'] ?? 0);
    $minTrade = (float)($trade['min_trade_price'] ?? 20);

    $sellOffer = (float)$sell['breakdown']['final_offer'];
    $tradeOffer = $sellOffer + ($sellOffer * $bonusPercent / 100) + $exchangeBonus;
    $tradeOffer = max($minTrade, round($tradeOffer / 5) * 5);

    json_response([
      'ok' => true,
      'sell_offer' => $sellOffer,
      'trade_offer' => $tradeOffer,
      'bonus_percent' => $bonusPercent,
      'exchange_bonus_value' => $exchangeBonus,
      'currency' => $sell['breakdown']['currency']
    ]);
} catch (Throwable $e) {
    json_throwable($e);
}

