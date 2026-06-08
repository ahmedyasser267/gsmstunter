<?php
declare(strict_types=1);

namespace Integrations\Vendit\Dto;

final class ImportResult
{
    /** @param list<array<string, mixed>> $files */
    public function __construct(
        public readonly int $processed,
        public readonly int $failed,
        public readonly array $files,
        public readonly string $status,
        public readonly ?string $message = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'processed' => $this->processed,
            'failed' => $this->failed,
            'status' => $this->status,
            'message' => $this->message,
            'files' => $this->files,
        ];
    }
}
