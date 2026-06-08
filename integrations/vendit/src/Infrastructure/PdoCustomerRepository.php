<?php
declare(strict_types=1);

namespace Integrations\Vendit\Infrastructure;

use DateTimeImmutable;
use Integrations\Vendit\Contract\CustomerRepositoryInterface;

final class PdoCustomerRepository implements CustomerRepositoryInterface
{
    public function __construct(private readonly PdoConnection $db)
    {
    }

    public function findIdByCustomerNumber(string $customerNumber): ?int
    {
        $row = $this->db->fetchOne(
            'SELECT id FROM vendit_customers WHERE customer_number = :customer_number LIMIT 1',
            ['customer_number' => $customerNumber]
        );

        return is_array($row) ? (int) $row['id'] : null;
    }

    public function upsertCustomer(array $data): int
    {
        $customerNumber = trim((string) ($data['customer_number'] ?? ''));
        if ($customerNumber === '') {
            throw new \RuntimeException('customer_number is required.');
        }

        $existingId = $this->findIdByCustomerNumber($customerNumber);
        $fields = [
            'customer_number' => $customerNumber,
            'creation_datetime' => $this->normalizeDateTime($data['creation_datetime'] ?? null),
            'transactions_blocked' => $this->toBoolInt($data['transactions_blocked'] ?? false),
            'company_name' => $data['company_name'] ?? null,
            'kvk_number' => $data['kvk_number'] ?? null,
            'vat_number' => $data['vat_number'] ?? null,
            'iban_number' => $data['iban_number'] ?? null,
            'allow_on_account' => $this->toBoolInt($data['allow_on_account'] ?? false),
            'fixed_raise_percentage' => $this->normalizeDecimal($data['fixed_raise_percentage'] ?? null),
            'fixed_discount_percentage' => $this->normalizeDecimal($data['fixed_discount_percentage'] ?? null),
            'term_of_payment' => $data['term_of_payment'] ?? null,
            'pricelist_name' => $data['pricelist_name'] ?? null,
            'opt_in' => $this->toBoolInt($data['opt_in'] ?? false),
            'opt_in_date' => $this->normalizeDateTime($data['opt_in_date'] ?? null),
            'title' => $data['title'] ?? null,
            'first_name' => $data['first_name'] ?? null,
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'sex' => $data['sex'] ?? null,
            'birthdate' => $this->normalizeDateTime($data['birthdate'] ?? null),
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'street' => $data['street'] ?? null,
            'zip_code' => $data['zip_code'] ?? null,
            'house_number' => $data['house_number'] ?? null,
            'house_number_suffix' => $data['house_number_suffix'] ?? null,
            'city' => $data['city'] ?? null,
            'country' => $data['country'] ?? null,
            'country_code' => $data['country_code'] ?? null,
            'source_format' => $data['source_format'] ?? 'CustomerExport',
            'xml_hash' => hash('sha256', json_encode($data, JSON_UNESCAPED_UNICODE) ?: ''),
            'last_imported_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ];

        if ($existingId !== null) {
            $params = ['id' => $existingId];
            $set = [];
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

            return $existingId;
        }

        $columns = array_keys($fields);
        $placeholders = array_map(static fn(string $column): string => ':' . $column, $columns);
        $this->db->query(
            'INSERT INTO vendit_customers (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')',
            $fields
        );

        return (int) $this->db->lastInsertId();
    }

    public function replaceGroups(int $customerId, array $groups): void
    {
        $this->db->query('DELETE FROM vendit_customer_groups WHERE customer_id = :customer_id', ['customer_id' => $customerId]);
        foreach ($groups as $group) {
            if (!is_array($group)) {
                continue;
            }
            $groupName = trim((string) ($group['group_name'] ?? ''));
            if ($groupName === '') {
                continue;
            }

            $this->db->query(
                'INSERT INTO vendit_customer_groups (customer_id, group_name) VALUES (:customer_id, :group_name)',
                ['customer_id' => $customerId, 'group_name' => $groupName]
            );
        }
    }

    public function replaceAddresses(int $customerId, array $addresses): void
    {
        $this->db->query('DELETE FROM vendit_customer_addresses WHERE customer_id = :customer_id', ['customer_id' => $customerId]);

        foreach ($addresses as $address) {
            if (!is_array($address)) {
                continue;
            }
            $addressId = $this->insertAddress($customerId, $address);
            $this->insertContacts($addressId, $address['contacts'] ?? []);
            $this->insertPhones($addressId, null, $address['phones'] ?? []);
        }
    }

    public function listForExport(?string $since = null): array
    {
        $params = [];
        $sql = 'SELECT * FROM vendit_customers';
        if ($since !== null && trim($since) !== '') {
            $sql .= ' WHERE updated_at >= :since';
            $params['since'] = $this->normalizeDateTime($since) ?? $since;
        }
        $sql .= ' ORDER BY id ASC';

        $customers = $this->db->fetchAll($sql, $params);
        if ($customers === []) {
            return [];
        }

        foreach ($customers as &$customer) {
            $customerId = (int) $customer['id'];
            $customer['groups'] = $this->db->fetchAll(
                'SELECT group_name FROM vendit_customer_groups WHERE customer_id = :customer_id ORDER BY id ASC',
                ['customer_id' => $customerId]
            );
            $customer['addresses'] = $this->loadAddressesForCustomer($customerId);
        }
        unset($customer);

        return $customers;
    }

    public function markExported(int $customerId, string $fileName): void
    {
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $this->db->query(
            'UPDATE vendit_customers
             SET last_imported_at = :last_imported_at, xml_hash = :xml_hash
             WHERE id = :id',
            [
                'last_imported_at' => $now,
                'xml_hash' => hash('sha256', $fileName . '|' . $now),
                'id' => $customerId,
            ]
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function loadAddressesForCustomer(int $customerId): array
    {
        $addresses = $this->db->fetchAll(
            'SELECT * FROM vendit_customer_addresses WHERE customer_id = :customer_id ORDER BY id ASC',
            ['customer_id' => $customerId]
        );

        foreach ($addresses as &$address) {
            $addressId = (int) $address['id'];
            $contacts = $this->db->fetchAll(
                'SELECT * FROM vendit_customer_contacts WHERE address_id = :address_id ORDER BY id ASC',
                ['address_id' => $addressId]
            );
            foreach ($contacts as &$contact) {
                $contact['phones'] = $this->db->fetchAll(
                    'SELECT phone_number, dialing_code, phone_type_description, default_phone
                     FROM vendit_customer_phones
                     WHERE contact_id = :contact_id
                     ORDER BY id ASC',
                    ['contact_id' => (int) $contact['id']]
                );
            }
            unset($contact);

            $address['contacts'] = $contacts;
            $address['phones'] = $this->db->fetchAll(
                'SELECT phone_number, dialing_code, phone_type_description, default_phone
                 FROM vendit_customer_phones
                 WHERE address_id = :address_id AND contact_id IS NULL
                 ORDER BY id ASC',
                ['address_id' => $addressId]
            );
        }
        unset($address);

        return $addresses;
    }

    /**
     * @param array<string, mixed> $address
     */
    private function insertAddress(int $customerId, array $address): int
    {
        $this->db->query(
            'INSERT INTO vendit_customer_addresses
             (customer_id, address_type_description, street, zip_code, house_number, house_number_suffix, city, email_address, default_address, country, country_code)
             VALUES
             (:customer_id, :address_type_description, :street, :zip_code, :house_number, :house_number_suffix, :city, :email_address, :default_address, :country, :country_code)',
            [
                'customer_id' => $customerId,
                'address_type_description' => $address['address_type_description'] ?? 'Bezoekadres',
                'street' => $address['street'] ?? null,
                'zip_code' => $address['zip_code'] ?? null,
                'house_number' => $address['house_number'] ?? null,
                'house_number_suffix' => $address['house_number_suffix'] ?? null,
                'city' => $address['city'] ?? null,
                'email_address' => $address['email_address'] ?? null,
                'default_address' => $this->toBoolInt($address['default_address'] ?? false),
                'country' => $address['country'] ?? null,
                'country_code' => $address['country_code'] ?? null,
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    /**
     * @param list<array<string, mixed>> $contacts
     */
    private function insertContacts(int $addressId, array $contacts): void
    {
        foreach ($contacts as $contact) {
            if (!is_array($contact)) {
                continue;
            }
            $this->db->query(
                'INSERT INTO vendit_customer_contacts
                 (address_id, title, last_name, first_name, middle_name, sex, birthdate, email_address, additional_info, function_description, drivers_license_number, bank_account, department, iban_number, default_contact, opt_in, opt_in_date)
                 VALUES
                 (:address_id, :title, :last_name, :first_name, :middle_name, :sex, :birthdate, :email_address, :additional_info, :function_description, :drivers_license_number, :bank_account, :department, :iban_number, :default_contact, :opt_in, :opt_in_date)',
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
                    'default_contact' => $this->toBoolInt($contact['default_contact'] ?? false),
                    'opt_in' => $this->toBoolInt($contact['opt_in'] ?? false),
                    'opt_in_date' => $this->normalizeDateTime($contact['opt_in_date'] ?? null),
                ]
            );
            $contactId = (int) $this->db->lastInsertId();
            $this->insertPhones($addressId, $contactId, $contact['phones'] ?? []);
        }
    }

    /**
     * @param list<array<string, mixed>> $phones
     */
    private function insertPhones(?int $addressId, ?int $contactId, array $phones): void
    {
        foreach ($phones as $phone) {
            if (!is_array($phone)) {
                continue;
            }
            $number = trim((string) ($phone['phone_number'] ?? ''));
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
                    'default_phone' => $this->toBoolInt($phone['default_phone'] ?? false),
                ]
            );
        }
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

    private function normalizeDecimal(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        return number_format((float) $value, 4, '.', '');
    }

    private function toBoolInt(mixed $value): int
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
    }
}
