<?php
declare(strict_types=1);

/**
 * Set GSM_APP_DEBUG=1 in the environment to expose exception messages in JSON errors (dev only).
 */
function app_debug(): bool
{
    static $v = null;
    if ($v !== null) {
        return $v;
    }
    $v = getenv('GSM_APP_DEBUG') === '1';
    return $v;
}

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
}

const DB_HOST = '127.0.0.1';
const DB_PORT = 3306;
const DB_NAME = 'gsmstunter';
const DB_USER = 'root';
const DB_PASS = '';

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
}

function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if (!$raw) {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function require_admin(): void
{
    if (empty($_SESSION['admin_id'])) {
        json_response(['ok' => false, 'error' => 'Unauthorized'], 401);
    }
}

/**
 * Map unexpected exceptions to JSON without leaking internals in production.
 */
function json_throwable(Throwable $e, string $publicMessage = 'Er is een technische fout opgetreden.', int $status = 500): void
{
    $msg = app_debug() ? $e->getMessage() : $publicMessage;
    json_response(['ok' => false, 'error' => $msg], $status);
}

