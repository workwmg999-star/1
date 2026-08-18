@extends('layouts.app')
@section('title', $document['title'] ?? 'Document Details')

@section('content')
<div class="topbar">
    <button class="btn btn-ghost btn-icon" onclick="openSidebar()" id="menuBtnDocDetails" style="display:none;">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <script>if(window.innerWidth<=768)document.getElementById('menuBtnDocDetails').style.display='flex';</script>
    <div class="topbar-left">
        <div class="topbar-title">
            <a href="{{ route('documents.index') }}" style="color:var(--text-muted);font-weight:500;font-size:14px;text-decoration:none;">Documents</a>
            <span style="color:var(--text-light);margin:0 6px;">/</span>
            <span>{{ $document['title'] }}</span>
        </div>
        <div class="topbar-subtitle">{{ strtoupper($document['file_type'] ?? 'PDF') }} · {{ $document['size_formatted'] ?? '1.2 MB' }}</div>
    </div>
    <div class="topbar-actions">
        <a href="{{ route('documents.download', $document['id']) }}" class="btn btn-gradient" style="gap:6px;">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Download
        </a>
    </div>
</div>

<div class="page-content">

    <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;">

        {{-- Preview Viewport --}}
        <div class="card" style="padding:0;overflow:hidden;min-height:500px;display:flex;flex-direction:column;background:var(--sidebar-bg);">
            <div style="padding:12px 16px;background:rgba(0,0,0,0.4);border-bottom:1px solid rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:space-between;color:#fff;font-size:12px;">
                <span>Document Viewer</span>
                <span class="badge badge-primary">Page 1 of {{ $document['pages_count'] ?? 1 }}</span>
            </div>
            <div style="flex:1;display:flex;align-items:center;justify-content:center;padding:24px;background:#0f172a;">
                @if(($document['file_type'] ?? 'pdf') === 'pdf')
                <div style="width:100%;max-width:560px;background:#fff;border-radius:var(--r);padding:40px;box-shadow:var(--shadow-lg);color:var(--text);min-height:440px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:2px solid var(--border);padding-bottom:16px;margin-bottom:20px;">
                        <div>
                            <div style="font-size:18px;font-weight:800;">{{ $document['title'] }}</div>
                            <div style="font-size:11px;color:var(--text-muted);">Ref: #DOC-{{ $document['id'] }}</div>
                        </div>
                        <span class="badge badge-danger">PDF</span>
                    </div>
                    <div style="font-size:13px;line-height:1.8;color:var(--text-2);">
                        <p style="margin-bottom:12px;">{{ $document['description'] ?? 'Scanned official business document. Digitised and uploaded to DocuScan Cloud Storage.' }}</p>
                        <div style="margin-top:24px;padding:16px;background:var(--bg-2);border-radius:var(--r-sm);border:1px solid var(--border);">
                            <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;">Cloud Storage Metadata</div>
                            <div style="font-size:12px;margin-top:6px;">Checksum: SHA-256 Verified</div>
                            <div style="font-size:12px;">Encryption: AES-256 Enterprise</div>
                        </div>
                    </div>
                </div>
                @else
                <img src="https://images.unsplash.com/photo-1568602471122-7832951cc4c5?w=800&q=80" alt="{{ $document['title'] }}" style="max-width:100%;max-height:460px;border-radius:var(--r);box-shadow:var(--shadow-lg);">
                @endif
            </div>
        </div>

        {{-- Info & Actions Sidebar --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

            {{-- Metadata Card --}}
            <div class="card">
                <div class="card-header"><div class="card-title">Document Info</div></div>
                <div class="card-body" style="padding-top:8px;">
                    <div style="display:flex;flex-direction:column;gap:12px;font-size:13px;">
                        <div style="display:flex;justify-content:space-between;">
                            <span class="text-muted">Folder</span>
                            <strong>{{ $document['folder']['name'] ?? 'Unfiled' }}</strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <span class="text-muted">File Format</span>
                            <span class="badge badge-primary">{{ strtoupper($document['file_type'] ?? 'pdf') }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <span class="text-muted">File Size</span>
                            <strong>{{ $document['size_formatted'] ?? '1.2 MB' }}</strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <span class="text-muted">Pages</span>
                            <strong>{{ $document['pages_count'] ?? 1 }}</strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <span class="text-muted">Uploaded</span>
                            <strong>{{ \Carbon\Carbon::parse($document['created_at'] ?? now())->format('d M Y, H:i') }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions Card --}}
            <div class="card">
                <div class="card-header"><div class="card-title">Actions</div></div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:8px;">
                    <a href="{{ route('documents.download', $document['id']) }}" class="btn btn-primary" style="justify-content:center;">
                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 013 3h10a3 3 0 013-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download Document
                    </a>
                    <button class="btn btn-outline" onclick="showToast('Share link copied to clipboard!', 'success')" style="justify-content:center;">
                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 107.032-4.004 3 3 0 00-7.032 4.004zm0 9.272a3 3 0 107.032 4.004 3 3 0 00-7.032-4.004z"/></svg>
                        Share Document
                    </button>
                    <button class="btn btn-outline" onclick="openModal('renameModal')" style="justify-content:center;">
                        ✏️ Rename
                    </button>
                    <form action="{{ route('documents.destroy', $document['id']) }}" method="POST" onsubmit="return confirm('Delete document?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block" style="justify-content:center;margin-top:8px;">
                            🗑 Delete Document
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
