<?php
declare(strict_types=1);
require_once __DIR__ . '/../../php/config.php';

try {
    $data = read_json_body();
    $username = (string)($data['username'] ?? '');
    $password = (string)($data['password'] ?? '');
    if ($username === '' || $password === '') {
        json_response(['ok' => false, 'error' => 'Missing credentials'], 422);
    }

    $stmt = db()->prepare("SELECT id, username, password_hash FROM admins WHERE username = :u LIMIT 1");
    $stmt->execute([':u' => $username]);
    $admin = $stmt->fetch();
    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        json_response(['ok' => false, 'error' => 'Invalid credentials'], 401);
    }

    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int)$admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    json_response(['ok' => true, 'username' => $admin['username']]);
} catch (Throwable $e) {
    json_throwable($e, 'Login failed.');
}

