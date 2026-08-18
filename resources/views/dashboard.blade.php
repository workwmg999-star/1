@extends('layouts.app')
@section('title', 'Dashboard')

@php
    $stats   = $dash['stats']   ?? [];
    $storage = $dash['storage'] ?? [];
    $plan    = $dash['plan']    ?? [];
    $recentDocs = $dash['recent_documents'] ?? [];
    $user = Auth::user() ?? session('auth_user');
    $userName = is_array($user) ? ($user['name'] ?? 'there') : ($user->name ?? 'there');
    $firstName = explode(' ', $userName)[0];
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $usedPct  = min($storage['usage_percent'] ?? 0, 100);
    $dashCircumference = 2 * 3.14159 * 32;
@endphp

@push('styles')
<style>
/* Dashboard specific */
.welcome-banner {
    background: linear-gradient(135deg, hsl(231,80%,58%) 0%, hsl(231,80%,46%) 40%, hsl(193,87%,45%) 100%);
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 24px;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    overflow: hidden;
    position: relative;
    box-shadow: 0 8px 32px hsl(231,80%,60%,0.35);
}
.welcome-banner::before {
    content: '';
    position: absolute;
    width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 65%);
    right: -60px; top: -60px;
    pointer-events: none;
}
.welcome-banner::after {
    content: '';
    position: absolute;
    width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(255,255,255,0.07) 0%, transparent 65%);
    left: 40%; bottom: -60px;
    pointer-events: none;
}
.welcome-greeting { font-size: 14px; font-weight: 500; opacity: 0.85; margin-bottom: 4px; }
.welcome-name { font-size: 26px; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 8px; }
.welcome-sub { font-size: 13px; opacity: 0.75; }
.welcome-scan-btn {
    background: rgba(255,255,255,0.22);
    border: 1.5px solid rgba(255,255,255,0.35);
    color: #fff;
    border-radius: 12px;
    padding: 12px 22px;
    font-size: 14px;
    font-weight: 700;
    display: flex; align-items: center; gap: 8px;
    text-decoration: none;
    backdrop-filter: blur(8px);
    transition: all 0.2s;
    white-space: nowrap;
    flex-shrink: 0;
    z-index: 1;
}
.welcome-scan-btn:hover {
    background: rgba(255,255,255,0.3);
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}
.welcome-scan-btn svg { width: 18px; height: 18px; }

/* Activity dot */
.act-upload  { background: hsl(231,80%,60%); }
.act-share   { background: hsl(193,87%,50%); }
.act-team    { background: hsl(162,76%,42%); }
.act-storage { background: hsl(43,96%,50%); }

/* Scroll x on mobile */
.stats-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: 4px; }
.stats-scroll::-webkit-scrollbar { height: 0; }
.stats-inner { display: flex; gap: 14px; min-width: max-content; }
.stats-inner .stat-card { min-width: 180px; }

@media (min-width: 769px) {
    .stats-scroll { overflow: visible; }
    .stats-inner { display: grid; grid-template-columns: repeat(4,1fr); min-width: unset; }
    .stats-inner .stat-card { min-width: unset; }
}

/* Content grid */
.dash-grid { display: grid; grid-template-columns: 1fr 300px; gap: 20px; }
@media (max-width: 1100px) { .dash-grid { grid-template-columns: 1fr; } }
@media (max-width: 768px) {
    .welcome-name { font-size: 22px; }
    .welcome-banner { padding: 20px 22px; }
}
</style>
@endpush

@section('content')

{{-- Topbar --}}
<div class="topbar">
    <button class="btn btn-ghost btn-icon" onclick="openSidebar()" style="display:none;" id="menuBtn">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <script>if(window.innerWidth<=768)document.getElementById('menuBtn').style.display='flex';</script>
    <div class="topbar-left">
        <div class="topbar-title">Dashboard</div>
        <div class="topbar-subtitle">{{ now()->format('l, d F Y') }}</div>
    </div>
    <div class="topbar-actions">
        <a href="{{ route('notifications.index') ?? '#' }}" class="btn btn-ghost btn-icon" title="Notifications" style="position:relative;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <span style="position:absolute;top:6px;right:6px;width:8px;height:8px;background:var(--danger);border-radius:50%;border:2px solid var(--card);"></span>
        </a>
        <a href="{{ route('scan') }}" class="btn btn-gradient" style="gap:7px;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
            Scan
        </a>
    </div>
</div>

<div class="page-content">

    @if(session('success'))
    <div class="alert alert-success" style="margin-bottom:20px;">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Welcome Banner --}}
    <div class="welcome-banner">
        <div style="z-index:1;">
            <div class="welcome-greeting">{{ $greeting }},</div>
            <div class="welcome-name">{{ $firstName }} 👋</div>
            <div class="welcome-sub">
                You have <strong>{{ $stats['total_documents'] ?? 0 }}</strong> documents in the cloud · {{ now()->format('d M Y') }}
            </div>
        </div>
        <a href="{{ route('scan') }}" class="welcome-scan-btn">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
            Scan Document
        </a>
    </div>

    {{-- Stats --}}
    <div class="stats-scroll" style="margin-bottom:20px;">
        <div class="stats-inner">
            <div class="stat-card">
                <div class="stat-icon" style="background:hsl(231,80%,95%);">
                    <svg fill="none" viewBox="0 0 24 24" stroke="hsl(231,80%,50%)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <div class="stat-value">{{ $stats['total_documents'] ?? 0 }}</div>
                    <div class="stat-label">Total Documents</div>
                    <div class="stat-change up">↑ 12 this week</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:hsl(162,76%,94%);">
                    <svg fill="none" viewBox="0 0 24 24" stroke="hsl(162,76%,35%)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
                </div>
                <div>
                    <div class="stat-value">{{ $stats['total_folders'] ?? 0 }}</div>
                    <div class="stat-label">Folders</div>
                    <div class="stat-change up">↑ 2 new</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:hsl(43,96%,94%);">
                    <svg fill="none" viewBox="0 0 24 24" stroke="hsl(43,96%,35%)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                </div>
                <div>
                    <div class="stat-value">{{ number_format($storage['used_gb'] ?? 0, 1) }}<span style="font-size:13px;font-weight:500;opacity:0.5;"> GB</span></div>
                    <div class="stat-label">Storage Used</div>
                    <div class="stat-change" style="color:var(--text-muted);">of {{ $storage['limit_gb'] ?? 10 }} GB</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:hsl(193,87%,93%);">
                    <svg fill="none" viewBox="0 0 24 24" stroke="hsl(193,87%,35%)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
                </div>
                <div>
                    <div class="stat-value">{{ $stats['total_users'] ?? 1 }}</div>
                    <div class="stat-label">Team Members</div>
                    <div class="stat-change up">Active now</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div style="margin-bottom:20px;">
        <div class="section-header">
            <div class="section-title">Quick Actions</div>
        </div>
        <div class="quick-actions">
            <a href="{{ route('scan') }}" class="quick-action">
                <div class="quick-action-icon" style="background:hsl(231,80%,95%);">
                    <svg fill="none" viewBox="0 0 24 24" stroke="hsl(231,80%,55%)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
                </div>
                <div class="quick-action-label">Scan</div>
            </a>
            <button class="quick-action" onclick="openModal('uploadModal')">
                <div class="quick-action-icon" style="background:hsl(162,76%,93%);">
                    <svg fill="none" viewBox="0 0 24 24" stroke="hsl(162,76%,38%)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                </div>
                <div class="quick-action-label">Upload</div>
            </button>
            <button class="quick-action" onclick="openModal('newFolderModal')">
                <div class="quick-action-icon" style="background:hsl(43,96%,93%);">
                    <svg fill="none" viewBox="0 0 24 24" stroke="hsl(43,96%,35%)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                </div>
                <div class="quick-action-label">New Folder</div>
            </button>
            <a href="{{ route('documents.search') ?? '#' }}" class="quick-action">
                <div class="quick-action-icon" style="background:hsl(193,87%,93%);">
                    <svg fill="none" viewBox="0 0 24 24" stroke="hsl(193,87%,38%)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <div class="quick-action-label">Search</div>
            </a>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="dash-grid">

        {{-- Recent Documents --}}
        <div>
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Recent Documents</div>
                        <div class="card-subtitle">Latest uploaded files</div>
                    </div>
                    <a href="{{ route('documents.index') }}" class="btn btn-outline btn-sm">View all</a>
                </div>

                @if(count($recentDocs))
                <div>
                    @foreach($recentDocs as $doc)
                    <div class="doc-item">
                        <div class="doc-thumb {{ $doc['file_type'] === 'pdf' ? 'pdf' : 'image' }}">
                            @if($doc['file_type'] === 'pdf')
                            <svg fill="none" viewBox="0 0 24 24" stroke="hsl(350,88%,55%)" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            @else
                            <svg fill="none" viewBox="0 0 24 24" stroke="hsl(231,80%,55%)" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            @endif
                        </div>
                        <div class="doc-info">
                            <div class="doc-title">{{ $doc['title'] }}</div>
                            <div class="doc-meta">
                                <span>{{ $doc['folder']['name'] ?? 'Unfiled' }}</span>
                                <span>{{ $doc['size_formatted'] }}</span>
                                <span>{{ \Carbon\Carbon::parse($doc['created_at'])->diffForHumans() }}</span>
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                            <span class="badge {{ $doc['file_type'] === 'pdf' ? 'badge-danger' : 'badge-primary' }}">
                                {{ strtoupper($doc['file_type']) }}
                            </span>
                            <a href="{{ route('documents.download', $doc['id']) }}" class="btn btn-ghost btn-icon" title="Download">
                                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div class="empty-title">No documents yet</div>
                    <div class="empty-desc">Scan or upload your first document to get started.</div>
                    <a href="{{ route('scan') }}" class="btn btn-gradient">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
                        Scan First Document
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- Right Column --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

            {{-- Storage --}}
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Storage</div>
                    <a href="{{ route('storage.index') ?? '#' }}" class="btn btn-ghost btn-sm" style="font-size:12px;color:var(--primary);">Details</a>
                </div>
                <div class="card-body" style="padding-top:12px;">
                    <div style="display:flex;align-items:center;gap:16px;margin-bottom:14px;">
                        <div class="ring" style="width:72px;height:72px;">
                            <svg viewBox="0 0 80 80" width="72" height="72">
                                <defs>
                                    <linearGradient id="rg1" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="0%" style="stop-color:hsl(231,80%,60%)"/>
                                        <stop offset="100%" style="stop-color:hsl(193,87%,55%)"/>
                                    </linearGradient>
                                </defs>
                                <circle class="ring-track" cx="40" cy="40" r="32"/>
                                <circle cx="40" cy="40" r="32"
                                    fill="none"
                                    stroke="url(#rg1)"
                                    stroke-width="7"
                                    stroke-linecap="round"
                                    stroke-dasharray="{{ $dashCircumference }}"
                                    stroke-dashoffset="{{ $dashCircumference * (1 - $usedPct / 100) }}"
                                    style="transform:rotate(-90deg);transform-origin:center;transition:stroke-dashoffset 1s ease;"/>
                            </svg>
                            <div class="ring-label" style="font-size:12px;font-weight:800;">{{ $usedPct }}%</div>
                        </div>
                        <div>
                            <div style="font-size:20px;font-weight:800;letter-spacing:-0.5px;">{{ number_format($storage['used_gb'] ?? 0, 2) }} <span style="font-size:12px;font-weight:500;color:var(--text-muted);">GB</span></div>
                            <div style="font-size:12px;color:var(--text-muted);">of {{ $storage['limit_gb'] ?? 10 }} GB used</div>
                        </div>
                    </div>
                    <div class="storage-bar">
                        <div class="storage-fill {{ $usedPct > 80 ? 'danger' : '' }}" style="width:{{ $usedPct }}%;"></div>
                    </div>
                    @if($usedPct > 75)
                    <div style="margin-top:10px;font-size:12px;color:var(--warning);font-weight:600;">⚠ Storage almost full</div>
                    @endif
                    <a href="{{ route('subscriptions') }}" class="btn btn-outline btn-sm" style="margin-top:14px;width:100%;justify-content:center;">Upgrade Storage</a>
                </div>
            </div>

            {{-- Current Plan --}}
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Current Plan</div>
                    <span class="badge badge-primary">{{ $plan['name'] ?? 'Free' }}</span>
                </div>
                <div class="card-body" style="padding-top:10px;">
                    <div style="display:flex;flex-direction:column;gap:8px;font-size:13px;color:var(--text-2);">
                        <div style="display:flex;justify-content:space-between;">
                            <span>Documents</span>
                            <strong>{{ $plan['max_documents'] ?? '500' }}</strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <span>Users</span>
                            <strong>{{ $plan['max_users'] ?? '2' }}</strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <span>Storage</span>
                            <strong>{{ $storage['limit_gb'] ?? 10 }} GB</strong>
                        </div>
                    </div>
                    <a href="{{ route('subscriptions') }}" class="btn btn-gradient btn-sm" style="margin-top:14px;width:100%;justify-content:center;">⚡ Upgrade Plan</a>
                </div>
            </div>

            {{-- Folders --}}
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Folders</div>
                    <a href="{{ route('folders.index') }}" class="btn btn-ghost btn-sm" style="font-size:12px;color:var(--primary);">All →</a>
                </div>
                <div class="card-body" style="padding-top:8px;padding-bottom:8px;">
                    @foreach(array_slice($folders ?? [], 0, 5) as $folder)
                    <a href="{{ route('documents.index', ['folder_id' => $folder['id']]) }}"
                       style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border);text-decoration:none;">
                        <span style="width:10px;height:10px;border-radius:50%;background:{{ $folder['color'] ?? 'var(--primary)' }};flex-shrink:0;"></span>
                        <span style="font-size:13px;font-weight:500;flex:1;color:var(--text-2);" class="truncate">{{ $folder['name'] }}</span>
                        <span style="font-size:11px;color:var(--text-muted);">{{ $folder['documents_count'] ?? 0 }}</span>
                    </a>
                    @endforeach
                    @if(empty($folders))
                    <div style="text-align:center;padding:12px;color:var(--text-muted);font-size:13px;">No folders yet</div>
                    @endif
                </div>
            </div>

            {{-- Recent Activity --}}
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Recent Activity</div>
                </div>
                <div class="card-body" style="padding-top:8px;">
                    <div class="activity-item">
                        <div class="activity-dot act-upload"></div>
                        <div class="activity-content">
                            <div class="activity-text">Document <strong>Facture_2024.pdf</strong> uploaded</div>
                            <div class="activity-time">2 minutes ago</div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-dot act-team"></div>
                        <div class="activity-content">
                            <div class="activity-text">New team member <strong>Sara A.</strong> joined</div>
                            <div class="activity-time">1 hour ago</div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-dot act-share"></div>
                        <div class="activity-content">
                            <div class="activity-text">Document shared with <strong>Finance team</strong></div>
                            <div class="activity-time">3 hours ago</div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-dot act-storage"></div>
                        <div class="activity-content">
                            <div class="activity-text">Storage usage at <strong>{{ $usedPct }}%</strong></div>
                            <div class="activity-time">Today</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

{{-- Upload Modal --}}
@include('partials.upload-modal')

{{-- New Folder Modal --}}
<div class="modal-overlay" id="newFolderModal">
    <div class="modal modal-sm">
        <div class="modal-header">
            <div class="modal-title">New Folder</div>
            <button class="modal-close" onclick="closeModal('newFolderModal')">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('folders.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Folder Name</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Factures 2024" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">Color</label>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    @foreach(['#4361ee','#06d6a0','#ef233c','#ffd166','#4cc9f0','#7c3aed','#f72585','#023e8a'] as $c)
                    <label style="cursor:pointer;">
                        <input type="radio" name="color" value="{{ $c }}" style="display:none;" {{ $c === '#4361ee' ? 'checked' : '' }}>
                        <span style="display:block;width:28px;height:28px;border-radius:50%;background:{{ $c }};border:3px solid transparent;transition:all 0.15s;"
                              onclick="this.previousElementSibling.checked=true;document.querySelectorAll('.color-opt').forEach(e=>e.style.borderColor='transparent');this.style.borderColor='var(--text)';" class="color-opt"></span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('newFolderModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Folder</button>
            </div>
        </form>
    </div>
</div>

@endsection
