@extends('layouts.app')
@section('title', 'Folders')

@section('content')
<div class="topbar">
    <div>
        <div class="topbar-title">Folders</div>
        <div class="topbar-subtitle">{{ count($folders) }} folders</div>
    </div>
    <button onclick="openModal('newFolderModal')" class="btn btn-primary">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        New Folder
    </button>
</div>

<div class="page-content">

    @if(session('success'))
    <div class="alert alert-success">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    @if(count($folders))
    <div class="folder-grid">
        @foreach($folders as $folder)
        <div style="position:relative;">
            <a href="{{ route('documents.index', ['folder_id' => $folder['id']]) }}" class="folder-card" style="border-color: transparent;">
                <div class="folder-card-icon" style="background:{{ $folder['color'] }};">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
                </div>
                <div class="folder-card-name">{{ $folder['name'] }}</div>
                <div class="folder-card-count">{{ $folder['documents_count'] ?? 0 }} documents</div>
                @if($folder['description'])
                <div style="font-size:11.5px;color:var(--text-light);line-height:1.4;">{{ Str::limit($folder['description'], 60) }}</div>
                @endif
            </a>
            <form action="{{ route('folders.destroy', $folder['id']) }}" method="POST"
                  style="position:absolute;top:10px;right:10px;"
                  onsubmit="return confirm('Delete folder? Documents will move to root.')">
                @csrf @method('DELETE')
                <button type="submit" class="folder-del-btn" title="Delete folder">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </form>
        </div>
        @endforeach
    </div>
    @else
    <div style="text-align:center;padding:80px 20px;">
        <svg width="64" height="64" fill="none" viewBox="0 0 24 24" stroke="#cbd5e1" stroke-width="1.2" style="margin:0 auto 16px;display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
        <p style="color:var(--text-muted);font-size:16px;font-weight:600;">No folders yet</p>
        <p style="color:var(--text-light);font-size:13px;margin:6px 0 20px;">Create folders to organise your documents: Factures, Douane, Fournisseurs…</p>
        <button onclick="openModal('newFolderModal')" class="btn btn-primary">Create First Folder</button>
    </div>
    @endif
</div>

{{-- New Folder Modal --}}
<div class="modal-overlay" id="newFolderModal">
    <div class="modal">
        <div class="modal-title">Create New Folder</div>
        <form action="{{ route('folders.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Folder Name <span style="color:var(--danger)">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Factures 2026" required>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label class="form-label">Color</label>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:4px;" id="colorPicker">
                        @foreach(['#6366f1','#10b981','#ef4444','#f59e0b','#3b82f6','#8b5cf6','#06b6d4','#ec4899'] as $color)
                        <label style="cursor:pointer;">
                            <input type="radio" name="color" value="{{ $color }}" style="display:none;" {{ $color === '#6366f1' ? 'checked' : '' }}>
                            <span style="display:block;width:26px;height:26px;border-radius:50%;background:{{ $color }};border:2px solid transparent;transition:border .15s;" class="color-swatch"></span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Icon</label>
                    <select name="icon" class="form-control">
                        <option value="folder">📁 Folder</option>
                        <option value="receipt">🧾 Receipt</option>
                        <option value="shield">🛡 Customs</option>
                        <option value="truck">🚚 Transport</option>
                        <option value="file-text">📄 Contracts</option>
                        <option value="users">👥 Suppliers</option>
                        <option value="archive">📦 Archive</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <input type="text" name="description" class="form-control" placeholder="Optional description">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('newFolderModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Folder</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Highlight selected color swatch
document.querySelectorAll('#colorPicker input[type=radio]').forEach(radio => {
    radio.addEventListener('change', () => {
        document.querySelectorAll('.color-swatch').forEach(s => s.style.borderColor = 'transparent');
        radio.nextElementSibling.style.borderColor = '#fff';
        radio.nextElementSibling.style.outline = '2px solid ' + radio.value;
    });
    if (radio.checked) { radio.nextElementSibling.style.outline = '2px solid ' + radio.value; }
});
</script>
@endpush
@endsection
