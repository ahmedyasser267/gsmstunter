<?php
declare(strict_types=1);
require_once __DIR__ . '/../../php/config.php';
require_admin();

/* ─── auto-create pricing variants table ─── */
function ensureVariantsTable(PDO $pdo): void {
    static $done = false;
    if ($done) return; $done = true;
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS product_pricing_variants (
        id            BIGINT       AUTO_INCREMENT PRIMARY KEY,
        product_id    BIGINT       NOT NULL,
        condition_key VARCHAR(50)  NOT NULL DEFAULT '',
        storage_label VARCHAR(50)  NOT NULL DEFAULT '',
        price         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        stock_qty     INT           NOT NULL DEFAULT 0,
        UNIQUE KEY uq_variant (product_id, condition_key, storage_label),
        CONSTRAINT fk_ppv_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

/* ─── save variant rows for a product ─── */
function saveVariants(PDO $pdo, int $productId, array $variants): void {
    $pdo->prepare("DELETE FROM product_pricing_variants WHERE product_id=:id")
        ->execute([':id' => $productId]);
    $ins = $pdo->prepare("
      INSERT INTO product_pricing_variants (product_id, condition_key, storage_label, price, stock_qty)
      VALUES (:pid, :ck, :sl, :price, :stock)
    ");
    foreach ($variants as $v) {
        $ins->execute([
            ':pid'   => $productId,
            ':ck'    => (string)($v['condition_key'] ?? ''),
            ':sl'    => (string)($v['storage_label'] ?? ''),
            ':price' => (float)($v['price'] ?? 0),
            ':stock' => (int)($v['stock_qty'] ?? 0),
        ]);
    }
}

try {
    $pdo = db();
    ensureVariantsTable($pdo);
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        /* Return variants for a single product */
        if (!empty($_GET['variants']) && !empty($_GET['id'])) {
            $rows = $pdo->prepare("SELECT * FROM product_pricing_variants WHERE product_id=:id ORDER BY condition_key, storage_label");
            $rows->execute([':id' => (int)$_GET['id']]);
            json_response(['ok' => true, 'variants' => $rows->fetchAll()]);
        }

        /* Full product list with translations */
        $rows = $pdo->query("
          SELECT p.*,
                 tnl.name AS name_nl, tnl.short_description AS short_nl, tnl.long_description AS long_nl,
                 tde.name AS name_de, tde.short_description AS short_de, tde.long_description AS long_de,
                 tfr.name AS name_fr, tfr.short_description AS short_fr, tfr.long_description AS long_fr
          FROM products p
          LEFT JOIN product_translations tnl ON tnl.product_id=p.id AND tnl.lang_code='nl'
          LEFT JOIN product_translations tde ON tde.product_id=p.id AND tde.lang_code='de'
          LEFT JOIN product_translations tfr ON tfr.product_id=p.id AND tfr.lang_code='fr'
          ORDER BY p.sort_order ASC, p.id DESC
        ")->fetchAll();
        json_response(['ok' => true, 'items' => $rows]);
    }

    $data   = read_json_body();
    $action = (string)($data['action'] ?? '');

    $productFields = static function(array $data): array {
        return [
            ':sku'                   => (string)($data['sku'] ?? ''),
            ':ptype'                 => (string)($data['product_type'] ?? 'smartphone'),
            ':category_id'           => !empty($data['category_id']) ? (int)$data['category_id'] : null,
            ':brand'                 => (string)($data['brand'] ?? ''),
            ':model'                 => (string)($data['model'] ?? ''),
            ':storage'               => $data['storage_label'] ?? null,
            ':ram_gb'                => isset($data['ram_gb']) && $data['ram_gb'] !== '' ? (int)$data['ram_gb'] : null,
            ':camera_mp'             => isset($data['camera_mp']) && $data['camera_mp'] !== '' ? (int)$data['camera_mp'] : null,
            ':battery_mah'           => isset($data['battery_mah']) && $data['battery_mah'] !== '' ? (int)$data['battery_mah'] : null,
            ':screen_size_in'        => isset($data['screen_size_in']) && $data['screen_size_in'] !== '' ? (float)$data['screen_size_in'] : null,
            ':chipset'               => $data['chipset'] ?? null,
            ':color'                 => $data['color'] ?? null,
            ':condition_key'         => $data['condition_key'] ?? null,
            ':price'                 => (float)($data['price'] ?? 0),
            ':dynamic_adjust_percent'=> (float)($data['dynamic_adjust_percent'] ?? 0),
            ':old_price'             => isset($data['old_price']) && $data['old_price'] !== '' ? (float)$data['old_price'] : null,
            ':stock_qty'             => (int)($data['stock_qty'] ?? 0),
            ':image_url'             => $data['image_url'] ?? null,
            ':is_visible'            => !empty($data['is_visible']) ? 1 : 0,
            ':sort_order'            => (int)($data['sort_order'] ?? 0),
        ];
    };

    if ($action === 'create') {
        $stmt = $pdo->prepare("
          INSERT INTO products (
            sku, product_type, category_id, brand, model, storage_label, ram_gb, camera_mp, battery_mah, screen_size_in,
            chipset, color, condition_key, price, dynamic_adjust_percent, old_price, stock_qty, image_url, is_visible, sort_order
          ) VALUES (
            :sku,:ptype,:category_id,:brand,:model,:storage,:ram_gb,:camera_mp,:battery_mah,:screen_size_in,
            :chipset,:color,:condition_key,:price,:dynamic_adjust_percent,:old_price,:stock_qty,:image_url,:is_visible,:sort_order
          )
        ");
        $stmt->execute($productFields($data));
        $productId = (int)$pdo->lastInsertId();
        upsertTranslation($pdo, $productId, 'nl', (string)($data['name_nl'] ?? ''), (string)($data['short_nl'] ?? ''), (string)($data['long_nl'] ?? ''));
        upsertTranslation($pdo, $productId, 'de', (string)($data['name_de'] ?? ''), (string)($data['short_de'] ?? ''), (string)($data['long_de'] ?? ''));
        upsertTranslation($pdo, $productId, 'fr', (string)($data['name_fr'] ?? ''), (string)($data['short_fr'] ?? ''), (string)($data['long_fr'] ?? ''));
        if (!empty($data['variants']) && is_array($data['variants'])) {
            saveVariants($pdo, $productId, $data['variants']);
        }
        json_response(['ok' => true, 'id' => $productId]);
    }

    if ($action === 'update') {
        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) json_response(['ok' => false, 'error' => 'Invalid id'], 422);
        $params = $productFields($data);
        $params[':id'] = $id;
        $stmt = $pdo->prepare("
          UPDATE products SET
            sku=:sku, product_type=:ptype, category_id=:category_id, brand=:brand, model=:model, storage_label=:storage,
            ram_gb=:ram_gb, camera_mp=:camera_mp, battery_mah=:battery_mah, screen_size_in=:screen_size_in, chipset=:chipset,
            color=:color, condition_key=:condition_key, price=:price, dynamic_adjust_percent=:dynamic_adjust_percent,
            old_price=:old_price, stock_qty=:stock_qty, image_url=:image_url, is_visible=:is_visible, sort_order=:sort_order
          WHERE id=:id
        ");
        $stmt->execute($params);
        upsertTranslation($pdo, $id, 'nl', (string)($data['name_nl'] ?? ''), (string)($data['short_nl'] ?? ''), (string)($data['long_nl'] ?? ''));
        upsertTranslation($pdo, $id, 'de', (string)($data['name_de'] ?? ''), (string)($data['short_de'] ?? ''), (string)($data['long_de'] ?? ''));
        upsertTranslation($pdo, $id, 'fr', (string)($data['name_fr'] ?? ''), (string)($data['short_fr'] ?? ''), (string)($data['long_fr'] ?? ''));
        if (isset($data['variants']) && is_array($data['variants'])) {
            saveVariants($pdo, $id, $data['variants']);
        }
        json_response(['ok' => true]);
    }

    if ($action === 'delete') {
        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) json_response(['ok' => false, 'error' => 'Invalid id'], 422);
        $pdo->prepare("DELETE FROM products WHERE id=:id")->execute([':id' => $id]);
        json_response(['ok' => true]);
    }

    json_response(['ok' => false, 'error' => 'Unsupported action'], 422);
} catch (Throwable $e) {
    json_throwable($e, 'Request failed.');
}

function upsertTranslation(PDO $pdo, int $productId, string $lang, string $name, string $short, string $long): void {
    $pdo->prepare("
      INSERT INTO product_translations (product_id, lang_code, name, short_description, long_description)
      VALUES (:pid,:lang,:name,:short,:longd)
      ON DUPLICATE KEY UPDATE name=VALUES(name), short_description=VALUES(short_description), long_description=VALUES(long_description)
    ")->execute([':pid' => $productId, ':lang' => $lang, ':name' => $name, ':short' => $short, ':longd' => $long]);
}
