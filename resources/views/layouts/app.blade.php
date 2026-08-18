<!DOCTYPE html>
<html lang="en" dir="ltr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DocuScan') — DocuScan Pro</title>
    <meta name="description" content="DocuScan Pro — Professional Document Scanner & Cloud Manager for Enterprise">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
    /* ═══════════════════════════════════════════════════════════════
       DOCUSCAN PRO — DESIGN SYSTEM v2.0
       Mobile-First Enterprise SaaS UI
    ═══════════════════════════════════════════════════════════════ */

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    /* ─── CSS TOKENS ─────────────────────────────────────────────── */
    :root {
        /* Primary Palette */
        --primary:        hsl(231, 80%, 60%);
        --primary-dark:   hsl(231, 80%, 50%);
        --primary-light:  hsl(231, 80%, 72%);
        --primary-ultra:  hsl(231, 80%, 96%);
        --primary-glow:   hsl(231, 80%, 60%, 0.25);

        /* Accent */
        --accent:         hsl(193, 87%, 55%);
        --accent-dark:    hsl(193, 87%, 43%);

        /* Semantic */
        --success:        hsl(162, 76%, 42%);
        --success-bg:     hsl(162, 76%, 95%);
        --warning:        hsl(43, 96%, 56%);
        --warning-bg:     hsl(43, 96%, 95%);
        --danger:         hsl(350, 88%, 55%);
        --danger-bg:      hsl(350, 88%, 96%);
        --info:           hsl(210, 90%, 55%);
        --info-bg:        hsl(210, 90%, 96%);

        /* Neutral Surfaces */
        --bg:             hsl(220, 20%, 97%);
        --bg-2:           hsl(220, 15%, 94%);
        --card:           hsl(0, 0%, 100%);
        --card-2:         hsl(220, 30%, 98%);
        --card-hover:     hsl(220, 30%, 99%);

        /* Sidebar */
        --sidebar-bg:     hsl(222, 47%, 11%);
        --sidebar-hover:  hsl(222, 47%, 16%);
        --sidebar-active: hsl(231, 80%, 25%);
        --sidebar-border: hsl(222, 47%, 16%);

        /* Text */
        --text:           hsl(222, 47%, 11%);
        --text-2:         hsl(215, 16%, 30%);
        --text-muted:     hsl(215, 16%, 47%);
        --text-light:     hsl(215, 16%, 65%);
        --text-placeholder: hsl(215, 16%, 72%);

        /* Borders */
        --border:         hsl(220, 13%, 91%);
        --border-2:       hsl(220, 13%, 85%);

        /* Shadows */
        --shadow-xs:      0 1px 2px hsl(220 13% 11% / 0.04);
        --shadow-sm:      0 1px 3px hsl(220 13% 11% / 0.08), 0 1px 2px hsl(220 13% 11% / 0.05);
        --shadow:         0 4px 6px -1px hsl(220 13% 11% / 0.08), 0 2px 4px -2px hsl(220 13% 11% / 0.05);
        --shadow-md:      0 8px 16px -4px hsl(220 13% 11% / 0.10), 0 4px 6px -2px hsl(220 13% 11% / 0.06);
        --shadow-lg:      0 20px 40px -8px hsl(220 13% 11% / 0.12), 0 8px 16px -4px hsl(220 13% 11% / 0.07);
        --shadow-primary: 0 8px 24px -4px hsl(231 80% 60% / 0.35);
        --shadow-card:    0 0 0 1px hsl(220 13% 91%), 0 2px 8px hsl(220 13% 11% / 0.06);

        /* Radius */
        --r-xs:   4px;
        --r-sm:   8px;
        --r:      12px;
        --r-md:   16px;
        --r-lg:   20px;
        --r-xl:   24px;
        --r-full: 9999px;

        /* Spacing */
        --space-1:  4px;
        --space-2:  8px;
        --space-3:  12px;
        --space-4:  16px;
        --space-5:  20px;
        --space-6:  24px;
        --space-8:  32px;
        --space-10: 40px;
        --space-12: 48px;

        /* Typography */
        --font:       'Plus Jakarta Sans', 'Inter', sans-serif;
        --font-mono:  'Menlo', 'Monaco', monospace;
        --text-xs:    11px;
        --text-sm:    13px;
        --text-base:  14px;
        --text-md:    15px;
        --text-lg:    17px;
        --text-xl:    20px;
        --text-2xl:   24px;
        --text-3xl:   30px;

        /* Transitions */
        --transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
        --transition-slow: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --transition-spring: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);

        /* Layout */
        --sidebar-w: 256px;
        --topbar-h: 60px;
        --bottom-nav-h: 72px;
    }

    /* ─── DARK MODE TOKENS ───────────────────────────────────────── */
    [data-theme="dark"] {
        --bg:             hsl(222, 47%, 8%);
        --bg-2:           hsl(222, 47%, 11%);
        --card:           hsl(222, 47%, 12%);
        --card-2:         hsl(222, 47%, 14%);
        --card-hover:     hsl(222, 47%, 15%);
        --border:         hsl(222, 30%, 20%);
        --border-2:       hsl(222, 30%, 24%);
        --text:           hsl(220, 20%, 96%);
        --text-2:         hsl(220, 15%, 80%);
        --text-muted:     hsl(215, 16%, 60%);
        --text-light:     hsl(215, 16%, 45%);
        --text-placeholder: hsl(215, 16%, 38%);
        --shadow-sm:      0 1px 3px hsl(0 0% 0% / 0.3), 0 1px 2px hsl(0 0% 0% / 0.2);
        --shadow:         0 4px 6px -1px hsl(0 0% 0% / 0.3), 0 2px 4px -2px hsl(0 0% 0% / 0.2);
        --shadow-md:      0 8px 16px -4px hsl(0 0% 0% / 0.35);
        --shadow-lg:      0 20px 40px -8px hsl(0 0% 0% / 0.4);
        --shadow-card:    0 0 0 1px hsl(222 30% 20%), 0 2px 8px hsl(0 0% 0% / 0.2);
        --primary-ultra:  hsl(231, 80%, 18%);
        --success-bg:     hsl(162, 76%, 12%);
        --warning-bg:     hsl(43, 96%, 12%);
        --danger-bg:      hsl(350, 88%, 12%);
        --info-bg:        hsl(210, 90%, 14%);
    }

    /* ─── BASE ────────────────────────────────────────────────────── */
    html { scroll-behavior: smooth; }
    body {
        font-family: var(--font);
        background: var(--bg);
        color: var(--text);
        min-height: 100vh;
        min-height: 100dvh;
        font-size: var(--text-base);
        line-height: 1.6;
        -webkit-font-smoothing: antialiased;
        transition: background 0.2s, color 0.2s;
    }
    a { color: inherit; text-decoration: none; }
    button { font-family: var(--font); cursor: pointer; }
    img { max-width: 100%; }

    /* ─── SCROLLBAR ────────────────────────────────────────────────── */
    ::-webkit-scrollbar { width: 5px; height: 5px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: var(--border-2); border-radius: var(--r-full); }
    ::-webkit-scrollbar-thumb:hover { background: var(--text-light); }

    /* ═══════════════════════════════════════════════════════════════
       LAYOUT
    ═══════════════════════════════════════════════════════════════ */
    .app-layout { display: flex; min-height: 100vh; }

    /* Sidebar — Desktop only */
    .sidebar {
        width: var(--sidebar-w);
        background: var(--sidebar-bg);
        display: flex;
        flex-direction: column;
        position: fixed;
        top: 0; left: 0; bottom: 0;
        z-index: 200;
        overflow-y: auto;
        overflow-x: hidden;
        border-right: 1px solid var(--sidebar-border);
        transition: transform 0.28s cubic-bezier(0.4,0,0.2,1);
    }
    .sidebar::-webkit-scrollbar { width: 3px; }
    .sidebar::-webkit-scrollbar-thumb { background: hsl(222,47%,20%); }

    .main {
        margin-left: var(--sidebar-w);
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        padding-bottom: 0;
    }

    /* Topbar */
    .topbar {
        background: var(--card);
        border-bottom: 1px solid var(--border);
        padding: 0 var(--space-6);
        height: var(--topbar-h);
        display: flex;
        align-items: center;
        gap: var(--space-4);
        position: sticky;
        top: 0;
        z-index: 100;
        box-shadow: var(--shadow-xs);
        backdrop-filter: blur(12px);
    }
    .topbar-left { flex: 1; min-width: 0; }
    .topbar-title { font-size: var(--text-lg); font-weight: 800; color: var(--text); letter-spacing: -0.3px; }
    .topbar-subtitle { font-size: var(--text-xs); color: var(--text-muted); font-weight: 500; margin-top: 1px; }
    .topbar-actions { display: flex; align-items: center; gap: var(--space-2); }

    /* Page Content */
    .page-content { padding: var(--space-6); flex: 1; }

    /* Mobile Bottom Nav */
    .bottom-nav {
        display: none;
        position: fixed;
        bottom: 0; left: 0; right: 0;
        height: var(--bottom-nav-h);
        background: var(--card);
        border-top: 1px solid var(--border);
        z-index: 200;
        box-shadow: 0 -8px 24px hsl(220 13% 11% / 0.08);
        padding-bottom: env(safe-area-inset-bottom, 0);
    }
    .bottom-nav-inner {
        display: flex;
        align-items: center;
        justify-content: space-around;
        height: 100%;
        padding: 0 var(--space-2);
    }
    .bottom-nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 3px;
        padding: var(--space-2) var(--space-3);
        border-radius: var(--r);
        color: var(--text-light);
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 0.3px;
        transition: var(--transition);
        text-decoration: none;
        min-width: 48px;
        position: relative;
    }
    .bottom-nav-item svg { width: 22px; height: 22px; transition: var(--transition); }
    .bottom-nav-item.active { color: var(--primary); }
    .bottom-nav-item.active svg { stroke: var(--primary); }
    .bottom-nav-item:not(.scan-btn):active { transform: scale(0.92); }

    /* Scan Center Button */
    .bottom-nav-scan {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 58px;
        height: 58px;
        background: linear-gradient(135deg, var(--primary), var(--accent));
        border-radius: var(--r-full);
        box-shadow: var(--shadow-primary);
        color: #fff;
        transition: var(--transition-spring);
        position: relative;
        top: -10px;
        border: 3px solid var(--card);
    }
    .bottom-nav-scan:hover { transform: scale(1.08) translateY(-2px); box-shadow: 0 12px 32px -4px hsl(231 80% 60% / 0.5); }
    .bottom-nav-scan:active { transform: scale(0.94) translateY(0); }
    .bottom-nav-scan svg { width: 24px; height: 24px; stroke: #fff; }

    /* ═══════════════════════════════════════════════════════════════
       COMPONENTS
    ═══════════════════════════════════════════════════════════════ */

    /* ─── BUTTONS ─────────────────────────────────────────────────── */
    .btn {
        display: inline-flex; align-items: center; justify-content: center;
        gap: var(--space-2);
        padding: 9px 18px;
        border-radius: var(--r-sm);
        font-size: var(--text-sm);
        font-weight: 600;
        font-family: var(--font);
        cursor: pointer;
        border: none;
        text-decoration: none;
        transition: var(--transition);
        white-space: nowrap;
        letter-spacing: 0.1px;
        user-select: none;
    }
    .btn:active { transform: scale(0.97); }
    .btn svg { width: 15px; height: 15px; flex-shrink: 0; }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary), hsl(231,80%,55%));
        color: #fff;
        box-shadow: 0 4px 12px hsl(231 80% 60% / 0.3);
    }
    .btn-primary:hover { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); box-shadow: var(--shadow-primary); transform: translateY(-1px); }

    .btn-gradient {
        background: linear-gradient(135deg, var(--primary), var(--accent));
        color: #fff;
        box-shadow: 0 4px 16px hsl(231 80% 60% / 0.35);
    }
    .btn-gradient:hover { box-shadow: 0 8px 24px hsl(231 80% 60% / 0.45); transform: translateY(-1px); }

    .btn-outline {
        background: transparent;
        border: 1.5px solid var(--border);
        color: var(--text-2);
    }
    .btn-outline:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-ultra); }

    .btn-ghost { background: transparent; color: var(--text-muted); border: none; }
    .btn-ghost:hover { background: var(--bg-2); color: var(--text); }

    .btn-danger { background: var(--danger-bg); color: var(--danger); border: 1.5px solid hsl(350 88% 55% / 0.2); }
    .btn-danger:hover { background: var(--danger); color: #fff; }

    .btn-success { background: var(--success); color: #fff; box-shadow: 0 4px 12px hsl(162 76% 42% / 0.3); }
    .btn-success:hover { background: hsl(162, 76%, 36%); transform: translateY(-1px); }

    .btn-sm { padding: 6px 12px; font-size: var(--text-xs); border-radius: var(--r-sm); }
    .btn-lg { padding: 12px 24px; font-size: var(--text-md); border-radius: var(--r); }
    .btn-xl { padding: 15px 32px; font-size: var(--text-lg); border-radius: var(--r-md); font-weight: 700; }
    .btn-block { width: 100%; }
    .btn-icon { padding: 8px; border-radius: var(--r-sm); }
    .btn-icon-md { padding: 9px; border-radius: var(--r); }
    .btn-circle { padding: 0; width: 36px; height: 36px; border-radius: var(--r-full); }

    /* ─── CARDS ───────────────────────────────────────────────────── */
    .card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--r-md);
        box-shadow: var(--shadow-card);
        overflow: hidden;
    }
    .card-sm { border-radius: var(--r); }
    .card-hover { transition: var(--transition); cursor: pointer; }
    .card-hover:hover { border-color: hsl(231 80% 60% / 0.3); box-shadow: var(--shadow-md); transform: translateY(-2px); }
    .card-header { padding: var(--space-5) var(--space-6); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border); }
    .card-title { font-size: var(--text-md); font-weight: 700; color: var(--text); letter-spacing: -0.2px; }
    .card-subtitle { font-size: var(--text-sm); color: var(--text-muted); margin-top: 2px; }
    .card-body { padding: var(--space-5) var(--space-6); }
    .card-footer { padding: var(--space-4) var(--space-6); border-top: 1px solid var(--border); background: var(--card-2); }

    /* Stat Cards */
    .stat-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--r-md);
        padding: var(--space-5);
        display: flex;
        align-items: flex-start;
        gap: var(--space-4);
        box-shadow: var(--shadow-card);
        transition: var(--transition);
        cursor: default;
    }
    .stat-card:hover { border-color: hsl(231 80% 60% / 0.25); box-shadow: var(--shadow-md); transform: translateY(-2px); }
    .stat-icon {
        width: 46px; height: 46px;
        border-radius: var(--r);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .stat-icon svg { width: 22px; height: 22px; }
    .stat-value { font-size: var(--text-2xl); font-weight: 800; color: var(--text); letter-spacing: -1px; line-height: 1.1; }
    .stat-label { font-size: var(--text-xs); color: var(--text-muted); font-weight: 500; margin-top: 3px; letter-spacing: 0.2px; }
    .stat-change { font-size: var(--text-xs); font-weight: 600; display: inline-flex; align-items: center; gap: 2px; margin-top: 4px; }
    .stat-change.up { color: var(--success); }
    .stat-change.down { color: var(--danger); }

    /* ─── FORMS ───────────────────────────────────────────────────── */
    .form-group { margin-bottom: var(--space-5); }
    .form-label {
        display: block;
        font-size: var(--text-sm);
        font-weight: 600;
        color: var(--text-2);
        margin-bottom: 6px;
        letter-spacing: 0.1px;
    }
    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid var(--border);
        border-radius: var(--r-sm);
        font-size: var(--text-base);
        color: var(--text);
        background: var(--card);
        outline: none;
        transition: var(--transition);
        font-family: var(--font);
        -webkit-appearance: none;
    }
    .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-glow); }
    .form-control::placeholder { color: var(--text-placeholder); }
    .form-control.is-invalid { border-color: var(--danger); box-shadow: 0 0 0 3px hsl(350 88% 55% / 0.15); }
    .form-control:disabled { background: var(--bg-2); color: var(--text-muted); cursor: not-allowed; }

    .form-control-icon { position: relative; }
    .form-control-icon .form-control { padding-left: 40px; }
    .form-control-icon .input-icon {
        position: absolute;
        left: 12px; top: 50%;
        transform: translateY(-50%);
        color: var(--text-light);
        width: 18px; height: 18px;
        pointer-events: none;
    }

    .invalid-feedback { color: var(--danger); font-size: var(--text-xs); margin-top: 4px; font-weight: 500; }
    .form-hint { color: var(--text-muted); font-size: var(--text-xs); margin-top: 4px; }

    /* Select */
    select.form-control { padding-right: 36px; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; cursor: pointer; }

    /* Checkbox / Toggle */
    .toggle-wrap { display: flex; align-items: center; gap: 10px; cursor: pointer; }
    .toggle {
        width: 44px; height: 24px;
        background: var(--border-2);
        border-radius: var(--r-full);
        position: relative;
        transition: var(--transition);
        flex-shrink: 0;
    }
    .toggle::after {
        content: '';
        position: absolute;
        left: 3px; top: 3px;
        width: 18px; height: 18px;
        background: #fff;
        border-radius: var(--r-full);
        transition: var(--transition-spring);
        box-shadow: 0 1px 4px hsl(0 0% 0% / 0.2);
    }
    input:checked + .toggle { background: var(--primary); }
    input:checked + .toggle::after { transform: translateX(20px); }
    .toggle-label { font-size: var(--text-sm); font-weight: 500; color: var(--text-2); }

    /* ─── BADGES ──────────────────────────────────────────────────── */
    .badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 9px;
        border-radius: var(--r-full);
        font-size: var(--text-xs);
        font-weight: 600;
        letter-spacing: 0.3px;
    }
    .badge-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
    .badge-primary { background: var(--primary-ultra); color: var(--primary-dark); }
    .badge-success { background: var(--success-bg); color: hsl(162, 76%, 30%); }
    .badge-warning { background: var(--warning-bg); color: hsl(43, 96%, 35%); }
    .badge-danger  { background: var(--danger-bg);  color: hsl(350, 88%, 42%); }
    .badge-info    { background: var(--info-bg);    color: hsl(210, 90%, 40%); }
    .badge-gray    { background: var(--bg-2);       color: var(--text-muted); border: 1px solid var(--border); }
    .badge-dark    { background: var(--text); color: var(--card); }

    /* ─── ALERTS ──────────────────────────────────────────────────── */
    .alert {
        padding: var(--space-3) var(--space-4);
        border-radius: var(--r-sm);
        font-size: var(--text-sm);
        font-weight: 500;
        display: flex; align-items: flex-start; gap: var(--space-3);
        margin-bottom: var(--space-4);
        border: 1px solid transparent;
    }
    .alert svg { width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px; }
    .alert-success { background: var(--success-bg); color: hsl(162,76%,28%); border-color: hsl(162 76% 42% / 0.25); }
    .alert-error   { background: var(--danger-bg);  color: hsl(350,88%,38%); border-color: hsl(350 88% 55% / 0.25); }
    .alert-warning { background: var(--warning-bg); color: hsl(43,96%,30%);  border-color: hsl(43 96% 56% / 0.3); }
    .alert-info    { background: var(--info-bg);    color: hsl(210,90%,36%); border-color: hsl(210 90% 55% / 0.25); }

    /* ─── TABLES ──────────────────────────────────────────────────── */
    .table-wrap { overflow-x: auto; border-radius: var(--r-md); border: 1px solid var(--border); box-shadow: var(--shadow-xs); }
    table { width: 100%; border-collapse: collapse; }
    thead tr { background: var(--card-2); }
    th { padding: 11px 16px; font-size: var(--text-xs); font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.7px; text-align: left; white-space: nowrap; border-bottom: 1px solid var(--border); }
    td { padding: 13px 16px; font-size: var(--text-sm); color: var(--text); border-bottom: 1px solid var(--border); vertical-align: middle; }
    tr:last-child td { border-bottom: none; }
    tbody tr { transition: background 0.1s; }
    tbody tr:hover { background: var(--card-2); }

    /* ─── MODALS ──────────────────────────────────────────────────── */
    .modal-overlay {
        display: none;
        position: fixed; inset: 0;
        background: hsl(222 47% 5% / 0.6);
        z-index: 1000;
        align-items: center; justify-content: center;
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        padding: var(--space-4);
        animation: fadeIn 0.15s ease;
    }
    .modal-overlay.show { display: flex; }
    .modal {
        background: var(--card);
        border-radius: var(--r-xl);
        padding: var(--space-8);
        width: 100%; max-width: 480px;
        box-shadow: var(--shadow-lg);
        animation: modalIn 0.22s cubic-bezier(0.34, 1.56, 0.64, 1);
        border: 1px solid var(--border);
        max-height: 90vh;
        overflow-y: auto;
    }
    .modal-sm { max-width: 360px; }
    .modal-lg { max-width: 600px; }
    @keyframes modalIn { from { transform: scale(0.92) translateY(8px); opacity: 0; } to { transform: scale(1) translateY(0); opacity: 1; } }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-6); }
    .modal-title { font-size: var(--text-xl); font-weight: 800; color: var(--text); letter-spacing: -0.3px; }
    .modal-close { width: 32px; height: 32px; border-radius: var(--r-full); border: none; background: var(--bg-2); color: var(--text-muted); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: var(--transition); }
    .modal-close:hover { background: var(--danger-bg); color: var(--danger); }
    .modal-footer { display: flex; justify-content: flex-end; gap: var(--space-3); margin-top: var(--space-6); padding-top: var(--space-5); border-top: 1px solid var(--border); }

    /* ─── BOTTOM SHEETS ───────────────────────────────────────────── */
    .sheet-overlay {
        display: none;
        position: fixed; inset: 0;
        background: hsl(222 47% 5% / 0.55);
        z-index: 1000;
        backdrop-filter: blur(4px);
    }
    .sheet-overlay.show { display: block; }
    .sheet-overlay.show .sheet { transform: translateY(0); }
    .sheet {
        position: fixed;
        bottom: 0; left: 0; right: 0;
        background: var(--card);
        border-radius: var(--r-xl) var(--r-xl) 0 0;
        padding: var(--space-6);
        padding-bottom: max(var(--space-6), env(safe-area-inset-bottom));
        transform: translateY(100%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 -20px 60px hsl(220 13% 11% / 0.15);
        border-top: 1px solid var(--border);
        max-height: 90vh;
        overflow-y: auto;
    }
    .sheet-handle { width: 40px; height: 4px; background: var(--border-2); border-radius: var(--r-full); margin: 0 auto var(--space-5); }

    /* ─── TOAST ───────────────────────────────────────────────────── */
    #toast-container { position: fixed; bottom: calc(var(--bottom-nav-h) + 16px); right: 16px; z-index: 2000; display: flex; flex-direction: column; gap: 8px; }
    .toast {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--r);
        padding: 12px 16px;
        font-size: var(--text-sm);
        font-weight: 500;
        color: var(--text);
        box-shadow: var(--shadow-md);
        display: flex; align-items: center; gap: 10px;
        min-width: 240px; max-width: 320px;
        animation: toastIn 0.28s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    @keyframes toastIn { from { transform: translateX(120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    .toast.toast-success { border-left: 3px solid var(--success); }
    .toast.toast-error   { border-left: 3px solid var(--danger); }
    .toast.toast-info    { border-left: 3px solid var(--primary); }

    /* ─── STORAGE BAR ─────────────────────────────────────────────── */
    .storage-bar { height: 8px; background: var(--bg-2); border-radius: var(--r-full); overflow: hidden; }
    .storage-fill { height: 100%; border-radius: var(--r-full); background: linear-gradient(90deg, var(--primary), var(--accent)); transition: width 0.8s cubic-bezier(0.4,0,0.2,1); }
    .storage-fill.danger { background: linear-gradient(90deg, var(--warning), var(--danger)); }

    /* ─── PROGRESS RING ───────────────────────────────────────────── */
    .ring { position: relative; flex-shrink: 0; }
    .ring svg { transform: rotate(-90deg); }
    .ring-track { fill: none; stroke: var(--bg-2); stroke-width: 7; }
    .ring-fill { fill: none; stroke: url(#ringGradient); stroke-width: 7; stroke-linecap: round; transition: stroke-dashoffset 1s cubic-bezier(0.4,0,0.2,1); }
    .ring-label { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-weight: 800; }

    /* ─── DOCUMENT ITEMS ──────────────────────────────────────────── */
    .doc-item {
        display: flex; align-items: center; gap: var(--space-3);
        padding: var(--space-4) var(--space-5);
        border-bottom: 1px solid var(--border);
        transition: background 0.12s;
        cursor: pointer;
    }
    .doc-item:last-child { border-bottom: none; }
    .doc-item:hover { background: var(--card-2); }
    .doc-thumb {
        width: 44px; height: 52px;
        border-radius: var(--r-sm);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        border: 1px solid var(--border);
        overflow: hidden;
        position: relative;
    }
    .doc-thumb.pdf { background: hsl(350 88% 97%); }
    .doc-thumb.image { background: hsl(231 80% 97%); }
    .doc-thumb svg { width: 22px; height: 22px; }
    .doc-info { flex: 1; min-width: 0; }
    .doc-title { font-size: var(--text-sm); font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .doc-meta { font-size: var(--text-xs); color: var(--text-muted); margin-top: 2px; display: flex; gap: 10px; flex-wrap: wrap; }
    .doc-actions { display: flex; gap: 4px; flex-shrink: 0; opacity: 0; transition: opacity 0.12s; }
    .doc-item:hover .doc-actions { opacity: 1; }

    /* Doc Grid */
    .doc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: var(--space-4); }
    .doc-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--r);
        overflow: hidden;
        cursor: pointer;
        transition: var(--transition);
    }
    .doc-card:hover { border-color: hsl(231 80% 60% / 0.3); box-shadow: var(--shadow-md); transform: translateY(-2px); }
    .doc-card-preview { height: 110px; background: var(--card-2); display: flex; align-items: center; justify-content: center; border-bottom: 1px solid var(--border); }
    .doc-card-body { padding: var(--space-3); }
    .doc-card-name { font-size: var(--text-xs); font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .doc-card-meta { font-size: 10px; color: var(--text-muted); margin-top: 2px; }

    /* ─── FOLDER CARDS ────────────────────────────────────────────── */
    .folder-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: var(--space-4); }
    .folder-card {
        background: var(--card);
        border: 1.5px solid var(--border);
        border-radius: var(--r-md);
        padding: var(--space-5);
        cursor: pointer;
        text-decoration: none;
        transition: var(--transition);
        display: flex; flex-direction: column; gap: var(--space-3);
        position: relative;
    }
    .folder-card:hover { border-color: hsl(231 80% 60% / 0.35); transform: translateY(-3px); box-shadow: var(--shadow-md); }
    .folder-card-icon { width: 48px; height: 48px; border-radius: var(--r); display: flex; align-items: center; justify-content: center; }
    .folder-card-icon svg { width: 24px; height: 24px; }
    .folder-card-name { font-size: var(--text-base); font-weight: 700; color: var(--text); }
    .folder-card-meta { font-size: var(--text-xs); color: var(--text-muted); display: flex; gap: var(--space-3); }
    .folder-menu-btn { position: absolute; top: var(--space-3); right: var(--space-3); opacity: 0; transition: opacity 0.12s; }
    .folder-card:hover .folder-menu-btn { opacity: 1; }

    /* ─── EMPTY STATE ─────────────────────────────────────────────── */
    .empty-state { text-align: center; padding: var(--space-12) var(--space-6); }
    .empty-icon { width: 72px; height: 72px; border-radius: var(--r-xl); background: var(--bg-2); display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-5); }
    .empty-icon svg { width: 32px; height: 32px; color: var(--text-light); }
    .empty-title { font-size: var(--text-lg); font-weight: 700; color: var(--text); margin-bottom: var(--space-2); }
    .empty-desc { font-size: var(--text-sm); color: var(--text-muted); max-width: 280px; margin: 0 auto var(--space-5); line-height: 1.7; }

    /* ─── LOADING ─────────────────────────────────────────────────── */
    .spinner { width: 20px; height: 20px; border: 2.5px solid var(--border); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.7s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .skeleton { background: linear-gradient(90deg, var(--bg-2) 25%, var(--card) 50%, var(--bg-2) 75%); background-size: 200% 100%; animation: shimmer 1.4s ease infinite; border-radius: var(--r-sm); }
    @keyframes shimmer { from { background-position: 200% 0; } to { background-position: -200% 0; } }

    /* ─── AVATAR ──────────────────────────────────────────────────── */
    .avatar { width: 36px; height: 36px; border-radius: var(--r-full); background: linear-gradient(135deg, var(--primary), var(--accent)); display: flex; align-items: center; justify-content: center; font-size: var(--text-sm); font-weight: 700; color: #fff; flex-shrink: 0; border: 2px solid var(--card); }
    .avatar-sm { width: 28px; height: 28px; font-size: var(--text-xs); }
    .avatar-lg { width: 48px; height: 48px; font-size: var(--text-lg); }
    .avatar-xl { width: 64px; height: 64px; font-size: var(--text-2xl); }

    /* ─── DIVIDER ─────────────────────────────────────────────────── */
    .divider { text-align: center; position: relative; margin: var(--space-6) 0; color: var(--text-muted); font-size: var(--text-xs); font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase; }
    .divider::before, .divider::after { content: ''; position: absolute; top: 50%; height: 1px; background: var(--border); width: calc(50% - 32px); }
    .divider::before { left: 0; } .divider::after { right: 0; }

    /* ─── SIDEBAR STYLES ──────────────────────────────────────────── */
    .sidebar-logo { display: flex; align-items: center; gap: var(--space-3); padding: var(--space-6) var(--space-5); border-bottom: 1px solid var(--sidebar-border); }
    .logo-icon { width: 38px; height: 38px; background: linear-gradient(135deg, var(--primary), var(--accent)); border-radius: var(--r); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .logo-text { font-size: 19px; font-weight: 800; color: #fff; letter-spacing: -0.5px; }
    .logo-badge { font-size: 9px; color: hsl(231 80% 72%); font-weight: 600; letter-spacing: 0.8px; text-transform: uppercase; }

    .sidebar-company { padding: var(--space-4) var(--space-5); border-bottom: 1px solid var(--sidebar-border); }
    .company-label { font-size: 9px; color: hsl(215 16% 40%); text-transform: uppercase; letter-spacing: 0.9px; margin-bottom: 3px; font-weight: 600; }
    .company-name { font-size: var(--text-sm); font-weight: 700; color: hsl(220 20% 90%); }
    .company-plan { display: inline-block; font-size: 9px; background: hsl(231 80% 30%); color: var(--primary-light); padding: 2px 8px; border-radius: var(--r-full); margin-top: 4px; font-weight: 600; letter-spacing: 0.3px; }

    .sidebar-nav { flex: 1; padding: var(--space-4) var(--space-3); }
    .nav-section-title { font-size: 9px; text-transform: uppercase; letter-spacing: 0.9px; color: hsl(215 16% 35%); padding: var(--space-3) var(--space-3) var(--space-1); font-weight: 700; }
    .nav-item {
        display: flex; align-items: center; gap: var(--space-3);
        padding: 9px var(--space-3);
        border-radius: var(--r-sm);
        color: hsl(215 16% 60%);
        text-decoration: none;
        font-size: var(--text-sm);
        font-weight: 500;
        margin-bottom: 1px;
        transition: var(--transition);
        cursor: pointer;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
    }
    .nav-item:hover { background: var(--sidebar-hover); color: hsl(220 20% 90%); }
    .nav-item.active { background: hsl(231 80% 28%); color: hsl(231 80% 85%); font-weight: 600; }
    .nav-item .nav-icon { width: 17px; height: 17px; opacity: 0.75; flex-shrink: 0; }
    .nav-item.active .nav-icon { opacity: 1; }
    .nav-badge { margin-left: auto; font-size: 9px; background: var(--primary); color: #fff; padding: 2px 7px; border-radius: var(--r-full); font-weight: 700; }

    .sidebar-footer { padding: var(--space-4) var(--space-3); border-top: 1px solid var(--sidebar-border); }
    .user-card { display: flex; align-items: center; gap: var(--space-3); padding: var(--space-3); border-radius: var(--r-sm); background: hsl(222 47% 16%); }
    .user-name { font-size: var(--text-sm); font-weight: 600; color: hsl(220 20% 88%); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .user-role { font-size: 11px; color: hsl(215 16% 45%); }

    /* ─── SIDEBAR SCAN BUTTON ─────────────────────────────────────── */
    .sidebar-scan-btn {
        display: flex; align-items: center; gap: var(--space-3);
        margin: var(--space-3) var(--space-3) var(--space-2);
        padding: var(--space-3) var(--space-4);
        background: linear-gradient(135deg, var(--primary), var(--accent));
        border-radius: var(--r-sm);
        color: #fff;
        font-size: var(--text-sm);
        font-weight: 700;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: var(--transition-spring);
        box-shadow: 0 4px 16px hsl(231 80% 60% / 0.4);
        letter-spacing: 0.1px;
    }
    .sidebar-scan-btn:hover { box-shadow: 0 6px 24px hsl(231 80% 60% / 0.55); transform: translateY(-1px); }
    .sidebar-scan-btn svg { width: 18px; height: 18px; flex-shrink: 0; }

    /* ─── SEARCH BAR ──────────────────────────────────────────────── */
    .search-bar { position: relative; }
    .search-bar input { padding-left: 40px; padding-right: var(--space-4); border-radius: var(--r-full); background: var(--bg-2); border-color: var(--border); }
    .search-bar input:focus { background: var(--card); }
    .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: var(--text-light); pointer-events: none; }

    /* ─── CHIP FILTERS ────────────────────────────────────────────── */
    .chip-group { display: flex; gap: var(--space-2); flex-wrap: wrap; }
    .chip { display: inline-flex; align-items: center; gap: 5px; padding: 5px 12px; border-radius: var(--r-full); font-size: var(--text-xs); font-weight: 600; border: 1.5px solid var(--border); color: var(--text-muted); background: var(--card); cursor: pointer; transition: var(--transition); letter-spacing: 0.1px; }
    .chip:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-ultra); }
    .chip.active { border-color: var(--primary); color: var(--primary); background: var(--primary-ultra); }

    /* ─── PROGRESS STEPS ──────────────────────────────────────────── */
    .steps { display: flex; align-items: center; gap: 0; margin-bottom: var(--space-8); }
    .step { display: flex; flex-direction: column; align-items: center; gap: var(--space-2); flex: 1; position: relative; }
    .step:not(:last-child)::after { content: ''; position: absolute; top: 16px; left: calc(50% + 18px); right: calc(-50% + 18px); height: 2px; background: var(--border); z-index: 0; }
    .step.done:not(:last-child)::after { background: var(--primary); }
    .step-circle { width: 32px; height: 32px; border-radius: var(--r-full); border: 2px solid var(--border); background: var(--card); display: flex; align-items: center; justify-content: center; font-size: var(--text-xs); font-weight: 700; color: var(--text-muted); z-index: 1; transition: var(--transition); }
    .step.active .step-circle { border-color: var(--primary); background: var(--primary); color: #fff; box-shadow: 0 0 0 4px var(--primary-glow); }
    .step.done .step-circle { border-color: var(--success); background: var(--success); color: #fff; }
    .step-label { font-size: 10px; font-weight: 600; color: var(--text-light); white-space: nowrap; }
    .step.active .step-label { color: var(--primary); }
    .step.done .step-label { color: var(--success); }

    /* ─── SECTION HEADER ──────────────────────────────────────────── */
    .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-4); }
    .section-title { font-size: var(--text-md); font-weight: 700; color: var(--text); letter-spacing: -0.2px; }
    .section-link { font-size: var(--text-xs); color: var(--primary); font-weight: 600; letter-spacing: 0.1px; }
    .section-link:hover { opacity: 0.8; }

    /* ─── UTILITY ─────────────────────────────────────────────────── */
    .d-flex { display: flex; }
    .align-center { align-items: center; }
    .justify-between { justify-content: space-between; }
    .gap-2 { gap: var(--space-2); }
    .gap-3 { gap: var(--space-3); }
    .gap-4 { gap: var(--space-4); }
    .flex-1 { flex: 1; }
    .text-muted { color: var(--text-muted); }
    .text-sm { font-size: var(--text-sm); }
    .text-xs { font-size: var(--text-xs); }
    .fw-700 { font-weight: 700; }
    .mt-1 { margin-top: var(--space-1); }
    .mt-2 { margin-top: var(--space-2); }
    .mt-4 { margin-top: var(--space-4); }
    .mb-4 { margin-bottom: var(--space-4); }
    .mb-6 { margin-bottom: var(--space-6); }
    .w-full { width: 100%; }
    .truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* ─── FILE UPLOAD ZONE ────────────────────────────────────────── */
    .upload-zone { border: 2px dashed var(--border); border-radius: var(--r-md); padding: var(--space-8); text-align: center; cursor: pointer; transition: var(--transition); background: var(--card-2); }
    .upload-zone:hover, .upload-zone.dragover { border-color: var(--primary); background: var(--primary-ultra); }
    .upload-zone-icon { width: 56px; height: 56px; border-radius: var(--r-md); background: var(--primary-ultra); display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4); }
    .upload-zone-icon svg { width: 28px; height: 28px; color: var(--primary); }

    /* ─── QUICK ACTION CARDS ──────────────────────────────────────── */
    .quick-actions { display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--space-3); }
    .quick-action {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--r);
        padding: var(--space-4) var(--space-3);
        display: flex; flex-direction: column; align-items: center; gap: var(--space-2);
        cursor: pointer;
        text-decoration: none;
        transition: var(--transition);
        text-align: center;
        color: var(--text-2);
    }
    .quick-action:hover { border-color: hsl(231 80% 60% / 0.3); background: var(--card-2); transform: translateY(-2px); box-shadow: var(--shadow); }
    .quick-action-icon { width: 44px; height: 44px; border-radius: var(--r-sm); display: flex; align-items: center; justify-content: center; }
    .quick-action-icon svg { width: 20px; height: 20px; }
    .quick-action-label { font-size: 11px; font-weight: 600; color: var(--text-2); }

    /* ─── ACTIVITY FEED ───────────────────────────────────────────── */
    .activity-item { display: flex; gap: var(--space-3); padding: var(--space-3) 0; border-bottom: 1px solid var(--border); }
    .activity-item:last-child { border-bottom: none; }
    .activity-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; margin-top: 5px; }
    .activity-content { flex: 1; }
    .activity-text { font-size: var(--text-sm); color: var(--text-2); line-height: 1.5; }
    .activity-time { font-size: 11px; color: var(--text-light); margin-top: 2px; }

    /* ═══════════════════════════════════════════════════════════════
       RESPONSIVE
    ═══════════════════════════════════════════════════════════════ */
    @media (max-width: 1024px) {
        :root { --sidebar-w: 220px; }
    }

    @media (max-width: 768px) {
        .sidebar { transform: translateX(-100%); }
        .sidebar.open { transform: translateX(0); }
        .main { margin-left: 0; padding-bottom: var(--bottom-nav-h); }
        .bottom-nav { display: flex; }
        .topbar { padding: 0 var(--space-4); }
        .page-content { padding: var(--space-4); }
        .stats-grid { grid-template-columns: repeat(2, 1fr); gap: var(--space-3); }
        .quick-actions { grid-template-columns: repeat(4, 1fr); gap: var(--space-2); }
        .quick-action { padding: var(--space-3) var(--space-2); }
        .folder-grid { grid-template-columns: repeat(2, 1fr); }
        .doc-grid { grid-template-columns: repeat(2, 1fr); }
        #toast-container { bottom: calc(var(--bottom-nav-h) + 8px); right: 12px; left: 12px; }
        .toast { min-width: unset; max-width: unset; width: 100%; }
    }

    @media (max-width: 480px) {
        .stats-grid { grid-template-columns: 1fr 1fr; }
        .quick-actions { gap: var(--space-2); }
        .modal { padding: var(--space-6); }
    }

    /* Stats grid general */
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--space-4); margin-bottom: var(--space-5); }
    @media (max-width: 1280px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 480px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }

    /* Mobile overlay */
    .sidebar-overlay { display: none; position: fixed; inset: 0; background: hsl(222 47% 5% / 0.5); z-index: 150; backdrop-filter: blur(2px); }
    .sidebar-overlay.show { display: block; }
    </style>

    @stack('styles')
</head>
<body>

<svg style="display:none" width="0" height="0">
    <defs>
        <linearGradient id="ringGradient" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" style="stop-color:hsl(231,80%,60%)"/>
            <stop offset="100%" style="stop-color:hsl(193,87%,55%)"/>
        </linearGradient>
    </defs>
</svg>

<div class="app-layout">
    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        @include('partials.sidebar-content')
    </aside>

    <!-- Main Content -->
    <div class="main">
        @yield('content')
    </div>
</div>

<!-- Bottom Navigation (Mobile) -->
<nav class="bottom-nav" id="bottomNav">
    <div class="bottom-nav-inner">
        <a href="{{ route('dashboard') }}" class="bottom-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" id="bnav-home">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline stroke-linecap="round" stroke-linejoin="round" points="9 22 9 12 15 12 15 22"/></svg>
            Home
        </a>
        <a href="{{ route('documents.index') }}" class="bottom-nav-item {{ request()->routeIs('documents.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Documents
        </a>
        <a href="{{ route('scan') }}" class="bottom-nav-item" style="color:transparent;">
            <div class="bottom-nav-scan">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
            </div>
            <span style="color:var(--text-light);font-size:10px;">Scan</span>
        </a>
        <a href="{{ route('folders.index') }}" class="bottom-nav-item {{ request()->routeIs('folders.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
            Folders
        </a>
        <a href="{{ route('profile') }}" class="bottom-nav-item {{ request()->routeIs('profile') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            Profile
        </a>
    </div>
</nav>

<!-- Toast Container -->
<div id="toast-container"></div>

<script>
// ─── Sidebar Mobile Toggle ────────────────────────────────────────
function openSidebar() {
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('sidebarOverlay').classList.add('show');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('show');
}

// ─── Modal ────────────────────────────────────────────────────────
function openModal(id) {
    const el = document.getElementById(id);
    el.classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    const el = document.getElementById(id);
    el.classList.remove('show');
    document.body.style.overflow = '';
}
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('show');
        document.body.style.overflow = '';
    }
});

// ─── Bottom Sheet ─────────────────────────────────────────────────
function openSheet(id) {
    const el = document.getElementById(id);
    el.classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeSheet(id) {
    const el = document.getElementById(id);
    el.classList.remove('show');
    document.body.style.overflow = '';
}
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('sheet-overlay')) {
        e.target.classList.remove('show');
        document.body.style.overflow = '';
    }
});

// ─── Toast ────────────────────────────────────────────────────────
function showToast(message, type = 'info', duration = 3500) {
    const icons = {
        success: '<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="color:var(--success)"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>',
        error: '<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="color:var(--danger)"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>',
        info: '<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="color:var(--primary)"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    };
    const t = document.createElement('div');
    t.className = `toast toast-${type}`;
    t.innerHTML = `${icons[type] || ''}<span>${message}</span>`;
    document.getElementById('toast-container').appendChild(t);
    setTimeout(() => { t.style.animation = 'toastIn 0.28s reverse'; setTimeout(() => t.remove(), 280); }, duration);
}

// ─── Dark Mode ────────────────────────────────────────────────────
(function() {
    const saved = localStorage.getItem('docuscan-theme') || 'light';
    document.documentElement.setAttribute('data-theme', saved);
})();
function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme');
    const next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('docuscan-theme', next);
}

// ─── Keyboard Shortcuts ───────────────────────────────────────────
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.show').forEach(m => m.classList.remove('show'));
        document.querySelectorAll('.sheet-overlay.show').forEach(s => s.classList.remove('show'));
        document.body.style.overflow = '';
        closeSidebar();
    }
});
</script>

@stack('scripts')
</body>
</html>
