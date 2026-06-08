<?php
declare(strict_types=1);

namespace Integrations\Vendit\Dto;

final class ExportResult
{
    /** @param list<string> $files */
    public function __construct(
        public readonly int $exported,
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
            'exported' => $this->exported,
            'failed' => $this->failed,
            'status' => $this->status,
            'message' => $this->message,
            'files' => $this->files,
        ];
    }
}
