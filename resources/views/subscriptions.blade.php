@extends('layouts.app')
@section('title', 'Subscription Plans')

@push('styles')
<style>
.pricing-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-top: 24px; }
@media (max-width: 1100px) { .pricing-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px) { .pricing-grid { grid-template-columns: 1fr; } }

.price-card {
    background: var(--card);
    border: 1.5px solid var(--border);
    border-radius: var(--r-xl);
    padding: 28px 24px;
    display: flex; flex-direction: column;
    position: relative;
    transition: var(--transition);
    box-shadow: var(--shadow-card);
}
.price-card:hover { border-color: hsl(231 80% 60% / 0.4); box-shadow: var(--shadow-md); transform: translateY(-4px); }
.price-card.popular { border-color: var(--primary); border-width: 2px; }
.popular-badge {
    position: absolute; top: -13px; left: 50%; transform: translateX(-50%);
    background: linear-gradient(135deg, var(--primary), var(--accent));
    color: #fff; font-size: 10px; font-weight: 800;
    padding: 4px 14px; border-radius: var(--r-full);
    letter-spacing: 0.5px; text-transform: uppercase;
    box-shadow: 0 4px 12px hsl(231 80% 60% / 0.3);
}

.price-header { margin-bottom: 20px; text-align: center; }
.price-name { font-size: 18px; font-weight: 800; color: var(--text); }
.price-desc { font-size: 12px; color: var(--text-muted); margin-top: 4px; }
.price-val { font-size: 34px; font-weight: 800; color: var(--text); letter-spacing: -1px; margin: 12px 0 4px; }
.price-val span { font-size: 14px; font-weight: 500; color: var(--text-muted); }

.price-features { display: flex; flex-direction: column; gap: 12px; margin-bottom: 28px; flex: 1; border-top: 1px solid var(--border); padding-top: 20px; }
.price-feature-item { display: flex; align-items: center; gap: 10px; font-size: 13px; color: var(--text-2); }
.price-feature-item svg { width: 16px; height: 16px; color: var(--success); flex-shrink: 0; }
</style>
@endpush

@section('content')
<div class="topbar">
    <button class="btn btn-ghost btn-icon" onclick="openSidebar()" id="menuBtnSub" style="display:none;">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <script>if(window.innerWidth<=768)document.getElementById('menuBtnSub').style.display='flex';</script>
    <div class="topbar-left">
        <div class="topbar-title">SaaS Subscription & Pricing</div>
        <div class="topbar-subtitle">Choose the right plan for your business</div>
    </div>
</div>

<div class="page-content">

    @if(session('success'))
    <div class="alert alert-success"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>{{ session('success') }}</div>
    @endif

    {{-- Usage Banner --}}
    <div class="card" style="padding:20px 24px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
        <div style="display:flex;align-items:center;gap:14px;">
            <div style="width:44px;height:44px;border-radius:var(--r);background:var(--primary-ultra);color:var(--primary);display:flex;align-items:center;justify-content:center;">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div style="font-size:15px;font-weight:700;">Current Active Plan: <span style="color:var(--primary);">{{ $current['plan']['name'] ?? 'Free' }}</span></div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">Storage: {{ number_format($current['usage']['storage_used_gb'] ?? 2.4, 1) }} / {{ $current['usage']['storage_limit_gb'] ?? 10 }} GB · Team: {{ $current['usage']['total_users'] ?? 1 }} / {{ $current['usage']['max_users'] ?? 2 }} seats</div>
            </div>
        </div>
        <span class="badge badge-success" style="font-size:12px;padding:6px 14px;">● Active</span>
    </div>

    {{-- Pricing Grid (4 Plans) --}}
    <div class="pricing-grid">

        {{-- 1. Free --}}
        <div class="price-card">
            <div class="price-header">
                <div class="price-name">Free</div>
                <div class="price-desc">For individuals & small testing</div>
                <div class="price-val">$0 <span>/mo</span></div>
            </div>
            <div class="price-features">
                <div class="price-feature-item">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    10 GB Cloud Storage
                </div>
                <div class="price-feature-item">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    2 Team Members
                </div>
                <div class="price-feature-item">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    500 Documents
                </div>
                <div class="price-feature-item">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Camera Scanning
                </div>
            </div>
            <button class="btn btn-outline btn-block" disabled>Current Plan</button>
        </div>

        {{-- 2. Basic --}}
        <div class="price-card">
            <div class="price-header">
                <div class="price-name">Basic</div>
                <div class="price-desc">For growing small businesses</div>
                <div class="price-val">$29 <span>/mo</span></div>
            </div>
            <div class="price-features">
                <div class="price-feature-item">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    50 GB Cloud Storage
                </div>
                <div class="price-feature-item">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    5 Team Members
                </div>
                <div class="price-feature-item">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    5,000 Documents
                </div>
                <div class="price-feature-item">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Auto Edge Detection
                </div>
                <div class="price-feature-item">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Email Support
                </div>
            </div>
            <button class="btn btn-outline btn-block" onclick="showToast('Upgrading to Basic plan...', 'success')">Upgrade Plan</button>
        </div>

        {{-- 3. Business (Popular) --}}
        <div class="price-card popular">
            <div class="popular-badge">Most Popular</div>
            <div class="price-header">
                <div class="price-name" style="color:var(--primary);">Business</div>
                <div class="price-desc">For importers & logistics firms</div>
                <div class="price-val" style="color:var(--primary);">$79 <span>/mo</span></div>
            </div>
            <div class="price-features">
                <div class="price-feature-item">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    250 GB Cloud Storage
                </div>
                <div class="price-feature-item">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    20 Team Members
                </div>
                <div class="price-feature-item">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Unlimited Documents
                </div>
                <div class="price-feature-item">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Team Roles & Permissions
                </div>
                <div class="price-feature-item">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Priority 24/7 Support
                </div>
            </div>
            <button class="btn btn-gradient btn-block" onclick="showToast('Upgrading to Business plan...', 'success')">Upgrade to Business</button>
        </div>

        {{-- 4. Enterprise --}}
        <div class="price-card">
            <div class="price-header">
                <div class="price-name">Enterprise</div>
                <div class="price-desc">Custom enterprise infrastructure</div>
                <div class="price-val">$199 <span>/mo</span></div>
            </div>
            <div class="price-features">
                <div class="price-feature-item">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    1 TB Cloud Storage
                </div>
                <div class="price-feature-item">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Unlimited Users
                </div>
                <div class="price-feature-item">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Custom API & Webhooks
                </div>
                <div class="price-feature-item">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Dedicated Account Manager
                </div>
            </div>
            <button class="btn btn-outline btn-block" onclick="showToast('Contacting Enterprise Sales...', 'info')">Contact Sales</button>
        </div>

    </div>

</div>
@endsection
