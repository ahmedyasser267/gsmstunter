<?php
declare(strict_types=1);
require_once __DIR__ . '/../../php/config.php';
require_admin();

try {
    $rows = db()->query("
      SELECT c.id, c.full_name, c.email, c.phone, c.created_at,
             COUNT(o.id) AS orders_count,
             IFNULL(SUM(o.total_amount),0) AS lifetime_value
      FROM customers c
      LEFT JOIN orders o ON o.customer_id = c.id
      GROUP BY c.id
      ORDER BY c.id DESC
      LIMIT 500
    ")->fetchAll();
    json_response(['ok' => true, 'items' => $rows]);
} catch (Throwable $e) {
    json_throwable($e, 'Request failed.');
}

