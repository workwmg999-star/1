@extends('layouts.app')
@section('title', 'Storage Usage')

@php
    $usedGb  = $company['storage_used_gb'] ?? 2.4;
    $limitGb = $company['storage_limit_gb'] ?? 10;
    $availGb = max(0, $limitGb - $usedGb);
    $pct     = min(100, round(($usedGb / max(1, $limitGb)) * 100));
@endphp

@section('content')
<div class="topbar">
    <button class="btn btn-ghost btn-icon" onclick="openSidebar()" id="menuBtnStor" style="display:none;">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <script>if(window.innerWidth<=768)document.getElementById('menuBtnStor').style.display='flex';</script>
    <div class="topbar-left">
        <div class="topbar-title">Cloud Storage Usage</div>
        <div class="topbar-subtitle">Monitor cloud bandwidth & enterprise quota</div>
    </div>
    <div class="topbar-actions">
        <a href="{{ route('subscriptions') }}" class="btn btn-gradient" style="gap:6px;">
            ⚡ Upgrade Storage Plan
        </a>
    </div>
</div>

<div class="page-content" style="max-width: 800px; margin: 0 auto;">

    {{-- Main Usage Overview Card --}}
    <div class="card" style="padding: 32px; margin-bottom: 24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:20px;margin-bottom:24px;">
            <div>
                <div style="font-size:12px;font-weight:700;color:var(--primary);text-transform:uppercase;letter-spacing:0.8px;">Storage Quota</div>
                <div style="font-size:32px;font-weight:800;letter-spacing:-1px;margin-top:4px;">
                    {{ number_format($usedGb, 2) }} GB <span style="font-size:16px;color:var(--text-muted);font-weight:500;">/ {{ $limitGb }} GB</span>
                </div>
            </div>
            <div class="ring" style="width:90px;height:90px;">
                <svg viewBox="0 0 80 80" width="90" height="90">
                    <circle class="ring-track" cx="40" cy="40" r="32"/>
                    <circle cx="40" cy="40" r="32" fill="none" stroke="url(#ringGradient)" stroke-width="7" stroke-linecap="round"
                            stroke-dasharray="201" stroke-dashoffset="{{ 201 * (1 - $pct/100) }}"
                            style="transform:rotate(-90deg);transform-origin:center;"/>
                </svg>
                <div class="ring-label" style="font-size:16px;font-weight:800;">{{ $pct }}%</div>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="storage-bar" style="height:12px;margin-bottom:16px;">
            <div class="storage-fill" style="width:{{ $pct }}%;"></div>
        </div>

        <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--text-muted);font-weight:500;">
            <span>Used: {{ number_format($usedGb, 2) }} GB</span>
            <span>Available: {{ number_format($availGb, 2) }} GB</span>
        </div>
    </div>

    {{-- File Breakdown Card --}}
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header"><div class="card-title">Storage Breakdown by Category</div></div>
        <div class="card-body" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;">
            <div style="display:flex;align-items:center;gap:14px;padding:12px;background:var(--bg-2);border-radius:var(--r-md);">
                <div style="width:42px;height:42px;border-radius:var(--r-sm);background:hsl(350,88%,95%);color:var(--danger);display:flex;align-items:center;justify-content:center;">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <div style="font-size:16px;font-weight:800;">1.65 GB</div>
                    <div style="font-size:12px;color:var(--text-muted);">PDF Documents</div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:14px;padding:12px;background:var(--bg-2);border-radius:var(--r-md);">
                <div style="width:42px;height:42px;border-radius:var(--r-sm);background:var(--primary-ultra);color:var(--primary);display:flex;align-items:center;justify-content:center;">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <div style="font-size:16px;font-weight:800;">0.75 GB</div>
                    <div style="font-size:12px;color:var(--text-muted);">Scanned Images</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Upgrade Banner --}}
    <div style="background:linear-gradient(135deg,var(--primary),var(--accent));border-radius:var(--r-xl);padding:28px;color:#fff;display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;">
        <div>
            <div style="font-size:20px;font-weight:800;margin-bottom:4px;">Need more storage?</div>
            <div style="font-size:14px;opacity:0.85;">Upgrade to Business or Enterprise plan for 100 GB+ storage and unlimited team seats.</div>
        </div>
        <a href="{{ route('subscriptions') }}" class="btn btn-lg" style="background:#fff;color:var(--primary);font-weight:800;">
            Upgrade Now →
        </a>
    </div>

</div>
@endsection
