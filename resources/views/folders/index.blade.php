@extends('layouts.app')
@section('title', 'Folders')

@push('styles')
<style>
.folder-stats-row { display: grid; grid-template-columns: repeat(3,1fr); gap: 12px; margin-bottom: 24px; }
.folder-stat-mini { background: var(--card); border: 1px solid var(--border); border-radius: var(--r); padding: 16px; text-align: center; }
.folder-stat-mini .val { font-size: 22px; font-weight: 800; color: var(--text); }
.folder-stat-mini .lbl { font-size: 11px; color: var(--text-muted); font-weight: 600; margin-top: 3px; }
.default-folders { margin-bottom: 28px; }
.default-folder-chip {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 7px 14px; border-radius: var(--r-full);
    border: 1.5px solid var(--border); background: var(--card);
    font-size: 13px; font-weight: 600; color: var(--text-2);
    cursor: pointer; transition: var(--transition);
    text-decoration: none;
}
.default-folder-chip:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-ultra); }
.default-folder-chip .dot { width: 8px; height: 8px; border-radius: 50%; }
</style>
@endpush

@section('content')
<div class="topbar">
    <button class="btn btn-ghost btn-icon" onclick="openSidebar()" id="menuBtnF" style="display:none;">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <script>if(window.innerWidth<=768)document.getElementById('menuBtnF').style.display='flex';</script>
    <div class="topbar-left">
        <div class="topbar-title">Folders</div>
        <div class="topbar-subtitle">{{ count($folders) }} folders · Organise your documents</div>
    </div>
    <div class="topbar-actions">
        <button onclick="openModal('newFolderModal')" class="btn btn-gradient">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            New Folder
        </button>
    </div>
</div>

<div class="page-content">

    @if(session('success'))
    <div class="alert alert-success">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Mini Stats --}}
    <div class="folder-stats-row">
        <div class="folder-stat-mini">
            <div class="val">{{ count($folders) }}</div>
            <div class="lbl">Total Folders</div>
        </div>
        <div class="folder-stat-mini">
            <div class="val">{{ array_sum(array_column($folders, 'documents_count')) }}</div>
            <div class="lbl">Total Documents</div>
        </div>
        <div class="folder-stat-mini">
            <div class="val">{{ count(array_filter($folders, fn($f) => ($f['documents_count'] ?? 0) == 0)) }}</div>
            <div class="lbl">Empty Folders</div>
        </div>
    </div>

    {{-- Default Folders Quick Links --}}
    <div class="default-folders">
        <div class="section-header" style="margin-bottom:12px;">
            <div class="section-title" style="font-size:13px;color:var(--text-muted);">Default Categories</div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            @php
            $defaults = [
                ['name'=>'Factures','color'=>'#4361ee','icon'=>'🧾'],
                ['name'=>'Documents Douane','color'=>'#ef233c','icon'=>'🛃'],
                ['name'=>'Fournisseurs','color'=>'#06d6a0','icon'=>'🏭'],
                ['name'=>'Transport','color'=>'#ffd166','icon'=>'🚚'],
                ['name'=>'Contrats','color'=>'#7c3aed','icon'=>'📄'],
                ['name'=>'Autres','color'=>'#64748b','icon'=>'📦'],
            ];
            @endphp
            @foreach($defaults as $d)
            <a href="{{ route('documents.index', ['q' => $d['name']]) }}" class="default-folder-chip">
                <span class="dot" style="background:{{ $d['color'] }};"></span>
                {{ $d['icon'] }} {{ $d['name'] }}
            </a>
            @endforeach
        </div>
    </div>

    {{-- Folders Grid --}}
    @if(count($folders))
    <div class="section-header">
        <div class="section-title">All Folders</div>
        <div style="display:flex;gap:8px;">
            <button class="btn btn-ghost btn-sm" onclick="setFolderView('grid')" id="fGrid" style="color:var(--primary);">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            </button>
            <button class="btn btn-ghost btn-sm" onclick="setFolderView('list')" id="fList">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            </button>
        </div>
    </div>

    {{-- Grid View --}}
    <div id="folderGridView" class="folder-grid">
        @foreach($folders as $folder)
        <div style="position:relative;">
            <a href="{{ route('documents.index', ['folder_id' => $folder['id']]) }}" class="folder-card">
                <div class="folder-card-icon" style="background:{{ $folder['color'] ?? 'var(--primary)' }}1a;border:1.5px solid {{ $folder['color'] ?? 'var(--primary)' }}30;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="{{ $folder['color'] ?? 'var(--primary)' }}" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
                </div>
                <div class="folder-card-name">{{ $folder['name'] }}</div>
                <div class="folder-card-meta">
                    <span>{{ $folder['documents_count'] ?? 0 }} docs</span>
                    @if($folder['description'] ?? null)
                    <span>·</span>
                    <span>{{ Str::limit($folder['description'], 30) }}</span>
                    @endif
                </div>
                {{-- Color indicator --}}
                <div style="position:absolute;top:0;left:0;right:0;height:3px;background:{{ $folder['color'] ?? 'var(--primary)' }};border-radius:var(--r-md) var(--r-md) 0 0;"></div>
            </a>

            {{-- Actions --}}
            <div class="folder-menu-btn">
                <button class="btn btn-ghost btn-icon" onclick="event.preventDefault();openFolderMenu('{{ $folder['id'] }}')" style="background:var(--card);box-shadow:var(--shadow-sm);">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="5" r="1" fill="currentColor"/><circle cx="12" cy="12" r="1" fill="currentColor"/><circle cx="12" cy="19" r="1" fill="currentColor"/></svg>
                </button>
            </div>

            {{-- Folder Action Menu --}}
            <div id="menu-{{ $folder['id'] }}" style="display:none;position:absolute;top:48px;right:10px;background:var(--card);border:1px solid var(--border);border-radius:var(--r);box-shadow:var(--shadow-md);min-width:140px;z-index:50;overflow:hidden;">
                <a href="{{ route('documents.index', ['folder_id' => $folder['id']]) }}" style="display:flex;align-items:center;gap:8px;padding:10px 14px;font-size:13px;color:var(--text-2);text-decoration:none;transition:background 0.1s;" onmouseover="this.style.background='var(--bg-2)'" onmouseout="this.style.background=''">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Open
                </a>
                <div style="border-top:1px solid var(--border);"></div>
                <form action="{{ route('folders.destroy', $folder['id']) }}" method="POST"
                      onsubmit="return confirm('Delete folder? Documents will move to root.')">
                    @csrf @method('DELETE')
                    <button type="submit" style="display:flex;align-items:center;gap:8px;padding:10px 14px;font-size:13px;color:var(--danger);background:none;border:none;cursor:pointer;width:100%;text-align:left;transition:background 0.1s;font-family:var(--font);" onmouseover="this.style.background='var(--danger-bg)'" onmouseout="this.style.background=''">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    {{-- List View --}}
    <div id="folderListView" style="display:none;">
        <div class="card">
            @foreach($folders as $folder)
            <div style="display:flex;align-items:center;gap:14px;padding:14px 20px;border-bottom:1px solid var(--border);transition:background 0.12s;" onmouseover="this.style.background='var(--card-2)'" onmouseout="this.style.background=''">
                <div style="width:40px;height:40px;border-radius:var(--r-sm);background:{{ $folder['color'] ?? 'var(--primary)' }}20;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="{{ $folder['color'] ?? 'var(--primary)' }}" stroke-width="2" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:14px;font-weight:600;color:var(--text);">{{ $folder['name'] }}</div>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">{{ $folder['documents_count'] ?? 0 }} documents</div>
                </div>
                <a href="{{ route('documents.index', ['folder_id' => $folder['id']]) }}" class="btn btn-outline btn-sm">Open →</a>
            </div>
            @endforeach
        </div>
    </div>

    @else
    <div class="empty-state">
        <div class="empty-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
        </div>
        <div class="empty-title">No folders yet</div>
        <div class="empty-desc">Create folders to organise your documents: Factures, Douane, Fournisseurs, Contrats…</div>
        <button onclick="openModal('newFolderModal')" class="btn btn-gradient">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Create First Folder
        </button>
    </div>
    @endif

</div>

{{-- New Folder Modal --}}
<div class="modal-overlay" id="newFolderModal">
    <div class="modal modal-sm">
        <div class="modal-header">
            <div class="modal-title">New Folder</div>
            <button class="modal-close" onclick="closeModal('newFolderModal')">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('folders.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Folder Name <span style="color:var(--danger)">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Factures 2024" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">Quick Templates</label>
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                    @foreach(['Factures','Documents Douane','Fournisseurs','Transport','Contrats','Autres'] as $tpl)
                    <button type="button" class="chip" onclick="document.querySelector('[name=name]').value='{{ $tpl }}'">{{ $tpl }}</button>
                    @endforeach
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Color</label>
                <div style="display:flex;gap:10px;flex-wrap:wrap;" id="colorPicker">
                    @foreach(['#4361ee','#06d6a0','#ef233c','#ffd166','#4cc9f0','#7c3aed','#f72585','#023e8a'] as $c)
                    <label style="cursor:pointer;">
                        <input type="radio" name="color" value="{{ $c }}" style="display:none;" {{ $c === '#4361ee' ? 'checked' : '' }}>
                        <span style="display:block;width:28px;height:28px;border-radius:50%;background:{{ $c }};border:3px solid transparent;transition:all 0.15s;box-shadow:0 2px 4px {{ $c }}55;" class="color-swatch"
                              onclick="selectColor(this, '{{ $c }}')"></span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Icon</label>
                <select name="icon" class="form-control">
                    <option value="folder">📁 General Folder</option>
                    <option value="receipt">🧾 Factures / Invoices</option>
                    <option value="shield">🛃 Customs / Douane</option>
                    <option value="truck">🚚 Transport</option>
                    <option value="file-text">📄 Contracts</option>
                    <option value="users">🏭 Suppliers / Fournisseurs</option>
                    <option value="archive">📦 Archive</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Description <span style="color:var(--text-light);font-weight:400;">(optional)</span></label>
                <input type="text" name="description" class="form-control" placeholder="Brief description of folder contents">
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
let folderView = 'grid';
function setFolderView(v) {
    folderView = v;
    document.getElementById('folderGridView').style.display = v === 'grid' ? 'grid' : 'none';
    document.getElementById('folderListView').style.display = v === 'list' ? 'block' : 'none';
    document.getElementById('fGrid').style.color = v === 'grid' ? 'var(--primary)' : 'var(--text-muted)';
    document.getElementById('fList').style.color = v === 'list' ? 'var(--primary)' : 'var(--text-muted)';
}

function openFolderMenu(id) {
    document.querySelectorAll('[id^="menu-"]').forEach(m => { if(m.id !== 'menu-'+id) m.style.display = 'none'; });
    const m = document.getElementById('menu-'+id);
    m.style.display = m.style.display === 'none' ? 'block' : 'none';
}
document.addEventListener('click', e => {
    if (!e.target.closest('[id^="menu-"]') && !e.target.closest('.folder-menu-btn')) {
        document.querySelectorAll('[id^="menu-"]').forEach(m => m.style.display = 'none');
    }
});

function selectColor(el, c) {
    document.querySelectorAll('.color-swatch').forEach(s => { s.style.borderColor = 'transparent'; s.style.outline = 'none'; });
    el.style.borderColor = '#fff';
    el.style.outline = '3px solid ' + c;
    el.previousElementSibling.checked = true;
}
document.querySelectorAll('#colorPicker input[type=radio]:checked').forEach(r => selectColor(r.nextElementSibling, r.value));
</script>
@endpush
@endsection
