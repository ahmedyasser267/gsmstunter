<?php
declare(strict_types=1);
require_once __DIR__ . '/../../php/config.php';

try {
    $pdo = db();

    /* Prefer active PHP session; fall back to customer_id hint from localStorage */
    $customerId = (int)($_SESSION['customer_id'] ?? 0);

    if (!$customerId) {
        $hint = (int)($_GET['customer_id'] ?? 0);
        if ($hint > 0) {
            /* Verify the customer exists before trusting the hint */
            $check = $pdo->prepare("SELECT id FROM customers WHERE id=:id LIMIT 1");
            $check->execute([':id' => $hint]);
            if ($check->fetchColumn()) {
                $customerId = $hint;
                /* Restore session so subsequent requests don't need the hint */
                $_SESSION['customer_id'] = $customerId;
            }
        }
    }

    if (!$customerId) {
        json_response(['ok' => true, 'logged_in' => false]);
    }

    $customerId = (int)$customerId;
    $customer = $pdo->prepare("SELECT id, full_name, email, phone, created_at FROM customers WHERE id=:id LIMIT 1");
    $customer->execute([':id' => $customerId]);
    $profile = $customer->fetch();
    if (!$profile) {
        unset($_SESSION['customer_id'], $_SESSION['customer_email']);
        json_response(['ok' => true, 'logged_in' => false]);
    }

    $orders = $pdo->prepare("
      SELECT o.id, o.order_reference, o.status, o.total_amount, o.created_at
      FROM orders o
      WHERE o.customer_id = :cid
      ORDER BY o.id DESC
      LIMIT 20
    ");
    $orders->execute([':cid' => $customerId]);
    $orderRows = $orders->fetchAll();

    $itemsStmt = $pdo->prepare("
      SELECT product_name, quantity, line_total
      FROM order_items
      WHERE order_id = :oid
      ORDER BY id ASC
      LIMIT 3
    ");
    foreach ($orderRows as &$o) {
        $itemsStmt->execute([':oid' => (int)$o['id']]);
        $o['items'] = $itemsStmt->fetchAll();
    }
    unset($o);

    $wishlist = json_decode((string)($_COOKIE['gsmstunter_wishlist'] ?? '[]'), true);
    if (!is_array($wishlist)) {
        $wishlist = [];
    }

    json_response([
        'ok' => true,
        'logged_in' => true,
        'profile' => $profile,
        'orders' => $orderRows,
        'stats' => [
            'total_orders' => count($orderRows),
            'total_spent' => array_reduce($orderRows, static fn($c, $x) => $c + (float)($x['total_amount'] ?? 0), 0.0),
            'wishlist_count' => count($wishlist),
        ]
    ]);
} catch (Throwable $e) {
    json_throwable($e);
}

