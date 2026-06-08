<?php
declare(strict_types=1);
require_once __DIR__ . '/../../php/config.php';
require_admin();

try {
    $pdo    = db();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        /* Single order detail */
        if (!empty($_GET['id'])) {
            $id  = (int)$_GET['id'];
            $row = $pdo->prepare("
              SELECT o.id, o.order_reference, o.status, o.payment_method, o.shipping_method,
                     o.shipping_address, o.subtotal, o.shipping_cost, o.tax_amount, o.total_amount,
                     o.notes, o.created_at,
                     c.full_name, c.email, c.phone
              FROM orders o
              JOIN customers c ON c.id = o.customer_id
              WHERE o.id = :id
            ");
            $row->execute([':id' => $id]);
            $order = $row->fetch();
            if (!$order) {
                json_response(['ok' => false, 'error' => 'Order not found'], 404);
            }
            $items = $pdo->prepare("
              SELECT oi.product_name, oi.sku, oi.quantity, oi.unit_price, oi.line_total, oi.meta_json
              FROM order_items oi
              WHERE oi.order_id = :id
              ORDER BY oi.id ASC
            ");
            $items->execute([':id' => $id]);
            $order['items'] = $items->fetchAll();

            $hist = $pdo->prepare("
              SELECT status, changed_by, created_at
              FROM order_status_history
              WHERE order_id = :id
              ORDER BY id ASC
            ");
            $hist->execute([':id' => $id]);
            $order['history'] = $hist->fetchAll();

            json_response(['ok' => true, 'order' => $order]);
        }

        /* All orders list */
        $rows = $pdo->query("
          SELECT o.id, o.order_reference, o.status, o.payment_method, o.shipping_method,
                 o.subtotal, o.shipping_cost, o.tax_amount, o.total_amount, o.created_at,
                 c.full_name, c.email, c.phone
          FROM orders o
          JOIN customers c ON c.id = o.customer_id
          ORDER BY o.id DESC
          LIMIT 500
        ")->fetchAll();
        json_response(['ok' => true, 'items' => $rows]);
    }

    /* POST – update status */
    $data   = read_json_body();
    $id     = (int)($data['id'] ?? 0);
    $status = (string)($data['status'] ?? '');
    if ($id <= 0 || $status === '') {
        json_response(['ok' => false, 'error' => 'Invalid payload'], 422);
    }

    $allowed = ['new', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (!in_array($status, $allowed, true)) {
        json_response(['ok' => false, 'error' => 'Invalid status'], 422);
    }

    $pdo->prepare("UPDATE orders SET status=:s WHERE id=:id")
        ->execute([':s' => $status, ':id' => $id]);

    $pdo->prepare("INSERT INTO order_status_history (order_id, status, changed_by) VALUES (:o,:s,:by)")
        ->execute([':o' => $id, ':s' => $status, ':by' => ($_SESSION['admin_username'] ?? 'admin')]);

    json_response(['ok' => true]);

} catch (Throwable $e) {
    json_throwable($e, 'Request failed.');
}
