<?php
declare(strict_types=1);
require_once __DIR__ . '/../../php/config.php';
require_admin();

try {
    $rows = db()->query("
      SELECT id, snapshot_reference, customer_email, subtotal, currency, created_at
      FROM cart_snapshots
      ORDER BY id DESC
      LIMIT 300
    ")->fetchAll();
    json_response(['ok' => true, 'items' => $rows]);
} catch (Throwable $e) {
    json_throwable($e, 'Request failed.');
}

