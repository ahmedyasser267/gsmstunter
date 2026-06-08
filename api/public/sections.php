<?php
declare(strict_types=1);
require_once __DIR__ . '/../../php/config.php';

try {
    $rows = db()->query("SELECT section_key, is_visible FROM section_visibility")->fetchAll();
    $out = [];
    foreach ($rows as $r) {
      $out[$r['section_key']] = (bool)$r['is_visible'];
    }
    json_response(['ok' => true, 'sections' => $out]);
} catch (Throwable $e) {
    json_throwable($e);
}

