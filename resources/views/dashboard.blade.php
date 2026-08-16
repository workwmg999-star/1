@extends('layouts.app')
@section('title', 'Dashboard')

@php
    $stats   = $dash['stats']   ?? [];
    $storage = $dash['storage'] ?? [];
    $plan    = $dash['plan']    ?? [];
    $recentDocs = $dash['recent_documents'] ?? [];
@endphp

@section('content')
<div class="topbar">
    <div>
        <div class="topbar-title">Dashboard</div>
        <div class="topbar-subtitle">{{ now()->format('l, d F Y') }}</div>
    </div>
    <div style="display:flex;gap:10px;">
        <a href="{{ route('scan') }}" class="btn btn-primary" style="background:linear-gradient(135deg,#4f46e5,#06b6d4);box-shadow:0 4px 12px rgba(79,70,229,0.3);">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
            📷 Scan Document
        </a>
        <button onclick="openModal('uploadModal')" class="btn btn-outline">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Upload File
        </button>
    </div>
</div>

<div class="page-content">

    @if(session('success'))
    <div class="alert alert-success">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:#ede9fe;">
                <svg fill="none" viewBox="0 0 24 24" stroke="#7c3aed" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['total_documents'] ?? 0 }}</div>
                <div class="stat-label">Total Documents</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#dbeafe;">
                <svg fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['total_folders'] ?? 0 }}</div>
                <div class="stat-label">Folders</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#d1fae5;">
                <svg fill="none" viewBox="0 0 24 24" stroke="#059669" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['total_users'] ?? 0 }}</div>
                <div class="stat-label">Team Members</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;">
                <svg fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ number_format($storage['used_gb'] ?? 0, 1) }}</div>
                <div class="stat-label">GB Used of {{ $storage['limit_gb'] ?? 0 }} GB</div>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;">

        {{-- Recent Documents --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">Recent Documents</div>
                <a href="{{ route('documents.index') }}" class="btn btn-outline btn-sm">View all</a>
            </div>
            @if(count($recentDocs))
            <div>
                @foreach($recentDocs as $doc)
                <div class="doc-item">
                    <div class="doc-icon {{ $doc['file_type'] === 'pdf' ? 'pdf' : 'image' }}">
                        @if($doc['file_type'] === 'pdf')
                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        @else
                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
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
                    <div class="doc-actions">
                        <a href="{{ route('documents.download', $doc['id']) }}" class="btn btn-outline btn-sm">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="card-body" style="text-align:center;padding:40px;">
                <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="#cbd5e1" stroke-width="1.5" style="margin:0 auto 12px;display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p style="color:var(--text-muted);font-size:14px;">No documents yet. Upload your first scan!</p>
                <button onclick="openModal('uploadModal')" class="btn btn-primary" style="margin-top:12px;">Upload Document</button>
            </div>
            @endif
        </div>

        {{-- Storage + Plan sidebar --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

            <div class="card">
                <div class="card-header"><div class="card-title">Storage Usage</div></div>
                <div class="card-body" style="padding-top:8px;">
                    <div style="display:flex;align-items:center;gap:16px;margin-bottom:12px;">
                        <div class="ring">
                            <svg viewBox="0 0 80 80" width="80" height="80">
                                <circle class="ring-track" cx="40" cy="40" r="32"/>
                                <circle class="ring-fill" cx="40" cy="40" r="32"
                                    stroke-dasharray="{{ 2 * 3.14159 * 32 }}"
                                    stroke-dashoffset="{{ 2 * 3.14159 * 32 * (1 - ($storage['usage_percent'] ?? 0) / 100) }}"
                                    id="ringFill"/>
                            </svg>
                            <div class="ring-label">{{ $storage['usage_percent'] ?? 0 }}%</div>
                        </div>
                        <div>
                            <div style="font-size:22px;font-weight:800;color:var(--text);">{{ number_format($storage['used_gb'] ?? 0, 2) }} <span style="font-size:13px;font-weight:500;color:var(--text-muted);">GB</span></div>
                            <div style="font-size:12px;color:var(--text-muted);">of {{ $storage['limit_gb'] ?? 0 }} GB used</div>
                        </div>
                    </div>
                    <div class="storage-bar">
                        <div class="storage-fill" style="width:{{ min($storage['usage_percent'] ?? 0, 100) }}%"></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><div class="card-title">Current Plan</div></div>
                <div class="card-body" style="padding-top:8px;">
                    <div style="font-size:20px;font-weight:800;color:var(--primary);margin-bottom:8px;">{{ $plan['name'] ?? 'Free' }}</div>
                    <div style="font-size:13px;color:var(--text-muted);display:flex;flex-direction:column;gap:6px;">
                        <span>📄 Documents: {{ $plan['max_documents'] ?? '500' }}</span>
                        <span>👥 Users: {{ $plan['max_users'] ?? '2' }}</span>
                    </div>
                    <a href="{{ route('subscriptions') }}" class="btn btn-outline btn-sm" style="margin-top:14px;width:100%;justify-content:center;">Upgrade Plan</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><div class="card-title">Folders</div></div>
                <div class="card-body" style="padding-top:4px;">
                    @foreach(array_slice($folders ?? [], 0, 5) as $folder)
                    <a href="{{ route('documents.index', ['folder_id' => $folder['id']]) }}" style="display:flex;align-items:center;gap:10px;padding:8px 0;text-decoration:none;color:var(--text);border-bottom:1px solid var(--border);">
                        <span style="width:10px;height:10px;border-radius:50%;background:{{ $folder['color'] }};flex-shrink:0;"></span>
                        <span style="font-size:13.5px;font-weight:500;flex:1;">{{ $folder['name'] }}</span>
                        <span style="font-size:12px;color:var(--text-muted);">{{ $folder['documents_count'] ?? 0 }}</span>
                    </a>
                    @endforeach
                    @if(count($folders ?? []) > 5)
                    <a href="{{ route('folders.index') }}" style="font-size:12px;color:var(--primary);text-decoration:none;display:block;margin-top:8px;">View all folders →</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Upload Modal --}}
@include('partials.upload-modal')
@endsection
