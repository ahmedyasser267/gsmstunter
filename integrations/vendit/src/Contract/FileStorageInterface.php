<?php
declare(strict_types=1);

namespace Integrations\Vendit\Contract;

interface FileStorageInterface
{
    /** @return list<string> */
    public function listImportFiles(string $pattern = '*.xml'): array;

    public function readImportFile(string $path): string;

    public function archiveImportFile(string $path): void;

    public function writeExportFile(string $fileName, string $content): string;

    /** @return list<string> */
    public function listExportFiles(): array;
}
