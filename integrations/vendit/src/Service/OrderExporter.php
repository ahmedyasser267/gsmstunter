<?php
declare(strict_types=1);

namespace Integrations\Vendit\Service;

use DateTimeImmutable;
use Integrations\Vendit\Contract\FileStorageInterface;
use Integrations\Vendit\Contract\OrderRepositoryInterface;
use Integrations\Vendit\Contract\SyncLoggerInterface;
use Integrations\Vendit\Dto\ExportResult;
use Integrations\Vendit\Xml\XmlToolkit;

final class OrderExporter
{
    public function __construct(
        private readonly FileStorageInterface $fileStorage,
        private readonly XmlToolkit $xmlToolkit,
        private readonly OrderRepositoryInterface $repository,
        private readonly SyncLoggerInterface $logger,
    ) {
    }

    public function exportPendingOrders(): ExportResult
    {
        $this->logger->start('order_export', 'export');
        $orders = $this->repository->listPendingExport();

        if ($orders === []) {
            $this->logger->complete('success', 0, 0, 'No pending orders.');
            return new ExportResult(0, 0, [], 'success', 'No pending orders.');
        }

        $fileName = sprintf('order-%s.xml', (new DateTimeImmutable())->format('Ymd-His'));
        $exported = 0;
        $failed = 0;

        try {
            $xml = $this->xmlToolkit->buildOrderImport($orders);
            $path = $this->fileStorage->writeExportFile($fileName, $xml);

            foreach ($orders as $order) {
                if (!isset($order['id'])) {
                    continue;
                }
                try {
                    $this->repository->markExported((int) $order['id'], basename($path));
                    $exported++;
                } catch (\Throwable $e) {
                    $failed++;
                    $this->repository->markExportFailed((int) $order['id'], $e->getMessage());
                }
            }

            $status = $failed > 0 ? ($exported > 0 ? 'partial' : 'failed') : 'success';
            $this->logger->complete($status, $exported, $failed, 'Order export completed.');

            return new ExportResult($exported, $failed, [basename($path)], $status);
        } catch (\Throwable $e) {
            foreach ($orders as $order) {
                if (!isset($order['id'])) {
                    continue;
                }
                $this->repository->markExportFailed((int) $order['id'], $e->getMessage());
            }
            $this->logger->error($e->getMessage());
            $this->logger->complete('failed', 0, count($orders), 'Order export failed.');

            return new ExportResult(0, count($orders), [], 'failed', $e->getMessage());
        }
    }
}
