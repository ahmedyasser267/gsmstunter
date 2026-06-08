<?php
declare(strict_types=1);
require_once __DIR__ . '/../../php/config.php';

try {
    $lang = (string)($_GET['lang'] ?? 'nl');
    if (!in_array($lang, ['nl', 'de', 'fr'], true)) {
        $lang = 'nl';
    }

    $stmt = db()->prepare("
      SELECT p.id, p.sku, p.product_type, p.category_id, c.category_key, p.brand, p.model, p.storage_label, p.ram_gb, p.camera_mp, p.battery_mah, p.screen_size_in, p.chipset, p.color, p.condition_key, p.price, p.dynamic_adjust_percent, p.old_price, p.stock_qty, p.image_url,
             ROUND(p.price * (1 + (IFNULL(p.dynamic_adjust_percent,0) / 100)), 2) AS effective_price,
             pt.name, pt.short_description, pt.long_description
      FROM products p
      LEFT JOIN categories c ON c.id = p.category_id
      LEFT JOIN product_translations pt ON pt.product_id = p.id AND pt.lang_code=:lang
      WHERE p.is_visible=1
      ORDER BY p.sort_order ASC, p.id DESC
    ");
    $stmt->execute([':lang' => $lang]);
    json_response(['ok' => true, 'items' => $stmt->fetchAll()]);
} catch (Throwable $e) {
    json_throwable($e);
}

