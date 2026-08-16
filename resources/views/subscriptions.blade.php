@extends('layouts.app')
@section('title', 'Subscription Plans')

@section('content')
<div class="topbar">
    <div class="topbar-title">Subscription Plans</div>
</div>

<div class="page-content">

    @if(session('success'))
    <div class="alert alert-success">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Current Usage --}}
    <div class="card" style="margin-bottom:24px;">
        <div class="card-header">
            <div class="card-title">Current Usage</div>
            <span class="badge badge-purple">{{ $current['plan']['name'] ?? 'Free' }} Plan</span>
        </div>
        <div class="card-body" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;padding-top:8px;">
            @php $usage = $current['usage'] ?? []; @endphp
            <div>
                <div style="font-size:12px;color:var(--text-muted);font-weight:600;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;">Storage</div>
                <div style="font-size:22px;font-weight:800;color:var(--text);">{{ number_format($usage['storage_used_gb'] ?? 0, 2) }} <span style="font-size:13px;font-weight:500;color:var(--text-muted);">/ {{ $usage['storage_limit_gb'] ?? 0 }} GB</span></div>
                <div style="height:6px;background:var(--border);border-radius:3px;margin-top:8px;overflow:hidden;">
                    <div style="height:100%;width:{{ min($usage['storage_percent'] ?? 0, 100) }}%;background:linear-gradient(90deg,#4f46e5,#06b6d4);border-radius:3px;transition:width .5s;"></div>
                </div>
            </div>
            <div>
                <div style="font-size:12px;color:var(--text-muted);font-weight:600;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;">Documents</div>
                <div style="font-size:22px;font-weight:800;color:var(--text);">{{ $usage['total_documents'] ?? 0 }} <span style="font-size:13px;font-weight:500;color:var(--text-muted);">/ {{ $usage['max_documents'] ?? '500' }}</span></div>
            </div>
            <div>
                <div style="font-size:12px;color:var(--text-muted);font-weight:600;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;">Team Members</div>
                <div style="font-size:22px;font-weight:800;color:var(--text);">{{ $usage['total_users'] ?? 0 }} <span style="font-size:13px;font-weight:500;color:var(--text-muted);">/ {{ $usage['max_users'] ?? '2' }}</span></div>
            </div>
        </div>
    </div>

    {{-- Plans Grid --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;">
        @foreach($plans as $plan)
        @php $isCurrent = ($current['plan']['id'] ?? 0) === $plan['id']; @endphp
        <div class="card" style="padding:28px;position:relative;{{ $plan['slug']==='professional' ? 'border-color:#4f46e5;border-width:2px;' : '' }}">
            @if($plan['slug']==='professional')
            <div style="position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:#4f46e5;color:#fff;font-size:11px;font-weight:700;padding:4px 14px;border-radius:20px;white-space:nowrap;">Most Popular</div>
            @endif

            @if($isCurrent)
            <div style="position:absolute;top:16px;right:16px;">
                <span class="badge badge-green">Current</span>
            </div>
            @endif

            <div style="font-size:15px;font-weight:700;color:var(--text);margin-bottom:4px;">{{ $plan['name'] }}</div>
            <div style="margin-bottom:16px;">
                <span style="font-size:32px;font-weight:800;color:{{ $plan['slug']==='professional' ? '#4f46e5' : 'var(--text)' }};">${{ number_format($plan['price_monthly'], 0) }}</span>
                <span style="font-size:13px;color:var(--text-muted);">/month</span>
            </div>

            <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:20px;">
                <div style="font-size:13px;color:var(--text);display:flex;align-items:center;gap:8px;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2.5" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    {{ $plan['max_storage_gb'] }} GB Cloud Storage
                </div>
                <div style="font-size:13px;color:var(--text);display:flex;align-items:center;gap:8px;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2.5" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    {{ is_string($plan['max_users']) ? ucfirst($plan['max_users']) : $plan['max_users'] }} Users
                </div>
                <div style="font-size:13px;color:var(--text);display:flex;align-items:center;gap:8px;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2.5" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    {{ is_string($plan['max_documents']) ? 'Unlimited' : number_format($plan['max_documents']) }} Documents
                </div>
                @foreach(array_slice($plan['features'] ?? [], 3, 2) as $feature)
                <div style="font-size:13px;color:var(--text-muted);display:flex;align-items:center;gap:8px;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2.5" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    {{ $feature }}
                </div>
                @endforeach
            </div>

            @if(!$isCurrent)
            <form action="{{ route('subscriptions') }}" method="POST">
                @csrf
                <input type="hidden" name="_upgrade_plan" value="{{ $plan['id'] }}">
                <button type="submit" style="width:100%;padding:10px;border-radius:8px;border:1.5px solid {{ $plan['slug']==='professional' ? '#4f46e5' : 'var(--border)' }};background:{{ $plan['slug']==='professional' ? '#4f46e5' : '#fff' }};color:{{ $plan['slug']==='professional' ? '#fff' : 'var(--text)' }};font-size:13.5px;font-weight:700;cursor:pointer;transition:all .15s;font-family:inherit;">
                    {{ $plan['price_monthly'] == 0 ? 'Start Free' : 'Upgrade to ' . $plan['name'] }}
                </button>
            </form>
            @else
            <button disabled style="width:100%;padding:10px;border-radius:8px;border:1.5px solid var(--border);background:#f8fafc;color:var(--text-muted);font-size:13.5px;font-weight:600;cursor:not-allowed;font-family:inherit;">
                ✓ Current Plan
            </button>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endsection
