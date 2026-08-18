@php
    $user = Auth::user() ?? session('auth_user');
    $currentRoute = request()->route()?->getName() ?? '';
@endphp

<div class="sidebar-logo">
    <div class="logo-icon">
        <svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
    </div>
    <div>
        <div class="logo-text">DocuScan</div>
        <div class="logo-badge">Pro SaaS</div>
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

    {{-- Scan CTA --}}
    <a href="{{ route('scan') }}" class="sidebar-scan-btn">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
            <circle cx="12" cy="13" r="3"/>
        </svg>
        Scan Document
    </a>

    <div class="nav-section-title">Main</div>

    <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline stroke-linecap="round" stroke-linejoin="round" points="9 22 9 12 15 12 15 22"/>
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

    <div class="nav-section-title" style="margin-top:12px;">Manage</div>

    <a href="{{ route('team') ?? '#' }}" class="nav-item {{ request()->routeIs('team') ? 'active' : '' }}">
        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
        </svg>
        Team
    </a>

    <a href="{{ route('storage.index') ?? '#' }}" class="nav-item {{ request()->routeIs('storage.*') ? 'active' : '' }}">
        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12H3a9 9 0 1018 0h-2M12 3v9"/>
        </svg>
        Storage
    </a>

    <a href="{{ route('notifications.index') ?? '#' }}" class="nav-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        Notifications
        <span class="nav-badge">3</span>
    </a>

    <div class="nav-section-title" style="margin-top:12px;">Account</div>

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
        Profile & Settings
    </a>

</nav>

<div class="sidebar-footer">
    @if($user)
    @php
        $name = is_array($user) ? ($user['name'] ?? 'User') : ($user->name ?? 'User');
        $role = is_array($user) ? ($user['role'] ?? 'employee') : ($user->role ?? 'employee');
        $initials = strtoupper(substr($name, 0, 1));
    @endphp
    <div class="user-card">
        <div class="avatar avatar-sm" style="width:32px;height:32px;font-size:12px;">{{ $initials }}</div>
        <div style="flex:1;min-width:0;">
            <div class="user-name">{{ $name }}</div>
            <div class="user-role">{{ ucfirst($role) }}</div>
        </div>
        <form action="{{ route('logout') }}" method="POST" style="margin:0;">
            @csrf
            <button type="submit" class="btn btn-ghost btn-icon" title="Logout" style="color:hsl(215,16%,45%);padding:6px;">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </button>
        </form>
    </div>
    @endif

    {{-- Dark Mode Toggle --}}
    <button onclick="toggleTheme()" class="nav-item" style="margin-top:8px;width:100%;text-align:left;">
        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
        </svg>
        Toggle Dark Mode
    </button>
</div>
