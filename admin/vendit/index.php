<?php
declare(strict_types=1);

require_once __DIR__ . '/../../php/config.php';
require_once __DIR__ . '/service.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$flashPayload = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? $_POST['vendit_action'] ?? '';
    if ($action !== '') {
        try {
            $flashPayload = vendit_admin_process_post($action);
        } catch (Throwable $e) {
            $flashPayload = ['flash' => 'Error: ' . $e->getMessage(), 'flashType' => 'error'];
        }
    }
}

$vendit = vendit_admin_dashboard_data($flashPayload);
?>
<!doctype html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Vendit Integration</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    body{font-family:Inter,Arial,sans-serif;background:#f0f4f8;color:#0f172a;margin:0;padding:24px}
    .wrap{max-width:1200px;margin:0 auto}
    h1{margin:0 0 8px}
    .muted{color:#64748b;margin-bottom:24px}
    .topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
    .btn{border:none;border-radius:8px;padding:10px 16px;font-weight:600;cursor:pointer;text-decoration:none;display:inline-block;background:#e2e8f0;color:#0f172a}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="topbar">
      <div>
        <h1>Vendit E-commerce 2.0</h1>
        <p class="muted">Local folder mode — SFTP configured via <code>integrations/vendit/config.php</code> when ready.</p>
      </div>
      <a class="btn" href="../index.php#vendit"><i class="fas fa-arrow-left"></i> Admin dashboard</a>
    </div>
    <?php include __DIR__ . '/panel.php'; ?>
  </div>
</body>
</html>
