<?php
declare(strict_types=1);

namespace Integrations\Vendit\Xml;

use DateTimeImmutable;
use RuntimeException;
use SimpleXMLElement;

/**
 * Parses and validates Vendit E-commerce 2.0 XML documents via SimpleXML.
 */
final class XmlToolkit
{
    public const TYPE_CUSTOMER_EXPORT = 'CustomerExport';
    public const TYPE_CUSTOMER_IMPORT = 'CustomerImport';
    public const TYPE_ORDER_IMPORT = 'OrderImport';
    public const TYPE_STOCK_EXPORT = 'StockExport';
    public const TYPE_GROUP_EXPORT = 'GroupExport';

    public function detectType(string $xmlContent): string
    {
        return $this->loadXml($xmlContent)->getName();
    }

    /**
     * @param list<array<string, mixed>> $customers
     */
    public function buildCustomerExport(array $customers, ?DateTimeImmutable $exportDateTime = null): string
    {
        return $this->buildCustomerExportXml($customers, $exportDateTime);
    }

    /**
     * @param list<array<string, mixed>> $orders
     */
    public function buildOrderImport(array $orders, ?DateTimeImmutable $exportDateTime = null): string
    {
        return $this->buildOrderImportXml($orders, $exportDateTime);
    }

    /**
     * @return array{valid: bool, type: string|null, errors: list<string>}
     */
    public function validateStructure(string $xmlContent): array
    {
        $errors = [];
        $type = null;

        try {
            $xml = $this->loadXml($xmlContent);
        } catch (RuntimeException $e) {
            return ['valid' => false, 'type' => null, 'errors' => [$e->getMessage()]];
        }

        $root = $xml->getName();
        $type = $root;

        switch ($root) {
            case self::TYPE_CUSTOMER_EXPORT:
                $errors = array_merge($errors, $this->validateCustomerExport($xml));
                break;
            case self::TYPE_CUSTOMER_IMPORT:
                $errors = array_merge($errors, $this->validateCustomerImport($xml));
                break;
            case self::TYPE_ORDER_IMPORT:
                $errors = array_merge($errors, $this->validateOrderImport($xml));
                break;
            case self::TYPE_STOCK_EXPORT:
                $errors = array_merge($errors, $this->validateStockExport($xml));
                break;
            case self::TYPE_GROUP_EXPORT:
                $errors = array_merge($errors, $this->validateGroupExport($xml));
                break;
            default:
                $errors[] = 'Unknown root element: ' . $root;
        }

        return [
            'valid' => $errors === [],
            'type' => $type,
            'errors' => $errors,
        ];
    }

    /**
     * @return array{meta: array<string, mixed>, customers: list<array<string, mixed>>}
     */
    public function parseCustomerExport(string $xmlContent): array
    {
        $validation = $this->validateStructure($xmlContent);
        if (!$validation['valid'] || $validation['type'] !== self::TYPE_CUSTOMER_EXPORT) {
            throw new RuntimeException('Invalid CustomerExport XML: ' . implode('; ', $validation['errors']));
        }

        $xml = $this->loadXml($xmlContent);
        $meta = [
            'export_datetime' => $this->stringOrNull($xml->ExportInfo->ExportDateTime ?? null),
            'type' => $this->stringOrNull($xml->ExportInfo->Type ?? null),
            'export_started' => $this->stringOrNull($xml->ExportInfo->ExportStarted ?? null),
        ];

        $customers = [];
        if (isset($xml->Customers->Customer)) {
            foreach ($xml->Customers->Customer as $node) {
                $customers[] = $this->parseCustomerExportNode($node);
            }
        }

        return ['meta' => $meta, 'customers' => $customers];
    }

    /**
     * @return array{meta: array<string, mixed>, customers: list<array<string, mixed>>}
     */
    public function parseCustomerImport(string $xmlContent): array
    {
        $validation = $this->validateStructure($xmlContent);
        if (!$validation['valid'] || $validation['type'] !== self::TYPE_CUSTOMER_IMPORT) {
            throw new RuntimeException('Invalid CustomerImport XML: ' . implode('; ', $validation['errors']));
        }

        $xml = $this->loadXml($xmlContent);
        $meta = [
            'export_datetime' => $this->stringOrNull($xml->ImportInfo->ExportDateTime ?? null),
        ];

        $customers = [];
        if (isset($xml->Customers->Customer)) {
            foreach ($xml->Customers->Customer as $node) {
                $customers[] = $this->parseCustomerImportNode($node);
            }
        }

        return ['meta' => $meta, 'customers' => $customers];
    }

    /**
     * @return array{meta: array<string, mixed>, orders: list<array<string, mixed>>}
     */
    public function parseOrderImport(string $xmlContent): array
    {
        $validation = $this->validateStructure($xmlContent);
        if (!$validation['valid'] || $validation['type'] !== self::TYPE_ORDER_IMPORT) {
            throw new RuntimeException('Invalid OrderImport XML: ' . implode('; ', $validation['errors']));
        }

        $xml = $this->loadXml($xmlContent);
        $meta = [
            'export_datetime' => $this->stringOrNull($xml->ImportInfo->ExportDateTime ?? null),
        ];

        $orders = [];
        if (isset($xml->Orders->Order)) {
            foreach ($xml->Orders->Order as $node) {
                $orders[] = $this->parseOrderNode($node);
            }
        }

        return ['meta' => $meta, 'orders' => $orders];
    }

    /**
     * Build OrderImport XML (UTF-16LE) from structured order data.
     *
     * @param list<array<string, mixed>> $orders
     */
    public function buildOrderImportXml(array $orders, ?DateTimeImmutable $exportDateTime = null): string
    {
        $exportDateTime ??= new DateTimeImmutable();
        $iso = $exportDateTime->format('Y-m-d\TH:i:s.uP');

        $doc = new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?><OrderImport xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"></OrderImport>'
        );
        $importInfo = $doc->addChild('ImportInfo');
        $importInfo->addChild('ExportDateTime', htmlspecialchars($iso, ENT_XML1));

        $ordersNode = $doc->addChild('Orders');
        foreach ($orders as $order) {
            $this->appendOrderNode($ordersNode, $order);
        }

        $utf8 = $doc->asXML();
        if ($utf8 === false) {
            throw new RuntimeException('Failed to serialize OrderImport XML.');
        }

        return $this->convertToUtf16Le($utf8);
    }

    /**
     * Build CustomerExport XML (UTF-16LE) from structured customer data.
     *
     * @param list<array<string, mixed>> $customers
     */
    public function buildCustomerExportXml(array $customers, ?DateTimeImmutable $exportDateTime = null): string
    {
        $exportDateTime ??= new DateTimeImmutable();
        $iso = $exportDateTime->format('Y-m-d\TH:i:s.uP');

        $doc = new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?><CustomerExport xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"></CustomerExport>'
        );
        $exportInfo = $doc->addChild('ExportInfo');
        $exportInfo->addChild('ExportDateTime', htmlspecialchars($iso, ENT_XML1));
        $exportInfo->addChild('Type', 'Customers');
        $exportInfo->addChild('ExportStarted', 'false');

        $customersNode = $doc->addChild('Customers');
        foreach ($customers as $customer) {
            $this->appendCustomerExportNode($customersNode, $customer);
        }

        $utf8 = $doc->asXML();
        if ($utf8 === false) {
            throw new RuntimeException('Failed to serialize CustomerExport XML.');
        }

        return $this->convertToUtf16Le($utf8);
    }

    /**
     * @param array<string, mixed> $customer
     */
    private function appendCustomerExportNode(SimpleXMLElement $parent, array $customer): void
    {
        $node = $parent->addChild('Customer');
        $this->addChildIfSet($node, 'CustomerNumber', $customer['customer_number'] ?? null);
        $this->addChildIfSet($node, 'CreationDateTime', $customer['creation_datetime'] ?? null);
        $this->addBoolChild($node, 'TransactionsBlocked', $customer['transactions_blocked'] ?? false);
        $this->addChildIfSet($node, 'CompanyName', $customer['company_name'] ?? null);
        $this->addChildIfSet($node, 'KVKNumber', $customer['kvk_number'] ?? null);
        $this->addChildIfSet($node, 'VATNumber', $customer['vat_number'] ?? null);
        $this->addChildIfSet($node, 'IBANNumber', $customer['iban_number'] ?? null);
        $this->addBoolChild($node, 'AllowOnAccount', $customer['allow_on_account'] ?? false);
        $this->addDecimalChild($node, 'FixedRaisePercentage', $customer['fixed_raise_percentage'] ?? 0, 4);
        $this->addDecimalChild($node, 'FixedDiscountPercentage', $customer['fixed_discount_percentage'] ?? 0, 4);
        $this->addChildIfSet($node, 'TermOfPayment', $customer['term_of_payment'] ?? null);
        $this->addChildIfSet($node, 'PricelistName', $customer['pricelist_name'] ?? null);
        $this->addBoolChild($node, 'OptIn', $customer['opt_in'] ?? false);
        $this->addChildIfSet($node, 'OptInDate', $customer['opt_in_date'] ?? null);

        if (!empty($customer['groups'])) {
            $groupsNode = $node->addChild('Groups');
            foreach ($customer['groups'] as $group) {
                $g = $groupsNode->addChild('Group');
                $this->addChildIfSet($g, 'GroupName', $group['group_name'] ?? null);
            }
        }

        if (!empty($customer['addresses'])) {
            $addressesNode = $node->addChild('Addresses');
            foreach ($customer['addresses'] as $address) {
                $a = $addressesNode->addChild('Address');
                $this->addChildIfSet($a, 'AddressTypeDescription', $address['address_type_description'] ?? 'Bezoekadres');
                $this->addChildIfSet($a, 'Street', $address['street'] ?? null);
                $this->addChildIfSet($a, 'ZipCode', $address['zip_code'] ?? null);
                $this->addChildIfSet($a, 'HouseNumber', $address['house_number'] ?? null);
                $this->addChildIfSet($a, 'HouseNumberSuffix', $address['house_number_suffix'] ?? null);
                $this->addChildIfSet($a, 'City', $address['city'] ?? null);
                $this->addChildIfSet($a, 'EmailAddress', $address['email_address'] ?? null);
                $this->addBoolChild($a, 'DefaultAddress', $address['default_address'] ?? false);
                $this->addChildIfSet($a, 'Country', $address['country'] ?? null);
                $this->addChildIfSet($a, 'Code', $address['country_code'] ?? null);

                if (!empty($address['contacts'])) {
                    $contactsNode = $a->addChild('Contacts');
                    foreach ($address['contacts'] as $contact) {
                        $c = $contactsNode->addChild('Contact');
                        $this->addChildIfSet($c, 'Title', $contact['title'] ?? null);
                        $this->addChildIfSet($c, 'LastName', $contact['last_name'] ?? null);
                        $this->addChildIfSet($c, 'FirstName', $contact['first_name'] ?? null);
                        $this->addChildIfSet($c, 'MiddleName', $contact['middle_name'] ?? null);
                        $this->addChildIfSet($c, 'Sex', $contact['sex'] ?? null);
                        $this->addChildIfSet($c, 'Birthdate', $contact['birthdate'] ?? null);
                        $this->addChildIfSet($c, 'EmailAddress', $contact['email_address'] ?? null);
                        $this->addChildIfSet($c, 'FunctionDescription', $contact['function_description'] ?? null);
                        $this->addChildIfSet($c, 'Department', $contact['department'] ?? null);
                        $this->addBoolChild($c, 'DefaultContact', $contact['default_contact'] ?? false);
                        $this->addBoolChild($c, 'OptIn', $contact['opt_in'] ?? false);
                        $this->addChildIfSet($c, 'OptInDate', $contact['opt_in_date'] ?? null);
                        if (!empty($contact['phones'])) {
                            $phonesNode = $c->addChild('Phones');
                            foreach ($contact['phones'] as $phone) {
                                $this->appendPhoneNode($phonesNode, $phone);
                            }
                        }
                    }
                }

                if (!empty($address['phones'])) {
                    $phonesNode = $a->addChild('Phones');
                    foreach ($address['phones'] as $phone) {
                        $this->appendPhoneNode($phonesNode, $phone);
                    }
                }
            }
        }
    }

    /**
     * @param array<string, mixed> $phone
     */
    private function appendPhoneNode(SimpleXMLElement $parent, array $phone): void
    {
        $p = $parent->addChild('Phone');
        $this->addChildIfSet($p, 'PhoneNumber', $phone['phone_number'] ?? null);
        $this->addChildIfSet($p, 'DialingCode', $phone['dialing_code'] ?? null);
        $this->addChildIfSet($p, 'PhoneTypeDescription', $phone['phone_type_description'] ?? null);
        $this->addBoolChild($p, 'DefaultPhone', $phone['default_phone'] ?? false);
    }

    /**
     * @param array<string, mixed> $order
     */
    private function appendOrderNode(SimpleXMLElement $parent, array $order): void
    {
        $node = $parent->addChild('Order');
        $this->addChildIfSet($node, 'OrderNumber', $order['order_number'] ?? null);
        $this->addChildIfSet($node, 'StoreNumber', $order['store_number'] ?? null);
        $this->addChildIfSet($node, 'OrderType', $order['order_type'] ?? 'Order');
        $this->addChildIfSet($node, 'OrderDate', $order['order_date'] ?? null);
        $this->addChildIfSet($node, 'DeliveryDate', $order['delivery_date'] ?? null);
        $this->addDecimalChild($node, 'TotalOrderAmount', $order['total_order_amount'] ?? 0, 4);
        $this->addChildIfSet($node, 'PaymentMethod', $order['payment_method'] ?? null);
        $this->addDecimalChild($node, 'PaymentCosts', $order['payment_costs'] ?? 0, 4);
        $this->addDecimalChild($node, 'Paid', $order['paid'] ?? 0, 4);
        $this->addChildIfSet($node, 'ShippingMethod', $order['shipping_method'] ?? null);
        $this->addDecimalChild($node, 'ShippingCosts', $order['shipping_costs'] ?? 0, 4);
        $this->addChildIfSet($node, 'InvoiceDiscountName', $order['invoice_discount_name'] ?? null);
        $this->addDecimalChild($node, 'InvoiceDiscountAmount', $order['invoice_discount_amount'] ?? 0, 4);
        $this->addChildIfSet($node, 'Title', $order['invoice_title'] ?? null);
        $this->addChildIfSet($node, 'FirstName', $order['invoice_first_name'] ?? null);
        $this->addChildIfSet($node, 'MiddleName', $order['invoice_middle_name'] ?? null);
        $this->addChildIfSet($node, 'LastName', $order['invoice_last_name'] ?? null);
        $this->addChildIfSet($node, 'EmailAddress', $order['invoice_email'] ?? null);
        $this->addChildIfSet($node, 'Phone', $order['invoice_phone'] ?? null);
        $this->addChildIfSet($node, 'PhoneMobile', $order['invoice_phone_mobile'] ?? null);
        $this->addChildIfSet($node, 'InvoiceAddress', $order['invoice_address'] ?? null);
        $this->addChildIfSet($node, 'InvoiceHousenumber', $order['invoice_housenumber'] ?? null);
        $this->addChildIfSet($node, 'InvoiceHousenumberExtension', $order['invoice_housenumber_extension'] ?? null);
        $this->addChildIfSet($node, 'InvoiceZipcode', $order['invoice_zipcode'] ?? null);
        $this->addChildIfSet($node, 'InvoiceCity', $order['invoice_city'] ?? null);
        $this->addChildIfSet($node, 'InvoiceCountry', $order['invoice_country'] ?? null);
        $this->addChildIfSet($node, 'InvoiceCountryCode', $order['invoice_country_code'] ?? null);
        $this->addChildIfSet($node, 'CompanyName', $order['company_name'] ?? null);
        $this->addChildIfSet($node, 'IBANNumber', $order['iban_number'] ?? null);
        $this->addChildIfSet($node, 'BankAccount', $order['bank_account'] ?? null);
        $this->addChildIfSet($node, 'VatNumber', $order['vat_number'] ?? null);
        $this->addBoolChild($node, 'OptIn', $order['opt_in'] ?? null);
        $this->addChildIfSet($node, 'OptInDate', $order['opt_in_date'] ?? null);
        $this->addChildIfSet($node, 'DeliveryFirstName', $order['delivery_first_name'] ?? null);
        $this->addChildIfSet($node, 'DeliveryLastName', $order['delivery_last_name'] ?? null);
        $this->addChildIfSet($node, 'DeliveryAddress', $order['delivery_address'] ?? null);
        $this->addChildIfSet($node, 'DeliveryZipcode', $order['delivery_zipcode'] ?? null);
        $this->addChildIfSet($node, 'DeliveryCity', $order['delivery_city'] ?? null);
        $this->addChildIfSet($node, 'DeliveryCountry', $order['delivery_country'] ?? null);
        $this->addChildIfSet($node, 'DeliveryCountryCode', $order['delivery_country_code'] ?? null);
        $this->addChildIfSet($node, 'DeliveryCompanyName', $order['delivery_company_name'] ?? null);
        $this->addChildIfSet($node, 'OrderRemark', $order['order_remark'] ?? null);
        $this->addChildIfSet($node, 'OrderMessage', $order['order_message'] ?? null);
        $this->addChildIfSet($node, 'CustomerNumber', $order['customer_number'] ?? null);
        $this->addChildIfSet($node, 'OrderOrigin', $order['order_origin'] ?? null);

        $productsNode = $node->addChild('Products');
        foreach ($order['items'] ?? [] as $item) {
            $p = $productsNode->addChild('Product');
            $this->addChildIfSet($p, 'EcommerceProductGuid', $item['ecommerce_product_guid'] ?? null);
            $this->addChildIfSet($p, 'ProductId', $item['product_id'] ?? null);
            $this->addChildIfSet($p, 'EAN', $item['ean'] ?? null);
            $this->addDecimalChild($p, 'ProductSalesPriceEx', $item['product_sales_price_ex'] ?? 0, 4);
            $this->addDecimalChild($p, 'ProductSalesPriceInc', $item['product_sales_price_inc'] ?? 0, 4);
            $this->addDecimalChild($p, 'PrivateCopyLevy', $item['private_copy_levy'] ?? 0, 4);
            $this->addDecimalChild($p, 'Quantity', $item['quantity'] ?? 1, 4);
            $this->addChildIfSet($p, 'Remarks', $item['remarks'] ?? null);
            if (isset($item['office_id'])) {
                $p->addChild('OfficeId', (string) $item['office_id']);
            }
            $this->addBoolChild($p, 'ReserveStock', $item['reserve_stock'] ?? null);
            $this->addChildIfSet($p, 'Description', $item['description'] ?? null);
            $this->addBoolChild($p, 'IsDropshipment', $item['is_dropshipment'] ?? null);
        }
    }

    private function loadXml(string $xmlContent): SimpleXMLElement
    {
        $content = $this->normalizeEncoding($xmlContent);
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);
        if ($xml === false) {
            $messages = array_map(static fn($e) => trim($e->message), libxml_get_errors());
            libxml_clear_errors();
            throw new RuntimeException('XML parse error: ' . implode(' | ', $messages));
        }
        return $xml;
    }

    private function normalizeEncoding(string $content): string
    {
        if (str_starts_with($content, "\xFF\xFE") || str_starts_with($content, "\xFE\xFF")) {
            $converted = @mb_convert_encoding($content, 'UTF-8', 'UTF-16');
            if ($converted !== false) {
                return $converted;
            }
        }
        if (preg_match('/encoding=["\']UTF-16["\']/i', substr($content, 0, 200))) {
            $converted = @mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');
            if ($converted !== false) {
                return preg_replace('/encoding=["\']UTF-16["\']/i', 'encoding="UTF-8"', $converted, 1) ?? $converted;
            }
        }
        return $content;
    }

    private function convertToUtf16Le(string $utf8Xml): string
    {
        $withDecl = preg_replace('/encoding=["\']UTF-8["\']/i', 'encoding="UTF-16"', $utf8Xml, 1) ?? $utf8Xml;
        $utf16 = mb_convert_encoding($withDecl, 'UTF-16LE', 'UTF-8');
        if ($utf16 === false) {
            throw new RuntimeException('Failed to convert XML to UTF-16LE.');
        }
        return $utf16;
    }

    /**
     * @return list<string>
     */
    private function validateCustomerExport(SimpleXMLElement $xml): array
    {
        $errors = [];
        if (!isset($xml->ExportInfo)) {
            $errors[] = 'Missing ExportInfo element.';
        }
        if (!isset($xml->Customers)) {
            $errors[] = 'Missing Customers element.';
        }
        if (isset($xml->Customers->Customer)) {
            foreach ($xml->Customers->Customer as $i => $customer) {
                if ($this->stringOrNull($customer->CustomerNumber) === null) {
                    $errors[] = 'Customer #' . $i . ' missing CustomerNumber.';
                }
            }
        }
        return $errors;
    }

    /**
     * @return list<string>
     */
    private function validateCustomerImport(SimpleXMLElement $xml): array
    {
        $errors = [];
        if (!isset($xml->ImportInfo)) {
            $errors[] = 'Missing ImportInfo element.';
        }
        if (!isset($xml->Customers)) {
            $errors[] = 'Missing Customers element.';
        }
        if (isset($xml->Customers->Customer)) {
            foreach ($xml->Customers->Customer as $i => $customer) {
                foreach (['CustomerNumber', 'CreationDateTime', 'TransactionsBlocked', 'Birthdate'] as $field) {
                    if ($this->stringOrNull($customer->{$field} ?? null) === null && (string) ($customer->{$field} ?? '') === '') {
                        if ($field === 'Birthdate' || $field === 'CreationDateTime' || $field === 'CustomerNumber' || $field === 'TransactionsBlocked') {
                            $errors[] = 'Customer #' . $i . ' missing required field ' . $field . '.';
                        }
                    }
                }
            }
        }
        return $errors;
    }

    /**
     * @return list<string>
     */
    private function validateOrderImport(SimpleXMLElement $xml): array
    {
        $errors = [];
        if (!isset($xml->ImportInfo)) {
            $errors[] = 'Missing ImportInfo element.';
        }
        if (!isset($xml->Orders)) {
            $errors[] = 'Missing Orders element.';
        }
        if (isset($xml->Orders->Order)) {
            foreach ($xml->Orders->Order as $i => $order) {
                if ($this->stringOrNull($order->OrderNumber) === null) {
                    $errors[] = 'Order #' . $i . ' missing OrderNumber.';
                }
                if ($this->stringOrNull($order->OrderDate) === null) {
                    $errors[] = 'Order #' . $i . ' missing OrderDate.';
                }
            }
        }
        return $errors;
    }

    /**
     * @return list<string>
     */
    private function validateStockExport(SimpleXMLElement $xml): array
    {
        $errors = [];
        if (!isset($xml->ExportInfo)) {
            $errors[] = 'Missing ExportInfo element.';
        }
        if (!isset($xml->Products)) {
            $errors[] = 'Missing Products element.';
        }
        return $errors;
    }

    /**
     * @return list<string>
     */
    private function validateGroupExport(SimpleXMLElement $xml): array
    {
        $errors = [];
        if (!isset($xml->ExportInfo)) {
            $errors[] = 'Missing ExportInfo element.';
        }
        if (!isset($xml->Groups)) {
            $errors[] = 'Missing Groups element.';
        }
        return $errors;
    }

    /**
     * @return array<string, mixed>
     */
    private function parseCustomerExportNode(SimpleXMLElement $node): array
    {
        $customer = [
            'customer_number' => $this->stringOrNull($node->CustomerNumber),
            'creation_datetime' => $this->stringOrNull($node->CreationDateTime),
            'transactions_blocked' => $this->boolValue($node->TransactionsBlocked ?? 'false'),
            'company_name' => $this->stringOrNull($node->CompanyName),
            'kvk_number' => $this->stringOrNull($node->KVKNumber),
            'vat_number' => $this->stringOrNull($node->VATNumber),
            'iban_number' => $this->stringOrNull($node->IBANNumber),
            'allow_on_account' => $this->boolValue($node->AllowOnAccount ?? 'false'),
            'fixed_raise_percentage' => $this->decimalOrNull($node->FixedRaisePercentage ?? null),
            'fixed_discount_percentage' => $this->decimalOrNull($node->FixedDiscountPercentage ?? null),
            'term_of_payment' => $this->stringOrNull($node->TermOfPayment),
            'pricelist_name' => $this->stringOrNull($node->PricelistName ?? $node->Pricelist ?? null),
            'opt_in' => $this->boolValue($node->OptIn ?? 'false'),
            'opt_in_date' => $this->stringOrNull($node->OptInDate),
            'groups' => [],
            'addresses' => [],
            'source_format' => self::TYPE_CUSTOMER_EXPORT,
        ];

        if (isset($node->Groups->Group)) {
            foreach ($node->Groups->Group as $group) {
                $name = $this->stringOrNull($group->GroupName);
                if ($name !== null) {
                    $customer['groups'][] = ['group_name' => $name];
                }
            }
        }

        if (isset($node->Addresses->Address)) {
            foreach ($node->Addresses->Address as $addressNode) {
                $customer['addresses'][] = $this->parseAddressNode($addressNode);
            }
        }

        return $customer;
    }

    /**
     * @return array<string, mixed>
     */
    private function parseCustomerImportNode(SimpleXMLElement $node): array
    {
        return [
            'customer_number' => $this->stringOrNull($node->CustomerNumber),
            'creation_datetime' => $this->stringOrNull($node->CreationDateTime),
            'transactions_blocked' => $this->boolValue($node->TransactionsBlocked ?? 'false'),
            'title' => $this->stringOrNull($node->Title),
            'last_name' => $this->stringOrNull($node->Lastname),
            'middle_name' => $this->stringOrNull($node->Middlename),
            'first_name' => $this->stringOrNull($node->Firstname),
            'sex' => $this->stringOrNull($node->Sex),
            'birthdate' => $this->stringOrNull($node->Birthdate),
            'email' => $this->stringOrNull($node->eMailAddress),
            'phone' => $this->stringOrNull($node->PhoneNumber),
            'mobile' => $this->stringOrNull($node->MobileNumber),
            'company_name' => $this->stringOrNull($node->CompanyName),
            'kvk_number' => $this->stringOrNull($node->KVKNumber),
            'vat_number' => $this->stringOrNull($node->VATNumber),
            'street' => $this->stringOrNull($node->Street),
            'zip_code' => $this->stringOrNull($node->ZipCode),
            'house_number' => $this->stringOrNull($node->HouseNumber),
            'house_number_suffix' => $this->stringOrNull($node->HouseNumberSuffix),
            'city' => $this->stringOrNull($node->City),
            'country' => $this->stringOrNull($node->Country),
            'iban_number' => $this->stringOrNull($node->IBAN),
            'groups' => [],
            'addresses' => [],
            'source_format' => self::TYPE_CUSTOMER_IMPORT,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function parseAddressNode(SimpleXMLElement $node): array
    {
        $address = [
            'address_type_description' => $this->stringOrNull($node->AddressTypeDescription) ?? 'Bezoekadres',
            'street' => $this->stringOrNull($node->Street),
            'zip_code' => $this->stringOrNull($node->ZipCode),
            'house_number' => $this->stringOrNull($node->HouseNumber),
            'house_number_suffix' => $this->stringOrNull($node->HouseNumberSuffix),
            'city' => $this->stringOrNull($node->City),
            'email_address' => $this->stringOrNull($node->EmailAddress),
            'default_address' => $this->boolValue($node->DefaultAddress ?? 'false'),
            'country' => $this->stringOrNull($node->Country),
            'country_code' => $this->stringOrNull($node->Code),
            'contacts' => [],
            'phones' => [],
        ];

        if (isset($node->Contacts->Contact)) {
            foreach ($node->Contacts->Contact as $contactNode) {
                $address['contacts'][] = $this->parseContactNode($contactNode);
            }
        }

        if (isset($node->Phones->Phone)) {
            foreach ($node->Phones->Phone as $phoneNode) {
                $address['phones'][] = $this->parsePhoneNode($phoneNode);
            }
        }

        return $address;
    }

    /**
     * @return array<string, mixed>
     */
    private function parseContactNode(SimpleXMLElement $node): array
    {
        $contact = [
            'title' => $this->stringOrNull($node->Title),
            'last_name' => $this->stringOrNull($node->LastName),
            'first_name' => $this->stringOrNull($node->FirstName),
            'middle_name' => $this->stringOrNull($node->MiddleName),
            'sex' => $this->stringOrNull($node->Sex),
            'birthdate' => $this->stringOrNull($node->Birthdate),
            'email_address' => $this->stringOrNull($node->EmailAddress),
            'additional_info' => $this->stringOrNull($node->AdditionalInfo),
            'function_description' => $this->stringOrNull($node->FunctionDescription),
            'drivers_license_number' => $this->stringOrNull($node->DriversLicenseNumber),
            'bank_account' => $this->stringOrNull($node->BankAccount),
            'department' => $this->stringOrNull($node->Department),
            'iban_number' => $this->stringOrNull($node->IBANNumber),
            'default_contact' => $this->boolValue($node->DefaultContact ?? 'false'),
            'opt_in' => $this->boolValue($node->OptIn ?? 'false'),
            'opt_in_date' => $this->stringOrNull($node->OptInDate),
            'phones' => [],
        ];

        if (isset($node->Phones->Phone)) {
            foreach ($node->Phones->Phone as $phoneNode) {
                $contact['phones'][] = $this->parsePhoneNode($phoneNode);
            }
        }

        return $contact;
    }

    /**
     * @return array<string, mixed>
     */
    private function parsePhoneNode(SimpleXMLElement $node): array
    {
        return [
            'phone_number' => $this->stringOrNull($node->PhoneNumber) ?? '',
            'dialing_code' => $this->stringOrNull($node->DialingCode),
            'phone_type_description' => $this->stringOrNull($node->PhoneTypeDescription),
            'default_phone' => $this->boolValue($node->DefaultPhone ?? 'false'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function parseOrderNode(SimpleXMLElement $node): array
    {
        $order = [
            'order_number' => $this->stringOrNull($node->OrderNumber),
            'store_number' => $this->stringOrNull($node->StoreNumber),
            'order_type' => $this->stringOrNull($node->OrderType) ?? 'Order',
            'order_date' => $this->stringOrNull($node->OrderDate),
            'delivery_date' => $this->stringOrNull($node->DeliveryDate),
            'total_order_amount' => $this->decimalOrNull($node->TotalOrderAmount) ?? 0,
            'payment_method' => $this->stringOrNull($node->PaymentMethod),
            'payment_costs' => $this->decimalOrNull($node->PaymentCosts) ?? 0,
            'paid' => $this->decimalOrNull($node->Paid) ?? 0,
            'shipping_method' => $this->stringOrNull($node->ShippingMethod),
            'shipping_costs' => $this->decimalOrNull($node->ShippingCosts ?? $node->ShippingCost ?? null) ?? 0,
            'invoice_discount_name' => $this->stringOrNull($node->InvoiceDiscountName),
            'invoice_discount_amount' => $this->decimalOrNull($node->InvoiceDiscountAmount) ?? 0,
            'invoice_title' => $this->stringOrNull($node->Title),
            'invoice_first_name' => $this->stringOrNull($node->FirstName),
            'invoice_middle_name' => $this->stringOrNull($node->MiddleName),
            'invoice_last_name' => $this->stringOrNull($node->LastName),
            'invoice_email' => $this->stringOrNull($node->EmailAddress),
            'invoice_phone' => $this->stringOrNull($node->Phone),
            'invoice_phone_mobile' => $this->stringOrNull($node->PhoneMobile),
            'invoice_address' => $this->stringOrNull($node->InvoiceAddress),
            'invoice_housenumber' => $this->stringOrNull($node->InvoiceHousenumber),
            'invoice_housenumber_extension' => $this->stringOrNull($node->InvoiceHousenumberExtension),
            'invoice_zipcode' => $this->stringOrNull($node->InvoiceZipcode),
            'invoice_city' => $this->stringOrNull($node->InvoiceCity),
            'invoice_country' => $this->stringOrNull($node->InvoiceCountry),
            'invoice_country_code' => $this->stringOrNull($node->InvoiceCountryCode),
            'company_name' => $this->stringOrNull($node->CompanyName),
            'iban_number' => $this->stringOrNull($node->IBANNumber),
            'bank_account' => $this->stringOrNull($node->BankAccount),
            'vat_number' => $this->stringOrNull($node->VatNumber),
            'opt_in' => $this->boolValue($node->OptIn ?? 'false'),
            'opt_in_date' => $this->stringOrNull($node->OptInDate),
            'delivery_title' => $this->stringOrNull($node->DeliveryTitle),
            'delivery_first_name' => $this->stringOrNull($node->DeliveryFirstName),
            'delivery_middle_name' => $this->stringOrNull($node->DeliveryMiddleName),
            'delivery_last_name' => $this->stringOrNull($node->DeliveryLastName),
            'delivery_address' => $this->stringOrNull($node->DeliveryAddress),
            'delivery_housenumber' => $this->stringOrNull($node->DeliveryHousenumber),
            'delivery_housenumber_extension' => $this->stringOrNull($node->DeliveryHousenumberExtension),
            'delivery_zipcode' => $this->stringOrNull($node->DeliveryZipcode),
            'delivery_city' => $this->stringOrNull($node->DeliveryCity),
            'delivery_country' => $this->stringOrNull($node->DeliveryCountry),
            'delivery_country_code' => $this->stringOrNull($node->DeliveryCountryCode),
            'delivery_company_name' => $this->stringOrNull($node->DeliveryCompanyName),
            'order_remark' => $this->stringOrNull($node->OrderRemark),
            'order_message' => $this->stringOrNull($node->OrderMessage),
            'customer_number' => $this->stringOrNull($node->CustomerNumber),
            'order_origin' => $this->stringOrNull($node->OrderOrigin),
            'items' => [],
        ];

        if (isset($node->Products->Product)) {
            foreach ($node->Products->Product as $product) {
                $order['items'][] = [
                    'ecommerce_product_guid' => $this->stringOrNull($product->EcommerceProductGuid),
                    'product_id' => $this->stringOrNull($product->ProductId),
                    'ean' => $this->stringOrNull($product->EAN),
                    'product_sales_price_ex' => $this->decimalOrNull($product->ProductSalesPriceEx) ?? 0,
                    'product_sales_price_inc' => $this->decimalOrNull($product->ProductSalesPriceInc) ?? 0,
                    'private_copy_levy' => $this->decimalOrNull($product->PrivateCopyLevy) ?? 0,
                    'quantity' => $this->decimalOrNull($product->Quantity) ?? 1,
                    'remarks' => $this->stringOrNull($product->Remarks),
                    'office_id' => $this->stringOrNull($product->OfficeId),
                    'description' => $this->stringOrNull($product->Description),
                ];
            }
        }

        return $order;
    }

    private function stringOrNull(?SimpleXMLElement $node): ?string
    {
        if ($node === null) {
            return null;
        }
        $value = trim((string) $node);
        return $value === '' ? null : $value;
    }

    private function decimalOrNull(?SimpleXMLElement $node): ?string
    {
        $value = $this->stringOrNull($node);
        return $value;
    }

    private function boolValue(SimpleXMLElement|string|null $node): bool
    {
        $value = strtolower(trim((string) $node));
        return in_array($value, ['1', 'true', 'yes'], true);
    }

    private function addChildIfSet(SimpleXMLElement $parent, string $name, mixed $value): void
    {
        if ($value === null || $value === '') {
            return;
        }
        $parent->addChild($name, htmlspecialchars((string) $value, ENT_XML1));
    }

    private function addDecimalChild(SimpleXMLElement $parent, string $name, mixed $value, int $decimals): void
    {
        $formatted = number_format((float) $value, $decimals, '.', '');
        $parent->addChild($name, $formatted);
    }

    private function addBoolChild(SimpleXMLElement $parent, string $name, mixed $value): void
    {
        if ($value === null) {
            return;
        }
        $parent->addChild($name, filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false');
    }
}
