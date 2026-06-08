<?php
declare(strict_types=1);

namespace Integrations\Vendit\Xml;

use DateTimeImmutable;
use Integrations\Vendit\Contract\XmlValidatorInterface;

final class XmlValidator implements XmlValidatorInterface
{
    public function validateCustomerImport(array $customer): array
    {
        $errors = [];
        $this->requireField($customer, 'customer_number', $errors);
        $this->validateIsoDate($customer, 'creation_datetime', $errors, true);
        $this->validateIsoDate($customer, 'birthdate', $errors, true);
        $this->validateIsoDate($customer, 'opt_in_date', $errors, true);
        $this->validateDecimal($customer, 'fixed_raise_percentage', $errors);
        $this->validateDecimal($customer, 'fixed_discount_percentage', $errors);

        return $errors;
    }

    public function validateOrderImport(array $order): array
    {
        $errors = [];
        $this->requireField($order, 'order_number', $errors);
        $this->requireField($order, 'order_date', $errors);
        $this->validateIsoDate($order, 'order_date', $errors, false);
        $this->validateIsoDate($order, 'delivery_date', $errors, true);
        $this->validateIsoDate($order, 'opt_in_date', $errors, true);

        foreach ([
            'total_order_amount',
            'payment_costs',
            'paid',
            'shipping_costs',
            'invoice_discount_amount',
        ] as $decimalField) {
            $this->validateDecimal($order, $decimalField, $errors);
        }

        $items = $order['items'] ?? [];
        if (!is_array($items) || $items === []) {
            $errors[] = 'items is required and must contain at least one item.';
            return $errors;
        }

        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                $errors[] = sprintf('items[%d] must be an object.', $index);
                continue;
            }
            $this->validateDecimal($item, 'quantity', $errors, sprintf('items[%d].', $index), false);
            $this->validateDecimal($item, 'product_sales_price_ex', $errors, sprintf('items[%d].', $index), true);
            $this->validateDecimal($item, 'product_sales_price_inc', $errors, sprintf('items[%d].', $index), true);
            $this->validateDecimal($item, 'private_copy_levy', $errors, sprintf('items[%d].', $index), true);
        }

        return $errors;
    }

    public function validateCustomerExport(array $customer): array
    {
        return $this->validateCustomerImport($customer);
    }

    public function validateOrderExport(array $order): array
    {
        return $this->validateOrderImport($order);
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string> $errors
     */
    private function requireField(array $data, string $field, array &$errors): void
    {
        if (!array_key_exists($field, $data) || trim((string) $data[$field]) === '') {
            $errors[] = sprintf('%s is required.', $field);
        }
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string> $errors
     */
    private function validateIsoDate(array $data, string $field, array &$errors, bool $nullable): void
    {
        if (!array_key_exists($field, $data) || $data[$field] === null || trim((string) $data[$field]) === '') {
            if (!$nullable) {
                $errors[] = sprintf('%s must be a valid ISO date.', $field);
            }
            return;
        }

        $value = trim((string) $data[$field]);
        if (!$this->isIsoDate($value)) {
            $errors[] = sprintf('%s must be an ISO date/time string.', $field);
        }
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string> $errors
     */
    private function validateDecimal(
        array $data,
        string $field,
        array &$errors,
        string $prefix = '',
        bool $nullable = true
    ): void {
        if (!array_key_exists($field, $data) || $data[$field] === null || trim((string) $data[$field]) === '') {
            if (!$nullable) {
                $errors[] = sprintf('%s%s must be a decimal with up to 4 places.', $prefix, $field);
            }
            return;
        }

        $value = trim((string) $data[$field]);
        if (!preg_match('/^-?\d+(?:\.\d{1,4})?$/', $value)) {
            $errors[] = sprintf('%s%s must be numeric with max 4 decimals.', $prefix, $field);
        }
    }

    private function isIsoDate(string $value): bool
    {
        $formats = [
            DATE_ATOM,
            'Y-m-d\TH:i:s',
            'Y-m-d\TH:i:s.uP',
            'Y-m-d H:i:s',
            'Y-m-d',
        ];
        foreach ($formats as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $value);
            if ($date instanceof DateTimeImmutable) {
                return true;
            }
        }

        try {
            new DateTimeImmutable($value);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
