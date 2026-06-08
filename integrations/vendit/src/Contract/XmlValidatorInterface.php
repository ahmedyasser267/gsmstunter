<?php
declare(strict_types=1);

namespace Integrations\Vendit\Contract;

interface XmlValidatorInterface
{
    /** @return list<string> */
    public function validateCustomerImport(array $customer): array;

    /** @return list<string> */
    public function validateOrderImport(array $order): array;

    /** @return list<string> */
    public function validateCustomerExport(array $customer): array;

    /** @return list<string> */
    public function validateOrderExport(array $order): array;
}
