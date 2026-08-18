@extends('layouts.app')
@section('title', 'ماسح المستندات')

@push('styles')
<style>
    .scanner-frame { width: 100%; height: min(760px, calc(100vh - 150px)); min-height: 580px; border: 0; border-radius: var(--r-xl); background: #0b0e14; box-shadow: var(--shadow-lg); }
    @media (max-width: 768px) {
        body:has(.scanner-page) .main { padding-bottom: 0; }
        body:has(.scanner-page) .bottom-nav { display: none; }
        .scanner-page .topbar { display: none; }
        .scanner-page .page-content { padding: 0; height: 100dvh; }
        .scanner-page .scanner-tip { display: none; }
        .scanner-frame { display:block; width:100vw; height:100dvh; min-height:0; margin:0; border-radius:0; box-shadow:none; }
    }
</style>
@endpush

@section('content')
<div class="scanner-page">
    <div class="topbar">
        <div class="topbar-left">
            <div class="topbar-title">ماسح المستندات</div>
            <div class="topbar-subtitle">كشف تلقائي مع تحديد زوايا يدوي وقص منظور</div>
        </div>
        <div class="topbar-actions"><a href="{{ route('documents.index') }}" class="btn btn-outline">إلغاء</a></div>
    </div>

    <div class="page-content" style="max-width:760px;margin:0 auto;">
        <p class="scanner-tip text-muted" style="margin-bottom:10px;font-size:13px;">إن لم يظهر الإطار تلقائيًا، اسحب الدوائر الأربع لتحديد زوايا المستند.</p>
        <iframe class="scanner-frame" title="تحديد زوايا المستند ومسحه" src="{{ asset('scanner/index.html') }}"></iframe>
    </div>
</div>
@endsection

@push('scripts')
<script>
window.addEventListener('message', function (event) {
    if (event.origin !== window.location.origin || event.data?.type !== 'document-scanner:save' || !event.data.image) return;
    sessionStorage.setItem('scanned_image', event.data.image);
    window.location.assign(@json(route('scan.save')));
});
</script>
@endpush
