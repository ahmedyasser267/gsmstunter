<?php
declare(strict_types=1);

namespace Integrations\Vendit\Infrastructure;

use DateTimeImmutable;
use Integrations\Vendit\Contract\OrderRepositoryInterface;

final class PdoOrderRepository implements OrderRepositoryInterface
{
    public function __construct(private readonly PdoConnection $db)
    {
    }

    public function findIdByOrderNumber(string $orderNumber): ?int
    {
        $row = $this->db->fetchOne(
            'SELECT id FROM vendit_orders WHERE order_number = :order_number LIMIT 1',
            ['order_number' => $orderNumber]
        );

        return is_array($row) ? (int) $row['id'] : null;
    }

    public function upsertOrder(array $order): int
    {
        $orderNumber = trim((string) ($order['order_number'] ?? ''));
        if ($orderNumber === '') {
            throw new \RuntimeException('order_number is required.');
        }

        $existingId = $this->findIdByOrderNumber($orderNumber);
        $fields = $this->normalizeOrderData($order);

        if ($existingId !== null) {
            $set = [];
            $params = ['id' => $existingId];
            foreach ($fields as $column => $value) {
                if ($column === 'order_number') {
                    continue;
                }
                $set[] = $column . ' = :' . $column;
                $params[$column] = $value;
            }
            $this->db->query(
                'UPDATE vendit_orders SET ' . implode(', ', $set) . ' WHERE id = :id',
                $params
            );

            return $existingId;
        }

        $columns = array_keys($fields);
        $placeholders = array_map(static fn(string $column): string => ':' . $column, $columns);
        $this->db->query(
            'INSERT INTO vendit_orders (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')',
            $fields
        );

        return (int) $this->db->lastInsertId();
    }

    public function replaceItems(int $orderId, array $items): void
    {
        $this->db->query('DELETE FROM vendit_order_items WHERE order_id = :order_id', ['order_id' => $orderId]);

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $this->db->query(
                'INSERT INTO vendit_order_items
                 (order_id, ecommerce_product_guid, product_id, ean, product_sales_price_ex, product_sales_price_inc, private_copy_levy, quantity, remarks, office_id, reserve_stock, description, is_dropshipment)
                 VALUES
                 (:order_id, :ecommerce_product_guid, :product_id, :ean, :product_sales_price_ex, :product_sales_price_inc, :private_copy_levy, :quantity, :remarks, :office_id, :reserve_stock, :description, :is_dropshipment)',
                [
                    'order_id' => $orderId,
                    'ecommerce_product_guid' => $item['ecommerce_product_guid'] ?? null,
                    'product_id' => $item['product_id'] ?? null,
                    'ean' => $item['ean'] ?? null,
                    'product_sales_price_ex' => $this->normalizeDecimal($item['product_sales_price_ex'] ?? 0),
                    'product_sales_price_inc' => $this->normalizeDecimal($item['product_sales_price_inc'] ?? 0),
                    'private_copy_levy' => $this->normalizeDecimal($item['private_copy_levy'] ?? 0),
                    'quantity' => $this->normalizeDecimal($item['quantity'] ?? 1),
                    'remarks' => $item['remarks'] ?? null,
                    'office_id' => $item['office_id'] ?? null,
                    'reserve_stock' => $this->toNullableBoolInt($item['reserve_stock'] ?? null),
                    'description' => $item['description'] ?? null,
                    'is_dropshipment' => $this->toNullableBoolInt($item['is_dropshipment'] ?? null),
                ]
            );
        }
    }

    public function listPendingExport(int $limit = 50): array
    {
        $limit = max(1, $limit);
        $orders = $this->db->fetchAll(
            'SELECT * FROM vendit_orders WHERE export_status = :status ORDER BY id ASC LIMIT ' . $limit,
            ['status' => 'pending']
        );

        foreach ($orders as &$order) {
            $order['items'] = $this->db->fetchAll(
                'SELECT * FROM vendit_order_items WHERE order_id = :order_id ORDER BY id ASC',
                ['order_id' => (int) $order['id']]
            );
        }
        unset($order);

        return $orders;
    }

    public function markExported(int $orderId, string $fileName): void
    {
        $this->db->query(
            'UPDATE vendit_orders
             SET export_status = :status, export_file_path = :file_path, exported_at = :exported_at
             WHERE id = :id',
            [
                'status' => 'exported',
                'file_path' => $fileName,
                'exported_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
                'id' => $orderId,
            ]
        );
    }

    public function markExportFailed(int $orderId, string $message): void
    {
        $this->db->query(
            'UPDATE vendit_orders
             SET export_status = :status,
                 additional_order_info = CONCAT(COALESCE(additional_order_info, ""), :message)
             WHERE id = :id',
            [
                'status' => 'failed',
                'message' => "\nExport failed: " . $message,
                'id' => $orderId,
            ]
        );
    }

    /**
     * @param array<string, mixed> $order
     * @return array<string, mixed>
     */
    private function normalizeOrderData(array $order): array
    {
        return [
            'order_number' => $order['order_number'] ?? null,
            'store_number' => $order['store_number'] ?? null,
            'order_type' => $order['order_type'] ?? 'Order',
            'order_date' => $this->normalizeDateTime($order['order_date'] ?? null) ?? (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            'delivery_date' => $this->normalizeDateTime($order['delivery_date'] ?? null),
            'total_order_amount' => $this->normalizeDecimal($order['total_order_amount'] ?? 0),
            'payment_method' => $order['payment_method'] ?? null,
            'payment_costs' => $this->normalizeDecimal($order['payment_costs'] ?? 0),
            'paid' => $this->normalizeDecimal($order['paid'] ?? 0),
            'shipping_method' => $order['shipping_method'] ?? null,
            'shipping_costs' => $this->normalizeDecimal($order['shipping_costs'] ?? 0),
            'invoice_discount_name' => $order['invoice_discount_name'] ?? null,
            'invoice_discount_amount' => $this->normalizeDecimal($order['invoice_discount_amount'] ?? 0),
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
            'opt_in' => $this->toNullableBoolInt($order['opt_in'] ?? null),
            'opt_in_date' => $this->normalizeDateTime($order['opt_in_date'] ?? null),
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
            'gift' => $this->toNullableBoolInt($order['gift'] ?? null),
            'gift_message' => $order['gift_message'] ?? null,
            'invoice_ex_vat' => $this->toBoolInt($order['invoice_ex_vat'] ?? false),
            'default_address' => $this->toNullableBoolInt($order['default_address'] ?? null),
            'customer_number' => $order['customer_number'] ?? null,
            'office_id' => $order['office_id'] ?? null,
            'employee_id' => $order['employee_id'] ?? null,
            'order_priority_id' => $order['order_priority_id'] ?? null,
            'customer_group' => $order['customer_group'] ?? null,
            'order_origin' => $order['order_origin'] ?? null,
            'export_status' => $order['export_status'] ?? 'pending',
        ];
    }

    private function normalizeDateTime(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        try {
            return (new DateTimeImmutable((string) $value))->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    private function normalizeDecimal(mixed $value): string
    {
        return number_format((float) $value, 4, '.', '');
    }

    private function toBoolInt(mixed $value): int
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
    }

    private function toNullableBoolInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $this->toBoolInt($value);
    }
}
