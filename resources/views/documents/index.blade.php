@extends('layouts.app')
@section('title', 'Documents')

@section('content')
<div class="topbar">
    <div>
        <div class="topbar-title">
            @if($currentFolder)
                <a href="{{ route('documents.index') }}" style="color:var(--text-muted);text-decoration:none;font-size:14px;font-weight:500;">Documents</a>
                <span style="color:var(--text-muted);margin:0 6px;">/</span>
                <span style="color:{{ $currentFolder['color'] }}">{{ $currentFolder['name'] }}</span>
            @else
                Documents
            @endif
        </div>
        <div class="topbar-subtitle">{{ count($documents) }} documents</div>
    </div>
    <form action="{{ route('documents.search') }}" method="GET" style="display:flex;gap:8px;">
        <input type="text" name="q" class="form-control" placeholder="Search documents…" style="width:240px;" value="{{ request('q') }}">
        <button type="submit" class="btn btn-outline">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </button>
    </form>
    <div style="display:flex;gap:8px;">
        <a href="{{ route('scan') }}" class="btn btn-primary" style="background:linear-gradient(135deg,#4f46e5,#06b6d4);">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
            📷 Scan Document
        </a>
        <button onclick="openModal('uploadModal')" class="btn btn-outline">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Upload File
        </button>
    </div>
</div>

<div class="page-content">

    @if(session('success'))
    <div class="alert alert-success">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-error">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Filter bar --}}
    <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
        <a href="{{ route('documents.index', request()->except(['file_type','page'])) }}" class="btn btn-sm {{ !request('file_type') ? 'btn-primary' : 'btn-outline' }}">All</a>
        <a href="{{ route('documents.index', array_merge(request()->all(), ['file_type'=>'pdf'])) }}" class="btn btn-sm {{ request('file_type')==='pdf' ? 'btn-primary' : 'btn-outline' }}">PDF</a>
        <a href="{{ route('documents.index', array_merge(request()->all(), ['file_type'=>'image'])) }}" class="btn btn-sm {{ request('file_type')==='image' ? 'btn-primary' : 'btn-outline' }}">Images</a>

        @if(count($folders))
        <div style="margin-left:auto;display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('documents.index', request()->except(['folder_id','page'])) }}" class="btn btn-sm {{ !request('folder_id') ? 'btn-primary' : 'btn-outline' }}">All Folders</a>
            @foreach(array_slice($folders, 0, 6) as $f)
            <a href="{{ route('documents.index', array_merge(request()->all(), ['folder_id'=>$f['id']])) }}"
               class="btn btn-sm {{ request('folder_id') == $f['id'] ? 'btn-primary' : 'btn-outline' }}"
               style="{{ request('folder_id') == $f['id'] ? "background:{$f['color']};border-color:{$f['color']};" : '' }}">
                {{ $f['name'] }}
            </a>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Documents List --}}
    <div class="card">
        @if(count($documents))
        @foreach($documents as $doc)
        <div class="doc-item">
            <div class="doc-icon {{ $doc['file_type'] === 'pdf' ? 'pdf' : 'image' }}">
                @if($doc['file_type'] === 'pdf')
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                @else
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                @endif
            </div>
            <div class="doc-info">
                <div class="doc-title">{{ $doc['title'] }}</div>
                <div class="doc-meta">
                    @if($doc['folder'])
                    <span style="display:flex;align-items:center;gap:4px;">
                        <span style="width:8px;height:8px;border-radius:50%;background:#6366f1;display:inline-block;"></span>
                        {{ $doc['folder']['name'] }}
                    </span>
                    @else
                    <span style="color:var(--text-light)">Unfiled</span>
                    @endif
                    <span>{{ strtoupper($doc['file_type']) }}</span>
                    <span>{{ $doc['size_formatted'] }}</span>
                    <span>{{ \Carbon\Carbon::parse($doc['created_at'])->format('d M Y') }}</span>
                </div>
            </div>
            <div class="doc-actions">
                <a href="{{ route('documents.download', $doc['id']) }}" class="btn btn-outline btn-sm" title="Download">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download
                </a>
                <form action="{{ route('documents.destroy', $doc['id']) }}" method="POST" onsubmit="return confirm('Delete this document?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm" style="color:var(--danger);border:1.5px solid #fee2e2;background:#fff;" title="Delete">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
        @else
        <div style="text-align:center;padding:60px 20px;">
            <svg width="56" height="56" fill="none" viewBox="0 0 24 24" stroke="#cbd5e1" stroke-width="1.2" style="margin:0 auto 16px;display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p style="color:var(--text-muted);font-size:15px;font-weight:500;">No documents found</p>
            <p style="color:var(--text-light);font-size:13px;margin:6px 0 20px;">Upload your first scanned document to get started</p>
            <button onclick="openModal('uploadModal')" class="btn btn-primary">Upload Document</button>
        </div>
        @endif
    </div>

    {{-- Pagination --}}
    @if(($meta['last_page'] ?? 1) > 1)
    <div style="display:flex;align-items:center;justify-content:center;gap:8px;margin-top:20px;">
        @for($i = 1; $i <= $meta['last_page']; $i++)
        <a href="{{ route('documents.index', array_merge(request()->all(), ['page'=>$i])) }}"
           class="btn btn-sm {{ $meta['current_page'] == $i ? 'btn-primary' : 'btn-outline' }}">{{ $i }}</a>
        @endfor
    </div>
    @endif
</div>

@include('partials.upload-modal-with-folders', ['folders' => $folders])
@endsection
