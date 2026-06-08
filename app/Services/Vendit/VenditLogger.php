<?php
declare(strict_types=1);

namespace App\Services\Vendit;

use DateTimeImmutable;
use Throwable;

/**
 * Persists sync logs to MySQL and optional daily log files.
 */
final class VenditLogger
{
    private ?int $currentLogId = null;

    public function __construct(
        private readonly Database $db,
        private readonly string $logsFolder
    ) {
        if (!is_dir($this->logsFolder)) {
            mkdir($this->logsFolder, 0755, true);
        }
    }

    /**
     * @return int Sync log row id
     */
    public function start(
        string $syncType,
        string $direction,
        ?string $fileName = null,
        ?string $filePath = null
    ): int {
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $this->db->query(
            'INSERT INTO vendit_sync_logs
             (sync_type, direction, file_name, file_path, status, started_at)
             VALUES (:sync_type, :direction, :file_name, :file_path, :status, :started_at)',
            [
                'sync_type' => $syncType,
                'direction' => $direction,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'status' => 'started',
                'started_at' => $now,
            ]
        );
        $this->currentLogId = (int) $this->db->lastInsertId();
        $this->writeFile('INFO', sprintf('Started %s (%s)', $syncType, $direction));
        return $this->currentLogId;
    }

    public function setValidation(string $status, ?array $errors = null): void
    {
        if ($this->currentLogId === null) {
            return;
        }
        $this->db->query(
            'UPDATE vendit_sync_logs
             SET validation_status = :validation_status, validation_errors = :validation_errors
             WHERE id = :id',
            [
                'validation_status' => $status,
                'validation_errors' => $errors !== null ? json_encode($errors, JSON_UNESCAPED_UNICODE) : null,
                'id' => $this->currentLogId,
            ]
        );
    }

    public function complete(
        string $status,
        int $processed = 0,
        int $failed = 0,
        ?string $message = null
    ): void {
        if ($this->currentLogId === null) {
            return;
        }
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $this->db->query(
            'UPDATE vendit_sync_logs
             SET status = :status,
                 records_processed = :records_processed,
                 records_failed = :records_failed,
                 message = :message,
                 completed_at = :completed_at
             WHERE id = :id',
            [
                'status' => $status,
                'records_processed' => $processed,
                'records_failed' => $failed,
                'message' => $message,
                'completed_at' => $now,
                'id' => $this->currentLogId,
            ]
        );
        $this->writeFile('INFO', sprintf(
            'Completed status=%s processed=%d failed=%d %s',
            $status,
            $processed,
            $failed,
            $message ?? ''
        ));
    }

    public function error(string $message, ?Throwable $e = null): void
    {
        $full = $message;
        if ($e !== null) {
            $full .= ' | ' . $e->getMessage();
        }
        $this->writeFile('ERROR', $full);

        if ($this->currentLogId !== null) {
            $this->db->query(
                'UPDATE vendit_sync_logs SET message = CONCAT(COALESCE(message, ""), :msg) WHERE id = :id',
                ['msg' => "\n" . $full, 'id' => $this->currentLogId]
            );
        }
    }

    public function info(string $message): void
    {
        $this->writeFile('INFO', $message);
    }

    private function writeFile(string $level, string $message): void
    {
        $file = $this->logsFolder . '/vendit-' . date('Y-m-d') . '.log';
        $line = sprintf("[%s] [%s] %s\n", date('Y-m-d H:i:s'), $level, $message);
        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recentLogs(int $limit = 100): array
    {
        $limit = max(1, $limit);
        return $this->db->fetchAll(
            'SELECT * FROM vendit_sync_logs ORDER BY started_at DESC, id DESC LIMIT ' . $limit
        );
    }

    public function lastSyncDate(?string $syncType = null): ?string
    {
        if ($syncType !== null) {
            $row = $this->db->fetchOne(
                'SELECT MAX(completed_at) AS last_sync
                 FROM vendit_sync_logs
                 WHERE sync_type = :sync_type AND status IN ("success", "partial")',
                ['sync_type' => $syncType]
            );
        } else {
            $row = $this->db->fetchOne(
                'SELECT MAX(completed_at) AS last_sync
                 FROM vendit_sync_logs
                 WHERE status IN ("success", "partial")'
            );
        }
        return is_array($row) && !empty($row['last_sync']) ? (string) $row['last_sync'] : null;
    }
}
