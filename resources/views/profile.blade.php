@extends('layouts.app')
@section('title', 'Profile & Settings')

@section('content')
<div class="topbar">
    <button class="btn btn-ghost btn-icon" onclick="openSidebar()" id="menuBtnProf" style="display:none;">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <script>if(window.innerWidth<=768)document.getElementById('menuBtnProf').style.display='flex';</script>
    <div class="topbar-left">
        <div class="topbar-title">Profile & Workspace Settings</div>
        <div class="topbar-subtitle">Account credentials, preferences & security</div>
    </div>
</div>

<div class="page-content" style="max-width: 840px; margin: 0 auto;">

    {{-- Profile Header Card --}}
    <div class="card" style="padding:28px;margin-bottom:24px;display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
        @php
            $name = $user['name'] ?? 'Admin User';
            $initials = strtoupper(substr($name, 0, 1));
        @endphp
        <div class="avatar avatar-xl">{{ $initials }}</div>
        <div style="flex:1;">
            <div style="font-size:22px;font-weight:800;">{{ $name }}</div>
            <div style="font-size:13px;color:var(--text-muted);">{{ $user['email'] ?? 'admin@docuscan.test' }} · <span class="badge badge-primary">{{ ucfirst($user['role'] ?? 'owner') }}</span></div>
            <div style="font-size:12px;color:var(--text-light);margin-top:4px;">Workspace: <strong>{{ $company['name'] ?? 'My Company' }}</strong></div>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Logout
            </button>
        </form>
    </div>

    {{-- Settings Sections Grid --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

        {{-- 1. Company Info --}}
        <div class="card">
            <div class="card-header"><div class="card-title">Company Information</div></div>
            <div class="card-body">
                <form onsubmit="event.preventDefault();showToast('Company info updated!', 'success');">
                    <div class="form-group">
                        <label class="form-label">Company Name</label>
                        <input type="text" class="form-control" value="{{ $company['name'] ?? 'My Company' }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Country</label>
                        <select class="form-control">
                            <option value="DZ" selected>🇩🇿 Algeria</option>
                            <option value="MA">🇲🇦 Morocco</option>
                            <option value="TN">🇹🇳 Tunisia</option>
                            <option value="FR">🇫🇷 France</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                </form>
            </div>
        </div>

        {{-- 2. Personal Info --}}
        <div class="card">
            <div class="card-header"><div class="card-title">Personal Profile</div></div>
            <div class="card-body">
                <form onsubmit="event.preventDefault();showToast('Personal info updated!', 'success');">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" value="{{ $user['name'] ?? 'Admin User' }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" value="{{ $user['email'] ?? 'admin@docuscan.test' }}">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Save Profile</button>
                </form>
            </div>
        </div>

        {{-- 3. App Preferences & Language --}}
        <div class="card">
            <div class="card-header"><div class="card-title">App Preferences</div></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:16px;">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Language / اللغة / Langue</label>
                    <select class="form-control" onchange="setLanguage(this.value)">
                        <option value="en" selected>🌐 English (LTR)</option>
                        <option value="ar">🇩🇿 العربية (RTL)</option>
                        <option value="fr">🇫🇷 Français (LTR)</option>
                    </select>
                </div>
                <div style="display:flex;align-items:center;justify-space:between;" class="justify-between">
                    <div>
                        <div style="font-size:14px;font-weight:600;">Theme Mode</div>
                        <div style="font-size:12px;color:var(--text-muted);">Switch between Light and Dark theme</div>
                    </div>
                    <button class="btn btn-outline btn-sm" onclick="toggleTheme()">Toggle Theme</button>
                </div>
            </div>
        </div>

        {{-- 4. Security & Password --}}
        <div class="card">
            <div class="card-header"><div class="card-title">Security & Password</div></div>
            <div class="card-body">
                <form onsubmit="event.preventDefault();showToast('Password updated!', 'success');">
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" class="form-control" placeholder="••••••••">
                    </div>
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" placeholder="••••••••">
                    </div>
                    <button type="submit" class="btn btn-outline btn-sm">Update Password</button>
                </form>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
function setLanguage(lang) {
    if(lang === 'ar') {
        document.documentElement.setAttribute('dir', 'rtl');
        document.documentElement.setAttribute('lang', 'ar');
        showToast('تم تغيير الاتجاه إلى العربية (RTL)', 'info');
    } else {
        document.documentElement.setAttribute('dir', 'ltr');
        document.documentElement.setAttribute('lang', lang);
        showToast(`Language changed to ${lang.toUpperCase()}`, 'info');
    }
}
</script>
@endpush

@endsection
