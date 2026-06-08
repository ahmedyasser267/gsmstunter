<?php
declare(strict_types=1);
require_once __DIR__ . '/../../php/config.php';
require_admin();

try {
    $pdo = db();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method === 'GET') {
        $rows = $pdo->query("SELECT * FROM section_visibility ORDER BY section_key ASC")->fetchAll();
        json_response(['ok' => true, 'items' => $rows]);
    }

    $data = read_json_body();
    $stmt = $pdo->prepare("UPDATE section_visibility SET is_visible=:v, label=:l WHERE section_key=:k");
    $stmt->execute([
      ':v' => !empty($data['is_visible']) ? 1 : 0,
      ':l' => (string)($data['label'] ?? ''),
      ':k' => (string)($data['section_key'] ?? '')
    ]);
    json_response(['ok' => true]);
} catch (Throwable $e) {
    json_throwable($e, 'Request failed.');
}

