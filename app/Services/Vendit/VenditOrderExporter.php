<?php
declare(strict_types=1);

namespace App\Services\Vendit;

use DateTimeImmutable;
use RuntimeException;
use Throwable;

/**
 * Exports pending vendit_orders to Vendit OrderImport XML files.
 */
final class VenditOrderExporter
{
    public function __construct(
        private readonly Database $db,
        private readonly VenditXmlParser $parser,
        private readonly VenditLogger $logger,
        private readonly array $config
    ) {
    }

    /**
     * Export all pending orders to individual XML files.
     *
     * @return array{exported: int, failed: int, files: list<string>}
     */
    public function exportPendingOrders(): array
    {
        $orders = $this->db->fetchAll(
            'SELECT * FROM vendit_orders WHERE export_status = :status ORDER BY id ASC',
            ['status' => 'pending']
        );

        $exported = 0;
        $failed = 0;
        $files = [];

        if ($orders === []) {
            $this->logger->start('order_export', 'export');
            $this->logger->complete('success', 0, 0, 'No pending orders.');
            return ['exported' => 0, 'failed' => 0, 'files' => []];
        }

        $this->logger->start('order_export', 'export', null, $this->config['paths']['export_folder']);

        foreach ($orders as $orderRow) {
            try {
                $filePath = $this->exportOrder((int) $orderRow['id']);
                $files[] = basename($filePath);
                $exported++;
            } catch (Throwable $e) {
                $failed++;
                $this->markOrderFailed((int) $orderRow['id'], $e->getMessage());
                $this->logger->error('Order export failed for ' . ($orderRow['order_number'] ?? ''), $e);
            }
        }

        $status = $failed > 0 ? ($exported > 0 ? 'partial' : 'failed') : 'success';
        $this->logger->complete($status, $exported, $failed, 'Order export batch completed.');

        return ['exported' => $exported, 'failed' => $failed, 'files' => $files];
    }

    public function exportOrder(int $orderId): string
    {
        $orderRow = $this->db->fetchOne('SELECT * FROM vendit_orders WHERE id = :id', ['id' => $orderId]);
        if ($orderRow === false) {
            throw new RuntimeException('Order not found: ' . $orderId);
        }

        if (($orderRow['export_status'] ?? '') === 'exported') {
            throw new RuntimeException('Order already exported: ' . ($orderRow['order_number'] ?? $orderId));
        }

        $items = $this->db->fetchAll(
            'SELECT * FROM vendit_order_items WHERE order_id = :order_id ORDER BY id ASC',
            ['order_id' => $orderId]
        );

        $payload = $this->mapOrderToXmlPayload($orderRow, $items);
        $xml = $this->parser->buildOrderImportXml([$payload]);

        $exportDir = rtrim($this->config['paths']['export_folder'], '/\\');
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        $fileName = sprintf('order-%s.xml', preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $orderRow['order_number']));
        $filePath = $exportDir . '/' . $fileName;

        if (file_put_contents($filePath, $xml) === false) {
            throw new RuntimeException('Failed to write export file: ' . $filePath);
        }

        $this->db->beginTransaction();
        try {
            $this->db->query(
                'UPDATE vendit_orders
                 SET export_status = :export_status,
                     export_file_path = :export_file_path,
                     exported_at = :exported_at
                 WHERE id = :id',
                [
                    'export_status' => 'exported',
                    'export_file_path' => $filePath,
                    'exported_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
                    'id' => $orderId,
                ]
            );
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            @unlink($filePath);
            throw $e;
        }

        $this->logger->info('Exported order ' . $orderRow['order_number'] . ' to ' . $fileName);

        return $filePath;
    }

    /**
     * Queue a new order for Vendit export.
     *
     * @param array<string, mixed> $order
     * @param list<array<string, mixed>> $items
     */
    public function queueOrder(array $order, array $items): int
    {
        if (empty($order['order_number'])) {
            throw new RuntimeException('order_number is required.');
        }

        $this->db->beginTransaction();
        try {
            $existing = $this->db->fetchOne(
                'SELECT id, export_status FROM vendit_orders WHERE order_number = :order_number',
                ['order_number' => $order['order_number']]
            );

            if ($existing !== false && ($existing['export_status'] ?? '') === 'exported') {
                throw new RuntimeException('Order already exported and cannot be re-exported.');
            }

            if ($existing !== false) {
                $orderId = (int) $existing['id'];
                $this->updateOrderRow($orderId, $order);
                $this->db->query('DELETE FROM vendit_order_items WHERE order_id = :id', ['id' => $orderId]);
            } else {
                $orderId = $this->insertOrderRow($order);
            }

            foreach ($items as $item) {
                $this->insertOrderItem($orderId, $item);
            }

            $this->db->commit();
            return $orderId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function totalExportedOrders(): int
    {
        $row = $this->db->fetchOne(
            'SELECT COUNT(*) AS cnt FROM vendit_orders WHERE export_status = :status',
            ['status' => 'exported']
        );
        return is_array($row) ? (int) $row['cnt'] : 0;
    }

    /**
     * @param array<string, mixed> $orderRow
     * @param list<array<string, mixed>> $items
     * @return array<string, mixed>
     */
    private function mapOrderToXmlPayload(array $orderRow, array $items): array
    {
        $mappedItems = [];
        foreach ($items as $item) {
            $mappedItems[] = [
                'ecommerce_product_guid' => $item['ecommerce_product_guid'] ?? null,
                'product_id' => $item['product_id'] ?? null,
                'ean' => $item['ean'] ?? null,
                'product_sales_price_ex' => $item['product_sales_price_ex'] ?? 0,
                'product_sales_price_inc' => $item['product_sales_price_inc'] ?? 0,
                'private_copy_levy' => $item['private_copy_levy'] ?? 0,
                'quantity' => $item['quantity'] ?? 1,
                'remarks' => $item['remarks'] ?? null,
                'office_id' => $item['office_id'] ?? $this->config['store']['default_office_id'] ?? null,
                'reserve_stock' => !empty($item['reserve_stock']),
                'description' => $item['description'] ?? null,
                'is_dropshipment' => !empty($item['is_dropshipment']),
            ];
        }

        return [
            'order_number' => $orderRow['order_number'],
            'store_number' => $orderRow['store_number'] ?? $this->config['store']['store_number'] ?? '',
            'order_type' => $orderRow['order_type'] ?? 'Order',
            'order_date' => $this->formatOrderDate($orderRow['order_date'] ?? null),
            'delivery_date' => $orderRow['delivery_date'] ?? null,
            'total_order_amount' => $orderRow['total_order_amount'] ?? 0,
            'payment_method' => $orderRow['payment_method'] ?? null,
            'payment_costs' => $orderRow['payment_costs'] ?? 0,
            'paid' => $orderRow['paid'] ?? 0,
            'shipping_method' => $orderRow['shipping_method'] ?? null,
            'shipping_costs' => $orderRow['shipping_costs'] ?? 0,
            'invoice_discount_name' => $orderRow['invoice_discount_name'] ?? null,
            'invoice_discount_amount' => $orderRow['invoice_discount_amount'] ?? 0,
            'invoice_title' => $orderRow['invoice_title'] ?? null,
            'invoice_first_name' => $orderRow['invoice_first_name'] ?? null,
            'invoice_middle_name' => $orderRow['invoice_middle_name'] ?? null,
            'invoice_last_name' => $orderRow['invoice_last_name'] ?? null,
            'invoice_email' => $orderRow['invoice_email'] ?? null,
            'invoice_phone' => $orderRow['invoice_phone'] ?? null,
            'invoice_phone_mobile' => $orderRow['invoice_phone_mobile'] ?? null,
            'invoice_address' => $orderRow['invoice_address'] ?? null,
            'invoice_housenumber' => $orderRow['invoice_housenumber'] ?? null,
            'invoice_housenumber_extension' => $orderRow['invoice_housenumber_extension'] ?? null,
            'invoice_zipcode' => $orderRow['invoice_zipcode'] ?? null,
            'invoice_city' => $orderRow['invoice_city'] ?? null,
            'invoice_country' => $orderRow['invoice_country'] ?? null,
            'invoice_country_code' => $orderRow['invoice_country_code'] ?? null,
            'company_name' => $orderRow['company_name'] ?? null,
            'iban_number' => $orderRow['iban_number'] ?? null,
            'bank_account' => $orderRow['bank_account'] ?? null,
            'vat_number' => $orderRow['vat_number'] ?? null,
            'opt_in' => $orderRow['opt_in'] ?? null,
            'opt_in_date' => $orderRow['opt_in_date'] ?? null,
            'delivery_first_name' => $orderRow['delivery_first_name'] ?? null,
            'delivery_last_name' => $orderRow['delivery_last_name'] ?? null,
            'delivery_address' => $orderRow['delivery_address'] ?? null,
            'delivery_zipcode' => $orderRow['delivery_zipcode'] ?? null,
            'delivery_city' => $orderRow['delivery_city'] ?? null,
            'delivery_country' => $orderRow['delivery_country'] ?? null,
            'delivery_country_code' => $orderRow['delivery_country_code'] ?? null,
            'delivery_company_name' => $orderRow['delivery_company_name'] ?? null,
            'order_remark' => $orderRow['order_remark'] ?? null,
            'order_message' => $orderRow['order_message'] ?? null,
            'customer_number' => $orderRow['customer_number'] ?? null,
            'order_origin' => $orderRow['order_origin'] ?? $this->config['store']['order_origin'] ?? 'Website',
            'items' => $mappedItems,
        ];
    }

    /**
     * @param array<string, mixed> $order
     */
    private function insertOrderRow(array $order): int
    {
        $columns = $this->orderColumns();
        $data = $this->normalizeOrderData($order);
        $data['export_status'] = 'pending';

        $filtered = array_intersect_key($data, array_flip($columns));
        $cols = array_keys($filtered);
        $this->db->query(
            'INSERT INTO vendit_orders (' . implode(', ', $cols) . ')
             VALUES (' . implode(', ', array_map(static fn($c) => ':' . $c, $cols)) . ')',
            $filtered
        );
        return (int) $this->db->lastInsertId();
    }

    /**
     * @param array<string, mixed> $order
     */
    private function updateOrderRow(int $orderId, array $order): void
    {
        $data = $this->normalizeOrderData($order);
        unset($data['export_status']);
        $set = [];
        $params = ['id' => $orderId];
        foreach ($data as $column => $value) {
            $set[] = $column . ' = :' . $column;
            $params[$column] = $value;
        }
        $this->db->query(
            'UPDATE vendit_orders SET ' . implode(', ', $set) . ' WHERE id = :id',
            $params
        );
    }

    /**
     * @param array<string, mixed> $item
     */
    private function insertOrderItem(int $orderId, array $item): void
    {
        $this->db->query(
            'INSERT INTO vendit_order_items
             (order_id, ecommerce_product_guid, product_id, ean,
              product_sales_price_ex, product_sales_price_inc, private_copy_levy,
              quantity, remarks, office_id, reserve_stock, description, is_dropshipment)
             VALUES
             (:order_id, :ecommerce_product_guid, :product_id, :ean,
              :product_sales_price_ex, :product_sales_price_inc, :private_copy_levy,
              :quantity, :remarks, :office_id, :reserve_stock, :description, :is_dropshipment)',
            [
                'order_id' => $orderId,
                'ecommerce_product_guid' => $item['ecommerce_product_guid'] ?? null,
                'product_id' => $item['product_id'] ?? null,
                'ean' => $item['ean'] ?? null,
                'product_sales_price_ex' => $item['product_sales_price_ex'] ?? 0,
                'product_sales_price_inc' => $item['product_sales_price_inc'] ?? 0,
                'private_copy_levy' => $item['private_copy_levy'] ?? 0,
                'quantity' => $item['quantity'] ?? 1,
                'remarks' => $item['remarks'] ?? null,
                'office_id' => $item['office_id'] ?? null,
                'reserve_stock' => !empty($item['reserve_stock']) ? 1 : 0,
                'description' => $item['description'] ?? null,
                'is_dropshipment' => !empty($item['is_dropshipment']) ? 1 : 0,
            ]
        );
    }

    private function markOrderFailed(int $orderId, string $message): void
    {
        $this->db->query(
            'UPDATE vendit_orders SET export_status = :export_status WHERE id = :id',
            ['export_status' => 'failed', 'id' => $orderId]
        );
        $this->logger->error('Order id ' . $orderId . ' marked failed: ' . $message);
    }

    /**
     * @return list<string>
     */
    private function orderColumns(): array
    {
        return [
            'order_number', 'store_number', 'order_type', 'order_date', 'delivery_date',
            'total_order_amount', 'payment_method', 'payment_costs', 'paid',
            'shipping_method', 'shipping_costs', 'invoice_discount_name', 'invoice_discount_amount',
            'invoice_title', 'invoice_first_name', 'invoice_middle_name', 'invoice_last_name',
            'invoice_email', 'invoice_phone', 'invoice_phone_mobile',
            'invoice_address', 'invoice_housenumber', 'invoice_housenumber_extension',
            'invoice_zipcode', 'invoice_city', 'invoice_country', 'invoice_country_code',
            'company_name', 'iban_number', 'bank_account', 'vat_number',
            'opt_in', 'opt_in_date', 'order_status_id',
            'delivery_title', 'delivery_first_name', 'delivery_middle_name', 'delivery_last_name',
            'delivery_address', 'delivery_housenumber', 'delivery_housenumber_extension',
            'delivery_zipcode', 'delivery_city', 'delivery_country', 'delivery_country_code',
            'delivery_company_name', 'order_remark', 'order_message', 'additional_order_info',
            'gift', 'gift_message', 'invoice_ex_vat', 'default_address',
            'customer_number', 'office_id', 'employee_id', 'order_priority_id',
            'customer_group', 'order_origin', 'export_status',
        ];
    }

    /**
     * @param array<string, mixed> $order
     * @return array<string, mixed>
     */
    private function normalizeOrderData(array $order): array
    {
        return [
            'order_number' => $order['order_number'] ?? null,
            'store_number' => $order['store_number'] ?? $this->config['store']['store_number'] ?? null,
            'order_type' => $order['order_type'] ?? 'Order',
            'order_date' => $this->formatOrderDate($order['order_date'] ?? null),
            'delivery_date' => $order['delivery_date'] ?? null,
            'total_order_amount' => $order['total_order_amount'] ?? 0,
            'payment_method' => $order['payment_method'] ?? null,
            'payment_costs' => $order['payment_costs'] ?? 0,
            'paid' => $order['paid'] ?? 0,
            'shipping_method' => $order['shipping_method'] ?? null,
            'shipping_costs' => $order['shipping_costs'] ?? 0,
            'invoice_discount_name' => $order['invoice_discount_name'] ?? null,
            'invoice_discount_amount' => $order['invoice_discount_amount'] ?? 0,
            'invoice_title' => $order['invoice_title'] ?? null,
            'invoice_first_name' => $order['invoice_first_name'] ?? null,
            'invoice_middle_name' => $order['invoice_middle_name'] ?? null,
            'invoice_last_name' => $order['invoice_last_name'] ?? null,
            'invoice_email' => $order['invoice_email'] ?? null,
            'invoice_phone' => $order['invoice_phone'] ?? null,
            'invoice_phone_mobile' => $order['invoice_phone_mobile'] ?? null,
            'invoice_address' => $order['invoice_address'] ?? null,
            'invoice_housenumber' => $order['invoice_housenumber'] ?? null,
            'invoice_housenumber_extension' => $order['invoice_housenumber_extension'] ?? null,
            'invoice_zipcode' => $order['invoice_zipcode'] ?? null,
            'invoice_city' => $order['invoice_city'] ?? null,
            'invoice_country' => $order['invoice_country'] ?? null,
            'invoice_country_code' => $order['invoice_country_code'] ?? null,
            'company_name' => $order['company_name'] ?? null,
            'iban_number' => $order['iban_number'] ?? null,
            'bank_account' => $order['bank_account'] ?? null,
            'vat_number' => $order['vat_number'] ?? null,
            'opt_in' => isset($order['opt_in']) ? (int) (bool) $order['opt_in'] : null,
            'opt_in_date' => $order['opt_in_date'] ?? null,
            'order_status_id' => $order['order_status_id'] ?? null,
            'delivery_title' => $order['delivery_title'] ?? null,
            'delivery_first_name' => $order['delivery_first_name'] ?? null,
            'delivery_middle_name' => $order['delivery_middle_name'] ?? null,
            'delivery_last_name' => $order['delivery_last_name'] ?? null,
            'delivery_address' => $order['delivery_address'] ?? null,
            'delivery_housenumber' => $order['delivery_housenumber'] ?? null,
            'delivery_housenumber_extension' => $order['delivery_housenumber_extension'] ?? null,
            'delivery_zipcode' => $order['delivery_zipcode'] ?? null,
            'delivery_city' => $order['delivery_city'] ?? null,
            'delivery_country' => $order['delivery_country'] ?? null,
            'delivery_country_code' => $order['delivery_country_code'] ?? null,
            'delivery_company_name' => $order['delivery_company_name'] ?? null,
            'order_remark' => $order['order_remark'] ?? null,
            'order_message' => $order['order_message'] ?? null,
            'additional_order_info' => $order['additional_order_info'] ?? null,
            'gift' => isset($order['gift']) ? (int) (bool) $order['gift'] : null,
            'gift_message' => $order['gift_message'] ?? null,
            'invoice_ex_vat' => isset($order['invoice_ex_vat']) ? (int) (bool) $order['invoice_ex_vat'] : 0,
            'default_address' => isset($order['default_address']) ? (int) (bool) $order['default_address'] : null,
            'customer_number' => $order['customer_number'] ?? null,
            'office_id' => $order['office_id'] ?? null,
            'employee_id' => $order['employee_id'] ?? null,
            'order_priority_id' => $order['order_priority_id'] ?? null,
            'customer_group' => $order['customer_group'] ?? null,
            'order_origin' => $order['order_origin'] ?? $this->config['store']['order_origin'] ?? 'Website',
        ];
    }

    private function formatOrderDate(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return (new DateTimeImmutable())->format('Y-m-d H:i:s');
        }
        try {
            return (new DateTimeImmutable($value))->format('Y-m-d H:i:s');
        } catch (Throwable) {
            return $value;
        }
    }
}
