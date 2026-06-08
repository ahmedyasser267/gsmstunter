<?php
declare(strict_types=1);
require_once __DIR__ . '/../../php/config.php';

try {
    $lang = (string)($_GET['lang'] ?? 'nl');
    if (!in_array($lang, ['nl', 'de', 'fr'], true)) {
      $lang = 'nl';
    }

    $stmt = db()->prepare("
      SELECT c.id, c.category_key, c.parent_id, c.icon, c.image_url, c.sort_order, c.is_visible,
             ct.name, ct.description
      FROM categories c
      LEFT JOIN category_translations ct ON ct.category_id = c.id AND ct.lang_code = :lang
      WHERE c.is_visible = 1
      ORDER BY c.sort_order ASC, c.id ASC
    ");
    $stmt->execute([':lang' => $lang]);
    json_response(['ok' => true, 'items' => $stmt->fetchAll()]);
} catch (Throwable $e) {
    json_throwable($e);
}

