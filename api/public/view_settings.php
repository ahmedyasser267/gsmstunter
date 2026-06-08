<?php
declare(strict_types=1);
require_once __DIR__ . '/../../php/config.php';

try {
    $row = db()->query("SELECT default_view_mode, items_per_page, show_filters, show_sort FROM product_view_settings ORDER BY id ASC LIMIT 1")->fetch();
    json_response(['ok' => true, 'settings' => $row]);
} catch (Throwable $e) {
    json_throwable($e);
}

