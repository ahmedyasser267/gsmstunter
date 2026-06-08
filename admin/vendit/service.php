<?php
declare(strict_types=1);

require_once __DIR__ . '/../../integrations/vendit/bootstrap.php';

use Integrations\Vendit\Container;
use Integrations\Vendit\Xml\XmlToolkit;

/**
 * Admin helpers for /admin/vendit — backed by integrations/vendit module.
 */

function vendit_admin_container(): Container
{
    return vendit_container();
}

/**
 * @return array<string, mixed>
 */
function vendit_admin_process_post(string $action): array
{
    $container = vendit_admin_container();
    $parser = new XmlToolkit();

    return match ($action) {
        'import_customers' => (function () use ($container) {
            $result = $container->customerImporter()->importFromFolder();
            return [
                'flash' => sprintf('Customer import finished. Processed: %d, Failed: %d', $result->processed, $result->failed),
                'flashType' => $result->failed > 0 ? 'warn' : 'success',
            ];
        })(),
        'import_orders' => (function () use ($container) {
            $result = $container->orderImporter()->importFromFolder();
            return [
                'flash' => sprintf('Order import finished. Processed: %d, Failed: %d', $result->processed, $result->failed),
                'flashType' => $result->failed > 0 ? 'warn' : 'success',
            ];
        })(),
        'export_customers' => (function () use ($container) {
            $result = $container->customerExporter()->exportCustomers();
            return [
                'flash' => sprintf('Customer export finished. Exported: %d, Failed: %d', $result->exported, $result->failed),
                'flashType' => $result->failed > 0 ? 'warn' : 'success',
            ];
        })(),
        'export_orders' => (function () use ($container) {
            $result = $container->orderExporter()->exportPendingOrders();
            return [
                'flash' => sprintf('Order export finished. Exported: %d, Failed: %d', $result->exported, $result->failed),
                'flashType' => $result->failed > 0 ? 'warn' : 'success',
            ];
        })(),
        'validate_samples' => (function () use ($parser) {
            $config = vendit_config();
            $samples = glob(rtrim((string) $config['samples_path'], '/\\') . '/*.xml') ?: [];
            $invalid = 0;
            foreach ($samples as $sample) {
                $content = file_get_contents($sample);
                if ($content === false) {
                    $invalid++;
                    continue;
                }
                $validation = $parser->validateStructure($content);
                if (!$validation['valid']) {
                    $invalid++;
                }
            }
            return [
                'flash' => $invalid === 0
                    ? 'All sample XML files are valid.'
                    : "Sample validation completed with {$invalid} invalid file(s).",
                'flashType' => $invalid === 0 ? 'success' : 'warn',
            ];
        })(),
        'seed_import_samples' => (function () {
            $config = vendit_config();
            $import = rtrim((string) $config['import_path'], '/\\');
            $copied = 0;
            foreach (['customer_import.xml', 'order_import.xml'] as $file) {
                $src = rtrim((string) $config['samples_path'], '/\\') . '/' . $file;
                if (!is_file($src)) {
                    continue;
                }
                $dest = $import . '/' . $file;
                if (!is_file($dest) && copy($src, $dest)) {
                    $copied++;
                }
            }
            return [
                'flash' => $copied > 0
                    ? "Copied {$copied} sample file(s) to import folder."
                    : 'Import folder already contains sample files (or samples missing).',
                'flashType' => 'success',
            ];
        })(),
        default => [
            'flash' => 'Unknown Vendit action.',
            'flashType' => 'error',
        ],
    };
}

/**
 * @return array<string, mixed>
 */
function vendit_admin_dashboard_data(?array $flashPayload = null): array
{
    $container = vendit_admin_container();
    $config = vendit_config();
    $stats = $container->dashboardService()->getStats();
    $logger = $container->syncLogger();
    $parser = new XmlToolkit();

    $pendingOrders = $container->db()->fetchOne(
        'SELECT COUNT(*) AS cnt FROM vendit_orders WHERE export_status = :s',
        ['s' => 'pending']
    );

    $samples = glob(rtrim((string) $config['samples_path'], '/\\') . '/*.xml') ?: [];
    $sampleValidations = [];
    foreach ($samples as $sample) {
        $content = @file_get_contents($sample);
        if ($content === false) {
            $sampleValidations[] = [
                'file' => basename($sample),
                'valid' => false,
                'type' => null,
                'errors' => ['Unreadable file'],
            ];
            continue;
        }
        $validation = $parser->validateStructure($content);
        $sampleValidations[] = [
            'file' => basename($sample),
            'valid' => $validation['valid'],
            'type' => $validation['type'],
            'errors' => $validation['errors'],
        ];
    }

    $exportFiles = array_map(static fn($p) => basename($p), $stats['generated_export_files'] ?? []);

    return [
        'config' => $config,
        'flash' => $flashPayload['flash'] ?? null,
        'flashType' => $flashPayload['flashType'] ?? 'info',
        'lastSync' => $logger->lastSyncDate(),
        'lastCustomerImport' => $logger->lastSyncDate('customer_import'),
        'lastCustomerExport' => $logger->lastSyncDate('customer_export'),
        'lastOrderImport' => $logger->lastSyncDate('order_import'),
        'lastOrderSync' => $logger->lastSyncDate('order_export'),
        'totalCustomers' => (int) ($stats['total_customers'] ?? 0),
        'totalOrders' => (int) ($stats['total_orders'] ?? 0),
        'totalOrdersExported' => $container->db()->fetchOne(
            'SELECT COUNT(*) AS cnt FROM vendit_orders WHERE export_status = :s',
            ['s' => 'exported']
        )['cnt'] ?? 0,
        'pendingCount' => is_array($pendingOrders) ? (int) $pendingOrders['cnt'] : 0,
        'successCount' => (int) ($stats['success_count'] ?? 0),
        'errorCount' => (int) ($stats['error_count'] ?? 0),
        'lastImport' => $stats['last_import'] ?? null,
        'lastExport' => $stats['last_export'] ?? null,
        'generatedFiles' => $exportFiles,
        'logs' => $logger->recentLogs(100),
        'sampleValidations' => $sampleValidations,
        'autoExportOnCheckout' => false,
    ];
}

function vendit_admin_h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function vendit_admin_status_badge(string $status): string
{
    $map = [
        'success' => '#166534',
        'failed' => '#991b1b',
        'partial' => '#92400e',
        'started' => '#1e40af',
    ];
    $color = $map[$status] ?? '#334155';
    return '<span class="vendit-pill" style="background:' . $color . '">' . vendit_admin_h($status) . '</span>';
}

function vendit_admin_pending_count(): int
{
    try {
        $row = vendit_admin_container()->db()->fetchOne(
            'SELECT COUNT(*) AS cnt FROM vendit_orders WHERE export_status = :s',
            ['s' => 'pending']
        );
        return is_array($row) ? (int) $row['cnt'] : 0;
    } catch (Throwable) {
        return 0;
    }
}
