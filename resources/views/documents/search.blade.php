@extends('layouts.app')
@section('title', 'Search Results')

@section('content')
<div class="topbar">
    <div>
        <div class="topbar-title">Search Results for "{{ $q }}"</div>
        <div class="topbar-subtitle">{{ count($documents) }} result(s) found</div>
    </div>
    <form action="{{ route('documents.search') }}" method="GET" style="display:flex;gap:8px;">
        <input type="text" name="q" class="form-control" value="{{ $q }}" placeholder="Search again…" style="width:240px;">
        <button type="submit" class="btn btn-primary">Search</button>
    </form>
</div>

<div class="page-content">
    <div class="card">
        @if(count($documents))
        @foreach($documents as $doc)
        <div class="doc-item">
            <div class="doc-icon {{ $doc['file_type'] === 'pdf' ? 'pdf' : 'image' }}">
                @if($doc['file_type']==='pdf')
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                @else
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
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
            <div class="doc-actions" style="opacity:1;">
                <a href="{{ route('documents.download', $doc['id']) }}" class="btn btn-outline btn-sm">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download
                </a>
            </div>
        </div>
        @endforeach
        @else
        <div style="text-align:center;padding:60px 20px;">
            <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="#cbd5e1" stroke-width="1.2" style="margin:0 auto 12px;display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <p style="color:var(--text-muted);font-size:15px;font-weight:500;">No documents found for "{{ $q }}"</p>
            <a href="{{ route('documents.index') }}" class="btn btn-outline" style="margin-top:16px;">Browse All Documents</a>
        </div>
        @endif
    </div>
</div>
@endsection
