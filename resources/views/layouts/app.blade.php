<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DocuScan') — DocuScan SaaS</title>
    <meta name="description" content="DocuScan — Professional Document Scanner & Manager for businesses and importers">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --primary-light: #818cf8;
            --accent: #06b6d4;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --bg: #f8fafc;
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --card: #ffffff;
            --border: #e2e8f0;
            --text: #1e293b;
            --text-muted: #64748b;
            --text-light: #94a3b8;
            --shadow-sm: 0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.06);
            --shadow: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -2px rgba(0,0,0,.1);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,.1), 0 4px 6px -4px rgba(0,0,0,.1);
            --radius: 12px;
            --radius-sm: 8px;
        }

        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

        /* ── Layout ─────────────────────────────────────────────── */
        .app-layout { display: flex; min-height: 100vh; }

        .sidebar {
            width: 260px; background: var(--sidebar-bg);
            display: flex; flex-direction: column;
            position: fixed; top: 0; left: 0; bottom: 0;
            z-index: 100; overflow-y: auto;
        }

        .main { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }

        .topbar {
            background: var(--card); border-bottom: 1px solid var(--border);
            padding: 0 28px; height: 64px;
            display: flex; align-items: center; gap: 16px;
            position: sticky; top: 0; z-index: 50; box-shadow: var(--shadow-sm);
        }
        .topbar-title { font-size: 18px; font-weight: 700; color: var(--text); flex: 1; }
        .topbar-subtitle { font-size: 13px; color: var(--text-muted); }

        .page-content { padding: 28px; flex: 1; }

        /* ── Cards ───────────────────────────────────────────────── */
        .card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-sm); }
        .card-header { padding: 20px 24px 0; display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
        .card-title { font-size: 15px; font-weight: 700; color: var(--text); }
        .card-body { padding: 20px 24px; }

        /* ── Stat Cards ──────────────────────────────────────────── */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); padding: 20px; display: flex; align-items: center; gap: 16px; transition: transform .15s, box-shadow .15s; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow); }
        .stat-icon { width: 52px; height: 52px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .stat-icon svg { width: 24px; height: 24px; }
        .stat-info .stat-value { font-size: 26px; font-weight: 800; color: var(--text); letter-spacing: -1px; line-height: 1; }
        .stat-info .stat-label { font-size: 12px; color: var(--text-muted); font-weight: 500; margin-top: 4px; }

        /* ── Buttons ─────────────────────────────────────────────── */
        .btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: var(--radius-sm); font-size: 13.5px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; transition: all .15s ease; white-space: nowrap; }
        .btn svg { width: 16px; height: 16px; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-outline { background: transparent; border: 1.5px solid var(--border); color: var(--text); }
        .btn-outline:hover { border-color: var(--primary); color: var(--primary); background: rgba(79,70,229,.05); }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-danger:hover { background: #dc2626; }
        .btn-sm { padding: 6px 13px; font-size: 12.5px; }
        .btn-icon { padding: 8px; border-radius: var(--radius-sm); }

        /* ── Tables ──────────────────────────────────────────────── */
        .table-wrap { overflow-x: auto; border-radius: var(--radius); border: 1px solid var(--border); }
        table { width: 100%; border-collapse: collapse; }
        thead tr { background: #f8fafc; border-bottom: 1px solid var(--border); }
        th { padding: 12px 16px; font-size: 11.5px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .6px; text-align: left; white-space: nowrap; }
        td { padding: 14px 16px; font-size: 13.5px; color: var(--text); border-bottom: 1px solid var(--border); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #f8fafc; }

        /* ── Badges ──────────────────────────────────────────────── */
        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 600; }
        .badge-blue { background: #dbeafe; color: #1d4ed8; }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-purple { background: #ede9fe; color: #5b21b6; }
        .badge-gray { background: #f1f5f9; color: #475569; }

        /* ── Forms ───────────────────────────────────────────────── */
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 7px; }
        .form-control { width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 14px; color: var(--text); background: #fff; outline: none; transition: border-color .15s; font-family: inherit; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79,70,229,.1); }
        .form-control::placeholder { color: var(--text-light); }
        .is-invalid { border-color: var(--danger) !important; }
        .invalid-feedback { color: var(--danger); font-size: 12px; margin-top: 4px; }

        /* ── Alerts ──────────────────────────────────────────────── */
        .alert { padding: 12px 16px; border-radius: var(--radius-sm); font-size: 13.5px; font-weight: 500; display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }
        .alert svg { width: 18px; height: 18px; flex-shrink: 0; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        /* ── Storage bar ─────────────────────────────────────────── */
        .storage-bar { height: 8px; background: var(--border); border-radius: 4px; overflow: hidden; margin: 8px 0; }
        .storage-fill { height: 100%; border-radius: 4px; background: linear-gradient(90deg, var(--primary), var(--accent)); }

        /* ── Modal ───────────────────────────────────────────────── */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
        .modal-overlay.show { display: flex; }
        .modal { background: var(--card); border-radius: var(--radius); padding: 28px; width: 100%; max-width: 480px; box-shadow: var(--shadow-lg); animation: modalIn .2s ease; }
        @keyframes modalIn { from { transform: scale(.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .modal-title { font-size: 18px; font-weight: 700; margin-bottom: 20px; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; }

        /* ── File Upload ─────────────────────────────────────────── */
        .file-drop { border: 2px dashed var(--border); border-radius: var(--radius); padding: 32px; text-align: center; cursor: pointer; transition: all .2s; }
        .file-drop:hover, .file-drop.dragover { border-color: var(--primary); background: rgba(79,70,229,.03); }
        .file-drop-text { font-size: 14px; color: var(--text-muted); }
        .file-drop-text span { color: var(--primary); font-weight: 600; }

        /* ── Doc items ───────────────────────────────────────────── */
        .doc-item { display: flex; align-items: center; gap: 14px; padding: 14px 20px; border-bottom: 1px solid var(--border); transition: background .12s; }
        .doc-item:last-child { border-bottom: none; }
        .doc-item:hover { background: #f8fafc; }
        .doc-icon { width: 44px; height: 44px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .doc-icon.pdf { background: #fee2e2; color: var(--danger); }
        .doc-icon.image { background: #dbeafe; color: #1d4ed8; }
        .doc-info { flex: 1; min-width: 0; }
        .doc-title { font-size: 14px; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .doc-meta { font-size: 12px; color: var(--text-muted); margin-top: 2px; display: flex; gap: 12px; flex-wrap: wrap; }
        .doc-actions { display: flex; gap: 6px; flex-shrink: 0; opacity: 0; transition: opacity .15s; }
        .doc-item:hover .doc-actions { opacity: 1; }

        /* ── Folder Grid ─────────────────────────────────────────── */
        .folder-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 14px; }
        .folder-card { background: var(--card); border: 1.5px solid var(--border); border-radius: var(--radius); padding: 20px 16px; cursor: pointer; text-decoration: none; transition: all .15s; display: flex; flex-direction: column; gap: 10px; position: relative; }
        .folder-card:hover { border-color: var(--primary); transform: translateY(-2px); box-shadow: var(--shadow); }
        .folder-card-icon { width: 44px; height: 44px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; }
        .folder-card-name { font-size: 14px; font-weight: 700; color: var(--text); }
        .folder-card-count { font-size: 12px; color: var(--text-muted); }
        .folder-del-btn { position: absolute; top: 10px; right: 10px; display: none; background: var(--danger); color: #fff; border: none; border-radius: 6px; padding: 4px; cursor: pointer; }
        .folder-card:hover .folder-del-btn { display: flex; }

        /* ── Ring ────────────────────────────────────────────────── */
        .ring { position: relative; width: 80px; height: 80px; }
        .ring svg { transform: rotate(-90deg); }
        .ring-track { fill: none; stroke: var(--border); stroke-width: 6; }
        .ring-fill { fill: none; stroke: var(--primary); stroke-width: 6; stroke-linecap: round; transition: stroke-dashoffset .6s ease; }
        .ring-label { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; }

        /* ── Sidebar Nav ─────────────────────────────────────────── */
        .sidebar-logo { display: flex; align-items: center; gap: 10px; padding: 24px 20px; border-bottom: 1px solid rgba(255,255,255,.08); }
        .sidebar-logo .logo-icon { width: 38px; height: 38px; background: linear-gradient(135deg, var(--primary), var(--accent)); border-radius: 10px; display: flex; align-items: center; justify-content: center; }
        .sidebar-logo .logo-text { font-size: 20px; font-weight: 700; color: #fff; letter-spacing: -0.5px; }
        .sidebar-logo .logo-badge { font-size: 10px; color: var(--primary-light); font-weight: 500; letter-spacing: .5px; }
        .sidebar-company { padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,.06); }
        .sidebar-company .company-label { font-size: 10px; color: var(--text-light); text-transform: uppercase; letter-spacing: .8px; margin-bottom: 4px; }
        .sidebar-company .company-name { font-size: 13px; font-weight: 600; color: #e2e8f0; }
        .sidebar-company .company-plan { display: inline-block; font-size: 10px; background: rgba(79,70,229,.3); color: var(--primary-light); padding: 2px 8px; border-radius: 20px; margin-top: 4px; font-weight: 500; }
        .sidebar-nav { flex: 1; padding: 16px 12px; }
        .nav-section-title { font-size: 10px; text-transform: uppercase; letter-spacing: .8px; color: #475569; padding: 8px 8px 4px; font-weight: 600; }
        .nav-item { display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: var(--radius-sm); color: #94a3b8; text-decoration: none; font-size: 13.5px; font-weight: 500; margin-bottom: 2px; transition: all .15s ease; cursor: pointer; border: none; background: none; width: 100%; text-align: left; }
        .nav-item:hover { background: var(--sidebar-hover); color: #e2e8f0; }
        .nav-item.active { background: rgba(79,70,229,.25); color: #c7d2fe; }
        .nav-item .nav-icon { width: 18px; height: 18px; opacity: .8; flex-shrink: 0; }
        .sidebar-footer { padding: 16px 12px; border-top: 1px solid rgba(255,255,255,.08); }
        .user-card { display: flex; align-items: center; gap: 10px; padding: 10px; border-radius: var(--radius-sm); background: rgba(255,255,255,.05); }
        .user-avatar { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--accent)); display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; color: #fff; flex-shrink: 0; }
        .user-name { font-size: 13px; font-weight: 600; color: #e2e8f0; }
        .user-role { font-size: 11px; color: #64748b; }
    </style>
    @stack('styles')
</head>
<body>

<div class="app-layout">
    <aside class="sidebar">
        @include('partials.sidebar-content')
    </aside>
    <div class="main">
        @yield('content')
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }
document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', function(e) { if (e.target === this) this.classList.remove('show'); });
});
</script>
@stack('scripts')
</body>
</html>
