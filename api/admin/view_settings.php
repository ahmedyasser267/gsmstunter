<?php
declare(strict_types=1);
require_once __DIR__ . '/../../php/config.php';
require_admin();

try {
    $pdo = db();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method === 'GET') {
        $row = $pdo->query("SELECT * FROM product_view_settings ORDER BY id ASC LIMIT 1")->fetch();
        json_response(['ok' => true, 'settings' => $row]);
    }

    $data = read_json_body();
    $stmt = $pdo->prepare("
      UPDATE product_view_settings
      SET default_view_mode=:mode, items_per_page=:ipp, show_filters=:sf, show_sort=:ss
      WHERE id=1
    ");
    $stmt->execute([
      ':mode' => (string)($data['default_view_mode'] ?? 'grid'),
      ':ipp' => (int)($data['items_per_page'] ?? 12),
      ':sf' => !empty($data['show_filters']) ? 1 : 0,
      ':ss' => !empty($data['show_sort']) ? 1 : 0,
    ]);
    json_response(['ok' => true]);
} catch (Throwable $e) {
    json_throwable($e, 'Request failed.');
}

