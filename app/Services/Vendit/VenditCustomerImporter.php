<?php
declare(strict_types=1);

namespace App\Services\Vendit;

use DateTimeImmutable;
use RuntimeException;
use Throwable;

/**
 * Imports Vendit CustomerExport / CustomerImport XML into vendit_* tables.
 */
final class VenditCustomerImporter
{
    public function __construct(
        private readonly Database $db,
        private readonly VenditXmlParser $parser,
        private readonly VenditLogger $logger,
        private readonly array $config
    ) {
    }

    /**
     * Import all XML files from the configured import folder.
     *
     * @return array{processed: int, failed: int, files: list<string>}
     */
    public function importFromFolder(?string $folder = null): array
    {
        $folder ??= $this->config['paths']['import_folder'];
        $files = glob(rtrim($folder, '/\\') . '/*.{xml,XML}', GLOB_BRACE) ?: [];

        $processed = 0;
        $failed = 0;
        $handled = [];

        foreach ($files as $filePath) {
            try {
                $result = $this->importFile($filePath);
                $processed += $result['processed'];
                $failed += $result['failed'];
                $handled[] = basename($filePath);
            } catch (Throwable $e) {
                $failed++;
                $this->logger->error('File import failed: ' . basename($filePath), $e);
            }
        }

        return ['processed' => $processed, 'failed' => $failed, 'files' => $handled];
    }

    /**
     * @return array{processed: int, failed: int}
     */
    public function importFile(string $filePath): array
    {
        if (!is_readable($filePath)) {
            throw new RuntimeException('Cannot read file: ' . $filePath);
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new RuntimeException('Failed to read file: ' . $filePath);
        }

        $fileName = basename($filePath);
        $this->logger->start('customer_import', 'import', $fileName, $filePath);

        $validation = $this->parser->validateStructure($content);
        $this->logger->setValidation(
            $validation['valid'] ? 'valid' : 'invalid',
            $validation['errors']
        );

        if (!$validation['valid']) {
            $this->logger->complete('failed', 0, 1, implode('; ', $validation['errors']));
            throw new RuntimeException('XML validation failed for ' . $fileName);
        }

        $type = $validation['type'];
        $parsed = match ($type) {
            VenditXmlParser::TYPE_CUSTOMER_EXPORT => $this->parser->parseCustomerExport($content),
            VenditXmlParser::TYPE_CUSTOMER_IMPORT => $this->parser->parseCustomerImport($content),
            default => throw new RuntimeException('Unsupported customer XML type: ' . (string) $type),
        };

        $processed = 0;
        $failed = 0;

        foreach ($parsed['customers'] as $customer) {
            try {
                $this->importCustomer($customer);
                $processed++;
            } catch (Throwable $e) {
                $failed++;
                $number = $customer['customer_number'] ?? 'unknown';
                $this->logger->error('Customer import failed for ' . $number, $e);
            }
        }

        $status = $failed > 0 ? ($processed > 0 ? 'partial' : 'failed') : 'success';
        $this->logger->complete($status, $processed, $failed, sprintf('Imported from %s', $fileName));

        if (($this->config['sync']['archive_processed_files'] ?? true) && $status !== 'failed') {
            $this->archiveFile($filePath);
        }

        return ['processed' => $processed, 'failed' => $failed];
    }

    /**
     * @param array<string, mixed> $customer
     */
    public function importCustomer(array $customer): int
    {
        $customerNumber = $customer['customer_number'] ?? null;
        if ($customerNumber === null || $customerNumber === '') {
            throw new RuntimeException('CustomerNumber is required.');
        }

        $this->db->beginTransaction();
        try {
            $customerId = $this->upsertCustomerByNumber($customerNumber, $customer);
            $this->importGroups($customerId, $customer['groups'] ?? []);
            $this->importAddresses($customerId, $customer);
            $this->db->commit();
            return $customerId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateCustomerByNumber(string $customerNumber, array $data): int
    {
        $data['customer_number'] = $customerNumber;
        return $this->importCustomer($data);
    }

    /**
     * @param array<string, mixed> $customer
     */
    private function upsertCustomerByNumber(string $customerNumber, array $customer): int
    {
        $existing = $this->db->fetchOne(
            'SELECT id FROM vendit_customers WHERE customer_number = :customer_number LIMIT 1',
            ['customer_number' => $customerNumber]
        );

        $fields = [
            'customer_number' => $customerNumber,
            'creation_datetime' => $this->normalizeDateTime($customer['creation_datetime'] ?? null),
            'transactions_blocked' => ($customer['transactions_blocked'] ?? false) ? 1 : 0,
            'company_name' => $customer['company_name'] ?? null,
            'kvk_number' => $customer['kvk_number'] ?? null,
            'vat_number' => $customer['vat_number'] ?? null,
            'iban_number' => $customer['iban_number'] ?? null,
            'allow_on_account' => ($customer['allow_on_account'] ?? false) ? 1 : 0,
            'fixed_raise_percentage' => $customer['fixed_raise_percentage'] ?? null,
            'fixed_discount_percentage' => $customer['fixed_discount_percentage'] ?? null,
            'term_of_payment' => $customer['term_of_payment'] ?? null,
            'pricelist_name' => $customer['pricelist_name'] ?? null,
            'opt_in' => ($customer['opt_in'] ?? false) ? 1 : 0,
            'opt_in_date' => $this->normalizeDateTime($customer['opt_in_date'] ?? null),
            'title' => $customer['title'] ?? null,
            'first_name' => $customer['first_name'] ?? null,
            'middle_name' => $customer['middle_name'] ?? null,
            'last_name' => $customer['last_name'] ?? null,
            'sex' => $customer['sex'] ?? null,
            'birthdate' => $this->normalizeDateTime($customer['birthdate'] ?? null),
            'email' => $customer['email'] ?? null,
            'phone' => $customer['phone'] ?? null,
            'mobile' => $customer['mobile'] ?? null,
            'street' => $customer['street'] ?? null,
            'zip_code' => $customer['zip_code'] ?? null,
            'house_number' => $customer['house_number'] ?? null,
            'house_number_suffix' => $customer['house_number_suffix'] ?? null,
            'city' => $customer['city'] ?? null,
            'country' => $customer['country'] ?? null,
            'country_code' => $customer['country_code'] ?? null,
            'source_format' => $customer['source_format'] ?? VenditXmlParser::TYPE_CUSTOMER_EXPORT,
            'xml_hash' => hash('sha256', json_encode($customer, JSON_UNESCAPED_UNICODE) ?: ''),
            'last_imported_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ];

        if ($existing !== false) {
            $customerId = (int) $existing['id'];
            $set = [];
            $params = ['id' => $customerId];
            foreach ($fields as $column => $value) {
                if ($column === 'customer_number') {
                    continue;
                }
                $set[] = $column . ' = :' . $column;
                $params[$column] = $value;
            }
            $this->db->query(
                'UPDATE vendit_customers SET ' . implode(', ', $set) . ' WHERE id = :id',
                $params
            );
            $this->clearRelatedCustomerData($customerId);
            return $customerId;
        }

        $columns = array_keys($fields);
        $placeholders = array_map(static fn($c) => ':' . $c, $columns);
        $this->db->query(
            'INSERT INTO vendit_customers (' . implode(', ', $columns) . ')
             VALUES (' . implode(', ', $placeholders) . ')',
            $fields
        );

        return (int) $this->db->lastInsertId();
    }

    private function clearRelatedCustomerData(int $customerId): void
    {
        $this->db->query('DELETE FROM vendit_customer_groups WHERE customer_id = :id', ['id' => $customerId]);
        $this->db->query('DELETE FROM vendit_customer_addresses WHERE customer_id = :id', ['id' => $customerId]);
    }

    /**
     * @param list<array<string, mixed>> $groups
     */
    public function importGroups(int $customerId, array $groups): void
    {
        foreach ($groups as $group) {
            $name = $group['group_name'] ?? null;
            if ($name === null || $name === '') {
                continue;
            }
            $this->db->query(
                'INSERT INTO vendit_customer_groups (customer_id, group_name)
                 VALUES (:customer_id, :group_name)
                 ON DUPLICATE KEY UPDATE group_name = VALUES(group_name)',
                ['customer_id' => $customerId, 'group_name' => $name]
            );
        }
    }

    /**
     * @param array<string, mixed> $customer
     */
    public function importAddresses(int $customerId, array $customer): void
    {
        $addresses = $customer['addresses'] ?? [];

        if ($addresses === [] && ($customer['source_format'] ?? '') === VenditXmlParser::TYPE_CUSTOMER_IMPORT) {
            $addresses[] = [
                'address_type_description' => 'Bezoekadres',
                'street' => $customer['street'] ?? null,
                'zip_code' => $customer['zip_code'] ?? null,
                'house_number' => $customer['house_number'] ?? null,
                'house_number_suffix' => $customer['house_number_suffix'] ?? null,
                'city' => $customer['city'] ?? null,
                'email_address' => $customer['email'] ?? null,
                'default_address' => true,
                'country' => $customer['country'] ?? null,
                'country_code' => $customer['country_code'] ?? null,
                'contacts' => [],
                'phones' => array_filter([
                    $customer['phone'] ? [
                        'phone_number' => $customer['phone'],
                        'phone_type_description' => 'Telefoon',
                        'default_phone' => true,
                    ] : null,
                    $customer['mobile'] ? [
                        'phone_number' => $customer['mobile'],
                        'phone_type_description' => 'Mobiel',
                        'default_phone' => false,
                    ] : null,
                ]),
            ];
        }

        foreach ($addresses as $address) {
            $addressId = $this->insertAddress($customerId, $address);
            $this->importContacts($addressId, $address['contacts'] ?? []);
            $this->importPhones($addressId, null, $address['phones'] ?? []);
        }
    }

    /**
     * @param array<string, mixed> $address
     */
    private function insertAddress(int $customerId, array $address): int
    {
        $this->db->query(
            'INSERT INTO vendit_customer_addresses
             (customer_id, address_type_description, street, zip_code, house_number,
              house_number_suffix, city, email_address, default_address, country, country_code)
             VALUES
             (:customer_id, :address_type_description, :street, :zip_code, :house_number,
              :house_number_suffix, :city, :email_address, :default_address, :country, :country_code)',
            [
                'customer_id' => $customerId,
                'address_type_description' => $address['address_type_description'] ?? 'Bezoekadres',
                'street' => $address['street'] ?? null,
                'zip_code' => $address['zip_code'] ?? null,
                'house_number' => $address['house_number'] ?? null,
                'house_number_suffix' => $address['house_number_suffix'] ?? null,
                'city' => $address['city'] ?? null,
                'email_address' => $address['email_address'] ?? null,
                'default_address' => ($address['default_address'] ?? false) ? 1 : 0,
                'country' => $address['country'] ?? null,
                'country_code' => $address['country_code'] ?? null,
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    /**
     * @param list<array<string, mixed>> $contacts
     */
    public function importContacts(int $addressId, array $contacts): void
    {
        foreach ($contacts as $contact) {
            $this->db->query(
                'INSERT INTO vendit_customer_contacts
                 (address_id, title, last_name, first_name, middle_name, sex, birthdate,
                  email_address, additional_info, function_description, drivers_license_number,
                  bank_account, department, iban_number, default_contact, opt_in, opt_in_date)
                 VALUES
                 (:address_id, :title, :last_name, :first_name, :middle_name, :sex, :birthdate,
                  :email_address, :additional_info, :function_description, :drivers_license_number,
                  :bank_account, :department, :iban_number, :default_contact, :opt_in, :opt_in_date)',
                [
                    'address_id' => $addressId,
                    'title' => $contact['title'] ?? null,
                    'last_name' => $contact['last_name'] ?? null,
                    'first_name' => $contact['first_name'] ?? null,
                    'middle_name' => $contact['middle_name'] ?? null,
                    'sex' => $contact['sex'] ?? null,
                    'birthdate' => $this->normalizeDateTime($contact['birthdate'] ?? null),
                    'email_address' => $contact['email_address'] ?? null,
                    'additional_info' => $contact['additional_info'] ?? null,
                    'function_description' => $contact['function_description'] ?? null,
                    'drivers_license_number' => $contact['drivers_license_number'] ?? null,
                    'bank_account' => $contact['bank_account'] ?? null,
                    'department' => $contact['department'] ?? null,
                    'iban_number' => $contact['iban_number'] ?? null,
                    'default_contact' => ($contact['default_contact'] ?? false) ? 1 : 0,
                    'opt_in' => ($contact['opt_in'] ?? false) ? 1 : 0,
                    'opt_in_date' => $this->normalizeDateTime($contact['opt_in_date'] ?? null),
                ]
            );
            $contactId = (int) $this->db->lastInsertId();
            $this->importPhones($addressId, $contactId, $contact['phones'] ?? []);
        }
    }

    /**
     * @param list<array<string, mixed>> $phones
     */
    public function importPhones(?int $addressId, ?int $contactId, array $phones): void
    {
        foreach ($phones as $phone) {
            $number = $phone['phone_number'] ?? '';
            if ($number === '') {
                continue;
            }
            $this->db->query(
                'INSERT INTO vendit_customer_phones
                 (address_id, contact_id, phone_number, dialing_code, phone_type_description, default_phone)
                 VALUES
                 (:address_id, :contact_id, :phone_number, :dialing_code, :phone_type_description, :default_phone)',
                [
                    'address_id' => $addressId,
                    'contact_id' => $contactId,
                    'phone_number' => $number,
                    'dialing_code' => $phone['dialing_code'] ?? null,
                    'phone_type_description' => $phone['phone_type_description'] ?? null,
                    'default_phone' => ($phone['default_phone'] ?? false) ? 1 : 0,
                ]
            );
        }
    }

    public function totalImportedCustomers(): int
    {
        $row = $this->db->fetchOne('SELECT COUNT(*) AS cnt FROM vendit_customers');
        return is_array($row) ? (int) $row['cnt'] : 0;
    }

    private function normalizeDateTime(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        try {
            return (new DateTimeImmutable($value))->format('Y-m-d H:i:s');
        } catch (Throwable) {
            return $value;
        }
    }

    private function archiveFile(string $filePath): void
    {
        $archive = rtrim($this->config['paths']['archive_folder'], '/\\');
        if (!is_dir($archive)) {
            mkdir($archive, 0755, true);
        }
        $target = $archive . '/' . date('Ymd-His') . '-' . basename($filePath);
        @rename($filePath, $target);
    }
}
