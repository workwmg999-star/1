@extends('layouts.app')
@section('title', 'Scan Result & Enhance')

@push('styles')
<style>
.preview-stage {
    background: var(--sidebar-bg);
    border-radius: var(--r-xl);
    padding: var(--space-6);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border-2);
    min-height: 420px;
}

.preview-img-container {
    max-width: 100%;
    max-height: 440px;
    border-radius: var(--r);
    overflow: hidden;
    box-shadow: 0 12px 32px rgba(0,0,0,0.5);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: #fff;
}

.preview-img-container img {
    max-width: 100%;
    max-height: 440px;
    display: block;
    object-fit: contain;
}

/* Toolbar Buttons */
.toolbar-grid {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 16px;
    padding: 12px;
    background: var(--card);
    border-radius: var(--r-lg);
    border: 1px solid var(--border);
    box-shadow: var(--shadow-sm);
}

.toolbar-btn {
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    padding: 8px 12px;
    border-radius: var(--r);
    border: 1.5px solid var(--border);
    background: var(--card);
    color: var(--text-2);
    font-size: 11px; font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    min-width: 62px;
}
.toolbar-btn:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-ultra); transform: translateY(-2px); }
.toolbar-btn svg { width: 18px; height: 18px; }

/* Page Thumbnails Strip */
.thumbnail-strip {
    display: flex; gap: 10px;
    padding: 12px 0;
    overflow-x: auto;
    width: 100%;
}
.thumb-item {
    width: 60px; height: 76px;
    border-radius: var(--r-sm);
    border: 2px solid var(--border);
    overflow: hidden;
    cursor: pointer;
    flex-shrink: 0;
    position: relative;
    transition: var(--transition);
    background: #fff;
}
.thumb-item.active { border-color: var(--primary); box-shadow: var(--shadow-primary); }
.thumb-item img { width: 100%; height: 100%; object-fit: cover; }
.thumb-badge {
    position: absolute; bottom: 2px; right: 2px;
    background: rgba(0,0,0,0.7); color: #fff;
    font-size: 9px; font-weight: 700;
    padding: 1px 5px; border-radius: 4px;
}
</style>
@endpush

@section('content')
<div class="topbar">
    <button class="btn btn-ghost btn-icon" onclick="openSidebar()" id="menuBtnRes" style="display:none;">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <script>if(window.innerWidth<=768)document.getElementById('menuBtnRes').style.display='flex';</script>
    <div class="topbar-left">
        <div class="topbar-title">Scan Preview & Enhance</div>
        <div class="topbar-subtitle">Page 1 of 1 · Auto Filter Applied</div>
    </div>
    <div class="topbar-actions">
        <a href="{{ route('scan') }}" class="btn btn-outline">Retake</a>
        <a href="{{ route('scan.save') }}" class="btn btn-gradient" style="gap:6px;">
            Save Document →
        </a>
    </div>
</div>

<div class="page-content" style="max-width: 680px; margin: 0 auto;">

    {{-- Main Preview Stage --}}
    <div class="preview-stage">
        <div class="preview-img-container" id="imgBox">
            <img id="scannedDocImg" src="https://images.unsplash.com/photo-1568602471122-7832951cc4c5?w=800&q=80" alt="Scanned Document">
        </div>
    </div>

    {{-- Multi-page Thumbnails Strip --}}
    <div class="thumbnail-strip">
        <div class="thumb-item active">
            <img src="https://images.unsplash.com/photo-1568602471122-7832951cc4c5?w=200&q=80" alt="Page 1">
            <span class="thumb-badge">1</span>
        </div>
        <a href="{{ route('scan') }}" class="thumb-item" style="display:flex;align-items:center;justify-content:center;border-style:dashed;color:var(--text-light);" title="Add Page">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        </a>
    </div>

    {{-- Toolbar Actions --}}
    <div class="toolbar-grid">
        <button class="toolbar-btn" onclick="applyEnhance('magic')" title="Auto Enhance">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            Magic
        </button>
        <button class="toolbar-btn" onclick="rotateDoc()" title="Rotate">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Rotate
        </button>
        <button class="toolbar-btn" onclick="cropDoc()" title="Crop">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Crop
        </button>
        <button class="toolbar-btn" onclick="applyFilter('bw')" title="Black & White">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 3v18a9 9 0 000-18z"/></svg>
            B&W
        </button>
        <button class="toolbar-btn" onclick="adjustBrightness()" title="Brightness">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            Brighten
        </button>
        <a href="{{ route('scan') }}" class="toolbar-btn" style="text-decoration:none;">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add Page
        </a>
    </div>

    {{-- Bottom Save CTA --}}
    <div style="margin-top:20px;display:flex;gap:12px;">
        <a href="{{ route('scan') }}" class="btn btn-outline" style="flex:1;justify-content:center;">
            🔄 Retake Photo
        </a>
        <a href="{{ route('scan.save') }}" class="btn btn-gradient btn-lg" style="flex:2;justify-content:center;gap:8px;">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Save Document to Cloud
        </a>
    </div>

</div>

@push('scripts')
<script>
let currentRotation = 0;

(function() {
    const saved = sessionStorage.getItem('scanned_image');
    if (saved) {
        document.getElementById('scannedDocImg').src = saved;
    }
})();

function rotateDoc() {
    currentRotation = (currentRotation + 90) % 360;
    document.getElementById('scannedDocImg').style.transform = `rotate(${currentRotation}deg)`;
    showToast(`Rotated to ${currentRotation}°`, 'info');
}

function applyEnhance(type) {
    document.getElementById('scannedDocImg').style.filter = 'contrast(1.3) brightness(1.05) sharpen(1.2)';
    showToast('Auto Magic Color & Edge enhancement applied!', 'success');
}

function applyFilter(filter) {
    document.getElementById('scannedDocImg').style.filter = 'grayscale(100%) contrast(1.5)';
    showToast('High-Contrast B&W Document filter applied', 'info');
}

function adjustBrightness() {
    document.getElementById('scannedDocImg').style.filter = 'brightness(1.25) contrast(1.1)';
    showToast('Increased document brightness', 'info');
}

function cropDoc() {
    showToast('Opening crop handles...', 'info');
}
</script>
@endpush

@endsection
