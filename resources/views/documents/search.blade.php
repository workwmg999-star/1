@extends('layouts.app')
@section('title', 'Search Documents')

@section('content')
<div class="topbar">
    <button class="btn btn-ghost btn-icon" onclick="openSidebar()" id="menuBtnSrch" style="display:none;">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <script>if(window.innerWidth<=768)document.getElementById('menuBtnSrch').style.display='flex';</script>
    <div class="topbar-left">
        <div class="topbar-title">Search Documents</div>
        <div class="topbar-subtitle">{{ count($documents) }} result(s) for "{{ $q }}"</div>
    </div>
    <div class="topbar-actions">
        <a href="{{ route('documents.index') }}" class="btn btn-outline">Back to Documents</a>
    </div>
</div>

<div class="page-content" style="max-width: 800px; margin: 0 auto;">

    {{-- Search Input Form --}}
    <div class="card" style="padding: 20px; margin-bottom: 20px;">
        <form action="{{ route('documents.search') }}" method="GET" style="display:flex;gap:10px;">
            <div class="search-bar" style="flex:1;">
                <svg class="search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="q" class="form-control" value="{{ $q }}" placeholder="Search by name, folder, category, or tags..." autofocus required>
            </div>
            <button type="submit" class="btn btn-gradient">Search</button>
        </form>

        {{-- Filter Chips --}}
        <div style="display:flex;gap:8px;margin-top:14px;flex-wrap:wrap;align-items:center;">
            <span style="font-size:12px;color:var(--text-muted);font-weight:600;">Filters:</span>
            <button class="chip active">All Results</button>
            <button class="chip">PDF Only</button>
            <button class="chip">Images Only</button>
            <button class="chip">Last 30 Days</button>
        </div>
    </div>

    {{-- Search Results --}}
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
                    <span>{{ $doc['folder']['name'] ?? 'Unfiled' }}</span>
                    <span>{{ $doc['size_formatted'] }}</span>
                    <span>{{ \Carbon\Carbon::parse($doc['created_at'])->format('d M Y') }}</span>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                <span class="badge {{ $doc['file_type'] === 'pdf' ? 'badge-danger' : 'badge-primary' }}">
                    {{ strtoupper($doc['file_type']) }}
                </span>
                <a href="{{ route('documents.download', $doc['id']) }}" class="btn btn-ghost btn-icon" title="Download">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 013 3h10a3 3 0 013-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                </a>
            </div>
        </div>
        @endforeach
        @else
        <div class="empty-state">
            <div class="empty-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <div class="empty-title">No documents found</div>
            <div class="empty-desc">We couldn't find any documents matching "{{ $q }}". Try searching for keywords like "Facture", "Douane", or "Contrat".</div>
            <a href="{{ route('documents.index') }}" class="btn btn-outline">Browse All Documents</a>
        </div>
        @endif
    </div>

</div>
@endsection
