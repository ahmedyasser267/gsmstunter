<?php
declare(strict_types=1);
require_once __DIR__ . '/../../php/config.php';
require_once __DIR__ . '/../../php/checkout_catalog.php';

try {
    $pdo = db();
    $data = read_json_body();
    $email = strtolower(trim((string)($data['email'] ?? '')));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(['ok' => false, 'error' => 'Een geldig e-mailadres is verplicht om te bestellen.'], 422);
    }

    $fullName = trim((string)($data['full_name'] ?? ''));
    $phone = trim((string)($data['phone'] ?? ''));
    $items = $data['items'] ?? [];
    if (!is_array($items) || count($items) === 0) {
        json_response(['ok' => false, 'error' => 'Cart is empty'], 422);
    }

    checkout_validate_cart_lines($items);

    try {
        $paymentMethod = checkout_normalize_payment_method((string)($data['payment_method'] ?? ''));
        $shippingMethod = checkout_normalize_shipping_method((string)($data['shipping_method'] ?? ''));
    } catch (RuntimeException $e) {
        json_response(['ok' => false, 'error' => $e->getMessage()], 422);
    }

    $needByPid = checkout_aggregate_need($items);
    if ($needByPid === []) {
        json_response(['ok' => false, 'error' => 'Je winkelwagen bevat ongeldige producten. Vernieuw de pagina en probeer opnieuw.'], 422);
    }

    $pdo->beginTransaction();

    $customerStmt = $pdo->prepare('SELECT id FROM customers WHERE email=:email LIMIT 1');
    $customerStmt->execute([':email' => $email]);
    $customer = $customerStmt->fetch();
    if ($customer) {
        $customerId = (int)$customer['id'];
        $upd = $pdo->prepare('UPDATE customers SET full_name=:n, phone=:p WHERE id=:id');
        $upd->execute([':n' => $fullName !== '' ? $fullName : null, ':p' => $phone !== '' ? $phone : null, ':id' => $customerId]);
    } else {
        $ins = $pdo->prepare('INSERT INTO customers (full_name, email, phone) VALUES (:n,:e,:p)');
        $ins->execute([':n' => $fullName !== '' ? $fullName : null, ':e' => $email, ':p' => $phone !== '' ? $phone : null]);
        $customerId = (int)$pdo->lastInsertId();
    }

    $catalog = checkout_lock_and_validate_products($pdo, $needByPid);

    $priced = checkout_authoritative_lines($items, $catalog);
    $subtotal = $priced['subtotal'];
    // Promotional period: shipping is free.
    $shipping = 0.0;
    $tax = round($subtotal * 0.21, 2);
    $total = round($subtotal + $shipping, 2);
    $ref = 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

    $orderStmt = $pdo->prepare(
        '
      INSERT INTO orders (
        order_reference, customer_id, status, payment_method, shipping_method, shipping_address,
        subtotal, shipping_cost, tax_amount, total_amount
      ) VALUES (
        :ref, :customer_id, \'new\', :payment_method, :shipping_method, :shipping_address,
        :subtotal, :shipping, :tax, :total
      )
    '
    );
    $orderStmt->execute([
      ':ref' => $ref,
      ':customer_id' => $customerId,
      ':payment_method' => $paymentMethod,
      ':shipping_method' => $shippingMethod,
      ':shipping_address' => (string)($data['shipping_address'] ?? ''),
      ':subtotal' => $subtotal,
      ':shipping' => $shipping,
      ':tax' => $tax,
      ':total' => $total
    ]);
    $orderId = (int)$pdo->lastInsertId();

    $itemStmt = $pdo->prepare(
        '
      INSERT INTO order_items (order_id, product_id, product_name, sku, quantity, unit_price, line_total, meta_json)
      VALUES (:order_id, :product_id, :product_name, :sku, :quantity, :unit_price, :line_total, :meta_json)
    '
    );
    foreach ($priced['lines'] as $authLine) {
      $pid = (int)$authLine['product_id'];
      $row = $catalog[$pid];
      $qty = (int)$authLine['quantity'];
      $price = (float)$authLine['unit_price_authoritative'];
      $line = (float)$authLine['line_total_authoritative'];
      $meta = $authLine;

      $itemStmt->execute([
        ':order_id' => $orderId,
        ':product_id' => $pid,
        ':product_name' => $row['display_name'],
        ':sku' => $row['sku'],
        ':quantity' => $qty,
        ':unit_price' => $price,
        ':line_total' => $line,
        ':meta_json' => json_encode($meta, JSON_UNESCAPED_UNICODE),
      ]);
    }

    checkout_decrement_stock($pdo, $needByPid);

    $hist = $pdo->prepare("INSERT INTO order_status_history (order_id, status, changed_by) VALUES (:o, 'new', 'system')");
    $hist->execute([':o' => $orderId]);
    $pdo->commit();

    // Vendit: queue order for VMSII (never blocks checkout success)
    require_once __DIR__ . '/../../app/Services/Vendit/VenditCheckoutBridge.php';
    \App\Services\Vendit\VenditCheckoutBridge::afterCheckout([
        'order_id' => $orderId,
        'order_reference' => $ref,
        'customer_id' => $customerId,
        'email' => $email,
        'full_name' => $fullName,
        'phone' => $phone,
        'payment_method' => $paymentMethod,
        'shipping_method' => $shippingMethod,
        'shipping_address' => (string)($data['shipping_address'] ?? ''),
        'subtotal' => $subtotal,
        'shipping_cost' => $shipping,
        'tax_amount' => $tax,
        'total_amount' => $total,
        'priced_lines' => $priced['lines'],
        'catalog' => $catalog,
    ]);

    json_response(['ok' => true, 'order_reference' => $ref, 'total' => $total]);
} catch (RuntimeException $e) {
    try {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
    } catch (Throwable $_) {
    }
    json_response(['ok' => false, 'error' => $e->getMessage()], 422);
} catch (Throwable $e) {
    try {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
    } catch (Throwable $_) {
    }
    json_throwable($e);
}
