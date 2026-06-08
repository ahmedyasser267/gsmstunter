# Vendit E-commerce 2.0 Integration

This module connects the website to **Vendit VMSII** using the official E-commerce 2.0 XML specifications. It is fully isolated from the existing storefront and admin APIs.

## What the documentation describes

Vendit defines XML-based sync between **VMSII** (ERP/POS) and the **website**:

| Direction | XML root | File (typical) | Producer | Consumer |
|-----------|----------|----------------|----------|----------|
| ERP → Website | `CustomerExport` | Customers.xml | VMSII | Website |
| ERP → Website | `StockExport` | Stock.xml | VMSII | Website |
| ERP → Website | `GroupExport` | Groups.xml | VMSII | Website |
| Website → ERP | `CustomerImport` | customer-*.xml | Website | VMSII |
| Website → ERP | `OrderImport` | order-*.xml | Website | VMSII |

**Encoding:** UTF-16 LE (production). Sample files use UTF-8 for readability; the parser accepts both.

**Decimals:** Orders use 4 decimal places; stock uses 2.

## Folder structure

```
app/Services/Vendit/          PHP services (SOLID, PDO, transactions)
config/vendit.php             Module configuration (FTP placeholders)
storage/vendit/import/        Incoming XML (manual drop until FTP exists)
storage/vendit/export/        Generated OrderImport XML
storage/vendit/archive/         Processed import files
storage/vendit/logs/            Daily file logs + DB sync logs
storage/vendit/samples/         Reference XML from Vendit docs
admin/vendit/index.php        Admin dashboard
sync_customers.php            CLI customer import
export_orders.php             CLI order export
database/migration_v10_vendit_integration.sql
```

## Database tables (isolated)

Tables are prefixed with `vendit_` to avoid conflicting with existing `customers` / `orders`:

- `vendit_customers`
- `vendit_customer_groups`
- `vendit_customer_addresses`
- `vendit_customer_contacts`
- `vendit_customer_phones`
- `vendit_orders`
- `vendit_order_items`
- `vendit_sync_logs`

### Apply migration

```bash
mysql -u root gsmstunter < database/migration_v10_vendit_integration.sql
```

Or import via phpMyAdmin.

## Configuration

Edit `config/vendit.php`:

```php
'ftp' => [
    'host' => '',           // TODO
    'port' => 21,
    'username' => '',       // TODO
    'password' => '',       // TODO
    'import_folder' => '/import',
    'export_folder' => '/export',
],
'store' => [
    'store_number' => '',   // StoreNumber in OrderImport
],
```

Paths `import_folder` and `export_folder` in config refer to **local** storage paths. Remote FTP paths will be mapped in a future transport class.

## CLI usage

```bash
# Import all XML from storage/vendit/import/
php sync_customers.php

# Import one file
php sync_customers.php --file=storage/vendit/import/CustomerExport.xml

# Copy sample and import (good for first test)
php sync_customers.php --sample

# Export pending vendit_orders to XML
php export_orders.php

# Create demo order and export
php export_orders.php --seed-sample
```

## Admin dashboard

Open: `/admin/vendit/index.php` (requires existing admin login).

Shows:

- Last sync date
- Total customers imported / orders exported
- Sync logs with validation status
- Sample XML validation

## PHP classes

| Class | Responsibility |
|-------|----------------|
| `Database` | PDO wrapper, transactions |
| `VenditConfig` | Loads `config/vendit.php` |
| `VenditXmlParser` | SimpleXML parse/validate/build OrderImport |
| `VenditCustomerImporter` | CustomerExport/CustomerImport → DB |
| `VenditOrderExporter` | DB → OrderImport XML |
| `VenditLogger` | `vendit_sync_logs` + daily log files |

## Where FTP/SFTP will be added (future)

When credentials are available, add a transport layer **without changing** importer/exporter logic:

1. **Create** `app/Services/Vendit/VenditFtpTransport.php` (or `VenditSftpTransport.php`)
   - `downloadImports(): void` — fetch `CustomerExport.xml`, `Stock.xml`, `Groups.xml` from remote import folder into `storage/vendit/import/`
   - `uploadExports(): void` — push files from `storage/vendit/export/` to remote export folder
   - Use credentials from `config/vendit.php` → `ftp` section

2. **Create** CLI wrappers:
   - `php vendit_fetch.php` — download then call `sync_customers.php` logic
   - `php vendit_push.php` — call export then upload

3. **Hook cron** (Windows Task Scheduler / Linux cron):
   ```
   */15 * * * * php /path/to/sync_customers.php && php /path/to/export_orders.php
   ```
   After FTP exists: fetch → sync → export → push.

4. **Optional webhook**: VMSII can call a URL after uploading `Stock.xml`; add `api/vendit/trigger_import.php` that only runs the import pipeline.

Recommended libraries when implementing:

- FTP: PHP `ftp_*` functions or `league/flysystem-ftp`
- SFTP: `phpseclib/phpseclib` or `league/flysystem-sftp-v3`

## Queueing orders from checkout

After a successful checkout, `api/public/checkout_submit.php` calls `VenditCheckoutBridge::afterCheckout()` which:

1. Maps the order to Vendit `OrderImport` format
2. Inserts into `vendit_orders` / `vendit_order_items`
3. Exports XML to `storage/vendit/export/` when `sync.auto_export_on_checkout` is `true` (default)

Checkout success is **never** blocked if Vendit fails (errors are logged only).

To disable auto-export and only queue:

```php
// config/vendit.php
'auto_export_on_checkout' => false,
```

Then run `php export_orders.php` on a schedule.

## Error handling

- All DB writes use transactions per customer/order
- Failed imports log to `vendit_sync_logs` and `storage/vendit/logs/vendit-YYYY-MM-DD.log`
- XML is validated before import; invalid files are not archived
- Exported orders cannot be re-exported (Vendit requirement)

## Not yet implemented

- Stock import (`StockExport` → product stock)
- Groups import (`GroupExport` → categories)
- FTP/SFTP transport
- Bridge from core `orders` table to `vendit_orders`

These can be added as separate services following the same pattern.

## Vendit contact

Vendit B.V. — [www.vendit.nl](https://www.vendit.nl) — info@vendit.nl
