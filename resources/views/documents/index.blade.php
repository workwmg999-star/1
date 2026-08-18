@extends('layouts.app')
@section('title', 'Documents')

@section('content')
<div class="topbar">
    <button class="btn btn-ghost btn-icon" onclick="openSidebar()" id="menuBtnDocs" style="display:none;">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <script>if(window.innerWidth<=768)document.getElementById('menuBtnDocs').style.display='flex';</script>
    <div class="topbar-left">
        <div class="topbar-title">
            @if($currentFolder)
                <a href="{{ route('documents.index') }}" style="color:var(--text-muted);font-weight:500;font-size:15px;">Documents</a>
                <span style="color:var(--text-light);margin:0 6px;">/</span>
                <span style="color:{{ $currentFolder['color'] ?? 'var(--primary)' }}">{{ $currentFolder['name'] }}</span>
            @else
                Documents
            @endif
        </div>
        <div class="topbar-subtitle">{{ count($documents) }} documents</div>
    </div>
    <div class="topbar-actions">
        <form action="{{ route('documents.search') }}" method="GET">
            <div class="search-bar" style="display:none;" id="searchBarDesktop">
                <svg class="search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="q" class="form-control" placeholder="Search documents…" value="{{ request('q') }}" style="width:220px;">
            </div>
        </form>
        <button class="btn btn-ghost btn-icon" onclick="toggleSearchBar()" title="Search">
            <svg width="19" height="19" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </button>
        <button class="btn btn-ghost btn-icon" id="gridToggleBtn" onclick="toggleView()" title="Toggle view">
            <svg id="gridIcon" width="19" height="19" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
        </button>
        <button onclick="openModal('uploadModal')" class="btn btn-outline">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
            Upload
        </button>
        <a href="{{ route('scan') }}" class="btn btn-gradient">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
            Scan
        </a>
    </div>
</div>

<div class="page-content">

    @if(session('success'))
    <div class="alert alert-success"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-error"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>{{ session('error') }}</div>
    @endif

    {{-- Search Bar (mobile toggle) --}}
    <div id="mobileSearchBar" style="display:none;margin-bottom:16px;">
        <form action="{{ route('documents.search') }}" method="GET">
            <div class="search-bar">
                <svg class="search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="q" class="form-control" placeholder="Search documents…" value="{{ request('q') }}" autofocus>
            </div>
        </form>
    </div>

    {{-- Filter Row --}}
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
        <div class="chip-group">
            <a href="{{ route('documents.index', request()->except(['file_type','page'])) }}"
               class="chip {{ !request('file_type') ? 'active' : '' }}">All</a>
            <a href="{{ route('documents.index', array_merge(request()->all(), ['file_type'=>'pdf'])) }}"
               class="chip {{ request('file_type')==='pdf' ? 'active' : '' }}">
                <span style="width:6px;height:6px;border-radius:50%;background:var(--danger);flex-shrink:0;"></span>PDF
            </a>
            <a href="{{ route('documents.index', array_merge(request()->all(), ['file_type'=>'image'])) }}"
               class="chip {{ request('file_type')==='image' ? 'active' : '' }}">
                <span style="width:6px;height:6px;border-radius:50%;background:var(--primary);flex-shrink:0;"></span>Images
            </a>
        </div>

        @if(count($folders))
        <div style="margin-left:auto;display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
            <span style="font-size:12px;color:var(--text-muted);font-weight:500;">Folder:</span>
            <a href="{{ route('documents.index', request()->except(['folder_id','page'])) }}"
               class="chip {{ !request('folder_id') ? 'active' : '' }}">All</a>
            @foreach(array_slice($folders, 0, 5) as $f)
            <a href="{{ route('documents.index', array_merge(request()->all(), ['folder_id'=>$f['id']])) }}"
               class="chip {{ request('folder_id') == $f['id'] ? 'active' : '' }}">
                <span style="width:7px;height:7px;border-radius:50%;background:{{ $f['color'] ?? 'var(--primary)' }};"></span>
                {{ $f['name'] }}
            </a>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Documents --}}
    <div id="docListView">
        <div class="card">
            @if(count($documents))
            @foreach($documents as $doc)
            <div class="doc-item">
                <div class="doc-thumb {{ $doc['file_type'] === 'pdf' ? 'pdf' : 'image' }}">
                    @if($doc['file_type'] === 'pdf')
                    <svg fill="none" viewBox="0 0 24 24" stroke="hsl(350,88%,55%)" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    @else
                    <svg fill="none" viewBox="0 0 24 24" stroke="hsl(231,80%,55%)" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    @endif
                </div>
                <div class="doc-info">
                    <div class="doc-title">{{ $doc['title'] }}</div>
                    <div class="doc-meta">
                        @if($doc['folder'] ?? null)
                        <span style="display:flex;align-items:center;gap:4px;">
                            <span style="width:6px;height:6px;border-radius:50%;background:{{ $doc['folder']['color'] ?? 'var(--primary)' }};flex-shrink:0;"></span>
                            {{ $doc['folder']['name'] }}
                        </span>
                        @else
                        <span style="color:var(--text-light)">Unfiled</span>
                        @endif
                        <span>{{ $doc['size_formatted'] }}</span>
                        <span>{{ \Carbon\Carbon::parse($doc['created_at'])->format('d M Y') }}</span>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                    <span class="badge {{ $doc['file_type'] === 'pdf' ? 'badge-danger' : 'badge-primary' }}">
                        {{ strtoupper($doc['file_type']) }}
                    </span>
                    <a href="{{ route('documents.download', $doc['id']) }}" class="btn btn-ghost btn-icon" title="Download">
                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </a>
                    <form action="{{ route('documents.destroy', $doc['id']) }}" method="POST" onsubmit="return confirm('Delete this document?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-ghost btn-icon" title="Delete" style="color:var(--danger);">
                            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
            @else
            <div class="empty-state">
                <div class="empty-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div class="empty-title">No documents found</div>
                <div class="empty-desc">Upload or scan your first document to get started.</div>
                <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                    <a href="{{ route('scan') }}" class="btn btn-gradient">
                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
                        Scan Document
                    </a>
                    <button onclick="openModal('uploadModal')" class="btn btn-outline">Upload File</button>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Grid View (hidden by default) --}}
    <div id="docGridView" style="display:none;">
        @if(count($documents))
        <div class="doc-grid">
            @foreach($documents as $doc)
            <div class="doc-card">
                <div class="doc-card-preview">
                    @if($doc['file_type'] === 'pdf')
                    <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="hsl(350,88%,55%)" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    @else
                    <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="hsl(231,80%,55%)" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    @endif
                    <span class="badge {{ $doc['file_type'] === 'pdf' ? 'badge-danger' : 'badge-primary' }}" style="position:absolute;top:6px;right:6px;font-size:9px;">{{ strtoupper($doc['file_type']) }}</span>
                </div>
                <div class="doc-card-body">
                    <div class="doc-card-name">{{ $doc['title'] }}</div>
                    <div class="doc-card-meta">{{ $doc['size_formatted'] }} · {{ \Carbon\Carbon::parse($doc['created_at'])->format('d M') }}</div>
                    <a href="{{ route('documents.download', $doc['id']) }}" class="btn btn-outline btn-sm" style="margin-top:8px;width:100%;justify-content:center;font-size:11px;">Download</a>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Pagination --}}
    @if(($meta['last_page'] ?? 1) > 1)
    <div style="display:flex;align-items:center;justify-content:center;gap:6px;margin-top:24px;">
        @for($i = 1; $i <= $meta['last_page']; $i++)
        <a href="{{ route('documents.index', array_merge(request()->all(), ['page'=>$i])) }}"
           class="btn btn-sm {{ $meta['current_page'] == $i ? 'btn-primary' : 'btn-outline' }}">{{ $i }}</a>
        @endfor
    </div>
    @endif

</div>

@include('partials.upload-modal-with-folders', ['folders' => $folders])

@push('scripts')
<script>
let isGrid = false;
function toggleView() {
    isGrid = !isGrid;
    document.getElementById('docListView').style.display = isGrid ? 'none' : 'block';
    document.getElementById('docGridView').style.display = isGrid ? 'block' : 'none';
}
function toggleSearchBar() {
    const mobile = document.getElementById('mobileSearchBar');
    mobile.style.display = mobile.style.display === 'none' ? 'block' : 'none';
    if(mobile.style.display === 'block') mobile.querySelector('input')?.focus();
}
</script>
@endpush

@endsection
