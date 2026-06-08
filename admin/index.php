<?php
declare(strict_types=1);
require_once __DIR__ . '/../php/config.php';
if (empty($_SESSION['admin_id'])) {
  header('Location: login.php');
  exit;
}

$venditFlash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['vendit_action'])) {
  require_once __DIR__ . '/vendit/service.php';
  try {
    $venditFlash = vendit_admin_process_post((string) $_POST['vendit_action']);
  } catch (Throwable $e) {
    $venditFlash = ['flash' => 'Vendit: ' . $e->getMessage(), 'flashType' => 'error'];
  }
  $_SESSION['vendit_flash'] = $venditFlash;
  header('Location: index.php#vendit');
  exit;
}

if (!empty($_SESSION['vendit_flash'])) {
  $venditFlash = $_SESSION['vendit_flash'];
  unset($_SESSION['vendit_flash']);
}

$venditPendingBadge = 0;
try {
  require_once __DIR__ . '/vendit/service.php';
  $venditPendingBadge = vendit_admin_pending_count();
} catch (Throwable) {
  $venditPendingBadge = 0;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>GSMStunter Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/admin-products.css">
  <style>
    /* ═══ RESET & TOKENS ═══ */
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{
      --sidebar-w:260px;
      --sidebar-bg:#080E1A;
      --sidebar-border:rgba(255,255,255,.06);
      --sidebar-text:rgba(255,255,255,.55);
      --sidebar-text-active:#fff;
      --sidebar-active-bg:rgba(13,124,102,.25);
      --sidebar-active-border:#0d7c66;

      --primary:#0d7c66;--primary-dark:#095C4B;--primary-light:#e8f7f3;
      --accent:#FF6B2C;--accent-light:#fff3ed;
      --danger:#ef4444;--danger-light:#fef2f2;
      --warn:#F59E0B;--warn-light:#fffbeb;
      --success:#22c55e;--success-light:#f0fdf4;
      --info:#3b82f6;--info-light:#eff6ff;

      --bg:#F0F4F8;
      --surface:#ffffff;
      --surface-raised:#ffffff;
      --border:#E8EDF2;
      --border-strong:#D1D9E0;
      --text:#0F172A;
      --text-2:#334155;
      --text-muted:#64748B;
      --text-faint:#94A3B8;

      --radius-sm:8px;--radius:12px;--radius-lg:16px;--radius-xl:20px;
      --shadow-xs:0 1px 2px rgba(0,0,0,.04);
      --shadow-sm:0 2px 8px rgba(0,0,0,.06);
      --shadow:0 4px 16px rgba(0,0,0,.08);
      --shadow-lg:0 12px 32px rgba(0,0,0,.1);
      --shadow-xl:0 24px 48px rgba(0,0,0,.14);
      --transition:.2s ease;
      --font:'Inter',-apple-system,BlinkMacSystemFont,sans-serif;
    }
    html{height:100%;scroll-behavior:smooth}
    body{font-family:var(--font);background:var(--bg);color:var(--text);height:100%;font-size:14px;line-height:1.5;-webkit-font-smoothing:antialiased}

    /* ═══ LAYOUT ═══ */
    .app{display:flex;min-height:100vh}

    /* ═══ SIDEBAR ═══ */
    .sidebar{
      width:var(--sidebar-w);flex-shrink:0;
      background:var(--sidebar-bg);
      display:flex;flex-direction:column;
      position:fixed;top:0;left:0;height:100vh;
      border-right:1px solid var(--sidebar-border);
      z-index:50;overflow:hidden;
      transition:width var(--transition);
    }
    .sidebar::before{
      content:'';position:absolute;inset:0;
      background:radial-gradient(ellipse 80% 50% at 50% -10%, rgba(13,124,102,.2) 0%, transparent 70%);
      pointer-events:none;
    }
    .sidebar-header{
      padding:20px 18px 16px;
      border-bottom:1px solid var(--sidebar-border);
      position:relative;z-index:1;
    }
    .sidebar-logo{
      display:flex;align-items:center;gap:10px;text-decoration:none;
    }
    .sidebar-logo-icon{
      width:38px;height:38px;border-radius:10px;
      background:linear-gradient(135deg,var(--primary),var(--primary-dark));
      display:flex;align-items:center;justify-content:center;
      font-size:16px;color:white;flex-shrink:0;
      box-shadow:0 4px 12px rgba(13,124,102,.4);
    }
    .sidebar-logo-text{font-size:1.1rem;font-weight:800;color:white;letter-spacing:-.3px;line-height:1.1}
    .sidebar-logo-text span{color:rgba(255,255,255,.45);font-weight:400}
    .sidebar-logo-badge{
      margin-left:auto;font-size:.6rem;font-weight:700;
      background:rgba(13,124,102,.3);color:var(--primary-light);
      padding:2px 7px;border-radius:999px;border:1px solid rgba(13,124,102,.4);
      letter-spacing:.06em;
    }

    .sidebar-nav{flex:1;padding:12px 12px;overflow-y:auto;position:relative;z-index:1}
    .nav-section-label{
      font-size:.65rem;font-weight:700;letter-spacing:.12em;
      text-transform:uppercase;color:rgba(255,255,255,.2);
      padding:12px 8px 6px;
    }
    .nav-item{
      display:flex;align-items:center;gap:10px;
      padding:9px 12px;border-radius:var(--radius-sm);
      color:var(--sidebar-text);text-decoration:none;
      font-size:.875rem;font-weight:500;
      transition:all var(--transition);
      margin-bottom:2px;cursor:pointer;
      border:1px solid transparent;
      position:relative;
    }
    .nav-item:hover{color:rgba(255,255,255,.85);background:rgba(255,255,255,.05)}
    .nav-item.active{
      color:var(--sidebar-text-active);
      background:var(--sidebar-active-bg);
      border-color:rgba(13,124,102,.3);
    }
    .nav-item.active::before{
      content:'';position:absolute;left:0;top:20%;bottom:20%;
      width:3px;border-radius:0 3px 3px 0;background:var(--primary);
    }
    .nav-item__icon{
      width:32px;height:32px;border-radius:8px;
      display:flex;align-items:center;justify-content:center;
      font-size:.875rem;flex-shrink:0;
      background:rgba(255,255,255,.05);
      transition:all var(--transition);
    }
    .nav-item.active .nav-item__icon{background:rgba(13,124,102,.25);color:var(--primary-light);}
    .nav-item__label{flex:1}
    .nav-item__badge{
      font-size:.65rem;font-weight:700;min-width:18px;height:18px;
      border-radius:999px;background:var(--primary);color:white;
      display:flex;align-items:center;justify-content:center;padding:0 5px;
    }

    .sidebar-footer{
      padding:14px 12px;border-top:1px solid var(--sidebar-border);
      position:relative;z-index:1;
    }
    .sidebar-user{
      display:flex;align-items:center;gap:10px;padding:8px;
      border-radius:var(--radius-sm);cursor:pointer;
      transition:background var(--transition);
    }
    .sidebar-user:hover{background:rgba(255,255,255,.05)}
    .sidebar-user-avatar{
      width:34px;height:34px;border-radius:50%;
      background:linear-gradient(135deg,var(--primary),#10b981);
      display:flex;align-items:center;justify-content:center;
      font-size:.8rem;font-weight:700;color:white;flex-shrink:0;
    }
    .sidebar-user-info{flex:1}
    .sidebar-user-name{font-size:.825rem;font-weight:600;color:rgba(255,255,255,.85)}
    .sidebar-user-role{font-size:.72rem;color:rgba(255,255,255,.35)}

    /* ═══ MAIN CONTENT ═══ */
    .main{
      flex:1;margin-left:var(--sidebar-w);
      display:flex;flex-direction:column;min-height:100vh;
    }

    /* ═══ TOPBAR ═══ */
    .topbar{
      background:var(--surface);
      border-bottom:1px solid var(--border);
      padding:0 28px;height:64px;
      display:flex;align-items:center;gap:16px;
      position:sticky;top:0;z-index:40;
      box-shadow:var(--shadow-xs);
    }
    .topbar-title{
      font-size:1.05rem;font-weight:700;color:var(--text);
      flex:1;
    }
    .topbar-title span{color:var(--text-muted);font-weight:400;font-size:.9rem}
    .topbar-actions{display:flex;align-items:center;gap:10px}
    .tb-btn{
      height:36px;padding:0 14px;border-radius:var(--radius-sm);
      border:1px solid var(--border);background:var(--surface);
      font-family:var(--font);font-size:.82rem;font-weight:600;
      color:var(--text-2);cursor:pointer;
      display:flex;align-items:center;gap:6px;
      transition:all var(--transition);white-space:nowrap;
    }
    .tb-btn:hover{border-color:var(--border-strong);background:var(--bg)}
    .tb-btn.primary{
      background:linear-gradient(135deg,var(--primary),var(--primary-dark));
      color:white;border-color:transparent;
    }
    .tb-btn.primary:hover{filter:brightness(1.06)}
    .tb-btn.danger{background:var(--danger-light);color:var(--danger);border-color:rgba(239,68,68,.2)}
    .tb-btn.danger:hover{background:var(--danger);color:white}
    .tb-divider{width:1px;height:28px;background:var(--border)}

    /* ═══ CONTENT AREA ═══ */
    .content{padding:24px 28px;flex:1}

    /* ═══ KPI CARDS ═══ */
    .kpi-grid{
      display:grid;grid-template-columns:repeat(auto-fit,minmax(165px,1fr));
      gap:16px;margin-bottom:24px;
    }
    .kpi-card{
      background:var(--surface);border:1px solid var(--border);
      border-radius:var(--radius-lg);padding:18px 20px;
      box-shadow:var(--shadow-sm);transition:all var(--transition);
      position:relative;overflow:hidden;
    }
    .kpi-card:hover{transform:translateY(-2px);box-shadow:var(--shadow)}
    .kpi-card::after{
      content:'';position:absolute;top:0;right:0;
      width:80px;height:80px;border-radius:50%;
      transform:translate(20px,-20px);opacity:.06;
    }
    .kpi-card.green::after{background:var(--primary)}
    .kpi-card.orange::after{background:var(--accent)}
    .kpi-card.blue::after{background:var(--info)}
    .kpi-card.purple::after{background:#8b5cf6}
    .kpi-card__icon{
      width:36px;height:36px;border-radius:9px;
      display:flex;align-items:center;justify-content:center;
      font-size:.95rem;margin-bottom:12px;
    }
    .kpi-card.green .kpi-card__icon{background:var(--primary-light);color:var(--primary)}
    .kpi-card.orange .kpi-card__icon{background:var(--accent-light);color:var(--accent)}
    .kpi-card.blue .kpi-card__icon{background:var(--info-light);color:var(--info)}
    .kpi-card.purple .kpi-card__icon{background:#f3f0ff;color:#7c3aed}
    .kpi-card.warn .kpi-card__icon{background:var(--warn-light);color:var(--warn)}
    .kpi-card.warn::after{background:var(--warn)}
    .kpi-card__val{
      font-size:1.75rem;font-weight:800;color:var(--text);
      line-height:1;letter-spacing:-.5px;margin-bottom:4px;
    }
    .kpi-card__label{
      font-size:.72rem;font-weight:600;text-transform:uppercase;
      letter-spacing:.07em;color:var(--text-muted);
    }
    .kpi-card__trend{
      display:inline-flex;align-items:center;gap:3px;
      font-size:.75rem;font-weight:600;margin-top:8px;
      padding:3px 7px;border-radius:999px;
    }
    .kpi-card__trend.up{background:var(--success-light);color:#166534}
    .kpi-card__trend.down{background:var(--danger-light);color:#991b1b}

    /* ═══ CHARTS GRID ═══ */
    .charts-grid{display:grid;grid-template-columns:1.2fr .8fr;gap:16px;margin-bottom:24px}
    .chart-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);padding:20px;box-shadow:var(--shadow-sm)}
    .chart-card__header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}
    .chart-card__title{font-size:.925rem;font-weight:700;color:var(--text)}
    .chart-card__sub{font-size:.76rem;color:var(--text-muted);margin-top:2px}
    .chart-badge{font-size:.72rem;font-weight:700;padding:4px 10px;border-radius:999px;background:var(--primary-light);color:var(--primary)}
    canvas{display:block;width:100%!important}

    /* ═══ DATA CARDS (mini stats) ═══ */
    .mini-cards-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px}
    .mini-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);padding:18px 20px;box-shadow:var(--shadow-sm)}
    .mini-card__title{font-size:.85rem;font-weight:700;color:var(--text);margin-bottom:12px;display:flex;align-items:center;gap:8px}
    .mini-card__title i{color:var(--primary)}
    .mini-list-item{
      display:flex;align-items:center;justify-content:space-between;
      padding:7px 0;border-bottom:1px solid var(--border);font-size:.85rem;
    }
    .mini-list-item:last-child{border:none;padding-bottom:0}
    .mini-list-item__label{color:var(--text-2)}
    .mini-list-item__val{font-weight:600;color:var(--text)}

    /* ═══ SECTION TABS ═══ */
    .tabs-bar{
      display:flex;align-items:center;gap:4px;
      background:var(--surface);border:1px solid var(--border);
      border-radius:var(--radius-lg);padding:6px;
      margin-bottom:20px;flex-wrap:wrap;
      box-shadow:var(--shadow-xs);
    }
    .tab-btn{
      display:flex;align-items:center;gap:6px;
      padding:7px 14px;border-radius:var(--radius-sm);
      font-family:var(--font);font-size:.82rem;font-weight:600;
      border:none;cursor:pointer;color:var(--text-muted);
      background:transparent;transition:all var(--transition);white-space:nowrap;
    }
    .tab-btn:hover{color:var(--text);background:var(--bg)}
    .tab-btn.active{background:var(--primary);color:white;box-shadow:0 2px 8px rgba(13,124,102,.3)}
    .tab-btn i{font-size:.8rem}

    /* ═══ PANE SECTIONS ═══ */
    .pane{display:none}
    .pane.active{display:block}

    /* ═══ SECTION CARD ═══ */
    .sec-card{
      background:var(--surface);border:1px solid var(--border);
      border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);
      overflow:hidden;margin-bottom:16px;
    }
    .sec-card__header{
      padding:18px 22px;border-bottom:1px solid var(--border);
      display:flex;align-items:center;gap:12px;
    }
    .sec-card__icon{
      width:38px;height:38px;border-radius:10px;
      background:var(--primary-light);color:var(--primary);
      display:flex;align-items:center;justify-content:center;
      font-size:.95rem;flex-shrink:0;
    }
    .sec-card__title{font-size:1rem;font-weight:700;color:var(--text)}
    .sec-card__sub{font-size:.78rem;color:var(--text-muted);margin-top:2px}
    .sec-card__body{padding:20px 22px}
    .sec-card__actions{
      padding:14px 22px;border-top:1px solid var(--border);
      display:flex;align-items:center;gap:8px;background:var(--bg);
    }

    /* ═══ FORM ELEMENTS ═══ */
    .form-section-label{
      font-size:.72rem;font-weight:700;letter-spacing:.1em;
      text-transform:uppercase;color:var(--text-muted);
      margin:16px 0 10px;padding-bottom:6px;border-bottom:1px solid var(--border);
    }
    .form-section-label:first-child{margin-top:0}
    .fg{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
    .fg.cols-3{grid-template-columns:repeat(3,minmax(0,1fr))}
    .fg.cols-2{grid-template-columns:1fr 1fr}
    .fg.cols-1{grid-template-columns:1fr}
    .span-2{grid-column:span 2}
    .span-3{grid-column:span 3}
    .span-4{grid-column:span 4}
    .field{display:flex;flex-direction:column;gap:5px}
    .field label{font-size:.78rem;font-weight:600;color:var(--text-2)}
    .field-wrap{position:relative}
    .field-icon{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--text-faint);font-size:.8rem;pointer-events:none;transition:color var(--transition)}
    .field-wrap:focus-within .field-icon{color:var(--primary)}
    input:not([type=file]):not([type=checkbox]):not([type=radio]),select,textarea{
      width:100%;font-family:var(--font);font-size:.875rem;color:var(--text);
      background:var(--bg);border:1.5px solid var(--border);
      border-radius:var(--radius-sm);padding:9px 12px;
      transition:border-color var(--transition),box-shadow var(--transition),background var(--transition);
      outline:none;
    }
    .has-icon input:not([type=file]){padding-left:32px}
    input:not([type=file]):not([type=checkbox]):focus,select:focus,textarea:focus{
      border-color:var(--primary);background:#fff;
      box-shadow:0 0 0 3px rgba(13,124,102,.1);
    }
    textarea{resize:vertical;min-height:68px}
    select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center;background-size:14px;padding-right:32px}

    /* ═══ IMAGE UPLOAD ═══ */
    .img-upload-row{
      display:grid;grid-template-columns:108px 1fr;gap:14px;align-items:start;
      padding:14px;background:var(--bg);border-radius:var(--radius-sm);
      border:1.5px dashed var(--border);grid-column:span 4;
    }
    .img-preview-box{
      width:108px;height:90px;border-radius:var(--radius-sm);
      background:var(--surface);border:1px solid var(--border);
      display:flex;align-items:center;justify-content:center;
      overflow:hidden;color:var(--text-faint);font-size:1.5rem;
    }
    .img-preview-box img{width:100%;height:100%;object-fit:cover;border-radius:var(--radius-sm)}
    .img-upload-controls{display:flex;flex-direction:column;gap:8px}
    .img-upload-controls input[type=file]{
      font-size:.8rem;color:var(--text-muted);
      background:var(--surface);border:1px solid var(--border);
      border-radius:var(--radius-sm);padding:6px 10px;
      width:100%;cursor:pointer;
    }

    /* ═══ ACTION BUTTONS ═══ */
    .btn{
      display:inline-flex;align-items:center;gap:6px;
      padding:8px 16px;border-radius:var(--radius-sm);
      font-family:var(--font);font-size:.825rem;font-weight:600;
      border:1px solid transparent;cursor:pointer;
      transition:all var(--transition);white-space:nowrap;
    }
    .btn i{font-size:.8rem}
    .btn.primary{background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:white;box-shadow:0 2px 8px rgba(13,124,102,.25)}
    .btn.primary:hover{filter:brightness(1.06);transform:translateY(-1px)}
    .btn.secondary{background:var(--surface);color:var(--text-2);border-color:var(--border)}
    .btn.secondary:hover{border-color:var(--border-strong);background:var(--bg)}
    .btn.danger{background:var(--danger-light);color:var(--danger);border-color:rgba(239,68,68,.2)}
    .btn.danger:hover{background:var(--danger);color:white}
    .btn.ghost{background:transparent;color:var(--text-muted);border-color:var(--border)}
    .btn.ghost:hover{background:var(--bg);color:var(--text)}
    .btn.sm{padding:5px 10px;font-size:.76rem}
    .btn.xs{padding:4px 8px;font-size:.72rem}

    /* ═══ STATUS MESSAGE ═══ */
    .status-msg{
      font-size:.8rem;font-weight:500;margin-left:8px;
      padding:5px 12px;border-radius:999px;
    }
    .status-msg.ok{background:var(--success-light);color:#166534}
    .status-msg.err{background:var(--danger-light);color:#991b1b}

    /* ═══ TABLES ═══ */
    .table-container{overflow-x:auto;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--surface)}
    table{width:100%;border-collapse:collapse;font-size:.825rem}
    thead th{
      padding:10px 14px;text-align:left;
      background:var(--bg);border-bottom:1px solid var(--border);
      font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;
      color:var(--text-muted);white-space:nowrap;
      position:sticky;top:0;z-index:1;
    }
    tbody td{padding:10px 14px;border-bottom:1px solid var(--border);color:var(--text-2);vertical-align:middle}
    tbody tr:last-child td{border:none}
    tbody tr:hover td{background:rgba(13,124,102,.025);color:var(--text)}
    .table-thumb{
      width:42px;height:42px;border-radius:8px;object-fit:cover;
      border:1px solid var(--border);background:var(--bg);
    }
    .table-placeholder{
      width:42px;height:42px;border-radius:8px;border:1px solid var(--border);
      background:var(--bg);display:inline-flex;align-items:center;justify-content:center;
      color:var(--text-faint);font-size:.875rem;
    }

    /* ═══ STATUS PILLS ═══ */
    .pill{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:999px;font-size:.72rem;font-weight:700;letter-spacing:.03em}
    .pill::before{content:'';width:5px;height:5px;border-radius:50%;flex-shrink:0}
    .pill.new{background:#e0f2fe;color:#0369a1}.pill.new::before{background:#0369a1}
    .pill.processing{background:#fef3c7;color:#92400e}.pill.processing::before{background:#d97706}
    .pill.shipped{background:#ede9fe;color:#6d28d9}.pill.shipped::before{background:#7c3aed}
    .pill.delivered{background:#dcfce7;color:#166534}.pill.delivered::before{background:#16a34a}
    .pill.cancelled{background:#fee2e2;color:#b91c1c}.pill.cancelled::before{background:#dc2626}
    .pill.yes{background:var(--success-light);color:#166534}.pill.yes::before{background:#16a34a}
    .pill.no{background:var(--danger-light);color:#b91c1c}.pill.no::before{background:#dc2626}

    /* ═══ ORDER STATUS SELECT ═══ */
    .status-select{
      width:auto;max-width:130px;font-size:.78rem;padding:5px 28px 5px 9px;
      border-radius:6px;border-color:var(--border);background:var(--bg);
    }

    /* ═══ RECENT QUOTES TABLE ═══ */
    .quote-ref{font-family:monospace;font-size:.8rem;color:var(--primary);font-weight:600}
    .device-tag{display:inline-block;padding:2px 8px;border-radius:5px;background:var(--bg);font-size:.78rem;font-weight:600}

    /* ═══ TOAST NOTIFICATIONS ═══ */
    #toast-container{
      position:fixed;top:20px;right:20px;z-index:9999;
      display:flex;flex-direction:column;gap:8px;
    }
    .toast{
      display:flex;align-items:center;gap:10px;min-width:280px;max-width:380px;
      padding:12px 16px;border-radius:var(--radius);
      background:var(--surface);border:1px solid var(--border);
      box-shadow:var(--shadow-xl);font-size:.85rem;font-weight:500;
      animation:slideIn .25s ease forwards;
    }
    .toast.success{border-color:rgba(34,197,94,.3);background:#f0fdf4;color:#166534}
    .toast.error{border-color:rgba(239,68,68,.3);background:#fef2f2;color:#991b1b}
    .toast.info{border-color:rgba(59,130,246,.3);background:#eff6ff;color:#1e40af}
    .toast i{font-size:1rem;flex-shrink:0}
    .toast__close{margin-left:auto;cursor:pointer;opacity:.5;background:none;border:none;font-size:1rem;color:inherit;line-height:1;padding:0}
    .toast__close:hover{opacity:1}
    @keyframes slideIn{from{transform:translateX(120%);opacity:0}to{transform:translateX(0);opacity:1}}
    @keyframes slideOut{from{transform:translateX(0);opacity:1}to{transform:translateX(120%);opacity:0}}

    /* ═══ CUSTOM CONFIRM MODAL ═══ */
    .modal-overlay{
      position:fixed;inset:0;z-index:8000;
      background:rgba(0,0,0,.45);backdrop-filter:blur(4px);
      display:flex;align-items:center;justify-content:center;
      animation:fadeIn .2s ease;
    }
    .modal-overlay.hidden{display:none}
    .modal-box{
      background:var(--surface);border-radius:var(--radius-xl);
      padding:32px 28px;max-width:400px;width:90%;
      box-shadow:var(--shadow-xl);border:1px solid var(--border);
      animation:popIn .25s cubic-bezier(0.34,1.56,0.64,1);
    }
    .modal-icon{
      width:52px;height:52px;border-radius:50%;
      display:flex;align-items:center;justify-content:center;
      font-size:1.4rem;margin:0 auto 16px;
    }
    .modal-icon.warn{background:var(--warn-light);color:var(--warn)}
    .modal-icon.danger{background:var(--danger-light);color:var(--danger)}
    .modal-title{font-size:1.1rem;font-weight:800;color:var(--text);text-align:center;margin-bottom:8px}
    .modal-msg{font-size:.9rem;color:var(--text-muted);text-align:center;line-height:1.6;margin-bottom:24px}
    .modal-actions{display:flex;gap:10px;justify-content:center}
    .modal-actions .btn{flex:1;justify-content:center;height:40px}
    @keyframes fadeIn{from{opacity:0}to{opacity:1}}
    @keyframes popIn{from{transform:scale(.9);opacity:0}to{transform:scale(1);opacity:1}}

    /* ═══ LAYOUT SETTINGS ═══ */
    .layout-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}

    /* ═══ EMPTY STATE ═══ */
    .empty-state{text-align:center;padding:40px 20px;color:var(--text-muted)}
    .empty-state i{font-size:2.5rem;margin-bottom:12px;display:block;opacity:.3}
    .empty-state p{font-size:.9rem}

    /* ═══ DEFECTS / COSMETICS ═══ */
    .stat-row{
      display:flex;align-items:center;gap:10px;
      padding:7px 0;border-bottom:1px solid var(--border);
    }
    .stat-row:last-child{border:none;padding-bottom:0}
    .stat-row__label{flex:1;font-size:.85rem;color:var(--text-2)}
    .stat-row__bar{flex:2;height:6px;background:var(--border);border-radius:3px;overflow:hidden}
    .stat-row__fill{height:100%;background:linear-gradient(90deg,var(--primary),var(--primary-light));border-radius:3px}
    .stat-row__val{font-size:.82rem;font-weight:600;color:var(--text);min-width:28px;text-align:right}

    /* ═══ SEARCH BAR ═══ */
    .search-bar-wrap{
      padding:12px 16px;border-bottom:1px solid var(--border);
      background:var(--bg);
    }
    .search-bar{
      display:flex;align-items:center;gap:8px;
      background:var(--surface);border:1.5px solid var(--border);
      border-radius:var(--radius-sm);padding:7px 12px;
      transition:border-color var(--transition),box-shadow var(--transition);
    }
    .search-bar:focus-within{border-color:var(--primary);box-shadow:0 0 0 3px rgba(13,124,102,.1)}
    .search-bar i{color:var(--text-faint);font-size:.85rem;flex-shrink:0}
    .search-bar input{
      border:none;background:transparent;font-family:var(--font);
      font-size:.875rem;color:var(--text);outline:none;flex:1;
    }
    .search-bar input::placeholder{color:var(--text-faint)}
    .search-count{
      font-size:.78rem;color:var(--text-muted);
      padding:4px 10px;background:var(--bg);
      border-radius:999px;border:1px solid var(--border);white-space:nowrap;
    }

    /* ═══ ORDER DETAIL MODAL ═══ */
    .order-modal-overlay{
      position:fixed;inset:0;z-index:8500;
      background:rgba(0,0,0,.5);backdrop-filter:blur(6px);
      display:flex;align-items:center;justify-content:center;
      padding:16px;
      animation:fadeIn .2s ease;
    }
    .order-modal-overlay.hidden{display:none}
    .order-modal-box{
      background:var(--surface);border-radius:var(--radius-xl);
      width:100%;max-width:760px;max-height:90vh;
      overflow:hidden;display:flex;flex-direction:column;
      box-shadow:var(--shadow-xl);border:1px solid var(--border);
      animation:popIn .25s cubic-bezier(.34,1.56,.64,1);
    }
    .order-modal-header{
      padding:20px 24px;border-bottom:1px solid var(--border);
      display:flex;align-items:center;gap:14px;
      background:linear-gradient(135deg,var(--primary-light),#fff);
    }
    .order-modal-header-icon{
      width:44px;height:44px;border-radius:12px;
      background:linear-gradient(135deg,var(--primary),var(--primary-dark));
      color:white;display:flex;align-items:center;justify-content:center;
      font-size:1rem;flex-shrink:0;
      box-shadow:0 4px 12px rgba(13,124,102,.35);
    }
    .order-modal-header-info{flex:1}
    .order-modal-ref{font-size:1.1rem;font-weight:800;color:var(--text);font-family:monospace}
    .order-modal-date{font-size:.78rem;color:var(--text-muted);margin-top:2px}
    .order-modal-body{flex:1;overflow-y:auto;padding:24px}
    .order-modal-footer{
      padding:14px 24px;border-top:1px solid var(--border);
      display:flex;align-items:center;gap:10px;
      background:var(--bg);
    }
    .order-detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px}
    .order-detail-section{
      background:var(--bg);border:1px solid var(--border);
      border-radius:var(--radius);padding:16px;
    }
    .order-detail-section.full{grid-column:span 2}
    .order-detail-section__title{
      font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;
      color:var(--text-muted);margin-bottom:12px;
      display:flex;align-items:center;gap:6px;
    }
    .order-detail-section__title i{color:var(--primary)}
    .order-detail-row{
      display:flex;justify-content:space-between;align-items:baseline;
      padding:5px 0;border-bottom:1px solid var(--border);font-size:.865rem;
    }
    .order-detail-row:last-child{border:none;padding-bottom:0}
    .order-detail-row__label{color:var(--text-muted)}
    .order-detail-row__val{font-weight:600;color:var(--text);text-align:right;word-break:break-word;max-width:55%}
    .order-items-table{width:100%;border-collapse:collapse;font-size:.82rem}
    .order-items-table th{
      padding:8px 10px;text-align:left;background:var(--border);
      font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);
    }
    .order-items-table td{padding:10px 10px;border-bottom:1px solid var(--border);color:var(--text-2);vertical-align:middle}
    .order-items-table tr:last-child td{border:none}
    .order-history-item{
      display:flex;align-items:center;gap:10px;
      padding:8px 0;border-bottom:1px solid var(--border);font-size:.83rem;
    }
    .order-history-item:last-child{border:none}
    .order-history-dot{width:8px;height:8px;border-radius:50%;background:var(--primary);flex-shrink:0}

    /* ═══ RESPONSIVE ═══ */
    @media(max-width:1100px){
      .sidebar{display:none}.main{margin-left:0}
      .kpi-grid{grid-template-columns:repeat(2,1fr)}
      .charts-grid,.mini-cards-row{grid-template-columns:1fr}
      .fg{grid-template-columns:1fr 1fr}
    }
    @media(max-width:640px){
      .fg{grid-template-columns:1fr}
      .tabs-bar{gap:2px}
      .tab-btn{font-size:.75rem;padding:6px 10px}
      .content{padding:16px}
      .topbar{padding:0 16px}
    }
  </style>
</head>
<body>

<!-- ════════════════════════ SIDEBAR ════════════════════════ -->
<div class="app">
<aside class="sidebar">
  <div class="sidebar-header">
    <a href="#" class="sidebar-logo">
      <div class="sidebar-logo-icon"><i class="fas fa-recycle"></i></div>
      <div class="sidebar-logo-text">gsm<span>stunter</span></div>
      <span class="sidebar-logo-badge">ADMIN</span>
    </a>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Main</div>
    <a class="nav-item active" data-tab="overview">
      <div class="nav-item__icon"><i class="fas fa-gauge-high"></i></div>
      <span class="nav-item__label">Dashboard</span>
    </a>
    <a class="nav-item" data-tab="pricing">
      <div class="nav-item__icon"><i class="fas fa-sliders"></i></div>
      <span class="nav-item__label">Pricing Engine</span>
    </a>

    <div class="nav-section-label">Catalog</div>
    <a class="nav-item" data-tab="products">
      <div class="nav-item__icon"><i class="fas fa-mobile-screen"></i></div>
      <span class="nav-item__label">Products</span>
    </a>
    <a class="nav-item" data-tab="categories">
      <div class="nav-item__icon"><i class="fas fa-tags"></i></div>
      <span class="nav-item__label">Categories</span>
    </a>
    <a class="nav-item" data-tab="layout">
      <div class="nav-item__icon"><i class="fas fa-table-columns"></i></div>
      <span class="nav-item__label">Layout & Views</span>
    </a>

    <div class="nav-section-label">Commerce</div>
    <a class="nav-item" data-tab="orders">
      <div class="nav-item__icon"><i class="fas fa-box"></i></div>
      <span class="nav-item__label">Orders</span>
      <span class="nav-item__badge" id="sideOrderBadge" style="display:none">0</span>
    </a>
    <a class="nav-item" data-tab="customers">
      <div class="nav-item__icon"><i class="fas fa-users"></i></div>
      <span class="nav-item__label">Customers</span>
    </a>
    <a class="nav-item" data-tab="carts">
      <div class="nav-item__icon"><i class="fas fa-cart-shopping"></i></div>
      <span class="nav-item__label">Cart Snapshots</span>
    </a>
    <a class="nav-item" data-tab="vendit">
      <div class="nav-item__icon"><i class="fas fa-plug"></i></div>
      <span class="nav-item__label">Vendit ERP</span>
      <?php if ($venditPendingBadge > 0): ?>
      <span class="nav-item__badge"><?= (int) $venditPendingBadge ?></span>
      <?php endif; ?>
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="sidebar-user-avatar">A</div>
      <div class="sidebar-user-info">
        <div class="sidebar-user-name">Administrator</div>
        <div class="sidebar-user-role">Super Admin</div>
      </div>
      <i class="fas fa-arrow-right-from-bracket" id="logout" style="color:rgba(255,255,255,.3);font-size:.85rem;cursor:pointer;" title="Logout"></i>
    </div>
  </div>
</aside>

<!-- ════════════════════════ MAIN ════════════════════════ -->
<div class="main">
  <!-- Topbar -->
  <div class="topbar">
    <div class="topbar-title" id="topbarTitle">Dashboard <span>/ Overview</span></div>
    <div class="topbar-actions">
      <a class="tb-btn" href="../sell.html" target="_blank"><i class="fas fa-arrow-up-right-from-square"></i> Sell Flow</a>
      <div class="tb-divider"></div>
      <button class="tb-btn" onclick="exportReport('quotes')"><i class="fas fa-file-csv"></i> Quotes</button>
      <button class="tb-btn" onclick="exportReport('orders')"><i class="fas fa-file-csv"></i> Orders</button>
      <button class="tb-btn" onclick="exportReport('products')"><i class="fas fa-file-csv"></i> Products</button>
      <div class="tb-divider"></div>
      <button class="tb-btn danger" id="logoutBtn"><i class="fas fa-arrow-right-from-bracket"></i> Logout</button>
    </div>
  </div>

  <!-- Content -->
  <div class="content">

    <!-- ── Overview Section (above tabs) ── -->
    <div id="overviewTop">
      <div class="kpi-grid" id="stats"></div>
      <div class="charts-grid">
        <div class="chart-card">
          <div class="chart-card__header">
            <div>
              <div class="chart-card__title">Daily Offer Value Trend</div>
              <div class="chart-card__sub">Last 20 days</div>
            </div>
            <span class="chart-badge"><i class="fas fa-chart-line"></i> Live</span>
          </div>
          <canvas id="dailyChart" height="200"></canvas>
        </div>
        <div class="chart-card">
          <div class="chart-card__header">
            <div>
              <div class="chart-card__title">Top Devices</div>
              <div class="chart-card__sub">By quote volume</div>
            </div>
          </div>
          <canvas id="deviceChart" height="200"></canvas>
        </div>
      </div>
      <div class="mini-cards-row">
        <div class="mini-card">
          <div class="mini-card__title"><i class="fas fa-triangle-exclamation"></i> Top Defects</div>
          <div id="topDefects"></div>
        </div>
        <div class="mini-card">
          <div class="mini-card__title"><i class="fas fa-star-half-stroke"></i> Top Cosmetics</div>
          <div id="topCosmetics"></div>
        </div>
      </div>
    </div>

    <!-- ── Tabs Bar ── -->
    <div class="tabs-bar" role="tablist">
      <button class="tab-btn active" data-tab="overview"><i class="fas fa-gauge-high"></i> Overview</button>
      <button class="tab-btn" data-tab="pricing"><i class="fas fa-sliders"></i> Pricing</button>
      <button class="tab-btn" data-tab="products"><i class="fas fa-mobile-screen"></i> Products</button>
      <button class="tab-btn" data-tab="categories"><i class="fas fa-tags"></i> Categories</button>
      <button class="tab-btn" data-tab="layout"><i class="fas fa-table-columns"></i> Layout</button>
      <button class="tab-btn" data-tab="orders"><i class="fas fa-box"></i> Orders</button>
      <button class="tab-btn" data-tab="customers"><i class="fas fa-users"></i> Customers</button>
      <button class="tab-btn" data-tab="carts"><i class="fas fa-cart-shopping"></i> Carts</button>
      <button class="tab-btn" data-tab="vendit"><i class="fas fa-plug"></i> Vendit</button>
    </div>

    <!-- ── Overview Pane ── -->
    <div class="pane active" id="pane-overview">
      <div class="sec-card">
        <div class="sec-card__header">
          <div class="sec-card__icon"><i class="fas fa-clock-rotate-left"></i></div>
          <div>
            <div class="sec-card__title">Recent Quotes</div>
            <div class="sec-card__sub">Latest sell requests from the public flow</div>
          </div>
        </div>
        <div class="sec-card__body" style="padding:0">
          <div class="table-container">
            <table><thead><tr><th>Reference</th><th>Device</th><th>Offer</th><th>Status</th><th>Date</th></tr></thead><tbody id="recent"></tbody></table>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Pricing Pane ── -->
    <div class="pane" id="pane-pricing">
      <div class="admin-page">
        <div class="admin-toolbar">
          <div class="admin-toolbar__left">
            <div>
              <div class="admin-page-title">Pricing Engine</div>
              <div class="admin-page-sub">Configure buyback and trade calculation rules</div>
            </div>
          </div>
        </div>
        <div class="settings-tabs" id="pricingSettingsTabs">
          <button type="button" class="settings-tab active" data-panel="sell">Sell / Buyback</button>
          <button type="button" class="settings-tab" data-panel="trade">Trade / Exchange</button>
        </div>
        <div class="settings-panel active settings-card" data-settings-group="pricing" data-panel="sell">
          <div class="panel-heading">Sell Calculation</div>
          <div class="panel-desc">Rules applied when customers sell devices through the quote flow.</div>
          <div class="form-grid">
            <div class="field has-icon"><label>Min Price (€)</label><div class="field-wrap"><i class="fas fa-euro-sign field-icon"></i><input id="minPrice" type="number" placeholder="30"></div></div>
            <div class="field has-icon"><label>Global Reduction %</label><div class="field-wrap"><i class="fas fa-percent field-icon"></i><input id="globalReductionPercent" type="number" step="0.01" placeholder="0"></div></div>
            <div class="field has-icon"><label>Rounding Rule</label><div class="field-wrap"><i class="fas fa-ruler field-icon"></i><input id="rounding" placeholder="nearest_5"></div></div>
            <div class="field has-icon"><label>Currency</label><div class="field-wrap"><i class="fas fa-coins field-icon"></i><input id="currency" placeholder="EUR"></div></div>
          </div>
          <div class="settings-card__footer"><button class="btn primary" id="saveCalc"><i class="fas fa-floppy-disk"></i> Save Sell Settings</button></div>
        </div>
        <div class="settings-panel settings-card" data-settings-group="pricing" data-panel="trade">
          <div class="panel-heading">Trade / Exchange</div>
          <div class="panel-desc">Bonuses applied when customers trade in a device for a new purchase.</div>
          <div class="form-grid">
            <div class="field has-icon"><label>Trade Bonus %</label><div class="field-wrap"><i class="fas fa-percent field-icon"></i><input id="tradeBonusPercent" type="number" step="0.01" placeholder="0"></div></div>
            <div class="field has-icon"><label>Exchange Bonus (€)</label><div class="field-wrap"><i class="fas fa-euro-sign field-icon"></i><input id="exchangeBonusValue" type="number" step="0.01" placeholder="0"></div></div>
            <div class="field has-icon"><label>Min Trade Price (€)</label><div class="field-wrap"><i class="fas fa-euro-sign field-icon"></i><input id="minTradePrice" type="number" step="0.01" placeholder="20"></div></div>
          </div>
          <div class="settings-card__footer"><button class="btn primary" id="saveTrade"><i class="fas fa-floppy-disk"></i> Save Trade Settings</button></div>
        </div>
      </div>
    </div>

    <!-- ── Products Pane ── -->
    <div class="pane" id="pane-products">
      <div class="products-page">
        <div class="products-toolbar">
          <div class="products-toolbar__left">
            <div>
              <div class="products-page-title">Products</div>
              <div class="products-page-sub">Manage your catalog</div>
            </div>
          </div>
          <div class="products-toolbar__right">
            <button type="button" class="btn primary" id="btnAddProduct"><i class="fas fa-plus"></i> Add Product</button>
          </div>
        </div>

        <div class="products-toolbar">
          <div class="products-search">
            <i class="fas fa-magnifying-glass"></i>
            <input id="productSearch" placeholder="Search by name, SKU, brand, model…" autocomplete="off">
            <span class="search-count" id="productCount"></span>
          </div>
          <div class="products-filters">
            <div class="filter-chip"><label>Category</label><select id="productFilterCategory"></select></div>
            <div class="filter-chip"><label>Brand</label><select id="productFilterBrand"></select></div>
            <div class="filter-chip"><label>Status</label><select id="productFilterVisibility"><option value="">All</option><option value="1">Live</option><option value="0">Hidden</option></select></div>
            <div class="filter-chip"><label>Stock</label><select id="productFilterStock"><option value="">All</option><option value="in">In stock</option><option value="low">Low (≤5)</option><option value="out">Out of stock</option></select></div>
          </div>
        </div>

        <div class="products-table-card">
          <div class="table-container">
            <table>
              <thead><tr><th>ID</th><th></th><th>SKU</th><th>Product</th><th>Brand</th><th>Price</th><th>Stock</th><th>Status</th><th></th></tr></thead>
              <tbody id="productsBody"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Categories Pane ── -->
    <div class="pane" id="pane-categories">
      <div class="admin-page">
        <div class="admin-toolbar">
          <div class="admin-toolbar__left"><div><div class="admin-page-title">Categories</div><div class="admin-page-sub">Organize your product catalog</div></div></div>
          <div class="admin-toolbar__right"><button type="button" class="btn primary" id="btnAddCategory"><i class="fas fa-plus"></i> Add Category</button></div>
        </div>
        <div class="admin-toolbar">
          <div class="admin-search"><i class="fas fa-magnifying-glass"></i><input id="categorySearch" placeholder="Search categories…" autocomplete="off"><span class="search-count" id="categoryCount"></span></div>
          <div class="admin-filters"><div class="filter-chip"><label>Status</label><select id="categoryFilterVisibility"><option value="">All</option><option value="1">Live</option><option value="0">Hidden</option></select></div></div>
        </div>
        <div class="admin-table-card">
          <div class="table-container">
            <table><thead><tr><th>ID</th><th></th><th>Key</th><th>Name</th><th>Status</th><th></th></tr></thead><tbody id="categoriesBody"></tbody></table>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Layout Pane ── -->
    <div class="pane" id="pane-layout">
      <div class="admin-page">
        <div class="admin-toolbar">
          <div class="admin-toolbar__left"><div><div class="admin-page-title">Layout & Views</div><div class="admin-page-sub">Storefront display and section visibility</div></div></div>
        </div>
        <div class="settings-tabs" id="layoutSettingsTabs">
          <button type="button" class="settings-tab active" data-panel="storefront">Storefront</button>
          <button type="button" class="settings-tab" data-panel="sections">Sections</button>
        </div>
        <div class="settings-panel active settings-card" data-settings-group="layout" data-panel="storefront">
          <div class="panel-heading">Product View Settings</div>
          <div class="panel-desc">Control how products appear on the public shop.</div>
          <div class="form-grid">
            <div class="field"><label>Default View</label><select id="defaultViewMode"><option value="grid">Grid</option><option value="list">List</option></select></div>
            <div class="field has-icon"><label>Items Per Page</label><div class="field-wrap"><i class="fas fa-hashtag field-icon"></i><input id="itemsPerPage" type="number" placeholder="12"></div></div>
            <div class="field"><label>Filters</label><select id="showFilters"><option value="1">Show</option><option value="0">Hide</option></select></div>
            <div class="field"><label>Sort Controls</label><select id="showSort"><option value="1">Show</option><option value="0">Hide</option></select></div>
          </div>
          <div class="settings-card__footer"><button class="btn primary" id="saveViewSettings"><i class="fas fa-floppy-disk"></i> Save View Settings</button></div>
        </div>
        <div class="settings-panel admin-table-card" data-settings-group="layout" data-panel="sections">
          <div style="padding:18px 22px;border-bottom:1px solid var(--border)">
            <div class="panel-heading">Section Visibility</div>
            <div class="panel-desc" style="margin-bottom:0">Toggle sections on the public website.</div>
          </div>
          <div class="table-container"><table><thead><tr><th>Key</th><th>Label</th><th>Status</th><th></th></tr></thead><tbody id="sectionsBody"></tbody></table></div>
        </div>
      </div>
    </div>

    <!-- ── Orders Pane ── -->
    <div class="pane" id="pane-orders">
      <div class="admin-page">
        <div class="admin-toolbar">
          <div class="admin-toolbar__left"><div><div class="admin-page-title">Orders</div><div class="admin-page-sub">Manage customer orders and fulfillment</div></div></div>
        </div>
        <div class="admin-toolbar">
          <div class="admin-search"><i class="fas fa-magnifying-glass"></i><input id="orderSearch" placeholder="Search reference, customer, email…" autocomplete="off"><span class="search-count" id="orderCount"></span></div>
          <div class="admin-filters"><div class="filter-chip"><label>Status</label><select id="orderFilterStatus"><option value="">All</option><option value="new">New</option><option value="processing">Processing</option><option value="shipped">Shipped</option><option value="delivered">Delivered</option><option value="cancelled">Cancelled</option></select></div></div>
        </div>
        <div class="admin-table-card">
          <div class="table-container">
            <table><thead><tr><th>Reference</th><th>Customer</th><th>Email</th><th>Total</th><th>Status</th><th>Date</th><th></th></tr></thead><tbody id="ordersBody"></tbody></table>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Customers Pane ── -->
    <div class="pane" id="pane-customers">
      <div class="admin-page">
        <div class="admin-toolbar">
          <div class="admin-toolbar__left"><div><div class="admin-page-title">Customers</div><div class="admin-page-sub">Registered customers and lifetime value</div></div></div>
        </div>
        <div class="admin-toolbar">
          <div class="admin-search"><i class="fas fa-magnifying-glass"></i><input id="customerSearch" placeholder="Search name, email, phone…" autocomplete="off"><span class="search-count" id="customerCount"></span></div>
        </div>
        <div class="admin-table-card">
          <div class="table-container">
            <table><thead><tr><th>Customer</th><th>Phone</th><th>Orders</th><th>LTV</th><th>Joined</th><th></th></tr></thead><tbody id="customersBody"></tbody></table>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Carts Pane ── -->
    <div class="pane" id="pane-carts">
      <div class="sec-card">
        <div class="sec-card__header">
          <div class="sec-card__icon"><i class="fas fa-cart-shopping"></i></div>
          <div><div class="sec-card__title">Cart Snapshots</div><div class="sec-card__sub">Abandoned and saved shopping carts</div></div>
        </div>
        <div class="sec-card__body" style="padding:0">
          <div class="table-container">
            <table>
              <thead><tr><th>Snapshot Ref</th><th>Email</th><th>Subtotal</th><th>Currency</th><th>Time</th></tr></thead>
              <tbody id="cartsBody"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Vendit ERP Pane ── -->
    <div class="pane" id="pane-vendit">
      <div class="admin-page vendit-page">
        <div class="admin-toolbar">
          <div class="admin-toolbar__left">
            <div>
              <div class="admin-page-title">Vendit ERP</div>
              <div class="admin-page-sub">Sync with VMSII — import customers/stock, export orders as XML</div>
            </div>
          </div>
          <div class="admin-toolbar__right">
            <a class="btn ghost" href="vendit/index.php" target="_blank"><i class="fas fa-up-right-from-square"></i> Full page</a>
          </div>
        </div>
      <?php
        try {
          $vendit = vendit_admin_dashboard_data($venditFlash);
          include __DIR__ . '/vendit/panel.php';
        } catch (Throwable $e) {
          echo '<div class="sec-card"><div class="sec-card__body"><p style="color:var(--danger)">Vendit module not ready: '
            . htmlspecialchars($e->getMessage(), ENT_QUOTES)
            . '</p><p>Run <code>database/migration_v10_vendit_integration.sql</code> in MySQL first.</p></div></div>';
        }
      ?>
      </div>
    </div>

  </div><!-- /content -->
</div><!-- /main -->
</div><!-- /app -->

<!-- ════ TOAST CONTAINER ════ -->
<div id="toast-container"></div>

<!-- ════ CATEGORY DRAWER ════ -->
<div class="admin-drawer-overlay" id="categoryDrawerOverlay">
  <div class="admin-drawer product-drawer" role="dialog">
    <div class="admin-drawer__header product-drawer__header">
      <div class="admin-drawer__header-icon product-drawer__header-icon"><i class="fas fa-tags"></i></div>
      <div class="admin-drawer__header-text product-drawer__header-text">
        <div class="admin-drawer__title product-drawer__title" id="categoryDrawerTitle">Add Category</div>
        <div class="admin-drawer__subtitle product-drawer__subtitle" id="categoryDrawerSubtitle">Organize your product catalog</div>
      </div>
      <button type="button" class="admin-drawer__close product-drawer__close" data-close-drawer><i class="fas fa-xmark"></i></button>
    </div>
    <div class="wizard-steps" id="categoryWizardSteps">
      <div class="wizard-step active"><span class="wizard-step__num">1</span><span class="wizard-step__label">Basic</span></div><div class="wizard-step-divider"></div>
      <div class="wizard-step"><span class="wizard-step__num">2</span><span class="wizard-step__label">Image</span></div><div class="wizard-step-divider"></div>
      <div class="wizard-step"><span class="wizard-step__num">3</span><span class="wizard-step__label">Names</span></div><div class="wizard-step-divider"></div>
      <div class="wizard-step"><span class="wizard-step__num">4</span><span class="wizard-step__label">Review</span></div>
    </div>
    <div class="edit-tabs" id="categoryEditTabs" style="display:none">
      <button type="button" class="edit-tab active" data-tab="general">General</button>
      <button type="button" class="edit-tab" data-tab="image">Image</button>
      <button type="button" class="edit-tab" data-tab="names">Translations</button>
    </div>
    <div class="admin-drawer__body product-drawer__body">
      <div id="categoryWizardPanels">
        <div class="wizard-panel active">
          <div class="panel-heading">Basic Information</div>
          <div class="panel-desc">Category key, icon, sort order and visibility.</div>
          <div class="form-grid">
            <div class="field"><label>Category Key</label><input id="cf-key" placeholder="smartphones"></div>
            <div class="field"><label>Parent ID</label><input id="cf-parentId" type="number" placeholder="Optional"></div>
            <div class="field has-icon"><label>Icon class</label><div class="field-wrap"><i class="fas fa-icons field-icon"></i><input id="cf-icon" placeholder="fas fa-mobile-screen"></div></div>
            <div class="field has-icon"><label>Sort Order</label><div class="field-wrap"><i class="fas fa-sort field-icon"></i><input id="cf-sort" type="number" value="0"></div></div>
            <div class="field"><label>Visibility</label><select id="cf-visible"><option value="1">Live</option><option value="0">Hidden</option></select></div>
          </div>
        </div>
        <div class="wizard-panel">
          <div class="panel-heading">Category Image</div>
          <div class="panel-desc">Drag & drop or paste a URL.</div>
          <div class="image-dropzone" id="categoryDropzone"><div class="image-dropzone__icon"><i class="fas fa-cloud-arrow-up"></i></div><div class="image-dropzone__title">Drop image or click to upload</div><input type="file" id="categoryImageFile" accept="image/*"></div>
          <input type="hidden" id="cf-image">
          <div class="image-preview-grid" id="categoryImagePreview"></div>
          <div class="field" style="margin-top:14px"><label>Image URL</label><input id="cf-imageUrlInput" placeholder="https://…"></div>
        </div>
        <div class="wizard-panel">
          <div class="panel-heading">Names</div>
          <div class="panel-desc">Multilingual category names.</div>
          <div class="form-grid cols-1">
            <div class="field"><label>Name NL</label><input id="cf-nameNl" placeholder="Smartphones"></div>
            <div class="field"><label>Name DE</label><input id="cf-nameDe"></div>
            <div class="field"><label>Name FR</label><input id="cf-nameFr"></div>
          </div>
        </div>
        <div class="wizard-panel"><div class="panel-heading">Review</div><div class="panel-desc">Confirm before creating.</div><div id="catReviewSummary"></div></div>
      </div>
      <div id="categoryEditPanels" style="display:none">
        <input type="hidden" id="cf-id"><input type="hidden" id="cf-edit-id">
        <div class="edit-panel active" data-panel="general">
          <div class="form-grid">
            <div class="field"><label>Category Key</label><input id="cf-edit-key"></div>
            <div class="field"><label>Parent ID</label><input id="cf-edit-parentId" type="number"></div>
            <div class="field"><label>Icon</label><input id="cf-edit-icon"></div>
            <div class="field"><label>Sort Order</label><input id="cf-edit-sort" type="number"></div>
            <div class="field"><label>Visibility</label><select id="cf-edit-visible"><option value="1">Live</option><option value="0">Hidden</option></select></div>
          </div>
        </div>
        <div class="edit-panel" data-panel="image">
          <input type="hidden" id="cf-edit-image">
          <div class="field"><label>Image URL</label><input id="cf-edit-imageUrlInput"></div>
        </div>
        <div class="edit-panel" data-panel="names">
          <div class="form-grid cols-1">
            <div class="field"><label>Name NL</label><input id="cf-edit-nameNl"></div>
            <div class="field"><label>Name DE</label><input id="cf-edit-nameDe"></div>
            <div class="field"><label>Name FR</label><input id="cf-edit-nameFr"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="admin-drawer__footer product-drawer__footer">
      <div class="admin-drawer__footer-left product-drawer__footer-left" id="categoryDrawerFooterLeft"></div>
      <div class="admin-drawer__footer-right product-drawer__footer-right" id="categoryDrawerFooterRight"></div>
    </div>
  </div>
</div>

<!-- ════ ORDER DRAWER ════ -->
<div class="admin-drawer-overlay" id="orderDrawerOverlay">
  <div class="admin-drawer admin-drawer--wide product-drawer" role="dialog">
    <div class="admin-drawer__header product-drawer__header">
      <div class="admin-drawer__header-icon product-drawer__header-icon"><i class="fas fa-box-open"></i></div>
      <div class="admin-drawer__header-text product-drawer__header-text">
        <div class="admin-drawer__title product-drawer__title" id="orderDrawerTitle">Order</div>
        <div class="admin-drawer__subtitle product-drawer__subtitle" id="orderDrawerSubtitle"></div>
      </div>
      <button type="button" class="admin-drawer__close product-drawer__close" data-close-drawer><i class="fas fa-xmark"></i></button>
    </div>
    <div class="admin-drawer__body product-drawer__body" id="orderDrawerBody"></div>
    <div class="admin-drawer__footer product-drawer__footer" id="orderDrawerFooter"></div>
  </div>
</div>

<!-- ════ CUSTOMER DRAWER ════ -->
<div class="admin-drawer-overlay" id="customerDrawerOverlay">
  <div class="admin-drawer product-drawer" role="dialog">
    <div class="admin-drawer__header product-drawer__header">
      <div class="admin-drawer__header-icon product-drawer__header-icon"><i class="fas fa-user"></i></div>
      <div class="admin-drawer__header-text product-drawer__header-text">
        <div class="admin-drawer__title product-drawer__title" id="customerDrawerTitle">Customer</div>
        <div class="admin-drawer__subtitle product-drawer__subtitle" id="customerDrawerSubtitle"></div>
      </div>
      <button type="button" class="admin-drawer__close product-drawer__close" data-close-drawer><i class="fas fa-xmark"></i></button>
    </div>
    <div class="admin-drawer__body product-drawer__body" id="customerDrawerBody"></div>
  </div>
</div>

<!-- ════ PRODUCT DRAWER ════ -->
<div class="product-drawer-overlay" id="productDrawerOverlay">
  <div class="product-drawer" role="dialog" aria-modal="true" aria-labelledby="productDrawerTitle">
    <div class="product-drawer__header">
      <div class="product-drawer__header-icon"><i class="fas fa-box"></i></div>
      <div class="product-drawer__header-text">
        <div class="product-drawer__title" id="productDrawerTitle">Add Product</div>
        <div class="product-drawer__subtitle" id="productDrawerSubtitle">Fill in the details below</div>
      </div>
      <button type="button" class="product-drawer__close" data-close-drawer aria-label="Close"><i class="fas fa-xmark"></i></button>
    </div>

    <div class="wizard-steps" id="productWizardSteps">
      <div class="wizard-step active" data-step="0"><span class="wizard-step__num">1</span><span class="wizard-step__label">Basic</span></div>
      <div class="wizard-step-divider"></div>
      <div class="wizard-step" data-step="1"><span class="wizard-step__num">2</span><span class="wizard-step__label">Pricing</span></div>
      <div class="wizard-step-divider"></div>
      <div class="wizard-step" data-step="2"><span class="wizard-step__num">3</span><span class="wizard-step__label">Images</span></div>
      <div class="wizard-step-divider"></div>
      <div class="wizard-step" data-step="3"><span class="wizard-step__num">4</span><span class="wizard-step__label">Specs</span></div>
      <div class="wizard-step-divider"></div>
      <div class="wizard-step" data-step="4"><span class="wizard-step__num">5</span><span class="wizard-step__label">Review</span></div>
    </div>

    <div class="edit-tabs" id="productEditTabs" style="display:none">
      <button type="button" class="edit-tab active" data-tab="general">General</button>
      <button type="button" class="edit-tab" data-tab="pricing">Pricing</button>
      <button type="button" class="edit-tab" data-tab="inventory">Inventory</button>
      <button type="button" class="edit-tab" data-tab="images">Images</button>
      <button type="button" class="edit-tab" data-tab="variants">Variants</button>
      <button type="button" class="edit-tab" data-tab="seo">SEO</button>
    </div>

    <div class="product-drawer__body">
      <input type="hidden" id="pf-pid">
      <input type="hidden" id="pf-edit-pid">

      <div id="productWizardPanels">
        <div class="wizard-panel active" data-step="0">
          <div class="panel-heading">Basic Information</div>
          <div class="panel-desc">Start with the essentials — name, SKU, category and brand.</div>
          <div class="form-grid">
            <div class="field span-2"><label>Product Name</label><input id="pf-nameNl" placeholder="e.g. iPhone 14 Pro 128GB"></div>
            <div class="field"><label>SKU</label><input id="pf-sku" placeholder="GSM-001"></div>
            <div class="field"><label>Category</label><select id="pf-categoryId"><option value="">— Select —</option></select></div>
            <div class="field"><label>Brand</label><input id="pf-brand" list="brandDatalist" placeholder="Apple, Samsung…" autocomplete="off"></div>
            <datalist id="brandDatalist">
              <option value="Apple"><option value="Samsung"><option value="Google"><option value="OnePlus">
              <option value="Xiaomi"><option value="Huawei"><option value="Sony"><option value="LG">
              <option value="Motorola"><option value="Nokia"><option value="Oppo"><option value="Realme">
            </datalist>
          </div>
        </div>

        <div class="wizard-panel" data-step="1">
          <div class="panel-heading">Pricing & Inventory</div>
          <div class="panel-desc">Set your selling price, discount and stock level.</div>
          <div class="form-grid">
            <div class="field has-icon"><label>Price (€)</label><div class="field-wrap"><i class="fas fa-euro-sign field-icon"></i><input id="pf-price" type="number" step="0.01" placeholder="0.00"></div></div>
            <div class="field has-icon"><label>Discount (%)</label><div class="field-wrap"><i class="fas fa-percent field-icon"></i><input id="pf-discount" type="number" step="1" min="0" max="99" placeholder="0"></div></div>
            <div class="field has-icon"><label>Stock Quantity</label><div class="field-wrap"><i class="fas fa-boxes-stacked field-icon"></i><input id="pf-stockQty" type="number" placeholder="0"></div></div>
            <div class="field"><label>Visibility</label><select id="pf-visibleFlag"><option value="1">Live on storefront</option><option value="0">Hidden (draft)</option></select></div>
          </div>
        </div>

        <div class="wizard-panel" data-step="2">
          <div class="panel-heading">Product Images</div>
          <div class="panel-desc">Drag and drop an image or click to browse.</div>
          <div class="image-dropzone" id="productDropzone">
            <div class="image-dropzone__icon"><i class="fas fa-cloud-arrow-up"></i></div>
            <div class="image-dropzone__title">Drop image here or click to upload</div>
            <div class="image-dropzone__hint">PNG, JPG or WebP</div>
            <input type="file" id="productImageFile" accept="image/*">
          </div>
          <input type="hidden" id="pf-imageUrl">
          <div class="image-preview-grid" id="productImagePreviewGrid"></div>
          <div class="field" style="margin-top:16px"><label>Or paste image URL</label><input id="pf-imageUrlInput" placeholder="https://…"></div>
        </div>

        <div class="wizard-panel" data-step="3">
          <div class="panel-heading">Specifications & Variants</div>
          <div class="panel-desc">Add technical specs and condition/storage pricing variants.</div>
          <div class="form-grid">
            <div class="field"><label>Type</label><select id="pf-ptype"><option value="smartphone">Smartphone</option><option value="laptop">Laptop</option><option value="tablet">Tablet</option><option value="smartwatch">Smartwatch</option><option value="headphones">Headphones</option><option value="accessory">Accessory</option></select></div>
            <div class="field"><label>Model</label><input id="pf-model" placeholder="iPhone 14 Pro"></div>
            <div class="field has-icon"><label>RAM (GB)</label><div class="field-wrap"><i class="fas fa-memory field-icon"></i><input id="pf-ramGb" type="number"></div></div>
            <div class="field has-icon"><label>Camera (MP)</label><div class="field-wrap"><i class="fas fa-camera field-icon"></i><input id="pf-cameraMp" type="number"></div></div>
            <div class="field has-icon"><label>Battery (mAh)</label><div class="field-wrap"><i class="fas fa-battery-half field-icon"></i><input id="pf-batteryMah" type="number"></div></div>
            <div class="field has-icon"><label>Screen (inch)</label><div class="field-wrap"><i class="fas fa-display field-icon"></i><input id="pf-screenSizeIn" type="number" step="0.01"></div></div>
            <div class="field"><label>Chipset</label><input id="pf-chipset" placeholder="Apple A15"></div>
            <div class="field"><label>Color</label><input id="pf-color" placeholder="Midnight Black"></div>
            <div class="field"><label>Default Storage</label><select id="pf-storageLabel"><option value="">— None —</option><option value="32GB">32 GB</option><option value="64GB">64 GB</option><option value="128GB">128 GB</option><option value="256GB">256 GB</option><option value="512GB">512 GB</option><option value="1TB">1 TB</option></select></div>
            <div class="field"><label>Default Condition</label><select id="pf-conditionKey"><option value="">— Select —</option><option value="as_new">As New</option><option value="excellent">Excellent</option><option value="good">Good</option><option value="fair">Fair</option><option value="refurbished">Refurbished</option></select></div>
          </div>
          <div class="variants-wrap">
            <p><i class="fas fa-circle-info" style="color:var(--primary)"></i> Override price & stock per condition/storage combination.</p>
            <table class="variants-table"><thead><tr><th>Condition</th><th>Storage</th><th>Price</th><th>Stock</th><th></th></tr></thead><tbody id="productVariantRows"></tbody></table>
            <button type="button" class="btn secondary sm" id="pfAddVariant" style="margin-top:10px"><i class="fas fa-plus"></i> Add Variant</button>
          </div>
        </div>

        <div class="wizard-panel" data-step="4">
          <div class="panel-heading">Review & Publish</div>
          <div class="panel-desc">Confirm everything looks correct before publishing.</div>
          <div id="pfReviewSummary"></div>
          <div class="form-grid cols-3" style="margin-top:20px">
            <div class="field"><label>Name DE</label><input id="pf-nameDe" placeholder="Optional"></div>
            <div class="field"><label>Name FR</label><input id="pf-nameFr" placeholder="Optional"></div>
            <div class="field has-icon"><label>Dynamic Adjust %</label><div class="field-wrap"><i class="fas fa-percent field-icon"></i><input id="pf-dynamicAdjustPercent" type="number" step="0.01" placeholder="0"></div></div>
          </div>
        </div>
      </div>

      <div id="productEditPanels" style="display:none">
        <div class="edit-panel active" data-panel="general">
          <div class="panel-heading">General</div>
          <div class="panel-desc">Core product information and translations.</div>
          <div class="form-grid">
            <div class="field span-2"><label>Product Name (NL)</label><input id="pf-edit-nameNl"></div>
            <div class="field"><label>SKU</label><input id="pf-edit-sku"></div>
            <div class="field"><label>Category</label><select id="pf-edit-categoryId"><option value="">— Select —</option></select></div>
            <div class="field"><label>Brand</label><input id="pf-edit-brand" list="brandDatalist"></div>
            <div class="field"><label>Type</label><select id="pf-edit-ptype"><option value="smartphone">Smartphone</option><option value="laptop">Laptop</option><option value="tablet">Tablet</option><option value="smartwatch">Smartwatch</option><option value="headphones">Headphones</option><option value="accessory">Accessory</option></select></div>
            <div class="field"><label>Model</label><input id="pf-edit-model"></div>
            <div class="field"><label>Name DE</label><input id="pf-edit-nameDe"></div>
            <div class="field"><label>Name FR</label><input id="pf-edit-nameFr"></div>
          </div>
        </div>

        <div class="edit-panel" data-panel="pricing">
          <div class="panel-heading">Pricing</div>
          <div class="panel-desc">Manage price, discounts and dynamic adjustments.</div>
          <div class="form-grid">
            <div class="field has-icon"><label>Price (€)</label><div class="field-wrap"><i class="fas fa-euro-sign field-icon"></i><input id="pf-edit-price" type="number" step="0.01"></div></div>
            <div class="field has-icon"><label>Discount (%)</label><div class="field-wrap"><i class="fas fa-percent field-icon"></i><input id="pf-edit-discount" type="number" step="1" min="0" max="99"></div></div>
            <div class="field has-icon"><label>Compare-at Price (€)</label><div class="field-wrap"><i class="fas fa-tag field-icon"></i><input id="pf-edit-oldPrice" type="number" step="0.01" placeholder="Auto from discount"></div></div>
            <div class="field has-icon"><label>Dynamic Adjust %</label><div class="field-wrap"><i class="fas fa-percent field-icon"></i><input id="pf-edit-dynamicAdjustPercent" type="number" step="0.01"></div></div>
          </div>
        </div>

        <div class="edit-panel" data-panel="inventory">
          <div class="panel-heading">Inventory</div>
          <div class="panel-desc">Stock levels, sort order and visibility.</div>
          <div class="form-grid">
            <div class="field has-icon"><label>Stock Quantity</label><div class="field-wrap"><i class="fas fa-boxes-stacked field-icon"></i><input id="pf-edit-stockQty" type="number"></div></div>
            <div class="field has-icon"><label>Sort Order</label><div class="field-wrap"><i class="fas fa-sort field-icon"></i><input id="pf-edit-sortOrder" type="number"></div></div>
            <div class="field"><label>Visibility</label><select id="pf-edit-visibleFlag"><option value="1">Live on storefront</option><option value="0">Hidden</option></select></div>
            <div class="field"><label>Default Storage</label><select id="pf-edit-storageLabel"><option value="">— None —</option><option value="32GB">32 GB</option><option value="64GB">64 GB</option><option value="128GB">128 GB</option><option value="256GB">256 GB</option><option value="512GB">512 GB</option><option value="1TB">1 TB</option></select></div>
            <div class="field"><label>Default Condition</label><select id="pf-edit-conditionKey"><option value="">— Select —</option><option value="as_new">As New</option><option value="excellent">Excellent</option><option value="good">Good</option><option value="fair">Fair</option><option value="refurbished">Refurbished</option></select></div>
          </div>
        </div>

        <div class="edit-panel" data-panel="images">
          <div class="panel-heading">Images</div>
          <div class="panel-desc">Drag and drop or click to upload a product image.</div>
          <div class="image-dropzone" id="productDropzoneEdit">
            <div class="image-dropzone__icon"><i class="fas fa-cloud-arrow-up"></i></div>
            <div class="image-dropzone__title">Drop image here or click to upload</div>
            <div class="image-dropzone__hint">PNG, JPG or WebP</div>
          </div>
          <input type="hidden" id="pf-edit-imageUrl">
          <div class="image-preview-grid" id="productImagePreviewGrid"></div>
          <div class="field" style="margin-top:16px"><label>Or paste image URL</label><input id="pf-edit-imageUrlInput" placeholder="https://…"></div>
        </div>

        <div class="edit-panel" data-panel="variants">
          <div class="panel-heading">Variants</div>
          <div class="panel-desc">Condition and storage pricing combinations.</div>
          <div class="form-grid">
            <div class="field has-icon"><label>RAM (GB)</label><div class="field-wrap"><i class="fas fa-memory field-icon"></i><input id="pf-edit-ramGb" type="number"></div></div>
            <div class="field has-icon"><label>Camera (MP)</label><div class="field-wrap"><i class="fas fa-camera field-icon"></i><input id="pf-edit-cameraMp" type="number"></div></div>
            <div class="field has-icon"><label>Battery (mAh)</label><div class="field-wrap"><i class="fas fa-battery-half field-icon"></i><input id="pf-edit-batteryMah" type="number"></div></div>
            <div class="field has-icon"><label>Screen (inch)</label><div class="field-wrap"><i class="fas fa-display field-icon"></i><input id="pf-edit-screenSizeIn" type="number" step="0.01"></div></div>
            <div class="field"><label>Chipset</label><input id="pf-edit-chipset"></div>
            <div class="field"><label>Color</label><input id="pf-edit-color"></div>
          </div>
          <div class="variants-wrap">
            <table class="variants-table"><thead><tr><th>Condition</th><th>Storage</th><th>Price</th><th>Stock</th><th></th></tr></thead><tbody id="productVariantRowsEdit"></tbody></table>
            <button type="button" class="btn secondary sm" id="pfEditAddVariant" style="margin-top:10px"><i class="fas fa-plus"></i> Add Variant</button>
          </div>
        </div>

        <div class="edit-panel" data-panel="seo">
          <div class="panel-heading">SEO & Descriptions</div>
          <div class="panel-desc">Multilingual short and long descriptions for search and product pages.</div>
          <div class="seo-lang-tabs">
            <button type="button" class="seo-lang-tab active" data-lang="nl">🇳🇱 NL</button>
            <button type="button" class="seo-lang-tab" data-lang="de">🇩🇪 DE</button>
            <button type="button" class="seo-lang-tab" data-lang="fr">🇫🇷 FR</button>
          </div>
          <div class="seo-lang-panel active" data-lang="nl">
            <div class="field"><label>Short Description</label><textarea id="pf-edit-shortNl" rows="3"></textarea></div>
            <div class="field"><label>Long Description</label><textarea id="pf-edit-longNl" rows="5"></textarea></div>
          </div>
          <div class="seo-lang-panel" data-lang="de">
            <div class="field"><label>Short Description</label><textarea id="pf-edit-shortDe" rows="3"></textarea></div>
            <div class="field"><label>Long Description</label><textarea id="pf-edit-longDe" rows="5"></textarea></div>
          </div>
          <div class="seo-lang-panel" data-lang="fr">
            <div class="field"><label>Short Description</label><textarea id="pf-edit-shortFr" rows="3"></textarea></div>
            <div class="field"><label>Long Description</label><textarea id="pf-edit-longFr" rows="5"></textarea></div>
          </div>
          <input type="hidden" id="pf-shortNl"><input type="hidden" id="pf-shortDe"><input type="hidden" id="pf-shortFr">
          <input type="hidden" id="pf-longNl"><input type="hidden" id="pf-longDe"><input type="hidden" id="pf-longFr">
        </div>
      </div>
    </div>

    <div class="product-drawer__footer">
      <div class="product-drawer__footer-left" id="productDrawerFooterLeft"></div>
      <div class="product-drawer__footer-right" id="productDrawerFooterRight"></div>
    </div>
  </div>
</div>

<!-- ════ CONFIRM MODAL ════ -->
<div class="modal-overlay hidden" id="confirmModal">
  <div class="modal-box">
    <div class="modal-icon warn" id="confirmIcon"><i class="fas fa-triangle-exclamation"></i></div>
    <div class="modal-title" id="confirmTitle">Bevestiging vereist</div>
    <div class="modal-msg" id="confirmMsg"></div>
    <div class="modal-actions">
      <button class="btn ghost" id="confirmCancel"><i class="fas fa-xmark"></i> Cancel</button>
      <button class="btn primary" id="confirmOk"><i class="fas fa-check"></i> Confirm</button>
    </div>
  </div>
</div>

<script>
/* ═══════════════════════════════════════
   TOAST SYSTEM
═══════════════════════════════════════ */
function toast(msg, type='info', duration=3500){
  const icons={success:'fa-circle-check',error:'fa-circle-xmark',info:'fa-circle-info'};
  const t=document.createElement('div');
  t.className='toast '+type;
  t.innerHTML=`<i class="fas ${icons[type]||icons.info}"></i><span style="flex:1">${msg}</span><button class="toast__close" onclick="this.closest('.toast').remove()">✕</button>`;
  document.getElementById('toast-container').appendChild(t);
  setTimeout(()=>{t.style.animation='slideOut .25s ease forwards';setTimeout(()=>t.remove(),250)},duration);
}

/* ═══════════════════════════════════════
   CUSTOM CONFIRM (replaces window.confirm)
═══════════════════════════════════════ */
function confirmAction(message, isDanger=false){
  return new Promise(resolve=>{
    const modal=document.getElementById('confirmModal');
    const icon=document.getElementById('confirmIcon');
    document.getElementById('confirmMsg').textContent=message;
    icon.className='modal-icon '+(isDanger?'danger':'warn');
    icon.innerHTML=isDanger?'<i class="fas fa-trash"></i>':'<i class="fas fa-triangle-exclamation"></i>';
    const okBtn=document.getElementById('confirmOk');
    okBtn.className='btn '+(isDanger?'danger':'primary');
    modal.classList.remove('hidden');
    const cleanup=()=>modal.classList.add('hidden');
    document.getElementById('confirmCancel').onclick=()=>{cleanup();resolve(false)};
    okBtn.onclick=()=>{cleanup();resolve(true)};
    modal.onclick=(e)=>{if(e.target===modal){cleanup();resolve(false)}};
  });
}

/* ═══════════════════════════════════════
   NAVIGATION
═══════════════════════════════════════ */
const TAB_NAMES={
  overview:'Dashboard',pricing:'Pricing Engine',products:'Products',
  categories:'Categories',layout:'Layout & Views',
  orders:'Orders',customers:'Customers',carts:'Cart Snapshots',vendit:'Vendit ERP'
};
function switchTab(k){
  document.querySelectorAll('.tab-btn').forEach(x=>x.classList.toggle('active',x.dataset.tab===k));
  document.querySelectorAll('.nav-item').forEach(x=>x.classList.toggle('active',x.dataset.tab===k));
  document.querySelectorAll('.pane').forEach(p=>p.classList.remove('active'));
  const pane=document.getElementById('pane-'+k);
  if(pane) pane.classList.add('active');
  const top=document.getElementById('overviewTop');
  if(top) top.style.display=(k==='overview')?'':'none';
  location.hash=k;
  window.scrollTo({top:0,behavior:'smooth'});
  document.getElementById('topbarTitle').innerHTML=`${TAB_NAMES[k]||k} <span>/ ${TAB_NAMES[k]||k}</span>`;
}
document.querySelectorAll('.tab-btn,.nav-item').forEach(t=>{
  t.addEventListener('click',()=>switchTab(t.dataset.tab));
});
const initialHash=(location.hash||'#overview').replace('#','');
switchTab(initialHash);

/* ═══════════════════════════════════════
   DATA LOAD
═══════════════════════════════════════ */
async function loadAll(){
  let d,c,r,s,p,cats,views,orders,customers,carts;
  try{
    [d,c,r,s,p,cats,views,orders,customers,carts]=await Promise.all([
      fetch('../api/admin/dashboard.php').then(x=>x.json()),
      fetch('../api/admin/settings.php').then(x=>x.json()),
      fetch('../api/admin/reports.php').then(x=>x.json()),
      fetch('../api/admin/sections.php').then(x=>x.json()),
      fetch('../api/admin/products.php').then(x=>x.json()),
      fetch('../api/admin/categories.php').then(x=>x.json()),
      fetch('../api/admin/view_settings.php').then(x=>x.json()),
      fetch('../api/admin/orders.php').then(x=>x.json()),
      fetch('../api/admin/customers.php').then(x=>x.json()),
      fetch('../api/admin/carts.php').then(x=>x.json())
    ]);
  }catch(e){toast('Failed to load data: '+e.message,'error');return;}

  if(!d.ok){location.href='login.php';return;}

  /* ── KPI Cards ── */
  const colors=['green','orange','blue','purple','warn','green','orange'];
  const icons=['fas fa-file-invoice','fas fa-magnifying-glass','fas fa-euro-sign','fas fa-chart-bar','fas fa-box','fas fa-euro-sign','fas fa-users'];
  const kpiData=[
    {l:'Total Quotes',v:d.stats.total_quotes||0},
    {l:'Manual Reviews',v:d.stats.manual_reviews||0},
    {l:'Total Offer Value',v:'€'+Number(d.stats.total_offer_value||0).toFixed(0)},
    {l:'Average Offer',v:'€'+Number(d.stats.avg_offer||0).toFixed(0)},
    {l:'Total Orders',v:Number(d.orders?.total_orders||0)},
    {l:'Revenue',v:'€'+Number(d.orders?.total_revenue||0).toFixed(0)},
    {l:'Customers',v:Number(d.customers?.total_customers||0)},
  ];
  document.getElementById('stats').innerHTML=kpiData.map((k,i)=>`
    <div class="kpi-card ${colors[i]}">
      <div class="kpi-card__icon"><i class="${icons[i]}"></i></div>
      <div class="kpi-card__val">${k.v}</div>
      <div class="kpi-card__label">${k.l}</div>
      <div class="kpi-card__trend up"><i class="fas fa-arrow-trend-up"></i> Live</div>
    </div>
  `).join('');

  /* ── Badge on orders ── */
  const newOrders=(orders.items||[]).filter(o=>o.status==='new').length;
  const badge=document.getElementById('sideOrderBadge');
  if(badge){badge.textContent=newOrders;badge.style.display=newOrders>0?'flex':'none';}

  /* ── Recent Quotes ── */
  document.getElementById('recent').innerHTML=(d.recent_quotes||[]).length
    ? (d.recent_quotes||[]).map(x=>`
      <tr>
        <td><span class="quote-ref">${x.quote_reference}</span></td>
        <td><span class="device-tag">${x.device_key}</span></td>
        <td><strong>€ ${x.final_offer}</strong></td>
        <td><span class="pill ${x.status}">${x.status}</span></td>
        <td style="color:var(--text-muted);font-size:.8rem">${x.created_at}</td>
      </tr>`).join('')
    : '<tr><td colspan="5"><div class="empty-state"><i class="fas fa-inbox"></i><p>No quotes yet</p></div></td></tr>';

  /* ── Settings ── */
  const calc=c.calculation||{};
  minPrice.value=calc.min_price||30;
  globalReductionPercent.value=calc.global_reduction_percent||0;
  rounding.value=calc.rounding_rule||'nearest_5';
  currency.value=calc.currency||'EUR';
  tradeBonusPercent.value=c.trade?.trade_bonus_percent||0;
  exchangeBonusValue.value=c.trade?.exchange_bonus_value||0;
  minTradePrice.value=c.trade?.min_trade_price||20;

  /* ── Defects / Cosmetics ── */
  const defects=r.top_defects||[];
  const maxD=Math.max(1,...defects.map(x=>Number(x.total||0)));
  document.getElementById('topDefects').innerHTML=defects.length
    ? defects.map(x=>`<div class="stat-row"><span class="stat-row__label">${x.defect_key}</span><div class="stat-row__bar"><div class="stat-row__fill" style="width:${(Number(x.total)/maxD*100).toFixed(0)}%"></div></div><span class="stat-row__val">${x.total}</span></div>`).join('')
    : '<div class="empty-state"><i class="fas fa-check-circle"></i><p>No defects data</p></div>';
  const cosm=r.top_cosmetics||[];
  const maxC=Math.max(1,...cosm.map(x=>Number(x.total||0)));
  document.getElementById('topCosmetics').innerHTML=cosm.length
    ? cosm.map(x=>`<div class="stat-row"><span class="stat-row__label">${x.cosmetic_key}</span><div class="stat-row__bar"><div class="stat-row__fill" style="width:${(Number(x.total)/maxC*100).toFixed(0)}%"></div></div><span class="stat-row__val">${x.total}</span></div>`).join('')
    : '<div class="empty-state"><i class="fas fa-check-circle"></i><p>No cosmetics data</p></div>';

  /* ── Sections ── */
  if(typeof renderSections==='function') renderSections(s.items||[]);

  /* ── Products ── */
  window.__products=p.items||[];
  populateProductCategories(cats.items||[]);
  populateProductBrandFilter(window.__products);
  if(typeof applyProductFilters==='function') applyProductFilters();
  else renderProducts(window.__products);

  /* ── Categories ── */
  window.__categories=cats.items||[];
  if(typeof applyCategoryFilters==='function') applyCategoryFilters();
  else if(typeof renderCategories==='function') renderCategories(window.__categories);

  /* ── View Settings ── */
  defaultViewMode.value=views.settings?.default_view_mode||'grid';
  itemsPerPage.value=views.settings?.items_per_page||12;
  showFilters.value=views.settings?.show_filters?'1':'0';
  showSort.value=views.settings?.show_sort?'1':'0';

  /* ── Orders ── */
  window.__orders=orders.items||[];
  if(typeof applyOrderFilters==='function') applyOrderFilters();
  else if(typeof renderOrders==='function') renderOrders(window.__orders);

  /* ── Customers ── */
  window.__customers=customers.items||[];
  if(typeof applyCustomerFilters==='function') applyCustomerFilters();
  else if(typeof renderCustomers==='function') renderCustomers(window.__customers);

  /* ── Carts ── */
  document.getElementById('cartsBody').innerHTML=(carts.items||[]).length
    ? (carts.items||[]).map(cs=>`
      <tr>
        <td><span class="quote-ref">${cs.snapshot_reference}</span></td>
        <td style="color:var(--text-muted)">${cs.customer_email||'—'}</td>
        <td><strong>€ ${Number(cs.subtotal||0).toFixed(2)}</strong></td>
        <td>${cs.currency||'EUR'}</td>
        <td style="color:var(--text-muted);font-size:.8rem">${cs.created_at||''}</td>
      </tr>`).join('')
    : '<tr><td colspan="5"><div class="empty-state"><i class="fas fa-cart-shopping"></i><p>No cart snapshots yet.</p></div></td></tr>';

  drawDeviceChart(d.by_device||[]);
  drawDailyChart(r.daily||[]);
}

/* ═══ SECTIONS ═══ */
window.saveSection=async(key)=>{
  if(!await confirmAction(`Update section "${key}"?`)) return;
  const lbl=document.querySelector(`[data-lbl="${key}"]`)?.value||key;
  const vis=document.querySelector(`[data-vis="${key}"]`)?.checked?1:0;
  await fetch('../api/admin/sections.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({section_key:key,label:lbl,is_visible:vis})});
  toast(`Section "${key}" updated`,'success');
  loadAll();
};

/* ═══ EXPORT ═══ */
window.exportReport=(type)=>{
  const allowed=['quotes','orders','products','customers'];
  if(!allowed.includes(type))return;
  confirmAction(`Export ${type} report as CSV/Excel?`).then(ok=>{if(ok)window.open(`../api/admin/reports.php?export=${type}`,'_blank');});
};

/* ═══ LOGOUT ═══ */
async function doLogout(){
  if(!await confirmAction('Log out of the admin panel?'))return;
  await fetch('../api/admin/logout.php',{method:'POST'});
  location.href='login.php';
}
document.getElementById('logout').onclick=doLogout;
document.getElementById('logoutBtn').onclick=doLogout;

/* ═══ CHARTS ═══ */
function drawDeviceChart(data){
  const c=document.getElementById('deviceChart');
  c.width=c.offsetWidth||520;c.height=200;
  const ctx=c.getContext('2d');ctx.clearRect(0,0,c.width,c.height);
  const items=data.slice(0,7);
  if(!items.length){ctx.fillStyle='#94a3b8';ctx.font='13px Inter';ctx.textAlign='center';ctx.fillText('No data',c.width/2,c.height/2);return;}
  const pad=40,w=c.width-pad*2,h=c.height-pad-20,max=Math.max(1,...items.map(x=>Number(x.total||0)));
  const bw=Math.min(50,(w/items.length)-14);
  const colors=['#0d7c66','#10b981','#34d399','#6ee7b7','#a7f3d0','#0d7c66','#10b981'];
  items.forEach((d,i)=>{
    const bh=Math.max(4,(Number(d.total||0)/max)*(h));
    const x=pad+i*(w/items.length)+(w/items.length-bw)/2;
    const y=pad+h-bh;
    const gradient=ctx.createLinearGradient(0,y,0,y+bh);
    gradient.addColorStop(0,colors[i%colors.length]);
    gradient.addColorStop(1,colors[i%colors.length]+'66');
    ctx.fillStyle=gradient;
    ctx.beginPath();ctx.roundRect(x,y,bw,bh,4);ctx.fill();
    ctx.fillStyle='#64748b';ctx.font='10px Inter';ctx.textAlign='center';
    ctx.fillText((d.device_key||'').replace('iphone_','i').slice(0,9),x+bw/2,pad+h+14);
    ctx.fillStyle='#0f172a';ctx.font='bold 10px Inter';
    ctx.fillText(d.total,x+bw/2,y-4);
  });
}
function drawDailyChart(data){
  const c=document.getElementById('dailyChart');
  c.width=c.offsetWidth||700;c.height=200;
  const ctx=c.getContext('2d');ctx.clearRect(0,0,c.width,c.height);
  const arr=[...data].reverse().slice(-20);
  if(!arr.length){ctx.fillStyle='#94a3b8';ctx.font='13px Inter';ctx.textAlign='center';ctx.fillText('No data',c.width/2,c.height/2);return;}
  const pad=40,w=c.width-pad*2,h=c.height-pad-20;
  const max=Math.max(1,...arr.map(x=>Number(x.offer_sum||0)));
  /* Grid lines */
  ctx.strokeStyle='#f1f5f9';ctx.lineWidth=1;
  [0,.25,.5,.75,1].forEach(v=>{const y=pad+h-(v*h);ctx.beginPath();ctx.moveTo(pad,y);ctx.lineTo(pad+w,y);ctx.stroke();ctx.fillStyle='#94a3b8';ctx.font='10px Inter';ctx.textAlign='right';ctx.fillText('€'+(max*v).toFixed(0),pad-6,y+3);});
  /* Gradient fill */
  const gradient=ctx.createLinearGradient(0,pad,0,pad+h);
  gradient.addColorStop(0,'rgba(13,124,102,.25)');
  gradient.addColorStop(1,'rgba(13,124,102,.01)');
  ctx.beginPath();
  arr.forEach((d,i)=>{const x=pad+(i*w/(arr.length-1||1));const y=pad+h-(Number(d.offer_sum||0)/max)*h;i===0?ctx.moveTo(x,y):ctx.lineTo(x,y);});
  ctx.lineTo(pad+w,pad+h);ctx.lineTo(pad,pad+h);ctx.closePath();ctx.fillStyle=gradient;ctx.fill();
  /* Line */
  ctx.strokeStyle='#0d7c66';ctx.lineWidth=2.5;ctx.lineJoin='round';ctx.beginPath();
  arr.forEach((d,i)=>{const x=pad+(i*w/(arr.length-1||1));const y=pad+h-(Number(d.offer_sum||0)/max)*h;i===0?ctx.moveTo(x,y):ctx.lineTo(x,y);});
  ctx.stroke();
  /* Dots */
  arr.forEach((d,i)=>{const x=pad+(i*w/(arr.length-1||1));const y=pad+h-(Number(d.offer_sum||0)/max)*h;ctx.beginPath();ctx.arc(x,y,3,0,Math.PI*2);ctx.fillStyle='#0d7c66';ctx.fill();ctx.strokeStyle='white';ctx.lineWidth=1.5;ctx.stroke();});
}

</script>
<script src="../assets/js/admin-products.js"></script>
<script src="../assets/js/admin-categories.js"></script>
<script src="../assets/js/admin-orders.js"></script>
<script src="../assets/js/admin-customers.js"></script>
<script src="../assets/js/admin-settings.js"></script>
<script>
initProductAdmin();
initCategoriesAdmin();
initOrdersAdmin();
initCustomersAdmin();
initSettingsAdmin();
loadAll();
</script>
</body>
</html>
