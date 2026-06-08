<?php
declare(strict_types=1);
require_once __DIR__ . '/../../php/config.php';
require_admin();

try {
    $pdo = db();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        $rows = $pdo->query("
          SELECT c.*,
                 tnl.name AS name_nl, tde.name AS name_de, tfr.name AS name_fr
          FROM categories c
          LEFT JOIN category_translations tnl ON tnl.category_id = c.id AND tnl.lang_code='nl'
          LEFT JOIN category_translations tde ON tde.category_id = c.id AND tde.lang_code='de'
          LEFT JOIN category_translations tfr ON tfr.category_id = c.id AND tfr.lang_code='fr'
          ORDER BY c.sort_order ASC, c.id ASC
        ")->fetchAll();
        json_response(['ok' => true, 'items' => $rows]);
    }

    $data = read_json_body();
    $action = (string)($data['action'] ?? '');

    if ($action === 'create') {
        $stmt = $pdo->prepare("
          INSERT INTO categories (category_key, parent_id, icon, image_url, sort_order, is_visible)
          VALUES (:keyv, :parent_id, :icon, :image_url, :sort_order, :is_visible)
        ");
        $stmt->execute([
          ':keyv' => (string)($data['category_key'] ?? ''),
          ':parent_id' => !empty($data['parent_id']) ? (int)$data['parent_id'] : null,
          ':icon' => $data['icon'] ?? null,
          ':image_url' => $data['image_url'] ?? null,
          ':sort_order' => (int)($data['sort_order'] ?? 0),
          ':is_visible' => !empty($data['is_visible']) ? 1 : 0,
        ]);
        $id = (int)$pdo->lastInsertId();
        upsertCatTr($pdo, $id, 'nl', (string)($data['name_nl'] ?? ''), (string)($data['desc_nl'] ?? ''));
        upsertCatTr($pdo, $id, 'de', (string)($data['name_de'] ?? ''), (string)($data['desc_de'] ?? ''));
        upsertCatTr($pdo, $id, 'fr', (string)($data['name_fr'] ?? ''), (string)($data['desc_fr'] ?? ''));
        json_response(['ok' => true, 'id' => $id]);
    }

    if ($action === 'update') {
        $id = (int)($data['id'] ?? 0);
        $stmt = $pdo->prepare("
          UPDATE categories SET
            category_key=:keyv, parent_id=:parent_id, icon=:icon, image_url=:image_url,
            sort_order=:sort_order, is_visible=:is_visible
          WHERE id=:id
        ");
        $stmt->execute([
          ':id' => $id,
          ':keyv' => (string)($data['category_key'] ?? ''),
          ':parent_id' => !empty($data['parent_id']) ? (int)$data['parent_id'] : null,
          ':icon' => $data['icon'] ?? null,
          ':image_url' => $data['image_url'] ?? null,
          ':sort_order' => (int)($data['sort_order'] ?? 0),
          ':is_visible' => !empty($data['is_visible']) ? 1 : 0,
        ]);
        upsertCatTr($pdo, $id, 'nl', (string)($data['name_nl'] ?? ''), (string)($data['desc_nl'] ?? ''));
        upsertCatTr($pdo, $id, 'de', (string)($data['name_de'] ?? ''), (string)($data['desc_de'] ?? ''));
        upsertCatTr($pdo, $id, 'fr', (string)($data['name_fr'] ?? ''), (string)($data['desc_fr'] ?? ''));
        json_response(['ok' => true]);
    }

    if ($action === 'delete') {
        $id = (int)($data['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id=:id");
        $stmt->execute([':id' => $id]);
        json_response(['ok' => true]);
    }

    json_response(['ok' => false, 'error' => 'Unsupported action'], 422);
} catch (Throwable $e) {
    json_throwable($e, 'Request failed.');
}

function upsertCatTr(PDO $pdo, int $categoryId, string $lang, string $name, string $desc): void
{
    $stmt = $pdo->prepare("
      INSERT INTO category_translations (category_id, lang_code, name, description)
      VALUES (:cid, :lang, :name, :descr)
      ON DUPLICATE KEY UPDATE
        name=VALUES(name), description=VALUES(description)
    ");
    $stmt->execute([
      ':cid' => $categoryId,
      ':lang' => $lang,
      ':name' => $name,
      ':descr' => $desc
    ]);
}

