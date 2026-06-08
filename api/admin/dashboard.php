<?php
declare(strict_types=1);
require_once __DIR__ . '/../../php/config.php';
require_admin();

try {
    $pdo = db();
    $totals = $pdo->query("
      SELECT
        COUNT(*) AS total_quotes,
        SUM(CASE WHEN manual_review_required = 1 THEN 1 ELSE 0 END) AS manual_reviews,
        SUM(final_offer) AS total_offer_value,
        AVG(final_offer) AS avg_offer
      FROM quotes
    ")->fetch();
    $orderTotals = $pdo->query("
      SELECT
        COUNT(*) AS total_orders,
        SUM(total_amount) AS total_revenue,
        SUM(CASE WHEN status='new' THEN 1 ELSE 0 END) AS new_orders
      FROM orders
    ")->fetch();
    $customerCount = $pdo->query("SELECT COUNT(*) AS total_customers FROM customers")->fetch();

    $byDevice = $pdo->query("
      SELECT device_key, COUNT(*) total
      FROM quotes
      GROUP BY device_key
      ORDER BY total DESC
      LIMIT 8
    ")->fetchAll();

    $recent = $pdo->query("
      SELECT quote_reference, device_key, final_offer, status, created_at
      FROM quotes
      ORDER BY id DESC
      LIMIT 15
    ")->fetchAll();

    json_response([
      'ok' => true,
      'stats' => $totals,
      'orders' => $orderTotals,
      'customers' => $customerCount,
      'by_device' => $byDevice,
      'recent_quotes' => $recent
    ]);
} catch (Throwable $e) {
    json_throwable($e, 'Request failed.');
}

