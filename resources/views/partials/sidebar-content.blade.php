@php
    $user = Auth::user() ?? session('auth_user');
@endphp

<div class="sidebar-logo">
    <div class="logo-icon">
        <svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
    </div>
    <div>
        <div class="logo-text">DocuScan</div>
        <div class="logo-badge">SaaS Platform</div>
    </div>
</div>

@if($user)
<div class="sidebar-company">
    <div class="company-label">Company</div>
    <div class="company-name">{{ is_array($user) ? ($user['company']['name'] ?? 'My Company') : ($user->company->name ?? 'My Company') }}</div>
    <span class="company-plan">{{ is_array($user) ? ($user['company']['plan']['name'] ?? 'Free') : ($user->company->plan->name ?? 'Free') }}</span>
</div>
@endif

<nav class="sidebar-nav">
    <div class="nav-section-title">Main</div>

    <a href="{{ route('scan') }}" class="nav-item {{ request()->routeIs('scan') ? 'active' : '' }}" style="background:linear-gradient(135deg,rgba(79,70,229,0.3),rgba(6,182,212,0.2));color:#c7d2fe;font-weight:700;border:1px solid rgba(79,70,229,0.4);margin-bottom:8px;">
        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/>
        </svg>
        📷 Scan Document
    </a>

    <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h7v6H3zm0 10h7v-2H3zm11-10h7v2h-7zm0 6h7v6h-7z"/>
        </svg>
        Dashboard
    </a>

    <a href="{{ route('documents.index') }}" class="nav-item {{ request()->routeIs('documents.*') ? 'active' : '' }}">
        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Documents
    </a>

    <a href="{{ route('folders.index') }}" class="nav-item {{ request()->routeIs('folders.*') ? 'active' : '' }}">
        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
        </svg>
        Folders
    </a>

    <div class="nav-section-title" style="margin-top:12px;">Settings</div>

    <a href="{{ route('subscriptions') }}" class="nav-item {{ request()->routeIs('subscriptions') ? 'active' : '' }}">
        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Subscription
    </a>

    <a href="{{ route('profile') }}" class="nav-item {{ request()->routeIs('profile') ? 'active' : '' }}">
        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        Profile
    </a>
</nav>

<div class="sidebar-footer">
    @if($user)
    @php
        $name = is_array($user) ? ($user['name'] ?? 'User') : ($user->name ?? 'User');
        $role = is_array($user) ? ($user['role'] ?? 'employee') : ($user->role ?? 'employee');
    @endphp
    <div class="user-card">
        <div class="user-avatar">{{ strtoupper(substr($name, 0, 1)) }}</div>
        <div style="flex:1;min-width:0;">
            <div class="user-name">{{ $name }}</div>
            <div class="user-role">{{ ucfirst($role) }}</div>
        </div>
        <form action="{{ route('logout') }}" method="POST" style="margin:0;">
            @csrf
            <button type="submit" class="nav-item btn-icon" style="padding:6px;" title="Logout">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </button>
        </form>
    </div>
    @endif
</div>
