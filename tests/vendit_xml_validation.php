<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Services/Vendit/VenditXmlParser.php';

use App\Services\Vendit\VenditXmlParser;

$parser = new VenditXmlParser();
$dir = dirname(__DIR__) . '/storage/vendit/samples';
foreach (glob($dir . '/*.xml') as $file) {
    $result = $parser->validateStructure((string) file_get_contents($file));
    echo basename($file) . ': ' . ($result['valid'] ? 'OK' : 'FAIL') . ' (' . ($result['type'] ?? '-') . ')' . PHP_EOL;
    if (!$result['valid']) {
        echo '  ' . implode('; ', $result['errors']) . PHP_EOL;
    }
}

$orderXml = $parser->buildOrderImportXml([[
    'order_number' => 'TEST-1',
    'order_date' => '2024-01-01 12:00:00',
    'total_order_amount' => 10.5,
    'invoice_first_name' => 'A',
    'invoice_last_name' => 'B',
    'items' => [[
        'product_id' => '1-0',
        'product_sales_price_ex' => 8.0000,
        'product_sales_price_inc' => 9.6800,
        'quantity' => 1,
    ]],
]]);
$revalidate = $parser->validateStructure($orderXml);
echo 'Built OrderImport: ' . ($revalidate['valid'] ? 'OK' : 'FAIL') . PHP_EOL;
