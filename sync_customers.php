#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * CLI: Import Vendit customer XML files from storage/vendit/import/
 *
 * Usage:
 *   php sync_customers.php
 *   php sync_customers.php --file=path/to/CustomerExport.xml
 *   php sync_customers.php --sample
 */
$root = dirname(__DIR__);
require_once $root . '/app/Services/Vendit/bootstrap.php';

use App\Services\Vendit\VenditCustomerImporter;
use App\Services\Vendit\VenditXmlParser;

$options = getopt('', ['file:', 'sample', 'help']);

if (isset($options['help'])) {
    echo "Vendit customer sync\n";
    echo "  php sync_customers.php              Import all XML from import folder\n";
    echo "  php sync_customers.php --file=...   Import single file\n";
    echo "  php sync_customers.php --sample     Copy sample to import folder and import\n";
    exit(0);
}

try {
    $services = vendit_services();
    $config = $services['config'];
    $importer = new VenditCustomerImporter(
        $services['db'],
        new VenditXmlParser(),
        $services['logger'],
        $config
    );

    if (isset($options['sample'])) {
        $sample = $config['paths']['samples_folder'] . '/CustomerExport.sample.xml';
        $target = $config['paths']['import_folder'] . '/CustomerExport.sample.xml';
        if (!copy($sample, $target)) {
            throw new RuntimeException('Failed to copy sample file.');
        }
        echo "Copied sample to import folder.\n";
        $result = $importer->importFile($target);
    } elseif (isset($options['file'])) {
        $result = $importer->importFile((string) $options['file']);
    } else {
        $result = $importer->importFromFolder();
    }

    echo sprintf(
        "Done. Processed: %d, Failed: %d\n",
        $result['processed'],
        $result['failed']
    );
    exit($result['failed'] > 0 ? 1 : 0);
} catch (Throwable $e) {
    fwrite(STDERR, 'ERROR: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
