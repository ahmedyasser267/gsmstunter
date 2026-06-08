<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Login</title>
  <style>
    body{font-family:Arial,sans-serif;background:#0f172a;color:#fff;display:grid;place-items:center;height:100vh;margin:0}
    .card{background:#1e293b;padding:24px;border-radius:12px;max-width:360px;width:100%}
    input{width:100%;padding:10px;margin:8px 0;border-radius:8px;border:1px solid #334155;background:#0f172a;color:#fff}
    button{width:100%;padding:10px;border:none;border-radius:8px;background:#f97316;color:#fff;font-weight:700;cursor:pointer}
    small{opacity:.8}
  </style>
</head>
<body>
  <div class="card">
    <h2>Verkopen Admin</h2>
    <small>Default: admin / admin123</small>
    <input id="u" placeholder="Username" value="admin">
    <input id="p" placeholder="Password" type="password" value="admin123">
    <button id="b">Login</button>
    <p id="m"></p>
  </div>
  <script>
    document.getElementById('b').onclick = async () => {
      const res = await fetch('../api/admin/login.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({username: u.value, password: p.value})
      });
      const data = await res.json();
      if (data.ok) location.href = 'index.php';
      else m.textContent = data.error || 'Login failed';
    };
  </script>
</body>
</html>

