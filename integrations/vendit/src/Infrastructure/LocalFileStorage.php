<?php
declare(strict_types=1);

namespace Integrations\Vendit\Infrastructure;

use Integrations\Vendit\Contract\FileStorageInterface;
use RuntimeException;

final class LocalFileStorage implements FileStorageInterface
{
    public function __construct(
        private readonly string $importPath,
        private readonly string $exportPath,
        private readonly string $archivePath,
    ) {
        foreach ([$this->importPath, $this->exportPath, $this->archivePath] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    public function listImportFiles(string $pattern = '*.xml'): array
    {
        $files = glob(rtrim($this->importPath, '/\\') . '/*.{xml,XML}', GLOB_BRACE) ?: [];
        sort($files);
        return $files;
    }

    public function readImportFile(string $path): string
    {
        if (!is_readable($path)) {
            throw new RuntimeException('Cannot read import file: ' . $path);
        }
        $content = file_get_contents($path);
        if ($content === false) {
            throw new RuntimeException('Failed to read import file: ' . $path);
        }
        return $content;
    }

    public function archiveImportFile(string $path): void
    {
        if (!is_file($path)) {
            return;
        }
        $target = rtrim($this->archivePath, '/\\') . '/' . date('Ymd-His') . '-' . basename($path);
        if (!@rename($path, $target)) {
            throw new RuntimeException('Failed to archive file: ' . basename($path));
        }
    }

    public function writeExportFile(string $fileName, string $content): string
    {
        $safe = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName) ?: 'export.xml';
        $path = rtrim($this->exportPath, '/\\') . '/' . $safe;
        if (file_put_contents($path, $content) === false) {
            throw new RuntimeException('Failed to write export file: ' . $safe);
        }
        return $path;
    }

    public function listExportFiles(): array
    {
        $files = glob(rtrim($this->exportPath, '/\\') . '/*.{xml,XML}', GLOB_BRACE) ?: [];
        rsort($files);
        return $files;
    }
}
