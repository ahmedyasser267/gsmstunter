<?php
declare(strict_types=1);

namespace Integrations\Vendit\Service;

use Integrations\Vendit\Contract\FileStorageInterface;
use Integrations\Vendit\Infrastructure\PdoConnection;

final class DashboardService
{
    public function __construct(
        private readonly PdoConnection $db,
        private readonly FileStorageInterface $fileStorage,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getStats(): array
    {
        $lastImport = $this->db->fetchOne(
            'SELECT * FROM vendit_sync_logs WHERE direction = :direction ORDER BY started_at DESC, id DESC LIMIT 1',
            ['direction' => 'import']
        );
        $lastExport = $this->db->fetchOne(
            'SELECT * FROM vendit_sync_logs WHERE direction = :direction ORDER BY started_at DESC, id DESC LIMIT 1',
            ['direction' => 'export']
        );

        $counts = $this->db->fetchOne(
            'SELECT
                SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) AS success_count,
                SUM(CASE WHEN status IN ("failed", "partial") THEN 1 ELSE 0 END) AS error_count
             FROM vendit_sync_logs'
        );

        $totalCustomers = $this->db->fetchOne('SELECT COUNT(*) AS total_customers FROM vendit_customers');
        $totalOrders = $this->db->fetchOne('SELECT COUNT(*) AS total_orders FROM vendit_orders');

        return [
            'last_import' => $lastImport ?: null,
            'last_export' => $lastExport ?: null,
            'success_count' => (int) ($counts['success_count'] ?? 0),
            'error_count' => (int) ($counts['error_count'] ?? 0),
            'generated_export_files' => $this->fileStorage->listExportFiles(),
            'total_customers' => (int) ($totalCustomers['total_customers'] ?? 0),
            'total_orders' => (int) ($totalOrders['total_orders'] ?? 0),
        ];
    }
}
