# Vendit E-commerce 2.0 — Database Mapping Plan

Run migration: `database/migration_v10_vendit_integration.sql`

## XML → MySQL mapping

### CustomerImport / CustomerExport → `vendit_customers`

| XML field | DB column | Notes |
|-----------|-----------|-------|
| CustomerNumber | customer_number | UNIQUE, upsert key |
| CreationDateTime | creation_datetime | ISO / Vendit datetime |
| TransactionsBlocked | transactions_blocked | bool |
| CompanyName | company_name | |
| KVKNumber | kvk_number | |
| VATNumber | vat_number | |
| IBAN / IBANNumber | iban_number | |
| AllowOnAccount | allow_on_account | Export only |
| FixedRaisePercentage | fixed_raise_percentage | |
| FixedDiscountPercentage | fixed_discount_percentage | |
| TermOfPayment | term_of_payment | |
| PricelistName | pricelist_name | |
| OptIn / OptInDate | opt_in, opt_in_date | |
| Title, Firstname, Middlename, Lastname, Sex, Birthdate | title, first_name, middle_name, last_name, sex, birthdate | Import flat fields |
| eMailAddress | email | Import |
| PhoneNumber / MobileNumber | phone, mobile | Import |
| Street, ZipCode, HouseNumber, … | street, zip_code, … | Import fallback address |

Related tables (replaced on update):
- `vendit_customer_groups` ← Groups/Group/GroupName
- `vendit_customer_addresses` ← Addresses/Address
- `vendit_customer_contacts` ← Address/Contacts/Contact
- `vendit_customer_phones` ← Address/Phones/Phone, Contact/Phones/Phone

### OrderImport → `vendit_orders` + `vendit_order_items`

| XML field | DB column |
|-----------|-----------|
| OrderNumber | order_number (UNIQUE) |
| StoreNumber | store_number |
| OrderType | order_type |
| OrderDate | order_date |
| DeliveryDate | delivery_date |
| TotalOrderAmount | total_order_amount |
| PaymentMethod | payment_method |
| PaymentCosts | payment_costs |
| Paid | paid |
| ShippingMethod | shipping_method |
| ShippingCosts | shipping_costs |
| Invoice* fields | invoice_* columns |
| Delivery* fields | delivery_* columns |
| CustomerNumber | customer_number |
| Products/Product | vendit_order_items rows |

Export status: `export_status` = pending | exported | failed

### Sync audit → `vendit_sync_logs`

All import/export batches write to `vendit_sync_logs` plus daily files in `integrations/vendit/logs/`.

## Local folder workflow (no SFTP)

```
integrations/vendit/import/     ← drop XML from Vendit (or copy samples)
integrations/vendit/export/     ← generated UTF-16 XML for Vendit pickup
integrations/vendit/archive/    ← processed import files
integrations/vendit/logs/       ← human-readable log lines
integrations/vendit/xml_samples/← reference documents
```

When SFTP is configured in `config.php`, a future `FtpTransport` can mirror these folders.
