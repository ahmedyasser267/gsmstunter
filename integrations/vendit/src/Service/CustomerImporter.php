<?php
declare(strict_types=1);

namespace Integrations\Vendit\Service;

use Integrations\Vendit\Contract\CustomerRepositoryInterface;
use Integrations\Vendit\Contract\FileStorageInterface;
use Integrations\Vendit\Contract\SyncLoggerInterface;
use Integrations\Vendit\Contract\XmlValidatorInterface;
use Integrations\Vendit\Dto\ImportResult;
use Integrations\Vendit\Xml\XmlToolkit;

final class CustomerImporter
{
    public function __construct(
        private readonly FileStorageInterface $fileStorage,
        private readonly XmlToolkit $xmlToolkit,
        private readonly XmlValidatorInterface $validator,
        private readonly CustomerRepositoryInterface $repository,
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
                $type = $this->detectType($content);
                if (!in_array($type, ['CustomerImport', 'CustomerExport'], true)) {
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
        if (!in_array($type, ['CustomerImport', 'CustomerExport'], true)) {
            throw new \RuntimeException('Unsupported XML type for customer importer: ' . $type);
        }

        $this->logger->start('customer_import', 'import', $fileName, $path);

        $parsed = $type === 'CustomerImport'
            ? $this->xmlToolkit->parseCustomerImport($content)
            : $this->xmlToolkit->parseCustomerExport($content);
        $customers = is_array($parsed['customers'] ?? null) ? $parsed['customers'] : [];

        $processed = 0;
        $failed = 0;
        foreach ($customers as $customer) {
            if (!is_array($customer)) {
                $failed++;
                $this->logger->error('Invalid customer payload in file ' . $fileName);
                continue;
            }

            $errors = $type === 'CustomerImport'
                ? $this->validator->validateCustomerImport($customer)
                : $this->validator->validateCustomerExport($customer);

            if ($errors !== []) {
                $failed++;
                $this->logger->error(
                    sprintf(
                        'Validation failed for customer %s: %s',
                        (string) ($customer['customer_number'] ?? 'unknown'),
                        implode('; ', $errors)
                    )
                );
                continue;
            }

            try {
                $customerId = $this->repository->upsertCustomer($customer);
                $this->repository->replaceGroups($customerId, is_array($customer['groups'] ?? null) ? $customer['groups'] : []);
                $this->repository->replaceAddresses($customerId, $this->addressesFromCustomer($customer));
                $processed++;
                $this->logger->info(
                    "Customer Imported\nCustomerNumber: " . (string) ($customer['customer_number'] ?? '')
                );
            } catch (\Throwable $e) {
                $failed++;
                $this->logger->error('Customer import failed: ' . $e->getMessage());
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

    /**
     * @param array<string, mixed> $customer
     * @return list<array<string, mixed>>
     */
    private function addressesFromCustomer(array $customer): array
    {
        $addresses = $customer['addresses'] ?? [];
        if (!is_array($addresses) || $addresses !== []) {
            return is_array($addresses) ? $addresses : [];
        }

        return [[
            'address_type_description' => 'Bezoekadres',
            'street' => $customer['street'] ?? null,
            'zip_code' => $customer['zip_code'] ?? null,
            'house_number' => $customer['house_number'] ?? null,
            'house_number_suffix' => $customer['house_number_suffix'] ?? null,
            'city' => $customer['city'] ?? null,
            'email_address' => $customer['email'] ?? null,
            'default_address' => true,
            'country' => $customer['country'] ?? null,
            'country_code' => $customer['country_code'] ?? null,
            'contacts' => [],
            'phones' => array_values(array_filter([
                !empty($customer['phone']) ? [
                    'phone_number' => $customer['phone'],
                    'phone_type_description' => 'Telefoon',
                    'default_phone' => true,
                ] : null,
                !empty($customer['mobile']) ? [
                    'phone_number' => $customer['mobile'],
                    'phone_type_description' => 'Mobiel',
                    'default_phone' => false,
                ] : null,
            ])),
        ]];
    }
}
