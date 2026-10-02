<?php

ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

$pageTitle = $pageTitle ?? APP_NAME;
$flash     = getFlash();


$depth = substr_count(str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']), '/') - 2;
$base  = str_repeat('../', max(0, $depth));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — <?= APP_NAME ?></title>

    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
   
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 270px;
            --navy:    #1B3A5C;
            --navy-dk: #122840;
            --navy-lt: #234B75;
            --accent:  #2E86DE;
            --accent2: #F39C12;
            --success: #27AE60;
            --danger:  #E74C3C;
            --bg:      #F0F4F8;
            --text:    #2D3748;
            --muted:   #718096;
            --border:  #CBD5E0;
            --card-bg: #FFFFFF;
            --sidebar-text: rgba(255,255,255,0.85);
            --sidebar-active: rgba(255,255,255,0.15);
            --sidebar-hover:  rgba(255,255,255,0.08);
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Tajawal', sans-serif;
            background: var(--bg);
            color: var(--text);
            margin: 0;
            overflow-x: hidden;
        }

        
        #sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: linear-gradient(180deg, var(--navy-dk) 0%, var(--navy) 60%, var(--navy-lt) 100%);
            position: fixed;
            top: 0; left: 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
            box-shadow: 4px 0 20px rgba(0,0,0,0.25);
        }

        #sidebar .sidebar-brand {
            padding: 24px 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.12);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        #sidebar .sidebar-brand .brand-icon {
            width: 42px; height: 42px;
            background: var(--accent);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; color: white;
            flex-shrink: 0;
        }

        #sidebar .sidebar-brand .brand-text {
            color: white;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.3;
            letter-spacing: 0.3px;
        }

        #sidebar .sidebar-brand .brand-text span {
            display: block;
            font-size: 10px;
            font-weight: 400;
            color: rgba(255,255,255,0.5);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        #sidebar .nav-section-title {
            padding: 16px 20px 6px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,0.35);
        }

        #sidebar .nav-link {
            color: var(--sidebar-text);
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-radius: 0;
            font-size: 14px;
            font-weight: 400;
            transition: all 0.2s;
            border-left: 3px solid transparent;
            text-decoration: none;
        }

        #sidebar .nav-link:hover {
            background: var(--sidebar-hover);
            color: white;
            border-left-color: rgba(255,255,255,0.3);
        }

        #sidebar .nav-link.active {
            background: var(--sidebar-active);
            color: white;
            border-left-color: var(--accent);
            font-weight: 600;
        }

        #sidebar .nav-link i {
            font-size: 16px;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }

        #sidebar .sidebar-footer {
            margin-top: auto;
            padding: 16px 20px;
            border-top: 1px solid rgba(255,255,255,0.12);
        }

        #sidebar .sidebar-footer .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        #sidebar .sidebar-footer .user-avatar {
            width: 36px; height: 36px;
            background: var(--accent);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 700; color: white;
            flex-shrink: 0;
        }

        #sidebar .sidebar-footer .user-name {
            color: white;
            font-size: 13px;
            font-weight: 600;
        }

        #sidebar .sidebar-footer .user-role {
            color: rgba(255,255,255,0.45);
            font-size: 11px;
        }

        /* ===== MAIN CONTENT ===== */
        #main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ===== TOP NAVBAR ===== */
        #topbar {
            background: white;
            border-bottom: 1px solid var(--border);
            padding: 0 28px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 8px rgba(0,0,0,0.06);
        }

        #topbar .page-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--navy);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        #topbar .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .topbar-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .topbar-badge.admin { background: #EFF6FF; color: #1D4ED8; }
        .topbar-badge.user  { background: #F0FDF4; color: #166534; }

        
        .page-content {
            padding: 28px;
            flex: 1;
        }

       
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06), 0 4px 16px rgba(0,0,0,0.04);
            background: var(--card-bg);
        }

        .card-header {
            background: white;
            border-bottom: 1px solid var(--border);
            border-radius: 12px 12px 0 0 !important;
            padding: 16px 20px;
            font-weight: 600;
            color: var(--navy);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        
        .stat-card {
            border-radius: 14px;
            padding: 24px;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }

        .stat-card .stat-icon {
            width: 52px; height: 52px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            margin-bottom: 16px;
        }

        .stat-card .stat-value {
            font-size: 32px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 6px;
        }

        .stat-card .stat-label {
            font-size: 13px;
            font-weight: 500;
            opacity: 0.75;
        }

        .stat-card .stat-bg-icon {
            position: absolute;
            right: -10px;
            bottom: -10px;
            font-size: 80px;
            opacity: 0.06;
        }

        
        .stat-navy   { background: var(--navy);   color: white; }
        .stat-blue   { background: var(--accent);  color: white; }
        .stat-orange { background: var(--accent2); color: white; }
        .stat-green  { background: var(--success); color: white; }

        
        .table-custom { font-size: 13.5px; }
        .table-custom thead th {
            background: #F8FAFC;
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 1px solid var(--border);
            padding: 12px 16px;
            white-space: nowrap;
        }
        .table-custom tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid #F1F5F9;
            vertical-align: middle;
            color: var(--text);
        }
        .table-custom tbody tr:last-child td { border-bottom: none; }
        .table-custom tbody tr:hover td { background: #F8FAFC; }

        
        .badge-status {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-actif    { background: #DCFCE7; color: #166534; }
        .badge-inactif  { background: #FEE2E2; color: #991B1B; }
        .badge-retraite { background: #FEF3C7; color: #92400E; }
        .badge-admin    { background: #EFF6FF; color: #1D4ED8; }
        .badge-user     { background: #F5F3FF; color: #5B21B6; }

        
        .btn-action {
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 6px;
        }

        .btn-navy {
            background: var(--navy);
            color: white;
            border: none;
        }
        .btn-navy:hover { background: var(--navy-dk); color: white; }

        
        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 6px;
        }
        .form-control, .form-select {
            border-radius: 8px;
            border-color: var(--border);
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(46,134,222,0.15);
        }

        /* ===== PAGINATION ===== */
        .pagination .page-link {
            border-radius: 6px !important;
            margin: 0 2px;
            border: 1px solid var(--border);
            color: var(--text);
            font-size: 13px;
        }
        .pagination .page-item.active .page-link {
            background: var(--navy);
            border-color: var(--navy);
        }

        
        #sidebar-toggle { display: none; }

        @media (max-width: 991px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.show { transform: translateX(0); }
            #main-content { margin-left: 0; }
            #sidebar-toggle { display: block; }
            .page-content { padding: 16px; }
        }

        
        .breadcrumb-item + .breadcrumb-item::before { color: var(--muted); }
        .breadcrumb { font-size: 13px; }
        .section-divider { height: 1px; background: var(--border); margin: 20px 0; }
        code { background: #F1F5F9; padding: 2px 6px; border-radius: 4px; font-family: 'JetBrains Mono', monospace; font-size: 12px; color: var(--navy); }

        .alert { border-radius: 10px; border: none; }
        .alert-success { background: #DCFCE7; color: #166534; }
        .alert-danger  { background: #FEE2E2; color: #991B1B; }
        .alert-warning { background: #FEF3C7; color: #92400E; }
        .alert-info    { background: #E0F2FE; color: #075985; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/sidebar.php'; ?>

<div id="main-content">
    
    <nav id="topbar">
        <div class="page-title">
            <button id="sidebar-toggle" class="btn btn-sm btn-outline-secondary me-2" onclick="document.getElementById('sidebar').classList.toggle('show')">
                <i class="bi bi-list"></i>
            </button>
            <?php if(isset($pageIcon)): ?>
            <i class="bi bi-<?= $pageIcon ?>" style="color:var(--accent)"></i>
            <?php endif; ?>
            <?= htmlspecialchars($pageTitle) ?>
        </div>
        <div class="topbar-right">
            <span class="topbar-badge <?= $_SESSION['role'] ?? 'user' ?>">
                <i class="bi bi-person-circle"></i>
                <?= htmlspecialchars($_SESSION['nom_utilisateur'] ?? 'Utilisateur') ?>
            </span>
            <a href="<?= APP_URL ?>/logout.php" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-box-arrow-right"></i>
                <span class="d-none d-md-inline ms-1">Déconnexion</span>
            </a>
        </div>
    </nav>

    
    <?php if ($flash): ?>
    <div class="mx-4 mt-3">
        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'danger' ? 'x-circle' : 'info-circle') ?> me-2"></i>
            <?= htmlspecialchars($flash['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>

    <div class="page-content">
