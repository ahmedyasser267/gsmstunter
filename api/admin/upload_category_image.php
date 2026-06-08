<?php
declare(strict_types=1);
require_once __DIR__ . '/../../php/config.php';
require_admin();

try {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
    }

    if (!isset($_FILES['image'])) {
        json_response(['ok' => false, 'error' => 'No image uploaded'], 422);
    }

    $file = $_FILES['image'];
    if (!is_array($file) || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        json_response(['ok' => false, 'error' => 'Upload failed'], 422);
    }

    $tmp = (string)($file['tmp_name'] ?? '');
    $size = (int)($file['size'] ?? 0);
    if ($tmp === '' || $size <= 0) {
        json_response(['ok' => false, 'error' => 'Invalid image'], 422);
    }

    if ($size > 6 * 1024 * 1024) {
        json_response(['ok' => false, 'error' => 'Image is too large (max 6MB)'], 422);
    }

    $mime = mime_content_type($tmp) ?: '';
    $extMap = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    if (!isset($extMap[$mime])) {
        json_response(['ok' => false, 'error' => 'Unsupported image type'], 422);
    }

    $uploadsDir = __DIR__ . '/../../uploads/categories';
    if (!is_dir($uploadsDir) && !mkdir($uploadsDir, 0775, true) && !is_dir($uploadsDir)) {
        json_response(['ok' => false, 'error' => 'Cannot create upload folder'], 500);
    }

    $ext = $extMap[$mime];
    $name = 'category_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $fullPath = $uploadsDir . '/' . $name;
    if (!move_uploaded_file($tmp, $fullPath)) {
        json_response(['ok' => false, 'error' => 'Failed to save image'], 500);
    }

    $publicUrl = '../uploads/categories/' . $name;
    json_response(['ok' => true, 'url' => $publicUrl]);
} catch (Throwable $e) {
    json_throwable($e, 'Request failed.');
}

