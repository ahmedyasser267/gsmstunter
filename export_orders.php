#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * CLI: Export pending vendit_orders to OrderImport XML in storage/vendit/export/
 *
 * Usage:
 *   php export_orders.php
 *   php export_orders.php --seed-sample   Queue and export a demo order
 */
$root = dirname(__DIR__);
require_once $root . '/app/Services/Vendit/bootstrap.php';

use App\Services\Vendit\VenditOrderExporter;
use App\Services\Vendit\VenditXmlParser;

$options = getopt('', ['seed-sample', 'help']);

if (isset($options['help'])) {
    echo "Vendit order export\n";
    echo "  php export_orders.php                 Export all pending orders\n";
    echo "  php export_orders.php --seed-sample     Create demo order and export\n";
    exit(0);
}

try {
    $services = vendit_services();
    $config = $services['config'];
    $exporter = new VenditOrderExporter(
        $services['db'],
        new VenditXmlParser(),
        $services['logger'],
        $config
    );

    if (isset($options['seed-sample'])) {
        $orderNumber = 'WEB-DEMO-' . date('YmdHis');
        $exporter->queueOrder(
            [
                'order_number' => $orderNumber,
                'order_type' => 'Order',
                'order_date' => date('Y-m-d H:i:s'),
                'total_order_amount' => 149.9500,
                'payment_method' => 'iDEAL',
                'payment_costs' => 0,
                'paid' => 149.9500,
                'shipping_method' => 'PostNL',
                'shipping_costs' => 6.9500,
                'invoice_discount_amount' => 0,
                'invoice_first_name' => 'John',
                'invoice_last_name' => 'Demo',
                'invoice_email' => 'demo@example.local',
                'invoice_phone' => '0612345678',
                'invoice_address' => 'Demo straat',
                'invoice_housenumber' => '1',
                'invoice_zipcode' => '1234AB',
                'invoice_city' => 'Amsterdam',
                'invoice_country' => 'Nederland',
                'invoice_country_code' => 'NL',
            ],
            [
                [
                    'ecommerce_product_guid' => '7942BC54-CEAD-4548-B75C-8A8E9E48C09F',
                    'product_id' => '71-0',
                    'product_sales_price_ex' => 118.1400,
                    'product_sales_price_inc' => 143.0000,
                    'private_copy_levy' => 0,
                    'quantity' => 1,
                    'description' => 'Demo product',
                    'reserve_stock' => true,
                ],
            ]
        );
        echo "Queued demo order {$orderNumber}\n";
    }

    $result = $exporter->exportPendingOrders();

    echo sprintf(
        "Done. Exported: %d, Failed: %d\n",
        $result['exported'],
        $result['failed']
    );
    if ($result['files'] !== []) {
        echo "Files:\n";
        foreach ($result['files'] as $file) {
            echo "  - {$file}\n";
        }
    }
    exit($result['failed'] > 0 ? 1 : 0);
} catch (Throwable $e) {
    fwrite(STDERR, 'ERROR: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
