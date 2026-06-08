<?php
declare(strict_types=1);

namespace Integrations\Vendit\Service;

use Integrations\Vendit\Contract\FileStorageInterface;
use Integrations\Vendit\Contract\OrderRepositoryInterface;
use Integrations\Vendit\Contract\SyncLoggerInterface;
use Integrations\Vendit\Contract\XmlValidatorInterface;
use Integrations\Vendit\Dto\ImportResult;
use Integrations\Vendit\Xml\XmlToolkit;

final class OrderImporter
{
    public function __construct(
        private readonly FileStorageInterface $fileStorage,
        private readonly XmlToolkit $xmlToolkit,
        private readonly XmlValidatorInterface $validator,
        private readonly OrderRepositoryInterface $repository,
        private readonly SyncLoggerInterface $logger,
        private readonly bool $archiveProcessed = true,
    ) {
    }

    public function importFromFolder(): ImportResult
    {
        $processed = 0;
        $failed = 0;
        $filesSummary = [];

        foreach ($this->fileStorage->listImportFiles('*.xml') as $path) {
            try {
                $content = $this->fileStorage->readImportFile($path);
                if ($this->detectType($content) !== 'OrderImport') {
                    continue;
                }
                $result = $this->importFile($path);
                $processed += $result->processed;
                $failed += $result->failed;
                $filesSummary = array_merge($filesSummary, $result->files);
            } catch (\Throwable $e) {
                $failed++;
                $filesSummary[] = [
                    'file' => basename($path),
                    'status' => 'failed',
                    'message' => $e->getMessage(),
                ];
            }
        }

        $status = $failed > 0 ? ($processed > 0 ? 'partial' : 'failed') : 'success';
        return new ImportResult($processed, $failed, $filesSummary, $status);
    }

    public function importFile(string $path): ImportResult
    {
        $content = $this->fileStorage->readImportFile($path);
        $fileName = basename($path);
        $type = $this->detectType($content);
        if ($type !== 'OrderImport') {
            throw new \RuntimeException('Unsupported XML type for order importer: ' . $type);
        }

        $this->logger->start('order_import', 'import', $fileName, $path);
        $parsed = $this->xmlToolkit->parseOrderImport($content);
        $orders = is_array($parsed['orders'] ?? null) ? $parsed['orders'] : [];

        $processed = 0;
        $failed = 0;

        foreach ($orders as $order) {
            if (!is_array($order)) {
                $failed++;
                $this->logger->error('Invalid order payload in file ' . $fileName);
                continue;
            }

            $errors = $this->validator->validateOrderImport($order);
            if ($errors !== []) {
                $failed++;
                $this->logger->error(
                    sprintf(
                        'Validation failed for order %s: %s',
                        (string) ($order['order_number'] ?? 'unknown'),
                        implode('; ', $errors)
                    )
                );
                continue;
            }

            try {
                $orderId = $this->repository->upsertOrder($order);
                $items = is_array($order['items'] ?? null) ? $order['items'] : [];
                $this->repository->replaceItems($orderId, $items);
                $processed++;
                $this->logger->info(
                    "Order Imported\nOrderNumber: " . (string) ($order['order_number'] ?? '')
                );
            } catch (\Throwable $e) {
                $failed++;
                $this->logger->error('Order import failed: ' . $e->getMessage());
            }
        }

        $status = $failed > 0 ? ($processed > 0 ? 'partial' : 'failed') : 'success';
        $this->logger->complete($status, $processed, $failed, sprintf('Processed %s', $fileName));

        if ($status === 'success' && $this->archiveProcessed) {
            $this->fileStorage->archiveImportFile($path);
        }

        return new ImportResult(
            $processed,
            $failed,
            [[
                'file' => $fileName,
                'processed' => $processed,
                'failed' => $failed,
                'status' => $status,
            ]],
            $status
        );
    }

    private function detectType(string $xmlContent): string
    {
        if (method_exists($this->xmlToolkit, 'detectType')) {
            return (string) $this->xmlToolkit->detectType($xmlContent);
        }

        if (preg_match('/<\s*([A-Za-z0-9_:-]+)/', ltrim($xmlContent), $matches) === 1) {
            return $matches[1];
        }

        throw new \RuntimeException('Unable to detect XML root type.');
    }
}
