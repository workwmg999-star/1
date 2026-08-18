@extends('layouts.app')
@section('title', 'Team Management')

@section('content')
<div class="topbar">
    <button class="btn btn-ghost btn-icon" onclick="openSidebar()" id="menuBtnTeam" style="display:none;">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <script>if(window.innerWidth<=768)document.getElementById('menuBtnTeam').style.display='flex';</script>
    <div class="topbar-left">
        <div class="topbar-title">Team Management</div>
        <div class="topbar-subtitle">Manage employee access and roles</div>
    </div>
    <div class="topbar-actions">
        <button onclick="openModal('inviteModal')" class="btn btn-gradient" style="gap:6px;">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Invite Member
        </button>
    </div>
</div>

<div class="page-content">

    @if(session('success'))
    <div class="alert alert-success"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>{{ session('success') }}</div>
    @endif

    {{-- Stats Row --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--primary-ultra);">
                <svg fill="none" viewBox="0 0 24 24" stroke="var(--primary)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
            </div>
            <div>
                <div class="stat-value">3</div>
                <div class="stat-label">Active Members</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--success-bg);">
                <svg fill="none" viewBox="0 0 24 24" stroke="var(--success)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="stat-value">1</div>
                <div class="stat-label">Owner / Admin</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--warning-bg);">
                <svg fill="none" viewBox="0 0 24 24" stroke="var(--warning)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="stat-value">2 Seats</div>
                <div class="stat-label">Available on Plan</div>
            </div>
        </div>
    </div>

    {{-- Members Table --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">Company Employees</div>
            <button onclick="openModal('inviteModal')" class="btn btn-outline btn-sm">+ Invite</button>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Activity</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div class="avatar avatar-sm">A</div>
                                <div>
                                    <div style="font-weight:700;">Admin User</div>
                                    <div style="font-size:11px;color:var(--text-muted);">Account Owner</div>
                                </div>
                            </div>
                        </td>
                        <td>owner@docuscan.test</td>
                        <td><span class="badge badge-dark">Owner</span></td>
                        <td><span class="badge badge-success">● Active</span></td>
                        <td>Just now</td>
                        <td style="text-align:right;">
                            <span class="text-muted" style="font-size:12px;">Primary</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div class="avatar avatar-sm" style="background:linear-gradient(135deg,#06d6a0,#4361ee);">S</div>
                                <div>
                                    <div style="font-weight:700;">Sara Ahmadi</div>
                                    <div style="font-size:11px;color:var(--text-muted);">Customs Agent</div>
                                </div>
                            </div>
                        </td>
                        <td>sara@docuscan.test</td>
                        <td><span class="badge badge-primary">Admin</span></td>
                        <td><span class="badge badge-success">● Active</span></td>
                        <td>2 hours ago</td>
                        <td style="text-align:right;">
                            <button class="btn btn-ghost btn-sm" onclick="showToast('Editing Sara\'s role...', 'info')">Edit</button>
                            <button class="btn btn-ghost btn-sm" style="color:var(--danger)" onclick="confirm('Remove member?')">Remove</button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div class="avatar avatar-sm" style="background:linear-gradient(135deg,#ffd166,#ef233c);">K</div>
                                <div>
                                    <div style="font-weight:700;">Karim Transport</div>
                                    <div style="font-size:11px;color:var(--text-muted);">Logistics Staff</div>
                                </div>
                            </div>
                        </td>
                        <td>karim@docuscan.test</td>
                        <td><span class="badge badge-gray">Employee</span></td>
                        <td><span class="badge badge-success">● Active</span></td>
                        <td>1 day ago</td>
                        <td style="text-align:right;">
                            <button class="btn btn-ghost btn-sm" onclick="showToast('Editing Karim\'s role...', 'info')">Edit</button>
                            <button class="btn btn-ghost btn-sm" style="color:var(--danger)" onclick="confirm('Remove member?')">Remove</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Invite Modal --}}
<div class="modal-overlay" id="inviteModal">
    <div class="modal modal-sm">
        <div class="modal-header">
            <div class="modal-title">Invite Team Member</div>
            <button class="modal-close" onclick="closeModal('inviteModal')">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form onsubmit="event.preventDefault();showToast('Invitation email sent!', 'success');closeModal('inviteModal');">
            <div class="form-group">
                <label class="form-label">Email Address <span style="color:var(--danger)">*</span></label>
                <input type="email" class="form-control" placeholder="employee@company.com" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">Access Role</label>
                <select class="form-control">
                    <option value="admin">Admin (Full Control)</option>
                    <option value="employee" selected>Employee (Scan & View)</option>
                    <option value="viewer">Viewer (Read Only)</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('inviteModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Send Invitation</button>
            </div>
        </form>
    </div>
</div>

@endsection
