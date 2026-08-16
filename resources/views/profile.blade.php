@extends('layouts.app')
@section('title', 'Profile')

@section('content')
<div class="topbar">
    <div class="topbar-title">My Profile</div>
</div>
<div class="page-content" style="max-width:640px;">
    @if(session('success'))
    <div class="alert alert-success">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="card" style="margin-bottom:20px;">
        <div class="card-header"><div class="card-title">Account Details</div></div>
        <div class="card-body">
            <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;">
                <div style="width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,#4f46e5,#06b6d4);display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;color:#fff;">
                    {{ strtoupper(substr($user['name'] ?? 'U', 0, 1)) }}
                </div>
                <div>
                    <div style="font-size:18px;font-weight:700;color:var(--text);">{{ $user['name'] }}</div>
                    <div style="font-size:13px;color:var(--text-muted);">{{ $user['email'] }}</div>
                    <span class="badge badge-purple" style="margin-top:4px;">{{ ucfirst($user['role']) }}</span>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;font-size:13.5px;">
                <div><span style="color:var(--text-muted);">Company:</span> <strong>{{ $company['name'] ?? '' }}</strong></div>
                <div><span style="color:var(--text-muted);">Plan:</span> <strong>{{ $company['plan']['name'] ?? '' }}</strong></div>
                <div><span style="color:var(--text-muted);">Storage Used:</span> <strong>{{ $company['storage']['used_gb'] ?? 0 }} GB</strong></div>
                <div><span style="color:var(--text-muted);">Active Since:</span> <strong>{{ \Carbon\Carbon::parse($company['created_at'])->format('M Y') }}</strong></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><div class="card-title">Company Information</div></div>
        <div class="card-body" style="font-size:13.5px;display:grid;gap:10px;">
            <div><span style="color:var(--text-muted);">Email:</span> {{ $company['email'] }}</div>
            @if($company['phone'])<div><span style="color:var(--text-muted);">Phone:</span> {{ $company['phone'] }}</div>@endif
            @if($company['address'])<div><span style="color:var(--text-muted);">Address:</span> {{ $company['address'] }}</div>@endif
            @if($company['country'])<div><span style="color:var(--text-muted);">Country:</span> {{ $company['country'] }}</div>@endif
        </div>
    </div>

    <div style="margin-top:16px;text-align:center;">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Sign Out
            </button>
        </form>
    </div>
</div>
@endsection
