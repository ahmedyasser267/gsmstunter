<?php
declare(strict_types=1);

namespace Integrations\Vendit;

use Integrations\Vendit\Contract\CustomerRepositoryInterface;
use Integrations\Vendit\Contract\FileStorageInterface;
use Integrations\Vendit\Contract\OrderRepositoryInterface;
use Integrations\Vendit\Contract\SyncLoggerInterface;
use Integrations\Vendit\Contract\XmlValidatorInterface;
use Integrations\Vendit\Infrastructure\LocalFileStorage;
use Integrations\Vendit\Infrastructure\PdoConnection;
use Integrations\Vendit\Infrastructure\PdoCustomerRepository;
use Integrations\Vendit\Infrastructure\PdoOrderRepository;
use Integrations\Vendit\Infrastructure\SyncLogger;
use Integrations\Vendit\Service\CustomerExporter;
use Integrations\Vendit\Service\CustomerImporter;
use Integrations\Vendit\Service\DashboardService;
use Integrations\Vendit\Service\OrderExporter;
use Integrations\Vendit\Service\OrderImporter;
use Integrations\Vendit\Xml\XmlToolkit;
use Integrations\Vendit\Xml\XmlValidator;

final class Container
{
    private function __construct(
        private readonly PdoConnection $db,
        private readonly FileStorageInterface $fileStorage,
        private readonly XmlToolkit $xmlToolkit,
        private readonly XmlValidatorInterface $xmlValidator,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly SyncLoggerInterface $syncLogger,
        private readonly CustomerImporter $customerImporter,
        private readonly OrderImporter $orderImporter,
        private readonly CustomerExporter $customerExporter,
        private readonly OrderExporter $orderExporter,
        private readonly DashboardService $dashboardService,
    ) {
    }

    /**
     * @param array<string, mixed> $config
     */
    public static function fromConfig(array $config): self
    {
        $db = PdoConnection::fromConfig((array) ($config['database'] ?? []));
        $fileStorage = new LocalFileStorage(
            (string) ($config['import_path'] ?? ''),
            (string) ($config['export_path'] ?? ''),
            (string) ($config['archive_path'] ?? '')
        );
        $xmlToolkit = new XmlToolkit();
        $xmlValidator = new XmlValidator();
        $customerRepository = new PdoCustomerRepository($db);
        $orderRepository = new PdoOrderRepository($db);
        $syncLogger = new SyncLogger($db, (string) ($config['logs_path'] ?? ''));
        $archiveProcessed = (bool) ($config['sync']['archive_processed_imports'] ?? true);

        $customerImporter = new CustomerImporter(
            $fileStorage,
            $xmlToolkit,
            $xmlValidator,
            $customerRepository,
            $syncLogger,
            $archiveProcessed
        );
        $orderImporter = new OrderImporter(
            $fileStorage,
            $xmlToolkit,
            $xmlValidator,
            $orderRepository,
            $syncLogger,
            $archiveProcessed
        );
        $customerExporter = new CustomerExporter(
            $fileStorage,
            $xmlToolkit,
            $customerRepository,
            $syncLogger
        );
        $orderExporter = new OrderExporter(
            $fileStorage,
            $xmlToolkit,
            $orderRepository,
            $syncLogger
        );
        $dashboardService = new DashboardService($db, $fileStorage);

        return new self(
            $db,
            $fileStorage,
            $xmlToolkit,
            $xmlValidator,
            $customerRepository,
            $orderRepository,
            $syncLogger,
            $customerImporter,
            $orderImporter,
            $customerExporter,
            $orderExporter,
            $dashboardService
        );
    }

    public function db(): PdoConnection
    {
        return $this->db;
    }

    public function fileStorage(): FileStorageInterface
    {
        return $this->fileStorage;
    }

    public function xmlToolkit(): XmlToolkit
    {
        return $this->xmlToolkit;
    }

    public function xmlValidator(): XmlValidatorInterface
    {
        return $this->xmlValidator;
    }

    public function customerRepository(): CustomerRepositoryInterface
    {
        return $this->customerRepository;
    }

    public function orderRepository(): OrderRepositoryInterface
    {
        return $this->orderRepository;
    }

    public function syncLogger(): SyncLoggerInterface
    {
        return $this->syncLogger;
    }

    public function customerImporter(): CustomerImporter
    {
        return $this->customerImporter;
    }

    public function orderImporter(): OrderImporter
    {
        return $this->orderImporter;
    }

    public function customerExporter(): CustomerExporter
    {
        return $this->customerExporter;
    }

    public function orderExporter(): OrderExporter
    {
        return $this->orderExporter;
    }

    public function dashboardService(): DashboardService
    {
        return $this->dashboardService;
    }
}
