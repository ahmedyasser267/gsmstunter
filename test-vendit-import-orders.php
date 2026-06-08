<?php
declare(strict_types=1);

require_once __DIR__ . '/integrations/vendit/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $container = vendit_container();
    $result = $container->orderImporter()->importFromFolder();
    echo json_encode([
        'route' => 'test-vendit-import-orders',
        'ok' => $result->status !== 'failed',
        'result' => $result->toArray(),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'route' => 'test-vendit-import-orders',
        'ok' => false,
        'error' => $e->getMessage(),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
