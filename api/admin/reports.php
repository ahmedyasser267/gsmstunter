<?php
declare(strict_types=1);
require_once __DIR__ . '/../../php/config.php';
require_admin();

function csv_response(string $filename, array $headers, array $rows): void
{
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    if ($out === false) {
        exit;
    }
    // UTF-8 BOM so Excel opens Arabic/UTF-8 text correctly.
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, $headers);
    foreach ($rows as $row) {
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}

try {
    $pdo = db();
    $export = (string)($_GET['export'] ?? '');
    if ($export !== '') {
        if ($export === 'quotes') {
            $rows = $pdo->query("
              SELECT quote_reference, device_key, final_offer, status, created_at
              FROM quotes
              ORDER BY id DESC
              LIMIT 5000
            ")->fetchAll();
            csv_response('quotes-report.csv', ['reference', 'device', 'offer', 'status', 'created_at'], array_map(static function ($r) {
                return [$r['quote_reference'], $r['device_key'], $r['final_offer'], $r['status'], $r['created_at']];
            }, $rows));
        }

        if ($export === 'orders') {
            $rows = $pdo->query("
              SELECT o.order_reference, c.full_name, c.email, o.status, o.total_amount, o.created_at
              FROM orders o
              LEFT JOIN customers c ON c.id = o.customer_id
              ORDER BY o.id DESC
              LIMIT 5000
            ")->fetchAll();
            csv_response('orders-report.csv', ['reference', 'customer', 'email', 'status', 'total', 'created_at'], array_map(static function ($r) {
                return [$r['order_reference'], $r['full_name'] ?? '', $r['email'] ?? '', $r['status'], $r['total_amount'], $r['created_at']];
            }, $rows));
        }

        if ($export === 'products') {
            $rows = $pdo->query("
              SELECT p.id, p.sku, p.brand, p.model, p.price, p.stock_qty, p.is_visible, p.image_url
              FROM products p
              ORDER BY p.id DESC
              LIMIT 5000
            ")->fetchAll();
            csv_response('products-report.csv', ['id', 'sku', 'brand', 'model', 'price', 'stock_qty', 'is_visible', 'image_url'], array_map(static function ($r) {
                return [$r['id'], $r['sku'], $r['brand'], $r['model'], $r['price'], $r['stock_qty'], $r['is_visible'], $r['image_url']];
            }, $rows));
        }

        if ($export === 'customers') {
            $rows = $pdo->query("
              SELECT full_name, email, phone, created_at
              FROM customers
              ORDER BY id DESC
              LIMIT 5000
            ")->fetchAll();
            csv_response('customers-report.csv', ['name', 'email', 'phone', 'created_at'], array_map(static function ($r) {
                return [$r['full_name'], $r['email'], $r['phone'], $r['created_at']];
            }, $rows));
        }

        json_response(['ok' => false, 'error' => 'Unsupported export'], 422);
    }

    $topDefects = $pdo->query("
      SELECT jt.defect_key, COUNT(*) AS total
      FROM quotes q
      JOIN JSON_TABLE(q.selected_defects_json, '$[*]' COLUMNS (defect_key VARCHAR(100) PATH '$')) jt
      GROUP BY jt.defect_key
      ORDER BY total DESC
      LIMIT 10
    ")->fetchAll();

    $topCosmetics = $pdo->query("
      SELECT jt.cosmetic_key, COUNT(*) AS total
      FROM quotes q
      JOIN JSON_TABLE(q.selected_cosmetics_json, '$[*]' COLUMNS (cosmetic_key VARCHAR(100) PATH '$')) jt
      GROUP BY jt.cosmetic_key
      ORDER BY total DESC
      LIMIT 10
    ")->fetchAll();

    $daily = $pdo->query("
      SELECT DATE(created_at) AS date_key, COUNT(*) AS total_quotes, SUM(final_offer) AS offer_sum
      FROM quotes
      GROUP BY DATE(created_at)
      ORDER BY DATE(created_at) DESC
      LIMIT 30
    ")->fetchAll();

    json_response([
      'ok' => true,
      'top_defects' => $topDefects,
      'top_cosmetics' => $topCosmetics,
      'daily' => $daily
    ]);
} catch (Throwable $e) {
    json_throwable($e, 'Request failed.');
}

