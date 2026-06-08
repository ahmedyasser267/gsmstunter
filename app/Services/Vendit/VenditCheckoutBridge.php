<?php
declare(strict_types=1);

namespace App\Services\Vendit;

use Throwable;

/**
 * Maps a successful website checkout into vendit_orders + optional XML export.
 */
final class VenditCheckoutBridge
{
    private const VAT_RATE = 0.21;

    /** @var array<string, array{0: string, 1: string}> */
    private const COUNTRY_MAP = [
        'nl' => ['Nederland', 'NL'],
        'be' => ['België', 'BE'],
        'de' => ['Duitsland', 'DE'],
        'fr' => ['Frankrijk', 'FR'],
        'lu' => ['Luxemburg', 'LU'],
    ];

    /**
     * Queue (and optionally export) a checkout order for Vendit/VMSII.
     *
     * @param array{
     *   order_id: int,
     *   order_reference: string,
     *   customer_id: int,
     *   email: string,
     *   full_name: string,
     *   phone: string,
     *   payment_method: string,
     *   shipping_method: string,
     *   shipping_address: string,
     *   subtotal: float,
     *   shipping_cost: float,
     *   tax_amount: float,
     *   total_amount: float,
     *   priced_lines: list<array<string, mixed>>,
     *   catalog: array<int, array<string, mixed>>
     * } $checkout
     */
    public static function afterCheckout(array $checkout): bool
    {
        try {
            require_once __DIR__ . '/bootstrap.php';

            $services = vendit_services();
            $config = $services['config'];
            $parser = new VenditXmlParser();
            $exporter = new VenditOrderExporter(
                $services['db'],
                $parser,
                $services['logger'],
                $config
            );

            $address = self::parseAddress($checkout['shipping_address'] ?? '');
            $names = self::resolveNames($checkout['full_name'] ?? '', $address);

            $countryCode = strtolower((string) ($address['country_code'] ?? 'nl'));
            [$countryName, $countryIso] = self::COUNTRY_MAP[$countryCode]
                ?? [($address['country'] ?? 'Nederland'), strtoupper($countryCode)];

            $paymentLabel = self::paymentLabel((string) ($checkout['payment_method'] ?? 'ideal'));
            $shippingLabel = self::shippingLabel((string) ($checkout['shipping_method'] ?? 'standard'));

            $shippingCost = (float) ($checkout['shipping_cost'] ?? 0);
            $totalAmount = (float) ($checkout['total_amount'] ?? 0);

            $orderPayload = [
                'order_number' => (string) $checkout['order_reference'],
                'order_type' => 'Order',
                'order_date' => date('Y-m-d H:i:s'),
                'total_order_amount' => self::decimal4($totalAmount),
                'payment_method' => $paymentLabel,
                'payment_costs' => self::decimal4(0),
                'paid' => self::decimal4($totalAmount),
                'shipping_method' => $shippingLabel,
                'shipping_costs' => self::decimal4($shippingCost),
                'invoice_discount_amount' => self::decimal4(0),
                'invoice_first_name' => $names['first'],
                'invoice_last_name' => $names['last'],
                'invoice_email' => (string) $checkout['email'],
                'invoice_phone' => (string) ($checkout['phone'] ?? ''),
                'invoice_address' => (string) ($address['address'] ?? ''),
                'invoice_zipcode' => (string) ($address['postcode'] ?? ''),
                'invoice_city' => (string) ($address['city'] ?? ''),
                'invoice_country' => $countryName,
                'invoice_country_code' => $countryIso,
                'delivery_first_name' => $names['first'],
                'delivery_last_name' => $names['last'],
                'delivery_address' => (string) ($address['address'] ?? ''),
                'delivery_zipcode' => (string) ($address['postcode'] ?? ''),
                'delivery_city' => (string) ($address['city'] ?? ''),
                'delivery_country' => $countryName,
                'delivery_country_code' => $countryIso,
                'customer_number' => 'WEB-' . (int) $checkout['customer_id'],
                'order_origin' => (string) ($config['store']['order_origin'] ?? 'Website'),
                'order_message' => 'Website order id ' . (int) $checkout['order_id'],
            ];

            $items = self::mapLineItems(
                $checkout['priced_lines'] ?? [],
                $checkout['catalog'] ?? []
            );

            $venditOrderId = $exporter->queueOrder($orderPayload, $items);

            if (!empty($config['sync']['auto_export_on_checkout'])) {
                $exporter->exportOrder($venditOrderId);
            }

            return true;
        } catch (Throwable $e) {
            try {
                if (class_exists(VenditLogger::class)) {
                    $services = vendit_services();
                    $services['logger']->error(
                        'Vendit checkout bridge failed for ' . ($checkout['order_reference'] ?? 'unknown'),
                        $e
                    );
                }
            } catch (Throwable) {
                error_log('Vendit checkout bridge: ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private static function parseAddress(string $raw): array
    {
        if ($raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $address
     * @return array{first: string, last: string}
     */
    private static function resolveNames(string $fullName, array $address): array
    {
        $first = trim((string) ($address['first_name'] ?? ''));
        $last = trim((string) ($address['last_name'] ?? ''));

        if ($first !== '' || $last !== '') {
            return ['first' => $first, 'last' => $last];
        }

        $fullName = trim($fullName);
        if ($fullName === '') {
            return ['first' => 'Guest', 'last' => 'Customer'];
        }

        $parts = preg_split('/\s+/u', $fullName, 2) ?: [];
        return [
            'first' => $parts[0] ?? $fullName,
            'last' => $parts[1] ?? $fullName,
        ];
    }

    /**
     * @param list<array<string, mixed>> $pricedLines
     * @param array<int, array<string, mixed>> $catalog
     * @return list<array<string, mixed>>
     */
    private static function mapLineItems(array $pricedLines, array $catalog): array
    {
        $items = [];
        foreach ($pricedLines as $line) {
            $pid = (int) ($line['product_id'] ?? 0);
            if ($pid <= 0 || !isset($catalog[$pid])) {
                continue;
            }
            $product = $catalog[$pid];
            $qty = (int) ($line['quantity'] ?? 1);
            $priceInc = (float) ($line['unit_price_authoritative'] ?? $product['unit_price'] ?? 0);
            $priceEx = round($priceInc / (1 + self::VAT_RATE), 4);

            $items[] = [
                'product_id' => (string) ($product['sku'] !== '' ? $product['sku'] : ('P-' . $pid)),
                'product_sales_price_ex' => self::decimal4($priceEx),
                'product_sales_price_inc' => self::decimal4($priceInc),
                'private_copy_levy' => self::decimal4(0),
                'quantity' => self::decimal4($qty),
                'description' => (string) ($product['display_name'] ?? ''),
                'reserve_stock' => true,
            ];
        }
        return $items;
    }

    private static function paymentLabel(string $method): string
    {
        return match (strtolower($method)) {
            'ideal' => 'iDEAL',
            'card' => 'Creditcard',
            'paypal' => 'PayPal',
            'klarna' => 'Klarna',
            default => ucfirst($method),
        };
    }

    private static function shippingLabel(string $method): string
    {
        return match (strtolower($method)) {
            'express' => 'PostNL Express',
            default => 'PostNL Standaard',
        };
    }

    private static function decimal4(float|int|string $value): float
    {
        return round((float) $value, 4);
    }
}
