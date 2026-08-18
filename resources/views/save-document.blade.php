@extends('layouts.app')
@section('title', 'Save Document to Cloud')

@section('content')
<div class="topbar">
    <button class="btn btn-ghost btn-icon" onclick="openSidebar()" id="menuBtnSave" style="display:none;">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <script>if(window.innerWidth<=768)document.getElementById('menuBtnSave').style.display='flex';</script>
    <div class="topbar-left">
        <div class="topbar-title">Save Document to Cloud</div>
        <div class="topbar-subtitle">Step 3 of 3 · Choose Folder & Format</div>
    </div>
    <div class="topbar-actions">
        <a href="{{ route('scan.result') }}" class="btn btn-outline">Back to Preview</a>
    </div>
</div>

<div class="page-content" style="max-width: 600px; margin: 0 auto;">

    {{-- Saved Confirmation Alert Container --}}
    <div id="saveAlert" style="display:none;" class="alert alert-success"></div>

    <div class="card" style="padding: 28px;">

        <form id="saveDocForm" action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Document Title --}}
            <div class="form-group">
                <label class="form-label">Document Name <span style="color:var(--danger)">*</span></label>
                <div class="input-wrap">
                    <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <input type="text" name="title" id="docTitle" class="form-control"
                           value="Scan_{{ now()->format('Y-m-d_His') }}" required autofocus>
                </div>
            </div>

            {{-- Target Folder --}}
            <div class="form-group">
                <label class="form-label">Destination Folder</label>
                <select name="folder_id" id="folderSelect" class="form-control">
                    <option value="">📁 No folder</option>
                    @foreach($folders as $folder)
                    <option value="{{ $folder['id'] }}">{{ $folder['name'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Quick Categories --}}
            <div class="form-group">
                <label class="form-label">Default Categories</label>
                <div class="chip-group">
                    @foreach(['Factures','Documents Douane','Fournisseurs','Transport','Contrats','Autres'] as $cat)
                    <button type="button" class="chip" onclick="selectCategory('{{ $cat }}')">{{ $cat }}</button>
                    @endforeach
                </div>
            </div>

            {{-- Export choice: the PDF is created locally from the final scanned image. --}}
            <div class="form-group">
                <label class="form-label">File Format</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <label class="btn btn-outline" style="cursor:pointer;justify-content:center;padding:12px;border-color:var(--primary);background:var(--primary-ultra);" id="fmtJpgLbl">
                        <input type="radio" name="format" value="jpg" checked style="display:none;" onchange="setFmt('jpg')">
                        🖼 <strong>JPG Image</strong>
                    </label>
                    <label class="btn btn-outline" style="cursor:pointer;justify-content:center;padding:12px;" id="fmtPdfLbl">
                        <input type="radio" name="format" value="pdf" style="display:none;" onchange="setFmt('pdf')">
                        📄 <strong>PDF Document</strong>
                    </label>
                </div>
            </div>

            {{-- Tags --}}
            <div class="form-group">
                <label class="form-label">Tags <span style="color:var(--text-light);font-weight:400;">(comma separated)</span></label>
                <input type="text" name="tags" class="form-control" placeholder="e.g. 2024, Paid, Urgent, Douane">
            </div>

            {{-- Additional Notes --}}
            <div class="form-group">
                <label class="form-label">Notes & Description</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Add optional reference notes or supplier details..."></textarea>
            </div>

            {{-- Dummy hidden file input for form submission --}}
            <input type="file" name="file" id="dummyFileInput" style="display:none;">

            {{-- Save CTA Button --}}
            <button type="submit" id="saveSubmitBtn" class="btn btn-gradient btn-xl btn-block" style="gap:8px;margin-top:8px;">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                ☁️ Save to Cloud Storage
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function setFmt(fmt) {
    const jpg = document.getElementById('fmtJpgLbl');
    const pdf = document.getElementById('fmtPdfLbl');
    const selected = fmt === 'jpg' ? jpg : pdf;
    const other = fmt === 'jpg' ? pdf : jpg;
    selected.style.borderColor = 'var(--primary)'; selected.style.background = 'var(--primary-ultra)';
    other.style.borderColor = 'var(--border)'; other.style.background = 'transparent';
}

function encodePdf(text) { return new TextEncoder().encode(text); }
function joinPdf(parts) {
    const size = parts.reduce((total, part) => total + part.length, 0);
    const result = new Uint8Array(size); let offset = 0;
    parts.forEach(part => { result.set(part, offset); offset += part.length; });
    return result;
}

async function imageToPdf(jpgBlob) {
    const bitmap = await createImageBitmap(jpgBlob);
    const imageWidth = bitmap.width, imageHeight = bitmap.height;
    const pageW = 595.28, pageH = 841.89, margin = 28;
    const ratio = Math.min((pageW - margin * 2) / imageWidth, (pageH - margin * 2) / imageHeight);
    const width = imageWidth * ratio, height = imageHeight * ratio;
    bitmap.close();
    const image = new Uint8Array(await jpgBlob.arrayBuffer());
    const stream = `q\n${width.toFixed(2)} 0 0 ${height.toFixed(2)} ${((pageW - width) / 2).toFixed(2)} ${((pageH - height) / 2).toFixed(2)} cm\n/Im0 Do\nQ\n`;
    const objects = [
        encodePdf('1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n'),
        encodePdf('2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n'),
        encodePdf('3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595.28 841.89] /Resources << /XObject << /Im0 4 0 R >> >> /Contents 5 0 R >>\nendobj\n'),
        joinPdf([encodePdf(`4 0 obj\n<< /Type /XObject /Subtype /Image /Width ${imageWidth} /Height ${imageHeight} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ${image.length} >>\nstream\n`), image, encodePdf('\nendstream\nendobj\n')]),
        encodePdf(`5 0 obj\n<< /Length ${stream.length} >>\nstream\n${stream}endstream\nendobj\n`),
    ];
    const header = encodePdf('%PDF-1.4\n%âãÏÓ\n');
    const offsets = []; let cursor = header.length;
    objects.forEach(object => { offsets.push(cursor); cursor += object.length; });
    const xref = `xref\n0 6\n0000000000 65535 f \n${offsets.map(offset => `${String(offset).padStart(10, '0')} 00000 n \n`).join('')}trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n${cursor}\n%%EOF`;
    return new Blob([header, ...objects, encodePdf(xref)], { type: 'application/pdf' });
}

async function attachScannedFile() {
    const image = sessionStorage.getItem('scanned_image');
    if (!image) throw new Error('No scanned image is available. Return to the scanner and capture a document first.');
    const response = await fetch(image);
    const imageBlob = await response.blob();
    const format = document.querySelector('input[name="format"]:checked').value;
    const blob = format === 'pdf' ? await imageToPdf(imageBlob) : imageBlob;
    const transfer = new DataTransfer();
    transfer.items.add(new File([blob], 'scan-' + Date.now() + (format === 'pdf' ? '.pdf' : '.jpg'), { type: blob.type }));
    document.getElementById('dummyFileInput').files = transfer.files;
}

function selectCategory(cat) {
    const sel = document.getElementById('folderSelect');
    for(let opt of sel.options) {
        if(opt.text.toLowerCase().includes(cat.toLowerCase())) {
            sel.value = opt.value;
            break;
        }
    }
    const title = document.getElementById('docTitle');
    title.value = cat + '_' + new Date().toISOString().slice(0,10);
    showToast(`Category set to ${cat}`, 'info');
}

// Intercept form submit to attach data if needed
document.getElementById('saveDocForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('saveSubmitBtn');
    try {
        await attachScannedFile();
    } catch (error) {
        showToast(error.message, 'error');
        return;
    }
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Uploading to Cloud...';
    sessionStorage.removeItem('scanned_image');
    this.submit();
});
</script>
@endpush

@endsection
