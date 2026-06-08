<?php
declare(strict_types=1);
require_once __DIR__ . '/../php/config.php';
require_admin();

header('Content-Type: text/html; charset=utf-8');

$pdo = db();
$checks = [];

function add_check(array &$checks, string $name, bool $ok, string $details = ''): void {
    $checks[] = ['name' => $name, 'ok' => $ok, 'details' => $details];
}

function table_exists(PDO $pdo, string $table): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :t");
    $stmt->execute([':t' => $table]);
    return (int)$stmt->fetchColumn() > 0;
}

function column_exists(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = :t AND column_name = :c");
    $stmt->execute([':t' => $table, ':c' => $column]);
    return (int)$stmt->fetchColumn() > 0;
}

try {
    $requiredTables = [
        'products',
        'product_translations',
        'categories',
        'category_translations',
        'section_visibility',
        'orders',
        'order_items',
        'customers',
        'cart_snapshots'
    ];
    foreach ($requiredTables as $t) {
        add_check($checks, "Table exists: {$t}", table_exists($pdo, $t));
    }

    $requiredProductColumns = [
        'category_id',
        'dynamic_adjust_percent',
        'ram_gb',
        'camera_mp',
        'battery_mah',
        'screen_size_in',
        'chipset'
    ];
    foreach ($requiredProductColumns as $c) {
        add_check($checks, "Column exists: products.{$c}", column_exists($pdo, 'products', $c));
    }

    $requiredSections = [
        'home.categories',
        'home.featured_products',
        'products.header',
        'products.grid',
        'sell.flow',
        'trade.flow'
    ];
    foreach ($requiredSections as $key) {
        $stmt = $pdo->prepare("SELECT is_visible FROM section_visibility WHERE section_key = :k LIMIT 1");
        $stmt->execute([':k' => $key]);
        $row = $stmt->fetch();
        add_check($checks, "Section key configured: {$key}", (bool)$row, $row ? ('is_visible=' . (int)$row['is_visible']) : 'missing');
    }

    $productsCount = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $categoriesCount = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    add_check($checks, 'Categories seeded', $categoriesCount > 0, "count={$categoriesCount}");
    add_check($checks, 'Products seeded', $productsCount > 0, "count={$productsCount}");

    // Auto-repair legacy rows that still miss a category assignment.
    $pdo->exec("
        UPDATE products p
        JOIN categories c
          ON c.category_key = CASE LOWER(TRIM(p.product_type))
            WHEN 'smartphone' THEN 'smartphones'
            WHEN 'laptop' THEN 'laptops'
            WHEN 'tablet' THEN 'tablets'
            WHEN 'smartwatch' THEN 'smartwatches'
            WHEN 'headphone' THEN 'headphones'
            WHEN 'accessory' THEN 'accessories'
            ELSE ''
          END
        SET p.category_id = c.id
        WHERE p.category_id IS NULL
    ");

    $linkedProducts = (int)$pdo->query("
        SELECT COUNT(*)
        FROM products p
        INNER JOIN categories c ON c.id = p.category_id
    ")->fetchColumn();
    $unlinkedProducts = $productsCount - $linkedProducts;
    add_check(
        $checks,
        'Products linked to categories',
        $productsCount === 0 ? true : $linkedProducts === $productsCount,
        "linked={$linkedProducts}, total={$productsCount}, unlinked={$unlinkedProducts}"
    );

    $missingNl = (int)$pdo->query("SELECT COUNT(*) FROM products p LEFT JOIN product_translations t ON t.product_id=p.id AND t.lang_code='nl' WHERE t.id IS NULL")->fetchColumn();
    $missingDe = (int)$pdo->query("SELECT COUNT(*) FROM products p LEFT JOIN product_translations t ON t.product_id=p.id AND t.lang_code='de' WHERE t.id IS NULL")->fetchColumn();
    $missingFr = (int)$pdo->query("SELECT COUNT(*) FROM products p LEFT JOIN product_translations t ON t.product_id=p.id AND t.lang_code='fr' WHERE t.id IS NULL")->fetchColumn();
    add_check($checks, 'Translations complete: NL', $missingNl === 0, "missing={$missingNl}");
    add_check($checks, 'Translations complete: DE', $missingDe === 0, "missing={$missingDe}");
    add_check($checks, 'Translations complete: FR', $missingFr === 0, "missing={$missingFr}");

    $allOk = true;
    foreach ($checks as $c) {
        if (!$c['ok']) {
            $allOk = false;
            break;
        }
    }
} catch (Throwable $e) {
    $allOk = false;
    add_check($checks, 'Runtime exception', false, $e->getMessage());
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>System Check</title>
  <style>
    body { font-family: Inter, Arial, sans-serif; background:#f6f8fb; margin:0; padding:24px; color:#0f172a; }
    .card { max-width: 980px; margin:0 auto; background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:20px; }
    h1 { margin:0 0 6px; }
    .status { font-weight:700; margin-bottom:16px; }
    .ok { color:#15803d; }
    .fail { color:#b91c1c; }
    table { width:100%; border-collapse: collapse; font-size:14px; }
    th, td { border-bottom:1px solid #eef2f7; padding:10px 8px; text-align:left; vertical-align:top; }
    th { background:#f8fafc; }
    .pill { display:inline-block; border-radius:999px; padding:3px 10px; font-size:12px; font-weight:700; }
    .pill.ok { background:#dcfce7; color:#166534; }
    .pill.fail { background:#fee2e2; color:#991b1b; }
    .hint { margin-top:14px; color:#475569; font-size:13px; }
    code { background:#f1f5f9; padding:2px 6px; border-radius:6px; }
  </style>
</head>
<body>
  <div class="card">
    <h1>System Health Check</h1>
    <div class="status <?php echo $allOk ? 'ok' : 'fail'; ?>">
      Overall: <?php echo $allOk ? 'PASS' : 'FAIL'; ?>
    </div>
    <table>
      <thead>
        <tr>
          <th>Check</th>
          <th>Result</th>
          <th>Details</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($checks as $c): ?>
        <tr>
          <td><?php echo htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td>
            <span class="pill <?php echo $c['ok'] ? 'ok' : 'fail'; ?>">
              <?php echo $c['ok'] ? 'PASS' : 'FAIL'; ?>
            </span>
          </td>
          <td><?php echo htmlspecialchars((string)$c['details'], ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <div class="hint">
      If any row fails, apply missing migrations from <code>database/</code>, then refresh this page.
    </div>
  </div>
</body>
</html>

