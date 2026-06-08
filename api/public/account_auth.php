<?php
declare(strict_types=1);
require_once __DIR__ . '/../../php/config.php';

/**
 * Ensure customers table has password_hash column.
 * Safe to call on every request – uses information_schema cache.
 */
function ensure_customer_auth_columns(PDO $pdo): void
{
    static $checked = false;
    if ($checked) return;
    $checked = true;
    $cols = $pdo->query("SHOW COLUMNS FROM customers")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('password_hash', $cols, true)) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN password_hash VARCHAR(255) NULL AFTER phone");
    }
}

/**
 * Build a safe customer payload to return to the frontend.
 */
function customer_payload(array $row): array
{
    return [
        'id'    => (int)$row['id'],
        'name'  => (string)($row['full_name'] ?? ''),
        'email' => (string)($row['email'] ?? ''),
        'phone' => (string)($row['phone'] ?? ''),
    ];
}

try {
    $pdo = db();
    ensure_customer_auth_columns($pdo);
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    /* ── Session check ── */
    if ($method === 'GET') {
        if (!empty($_SESSION['customer_id'])) {
            $stmt = $pdo->prepare("SELECT id, full_name, email, phone FROM customers WHERE id=:id LIMIT 1");
            $stmt->execute([':id' => (int)$_SESSION['customer_id']]);
            $row = $stmt->fetch();
            if ($row) {
                json_response(['ok' => true, 'logged_in' => true, 'customer' => customer_payload($row)]);
            }
        }
        json_response(['ok' => true, 'logged_in' => false]);
    }

    $data = read_json_body();
    $action = (string)($data['action'] ?? '');

    /* ─────────── REGISTER ─────────── */
    if ($action === 'register') {
        /* Accept both 'name' and 'full_name' keys from the frontend */
        $name     = trim((string)($data['name'] ?? $data['full_name'] ?? ''));
        $email    = strtolower(trim((string)($data['email'] ?? '')));
        $phone    = trim((string)($data['phone'] ?? ''));
        $password = (string)($data['password'] ?? '');

        if ($email === '' || $password === '') {
            json_response(['ok' => false, 'error' => 'E-mail en wachtwoord zijn verplicht'], 422);
        }
        if (strlen($password) < 6) {
            json_response(['ok' => false, 'error' => 'Wachtwoord moet minimaal 6 tekens hebben'], 422);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_response(['ok' => false, 'error' => 'Ongeldig e-mailadres'], 422);
        }

        $exists = $pdo->prepare("SELECT id FROM customers WHERE email=:e LIMIT 1");
        $exists->execute([':e' => $email]);
        if ($exists->fetch()) {
            json_response(['ok' => false, 'error' => 'Dit e-mailadres is al geregistreerd'], 409);
        }

        $stmt = $pdo->prepare("
          INSERT INTO customers (full_name, email, phone, password_hash)
          VALUES (:n, :e, :p, :h)
        ");
        $stmt->execute([
            ':n' => $name !== '' ? $name : null,
            ':e' => $email,
            ':p' => $phone !== '' ? $phone : null,
            ':h' => password_hash($password, PASSWORD_DEFAULT),
        ]);
        $newId = (int)$pdo->lastInsertId();

        session_regenerate_id(true);
        $_SESSION['customer_id']    = $newId;
        $_SESSION['customer_email'] = $email;

        json_response([
            'ok'       => true,
            'customer' => [
                'id'    => $newId,
                'name'  => $name,
                'email' => $email,
                'phone' => $phone,
            ],
        ]);
    }

    /* ─────────── LOGIN ─────────── */
    if ($action === 'login') {
        $email    = strtolower(trim((string)($data['email'] ?? '')));
        $password = (string)($data['password'] ?? '');

        if ($email === '' || $password === '') {
            json_response(['ok' => false, 'error' => 'E-mail en wachtwoord zijn verplicht'], 422);
        }

        $stmt = $pdo->prepare("SELECT id, full_name, email, phone, password_hash FROM customers WHERE email=:e LIMIT 1");
        $stmt->execute([':e' => $email]);
        $row = $stmt->fetch();

        if (!$row || empty($row['password_hash']) || !password_verify($password, (string)$row['password_hash'])) {
            json_response(['ok' => false, 'error' => 'Onjuiste inloggegevens. Controleer je e-mail en wachtwoord.'], 401);
        }

        session_regenerate_id(true);
        $_SESSION['customer_id']    = (int)$row['id'];
        $_SESSION['customer_email'] = (string)$row['email'];

        json_response([
            'ok'       => true,
            'customer' => customer_payload($row),
        ]);
    }

    /* ─────────── LOGOUT ─────────── */
    if ($action === 'logout') {
        unset($_SESSION['customer_id'], $_SESSION['customer_email']);
        json_response(['ok' => true]);
    }

    json_response(['ok' => false, 'error' => 'Onbekende actie'], 422);

} catch (Throwable $e) {
    json_throwable($e);
}
