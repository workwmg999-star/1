@extends('layouts.app')
@section('title', 'Notifications')

@section('content')
<div class="topbar">
    <button class="btn btn-ghost btn-icon" onclick="openSidebar()" id="menuBtnNotif" style="display:none;">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <script>if(window.innerWidth<=768)document.getElementById('menuBtnNotif').style.display='flex';</script>
    <div class="topbar-left">
        <div class="topbar-title">Notifications</div>
        <div class="topbar-subtitle">System alerts & activity stream</div>
    </div>
    <div class="topbar-actions">
        <button class="btn btn-outline btn-sm" onclick="showToast('All notifications marked as read', 'success')">Mark all read</button>
    </div>
</div>

<div class="page-content" style="max-width: 720px; margin: 0 auto;">

    <div class="card">
        <div class="card-header">
            <div class="card-title">Recent Activity & Alerts</div>
            <span class="badge badge-primary">5 New</span>
        </div>

        <div>
            {{-- Item 1: Document Uploaded --}}
            <div class="doc-item">
                <div style="width:42px;height:42px;border-radius:var(--r-sm);background:var(--primary-ultra);color:var(--primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                </div>
                <div class="doc-info">
                    <div style="font-size:14px;font-weight:700;">Document Uploaded</div>
                    <div style="font-size:13px;color:var(--text-2);margin-top:2px;">
                        Sara uploaded <strong>Facture_Douane_2024.pdf</strong> into <em>Documents Douane</em>.
                    </div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">10 minutes ago</div>
                </div>
                <span class="badge badge-dot" style="background:var(--primary);"></span>
            </div>

            {{-- Item 2: Document Shared --}}
            <div class="doc-item">
                <div style="width:42px;height:42px;border-radius:var(--r-sm);background:var(--info-bg);color:var(--info);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 107.032-4.004 3 3 0 00-7.032 4.004zm0 9.272a3 3 0 107.032 4.004 3 3 0 00-7.032-4.004z"/></svg>
                </div>
                <div class="doc-info">
                    <div style="font-size:14px;font-weight:700;">Document Shared</div>
                    <div style="font-size:13px;color:var(--text-2);margin-top:2px;">
                        Document <strong>Contrat_Fournisseur.pdf</strong> was shared with Karim Transport.
                    </div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">1 hour ago</div>
                </div>
                <span class="badge badge-dot" style="background:var(--primary);"></span>
            </div>

            {{-- Item 3: New Team Member --}}
            <div class="doc-item">
                <div style="width:42px;height:42px;border-radius:var(--r-sm);background:var(--success-bg);color:var(--success);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                </div>
                <div class="doc-info">
                    <div style="font-size:14px;font-weight:700;">New Team Member Joined</div>
                    <div style="font-size:13px;color:var(--text-2);margin-top:2px;">
                        <strong>Karim Transport</strong> accepted the invitation to join your workspace.
                    </div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">3 hours ago</div>
                </div>
            </div>

            {{-- Item 4: Storage Alert --}}
            <div class="doc-item">
                <div style="width:42px;height:42px;border-radius:var(--r-sm);background:var(--warning-bg);color:var(--warning);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="doc-info">
                    <div style="font-size:14px;font-weight:700;">Storage Alert</div>
                    <div style="font-size:13px;color:var(--text-2);margin-top:2px;">
                        Your storage is at <strong>24%</strong> (2.4 GB / 10 GB). Upgrade plan to expand quota.
                    </div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">Yesterday</div>
                </div>
            </div>

            {{-- Item 5: Subscription Notification --}}
            <div class="doc-item">
                <div style="width:42px;height:42px;border-radius:var(--r-sm);background:var(--primary-ultra);color:var(--primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="doc-info">
                    <div style="font-size:14px;font-weight:700;">Subscription Renewed</div>
                    <div style="font-size:13px;color:var(--text-2);margin-top:2px;">
                        Your <strong>Free Plan</strong> is active and in good standing.
                    </div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">3 days ago</div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
