<?php
declare(strict_types=1);

const CHECKOUT_MAX_CART_LINES = 50;

const CHECKOUT_MAX_QTY_PER_LINE = 99;

const CHECKOUT_CURRENCY_DEFAULT = 'EUR';

/** @return list<string> */
function checkout_payment_methods(): array
{
    return ['ideal', 'card', 'paypal', 'klarna'];
}

/** @return list<string> */
function checkout_shipping_methods(): array
{
    return ['standard', 'express'];
}

/** @return list<string> */
function checkout_allowed_currencies(): array
{
    return ['EUR'];
}

/**
 * Server-side catalog resolution for checkout (authoritative pricing + stock).
 */
function checkout_resolve_product_id(array $item): ?int
{
    if (!empty($item['product_id'])) {
        $p = (int)$item['product_id'];
        return $p > 0 ? $p : null;
    }
    if (isset($item['id']) && is_numeric($item['id'])) {
        $p = (int)$item['id'];
        return $p > 0 ? $p : null;
    }
    return null;
}

/**
 * Whitelist payment methods aligned with checkout UI (never trust arbitrary strings).
 */
function checkout_normalize_payment_method(string $raw): string
{
    $v = strtolower(trim($raw));
    if ($v === '') {
        $v = 'ideal';
    }
    if (!in_array($v, checkout_payment_methods(), true)) {
        throw new RuntimeException('Ongeldige betaalmethode.');
    }
    return $v;
}

function checkout_normalize_shipping_method(string $raw): string
{
    $v = strtolower(trim($raw));
    if ($v === '') {
        $v = 'standard';
    }
    if (!in_array($v, checkout_shipping_methods(), true)) {
        throw new RuntimeException('Ongeldige verzendmethode.');
    }
    return $v;
}

function checkout_normalize_currency(string $raw): string
{
    $v = strtoupper(trim($raw));
    if ($v === '') {
        $v = CHECKOUT_CURRENCY_DEFAULT;
    }
    if (!in_array($v, checkout_allowed_currencies(), true)) {
        throw new RuntimeException('Ongeldige valuta.');
    }
    return $v;
}

/**
 * Validate cart shape before touching money or inventory (anti-abuse caps).
 *
 * @param array<mixed> $items
 */
function checkout_validate_cart_lines(array $items): void
{
    $lines = 0;
    foreach ($items as $item) {
        if (!is_array($item)) {
            throw new RuntimeException('Je winkelwagen heeft een ongeldig formaat.');
        }
        if (checkout_resolve_product_id($item) === null) {
            throw new RuntimeException('Elke regel moet een geldig product hebben.');
        }
        $qty = (int)($item['quantity'] ?? 1);
        if ($qty < 1 || $qty > CHECKOUT_MAX_QTY_PER_LINE) {
            throw new RuntimeException('Ongeldige hoeveelheid voor een product.');
        }
        ++$lines;
    }
    if ($lines === 0 || $lines > CHECKOUT_MAX_CART_LINES) {
        throw new RuntimeException('Je winkelwagen bevat geen geldige productregels of te veel regels.');
    }
}

/**
 * Consolidate quantities per product for stock checks (handles duplicate cart lines).
 *
 * @param array<mixed> $items
 *
 * @return array<int,int> product_id => total quantity needed
 */
function checkout_aggregate_need(array $items): array
{
    $need = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $pid = checkout_resolve_product_id($item);
        if ($pid === null) {
            continue;
        }
        $qty = (int)($item['quantity'] ?? 1);
        $need[$pid] = ($need[$pid] ?? 0) + $qty;
    }
    return $need;
}

/**
 * Shared SELECT shape for pricing / visibility / stock (single round-trip).
 *
 * @param list<int> $sortedUniqueIds
 *
 * @return array<int, array{id:int,sku:string,display_name:string,stock_qty:int,is_visible:int,unit_price:float}>
 */
function checkout_select_products_map(PDO $pdo, array $sortedUniqueIds, bool $forUpdate): array
{
    if ($sortedUniqueIds === []) {
        return [];
    }
    $placeholders = implode(',', array_fill(0, count($sortedUniqueIds), '?'));
    $sql = "
      SELECT p.id, p.sku, p.stock_qty, p.is_visible,
        COALESCE(pt.name, CONCAT(p.brand, ' ', p.model)) AS display_name,
        ROUND(p.price * (1 + (IFNULL(p.dynamic_adjust_percent,0) / 100)), 2) AS unit_price
      FROM products p
      LEFT JOIN product_translations pt ON pt.product_id = p.id AND pt.lang_code = 'nl'
      WHERE p.id IN ($placeholders)
      ORDER BY p.id ASC
    ";
    if ($forUpdate) {
        $sql .= ' FOR UPDATE';
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($sortedUniqueIds);
    $map = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = (int)$row['id'];
        $map[$id] = [
            'id' => $id,
            'sku' => (string)$row['sku'],
            'display_name' => (string)$row['display_name'],
            'stock_qty' => (int)$row['stock_qty'],
            'is_visible' => (int)$row['is_visible'],
            'unit_price' => (float)$row['unit_price'],
        ];
    }
    return $map;
}

/**
 * Load visible products without locking — used for abandoned-cart snapshots & analytics.
 *
 * @param array<int,int> $needByProductId
 *
 * @return array<int, array{id:int,sku:string,display_name:string,stock_qty:int,is_visible:int,unit_price:float}>
 */
function checkout_load_visible_products(PDO $pdo, array $needByProductId): array
{
    if ($needByProductId === []) {
        throw new RuntimeException('Geen geldige productregels in winkelwagen.');
    }
    $ids = array_keys($needByProductId);
    sort($ids, SORT_NUMERIC);
    $catalog = checkout_select_products_map($pdo, $ids, false);
    foreach ($ids as $pid) {
        if (!isset($catalog[$pid])) {
            throw new RuntimeException('Een product in je winkelwagen bestaat niet meer.');
        }
        if (empty($catalog[$pid]['is_visible'])) {
            throw new RuntimeException('Een product in je winkelwagen is niet meer beschikbaar.');
        }
    }
    return $catalog;
}

/**
 * Load and lock all cart products inside an open transaction; validate visibility and stock.
 *
 * @param array<int,int> $needByProductId
 *
 * @return array<int, array{id:int,sku:string,display_name:string,stock_qty:int,is_visible:int,unit_price:float}>
 */
function checkout_lock_and_validate_products(PDO $pdo, array $needByProductId): array
{
    if ($needByProductId === []) {
        throw new RuntimeException('Geen geldige productregels in winkelwagen.');
    }
    $ids = array_keys($needByProductId);
    sort($ids, SORT_NUMERIC);
    $catalog = checkout_select_products_map($pdo, $ids, true);
    foreach ($ids as $pid) {
        if (!isset($catalog[$pid])) {
            throw new RuntimeException('Een product in je winkelwagen bestaat niet meer.');
        }
        $row = $catalog[$pid];
        if (empty($row['is_visible'])) {
            throw new RuntimeException('Een product in je winkelwagen is niet meer beschikbaar.');
        }
        $need = $needByProductId[$pid];
        if ($row['stock_qty'] < $need) {
            throw new RuntimeException(
                'Niet genoeg voorraad voor ' . $row['display_name'] . ' (gevraagd: ' . $need . ', beschikbaar: ' . $row['stock_qty'] . ')'
            );
        }
    }
    return $catalog;
}

/**
 * Apply stock decrements after order lines are written.
 *
 * @param array<int,int> $needByProductId
 */
function checkout_decrement_stock(PDO $pdo, array $needByProductId): void
{
    $stmt = $pdo->prepare('
      UPDATE products
      SET stock_qty = stock_qty - :q
      WHERE id = :id AND stock_qty >= :q
    ');
    foreach ($needByProductId as $pid => $qty) {
        $stmt->execute([':q' => $qty, ':id' => $pid]);
        if ($stmt->rowCount() !== 1) {
            throw new RuntimeException('Voorraad kon niet worden bijgewerkt; probeer opnieuw.');
        }
    }
}

/**
 * Build server-priced lines + subtotal for persistence (snapshot / reconciliation).
 *
 * @param array<mixed>           $items
 * @param array<int, mixed>      $catalog from checkout_load_visible_products or lock path
 *
 * @return array{lines: list<array<string,mixed>>, subtotal: float}
 */
function checkout_authoritative_lines(array $items, array $catalog): array
{
    $lines = [];
    $subtotal = 0.0;
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $pid = checkout_resolve_product_id($item);
        if ($pid === null || !isset($catalog[$pid])) {
            continue;
        }
        $row = $catalog[$pid];
        $qty = (int)($item['quantity'] ?? 1);
        $unit = $row['unit_price'];
        $lineTotal = round($qty * $unit, 2);
        $subtotal += $lineTotal;
        $lines[] = array_merge($item, [
            'product_id' => $pid,
            'quantity' => $qty,
            'unit_price_authoritative' => $unit,
            'line_total_authoritative' => $lineTotal,
            'sku_authoritative' => $row['sku'],
            'name_authoritative' => $row['display_name'],
        ]);
    }
    return ['lines' => $lines, 'subtotal' => round($subtotal, 2)];
}
