@extends('layouts.app')
@section('title', 'Smart High-Quality Document Scanner')

@section('content')
<div class="topbar">
    <div>
        <div class="topbar-title">⚡ High-Definition Document Scanner</div>
        <div class="topbar-subtitle">Adaptive Edge & Grid Line Preservation &bull; Perspective Warp (OpenCV) &bull; Cloud Storage</div>
    </div>
    <a href="{{ route('documents.index') }}" class="btn btn-outline">Back to Documents</a>
</div>

<div class="page-content">

    {{-- Live Alerts --}}
    <div id="alertBox" style="display:none;" class="alert"></div>

    {{-- OpenCV Status Indicator --}}
    <div id="cvStatusBadge" style="display:inline-flex;align-items:center;gap:6px;font-size:12px;color:var(--text-muted);background:#f1f5f9;padding:4px 10px;border-radius:20px;margin-bottom:16px;">
        <span id="cvDot" style="width:8px;height:8px;border-radius:50%;background:#f59e0b;"></span>
        <span id="cvStatusText">Loading OpenCV Vision Engine...</span>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:24px;align-items:start;">

        {{-- Left: Scanner Studio & 4-Point Perspective Warp --}}
        <div class="card" style="padding:24px;">

            {{-- Capture Controls --}}
            <div style="display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap;">
                <label class="btn btn-primary" style="background:linear-gradient(135deg,#10b981,#059669);cursor:pointer;flex:1;justify-content:center;padding:14px;font-size:15px;box-shadow:0 4px 12px rgba(16,185,129,0.3);">
                    📸 <strong>Take Photo with Phone Camera</strong>
                    <input type="file" id="cameraInput" accept="image/*" capture="environment" style="display:none;" onchange="handleIncomingFile(this.files[0])">
                </label>

                <label class="btn btn-outline" style="cursor:pointer;flex:1;justify-content:center;padding:14px;font-size:14px;">
                    📁 Choose Existing File
                    <input type="file" id="filePicker" accept="image/*" style="display:none;" onchange="handleIncomingFile(this.files[0])">
                </label>
            </div>

            {{-- 1. Initial State Placeholder --}}
            <div id="emptyView" style="border:2px dashed var(--border);border-radius:12px;padding:48px 20px;text-align:center;background:#fff;">
                <div style="width:64px;height:64px;border-radius:50%;background:#ede9fe;color:var(--primary);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" width="32" height="32"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
                </div>
                <div style="font-size:16px;font-weight:700;color:var(--text);margin-bottom:6px;">High-Quality Scanner Ready</div>
                <p style="font-size:13.5px;color:var(--text-muted);max-width:320px;margin:0 auto;">Take a photo of any document. Auto edge detection, table grid preservation, and perspective correction will enhance it instantly.</p>
            </div>

            {{-- 2. Step 1: 4-Point Interactive Corner Crop Studio --}}
            <div id="cropStudio" style="display:none;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:8px;">
                    <div>
                        <span style="font-size:14px;font-weight:700;">1. Adjust Paper Corners</span>
                        <div style="font-size:12px;color:var(--text-muted);">Drag the 4 corner points to match the paper edges</div>
                    </div>
                    <button type="button" class="btn btn-outline btn-sm" onclick="autoDetectCorners()">🎯 Auto Detect</button>
                </div>

                {{-- Interactive Corner Box Container --}}
                <div id="cropContainer" style="position:relative;background:#0f172a;border-radius:10px;overflow:hidden;text-align:center;user-select:none;touch-action:none;display:flex;align-items:center;justify-content:center;">
                    <canvas id="cropCanvas" style="max-width:100%;max-height:460px;display:block;margin:0 auto;"></canvas>
                    
                    {{-- 4 Draggable Corner Handles --}}
                    <div id="handle0" class="corner-handle" style="top:20px;left:20px;">TL</div>
                    <div id="handle1" class="corner-handle" style="top:20px;right:20px;">TR</div>
                    <div id="handle2" class="corner-handle" style="bottom:20px;right:20px;">BR</div>
                    <div id="handle3" class="corner-handle" style="bottom:20px;left:20px;">BL</div>
                </div>

                <div style="display:flex;justify-content:space-between;margin-top:14px;">
                    <button type="button" class="btn btn-outline" onclick="resetToEmpty()">🔄 Retake</button>
                    <button type="button" class="btn btn-primary" onclick="applyPerspectiveWarp()" style="background:linear-gradient(135deg,#4f46e5,#06b6d4);">
                        📐 Crop & Deskew Document →
                    </button>
                </div>
            </div>

            {{-- 3. Step 2: High-Def Enhancement Filters & Detail Tuning --}}
            <div id="filterStudio" style="display:none;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px;">
                    <div style="font-size:14px;font-weight:700;">2. Document Enhancement Filters:</div>
                    <button type="button" class="btn btn-outline btn-sm" onclick="backToCrop()">📐 Re-crop</button>
                </div>

                {{-- Filter Buttons --}}
                <div style="display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap;">
                    <button type="button" class="btn btn-sm btn-primary filter-btn" onclick="setFilterMode('adaptive_grid', this)">📑 Adaptive (Preserve Tables & Thin Lines)</button>
                    <button type="button" class="btn btn-sm btn-outline filter-btn" onclick="setFilterMode('magic_clean', this)">✨ Magic B&W (CamScanner)</button>
                    <button type="button" class="btn btn-sm btn-outline filter-btn" onclick="setFilterMode('crystal_color', this)">🎨 HD Clean Color</button>
                    <button type="button" class="btn btn-sm btn-outline filter-btn" onclick="setFilterMode('sharp_gray', this)">🔘 Sharp Grayscale</button>
                    <button type="button" class="btn btn-sm btn-outline filter-btn" onclick="setFilterMode('original', this)">📷 Original</button>
                </div>

                {{-- Fine Details & Ink Density Slider --}}
                <div style="background:#f8fafc;border:1px solid var(--border);border-radius:8px;padding:12px 16px;margin-bottom:16px;">
                    <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:600;margin-bottom:6px;">
                        <span>🖊️ Line & Ink Intensity (وضوح الخطوط وتفاصيل الجداول):</span>
                        <span id="intensityLabel" style="color:var(--primary);">Balanced</span>
                    </div>
                    <input type="range" id="intensitySlider" min="1" max="5" value="3" style="width:100%;cursor:pointer;" oninput="onIntensityChanged(this.value)">
                    <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--text-muted);margin-top:2px;">
                        <span>Light (خفيف)</span>
                        <span>Balanced (متوازن)</span>
                        <span>Deep Ink & Thick Lines (حبر داكن وخطوط عريضة)</span>
                    </div>
                </div>

                {{-- Result Canvas --}}
                <div style="text-align:center;background:#0f172a;padding:12px;border-radius:10px;margin-bottom:16px;">
                    <canvas id="resultCanvas" style="max-width:100%;max-height:440px;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,0.3);"></canvas>
                </div>

                <div style="display:flex;gap:10px;justify-content:flex-end;">
                    <button type="button" class="btn btn-outline" onclick="addPageAndScanAnother()">➕ Add Page</button>
                    <button type="button" class="btn btn-primary" onclick="confirmCurrentPage()">✅ Confirm Page</button>
                </div>
            </div>

            {{-- Scanned Pages Strip --}}
            <div id="pagesStrip" style="display:none;margin-top:20px;padding-top:16px;border-top:1px solid var(--border);">
                <div style="font-size:13px;font-weight:700;margin-bottom:10px;display:flex;justify-content:space-between;">
                    <span>Scanned Pages Queue:</span>
                    <span id="pageCounter" style="color:var(--primary);">1 Page</span>
                </div>
                <div id="pagesList" style="display:flex;gap:12px;overflow-x:auto;padding-bottom:8px;"></div>
            </div>

        </div>

        {{-- Right: Document Details & Cloud Storage Upload --}}
        <div class="card" style="padding:24px;">
            <div class="card-title" style="margin-bottom:16px;">Document Details</div>

            <form id="metaForm" onsubmit="event.preventDefault(); processAndUpload();">
                <div class="form-group">
                    <label class="form-label">Document Title <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="docTitle" class="form-control" placeholder="e.g. Facture Transport Casa #104" required value="Scan_{{ date('Y-m-d_His') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Folder / Category</label>
                    <select id="docFolderId" class="form-control">
                        <option value="">— Unfiled (Root) —</option>
                        @foreach($folders as $folder)
                        <option value="{{ $folder['id'] }}">{{ $folder['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Output Format</label>
                    <div style="display:flex;gap:16px;margin-top:6px;">
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:14px;">
                            <input type="radio" name="export_format" value="pdf" checked> 📄 PDF Document
                        </label>
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:14px;">
                            <input type="radio" name="export_format" value="jpg"> 🖼️ Image (JPG)
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description / Notes</label>
                    <textarea id="docDescription" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                </div>

                {{-- Progress Bar --}}
                <div id="uploadProgressBox" style="display:none;margin-bottom:14px;">
                    <div style="font-size:12.5px;font-weight:600;color:var(--primary);margin-bottom:4px;" id="uploadProgressLabel">Uploading...</div>
                    <div style="height:8px;background:var(--border);border-radius:4px;overflow:hidden;">
                        <div id="uploadProgressBar" style="height:100%;width:0%;background:linear-gradient(90deg,#4f46e5,#06b6d4);transition:width .3s;"></div>
                    </div>
                </div>

                <button type="button" id="btnSaveUpload" onclick="processAndUpload()" class="btn btn-primary" style="width:100%;justify-content:center;padding:14px;font-size:15px;background:linear-gradient(135deg,#4f46e5,#06b6d4);" disabled>
                    💾 Save & Upload to Cloud
                </button>
                <div id="uploadStatusText" style="text-align:center;font-size:12.5px;color:var(--text-muted);margin-top:8px;">
                    Take a photo of a document first
                </div>
            </form>
        </div>

    </div>
</div>

<style>
.corner-handle {
    position: absolute;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #06b6d4;
    color: #fff;
    font-size: 10px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
    cursor: grab;
    transform: translate(-50%, -50%);
    touch-action: none;
    z-index: 10;
}
.corner-handle:active {
    cursor: grabbing;
    background: #4f46e5;
    transform: translate(-50%, -50%) scale(1.2);
}
</style>

{{-- Load OpenCV.js for Computer Vision & jsPDF for PDF creation --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script async src="https://docs.opencv.org/4.8.0/opencv.js" onload="onCvLoaded()"></script>

<script>
let cvReady = false;
let sourceImage = null; // Full-resolution HTMLImageElement
let corners = [ {x:0,y:0}, {x:1,y:0}, {x:1,y:1}, {x:0,y:1} ]; // TL, TR, BR, BL relative (0 to 1)
let warpedCanvas = document.createElement('canvas');
let scannedPages = []; // array of data URLs
let currentFilter = 'adaptive_grid';
let currentIntensity = 3; // 1 to 5

const emptyView = document.getElementById('emptyView');
const cropStudio = document.getElementById('cropStudio');
const filterStudio = document.getElementById('filterStudio');
const cropCanvas = document.getElementById('cropCanvas');
const resultCanvas = document.getElementById('resultCanvas');
const cropContainer = document.getElementById('cropContainer');
const btnSaveUpload = document.getElementById('btnSaveUpload');
const uploadStatusText = document.getElementById('uploadStatusText');
const pageCounter = document.getElementById('pageCounter');
const pagesStrip = document.getElementById('pagesStrip');
const pagesList = document.getElementById('pagesList');
const alertBox = document.getElementById('alertBox');
const uploadProgressBox = document.getElementById('uploadProgressBox');
const uploadProgressBar = document.getElementById('uploadProgressBar');
const uploadProgressLabel = document.getElementById('uploadProgressLabel');
const intensityLabel = document.getElementById('intensityLabel');

function onCvLoaded() {
    cvReady = true;
    document.getElementById('cvDot').style.background = '#10b981';
    document.getElementById('cvStatusText').textContent = 'OpenCV Vision Engine Ready';
}

function showAlert(type, msg) {
    alertBox.className = type === 'success' ? 'alert alert-success' : 'alert alert-error';
    alertBox.textContent = msg;
    alertBox.style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// 1. Handle incoming photo
function handleIncomingFile(file) {
    if (!file) return;

    showAlert('success', 'Loading document...');

    const reader = new FileReader();
    reader.onload = e => {
        const img = new Image();
        img.onload = () => {
            sourceImage = img;
            emptyView.style.display = 'none';
            filterStudio.style.display = 'none';
            cropStudio.style.display = 'block';
            alertBox.style.display = 'none';

            initCropStudio();
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

function resetToEmpty() {
    cropStudio.style.display = 'none';
    filterStudio.style.display = 'none';
    emptyView.style.display = 'block';
}

function backToCrop() {
    filterStudio.style.display = 'none';
    cropStudio.style.display = 'block';
    initCropStudio();
}

// 2. Initialize interactive 4-corner crop studio
function initCropStudio() {
    if (!sourceImage) return;

    const maxDim = 900;
    let w = sourceImage.width;
    let h = sourceImage.height;
    if (w > maxDim || h > maxDim) {
        if (w > h) {
            h = Math.round((h * maxDim) / w);
            w = maxDim;
        } else {
            w = Math.round((w * maxDim) / h);
            h = maxDim;
        }
    }

    cropCanvas.width = w;
    cropCanvas.height = h;
    const ctx = cropCanvas.getContext('2d');
    ctx.drawImage(sourceImage, 0, 0, w, h);

    // Default corners
    corners = [
        { x: 0.05, y: 0.05 },
        { x: 0.95, y: 0.05 },
        { x: 0.95, y: 0.95 },
        { x: 0.05, y: 0.95 }
    ];

    if (cvReady) {
        autoDetectCorners();
    } else {
        updateHandlesPosition();
        drawCropPolygon();
    }
}

// Auto Edge Detection
function autoDetectCorners() {
    if (!cvReady || !sourceImage) return;

    try {
        let src = cv.imread(cropCanvas);
        let gray = new cv.Mat();
        cv.cvtColor(src, gray, cv.COLOR_RGBA2GRAY, 0);

        let blurred = new cv.Mat();
        cv.GaussianBlur(gray, blurred, new cv.Size(5, 5), 0);

        let edged = new cv.Mat();
        cv.Canny(blurred, edged, 75, 200);

        let contours = new cv.MatVector();
        let hierarchy = new cv.Mat();
        cv.findContours(edged, contours, hierarchy, cv.RETR_LIST, cv.CHAIN_APPROX_SIMPLE);

        let maxArea = 0;
        let bestContour = null;

        for (let i = 0; i < contours.size(); ++i) {
            let cnt = contours.get(i);
            let area = cv.contourArea(cnt);
            if (area > (cropCanvas.width * cropCanvas.height * 0.15)) {
                let peri = cv.arcLength(cnt, true);
                let approx = new cv.Mat();
                cv.approxPolyDP(cnt, approx, 0.02 * peri, true);

                if (approx.rows === 4 && area > maxArea) {
                    maxArea = area;
                    bestContour = approx;
                } else {
                    approx.delete();
                }
            }
            cnt.delete();
        }

        if (bestContour) {
            let pts = [];
            for (let i = 0; i < 4; i++) {
                pts.push({
                    x: bestContour.data32S[i * 2] / cropCanvas.width,
                    y: bestContour.data32S[i * 2 + 1] / cropCanvas.height
                });
            }
            // Sort corners: TL, TR, BR, BL
            pts.sort((a, b) => (a.y - b.y));
            let top = pts.slice(0, 2).sort((a, b) => a.x - b.x);
            let btm = pts.slice(2, 4).sort((a, b) => a.x - b.x);
            corners = [top[0], top[1], btm[1], btm[0]];
            bestContour.delete();
        }

        src.delete(); gray.delete(); blurred.delete(); edged.delete(); contours.delete(); hierarchy.delete();
    } catch (e) {
        console.warn("Auto edge detection fallback:", e);
    }

    updateHandlesPosition();
    drawCropPolygon();
}

function updateHandlesPosition() {
    const rect = cropCanvas.getBoundingClientRect();
    const contRect = cropContainer.getBoundingClientRect();

    const offsetX = rect.left - contRect.left;
    const offsetY = rect.top - contRect.top;

    for (let i = 0; i < 4; i++) {
        const handle = document.getElementById(`handle${i}`);
        const px = offsetX + corners[i].x * rect.width;
        const py = offsetY + corners[i].y * rect.height;
        handle.style.left = `${px}px`;
        handle.style.top = `${py}px`;
    }
}

function drawCropPolygon() {
    const ctx = cropCanvas.getContext('2d');
    ctx.drawImage(sourceImage, 0, 0, cropCanvas.width, cropCanvas.height);

    ctx.beginPath();
    ctx.moveTo(corners[0].x * cropCanvas.width, corners[0].y * cropCanvas.height);
    for (let i = 1; i < 4; i++) {
        ctx.lineTo(corners[i].x * cropCanvas.width, corners[i].y * cropCanvas.height);
    }
    ctx.closePath();

    ctx.fillStyle = 'rgba(6, 182, 212, 0.18)';
    ctx.fill();
    ctx.strokeStyle = '#06b6d4';
    ctx.lineWidth = 3;
    ctx.stroke();
}

// Handle Dragging
let activeHandle = null;

function setupDrag() {
    for (let i = 0; i < 4; i++) {
        const h = document.getElementById(`handle${i}`);
        const onStart = (e) => {
            e.preventDefault();
            activeHandle = i;
        };
        h.addEventListener('mousedown', onStart);
        h.addEventListener('touchstart', onStart, { passive: false });
    }

    const onMove = (e) => {
        if (activeHandle === null) return;
        e.preventDefault();

        const contRect = cropContainer.getBoundingClientRect();
        const rect = cropCanvas.getBoundingClientRect();
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;

        let rx = (clientX - rect.left) / rect.width;
        let ry = (clientY - rect.top) / rect.height;

        rx = Math.max(0, Math.min(1, rx));
        ry = Math.max(0, Math.min(1, ry));

        corners[activeHandle] = { x: rx, y: ry };
        updateHandlesPosition();
        drawCropPolygon();
    };

    const onEnd = () => { activeHandle = null; };

    window.addEventListener('mousemove', onMove);
    window.addEventListener('touchmove', onMove, { passive: false });
    window.addEventListener('mouseup', onEnd);
    window.addEventListener('touchend', onEnd);
}
setupDrag();

// 3. Perspective Warp (Deskew)
function applyPerspectiveWarp() {
    if (!sourceImage) return;

    showAlert('success', 'Deskewing & optimizing document quality...');

    const origW = sourceImage.width;
    const origH = sourceImage.height;

    const tl = { x: corners[0].x * origW, y: corners[0].y * origH };
    const tr = { x: corners[1].x * origW, y: corners[1].y * origH };
    const br = { x: corners[2].x * origW, y: corners[2].y * origH };
    const bl = { x: corners[3].x * origW, y: corners[3].y * origH };

    const widthTop = Math.hypot(tr.x - tl.x, tr.y - tl.y);
    const widthBottom = Math.hypot(br.x - bl.x, br.y - bl.y);
    const outWidth = Math.round(Math.max(widthTop, widthBottom));

    const heightLeft = Math.hypot(bl.x - tl.x, bl.y - tl.y);
    const heightRight = Math.hypot(br.x - tr.x, br.y - tr.y);
    const outHeight = Math.round(Math.max(heightLeft, heightRight));

    warpedCanvas.width = outWidth;
    warpedCanvas.height = outHeight;

    if (cvReady) {
        try {
            const tempCanvas = document.createElement('canvas');
            tempCanvas.width = origW;
            tempCanvas.height = origH;
            tempCanvas.getContext('2d').drawImage(sourceImage, 0, 0);

            let src = cv.imread(tempCanvas);
            let dst = new cv.Mat();

            let srcTri = cv.matFromArray(4, 1, cv.CV_32FC2, [
                tl.x, tl.y,
                tr.x, tr.y,
                br.x, br.y,
                bl.x, bl.y
            ]);

            let dstTri = cv.matFromArray(4, 1, cv.CV_32FC2, [
                0, 0,
                outWidth, 0,
                outWidth, outHeight,
                0, outHeight
            ]);

            let M = cv.getPerspectiveTransform(srcTri, dstTri);
            let dsize = new cv.Size(outWidth, outHeight);
            cv.warpPerspective(src, dst, M, dsize, cv.INTER_LINEAR, cv.BORDER_CONSTANT, new cv.Scalar());

            cv.imshow(warpedCanvas, dst);

            src.delete(); dst.delete(); srcTri.delete(); dstTri.delete(); M.delete();
        } catch (e) {
            console.error("OpenCV Warp error, fallback:", e);
            warpedCanvas.getContext('2d').drawImage(sourceImage, 0, 0, outWidth, outHeight);
        }
    } else {
        warpedCanvas.getContext('2d').drawImage(sourceImage, 0, 0, outWidth, outHeight);
    }

    cropStudio.style.display = 'none';
    filterStudio.style.display = 'block';
    alertBox.style.display = 'none';

    renderCurrentFilter();
}

function setFilterMode(filter, btnElem) {
    currentFilter = filter;
    if (btnElem) {
        document.querySelectorAll('.filter-btn').forEach(b => b.className = 'btn btn-sm btn-outline filter-btn');
        btnElem.className = 'btn btn-sm btn-primary filter-btn';
    }
    renderCurrentFilter();
}

function onIntensityChanged(val) {
    currentIntensity = parseInt(val, 10);
    const labels = ['Light (خفيف)', 'Soft Ink', 'Balanced (متوازن)', 'Sharp Grid & Dark Ink', 'Bold High Contrast (خطوط عريضة جداً)'];
    intensityLabel.textContent = labels[currentIntensity - 1] || 'Balanced';
    renderCurrentFilter();
}

// 4. Advanced Document Filtering (Preserves thin lines, tables, numbers & text)
function renderCurrentFilter() {
    if (!warpedCanvas.width) return;

    resultCanvas.width = warpedCanvas.width;
    resultCanvas.height = warpedCanvas.height;

    if (currentFilter === 'original') {
        resultCanvas.getContext('2d').drawImage(warpedCanvas, 0, 0);
        return;
    }

    // Adaptive Table & Thin-line Preserving Algorithm
    if (cvReady && (currentFilter === 'adaptive_grid' || currentFilter === 'magic_clean')) {
        try {
            let src = cv.imread(warpedCanvas);
            let gray = new cv.Mat();
            cv.cvtColor(src, gray, cv.COLOR_RGBA2GRAY, 0);

            if (currentFilter === 'adaptive_grid') {
                // Adaptive Gaussian Local Thresholding: Evaluates every pixel relative to local 21px window
                // This guarantees thin table lines and faint print never get erased!
                let dst = new cv.Mat();
                let blockSize = 21; // neighborhood
                let C_offset = (6 - currentIntensity) * 2.5 + 2; // Tuned by intensity slider

                cv.adaptiveThreshold(gray, dst, 255, cv.ADAPTIVE_THRESH_GAUSSIAN_C, cv.THRESH_BINARY, blockSize, C_offset);
                cv.imshow(resultCanvas, dst);
                dst.delete();
            } else if (currentFilter === 'magic_clean') {
                // Background Illumination Division: Whitens background while keeping all grayscale line gradients
                let bg = new cv.Mat();
                cv.GaussianBlur(gray, bg, new cv.Size(25, 25), 0);

                let dst = new cv.Mat();
                cv.divide(gray, bg, dst, 255);

                // Enhance contrast
                let contrastBoost = 1.0 + (currentIntensity * 0.15);
                dst.convertTo(dst, -1, contrastBoost, -20 * (currentIntensity - 2));

                cv.imshow(resultCanvas, dst);
                bg.delete(); dst.delete();
            }

            src.delete(); gray.delete();
            return;
        } catch (err) {
            console.warn("OpenCV filter fallback:", err);
        }
    }

    // High-performance Pure Canvas Fallback
    const ctx = resultCanvas.getContext('2d');
    ctx.drawImage(warpedCanvas, 0, 0);
    const imgData = ctx.getImageData(0, 0, resultCanvas.width, resultCanvas.height);
    const d = imgData.data;

    const thresholdOffset = (currentIntensity - 3) * 20;

    if (currentFilter === 'adaptive_grid' || currentFilter === 'magic_clean') {
        for (let i = 0; i < d.length; i += 4) {
            let gray = 0.299 * d[i] + 0.587 * d[i+1] + 0.114 * d[i+2];
            // Non-linear sigmoid curve preserving thin dark/grey lines
            let v = gray > (185 - thresholdOffset) ? 255 : (gray < (70 - thresholdOffset) ? 0 : ((gray - (70 - thresholdOffset)) / 115) * 255);
            d[i] = v; d[i+1] = v; d[i+2] = v;
        }
    } else if (currentFilter === 'crystal_color') {
        for (let i = 0; i < d.length; i += 4) {
            d[i]   = Math.min(255, (d[i] - 128) * 1.35 + 128 + 20);
            d[i+1] = Math.min(255, (d[i+1] - 128) * 1.35 + 128 + 20);
            d[i+2] = Math.min(255, (d[i+2] - 128) * 1.35 + 128 + 20);
        }
    } else if (currentFilter === 'sharp_gray') {
        for (let i = 0; i < d.length; i += 4) {
            let gray = 0.299 * d[i] + 0.587 * d[i+1] + 0.114 * d[i+2];
            let v = Math.min(255, Math.max(0, (gray - 128) * 1.25 + 128));
            d[i] = v; d[i+1] = v; d[i+2] = v;
        }
    }

    ctx.putImageData(imgData, 0, 0);
}

// 5. Page Queue Management
function confirmCurrentPage() {
    const pageDataUrl = resultCanvas.toDataURL('image/jpeg', 0.92);
    scannedPages.push(pageDataUrl);
    updatePagesUI();
    filterStudio.style.display = 'none';
    emptyView.style.display = 'block';
}

function addPageAndScanAnother() {
    confirmCurrentPage();
    document.getElementById('cameraInput').click();
}

function removePage(index) {
    scannedPages.splice(index, 1);
    updatePagesUI();
}

function updatePagesUI() {
    pageCounter.textContent = `${scannedPages.length} Page${scannedPages.length > 1 ? 's' : ''}`;
    pagesList.innerHTML = '';

    if (scannedPages.length > 0) {
        pagesStrip.style.display = 'block';
        btnSaveUpload.disabled = false;
        uploadStatusText.textContent = `Ready to upload ${scannedPages.length} page(s)`;
        uploadStatusText.style.color = 'var(--success)';

        scannedPages.forEach((dataUrl, idx) => {
            const wrap = document.createElement('div');
            wrap.style.cssText = 'position:relative;width:70px;height:90px;flex-shrink:0;border:1.5px solid var(--border);border-radius:6px;overflow:hidden;background:#fff;';
            wrap.innerHTML = `
                <img src="${dataUrl}" style="width:100%;height:100%;object-fit:cover;">
                <span style="position:absolute;bottom:2px;left:2px;background:rgba(0,0,0,0.7);color:#fff;font-size:10px;padding:1px 5px;border-radius:4px;">P${idx+1}</span>
                <button type="button" onclick="removePage(${idx})" style="position:absolute;top:2px;right:2px;background:var(--danger);color:#fff;border:none;border-radius:50%;width:18px;height:18px;font-size:11px;cursor:pointer;display:flex;align-items:center;justify-content:center;">&times;</button>
            `;
            pagesList.appendChild(wrap);
        });
    } else {
        pagesStrip.style.display = 'none';
        btnSaveUpload.disabled = true;
        uploadStatusText.textContent = 'Take a photo of a document first';
        uploadStatusText.style.color = 'var(--text-muted)';
        resetToEmpty();
    }
}

// 6. Direct FormData Upload (100% Reliable for Mobile Phone)
async function processAndUpload() {
    if (filterStudio.style.display !== 'none') {
        confirmCurrentPage();
    }

    if (scannedPages.length === 0) {
        showAlert('error', 'Please take at least one photo before uploading.');
        return;
    }

    btnSaveUpload.disabled = true;
    btnSaveUpload.innerHTML = '⏳ Compiling Document...';
    uploadProgressBox.style.display = 'block';
    uploadProgressBar.style.width = '25%';
    uploadProgressLabel.textContent = 'Compiling document pages...';

    try {
        const format = document.querySelector('input[name="export_format"]:checked').value;
        const title = document.getElementById('docTitle').value.trim() || `Scan_${Date.now()}`;
        const folderId = document.getElementById('docFolderId').value;
        const description = document.getElementById('docDescription').value;

        let blobToUpload = null;
        let filename = '';

        if (format === 'pdf' && window.jspdf) {
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('p', 'mm', 'a4');

            for (let i = 0; i < scannedPages.length; i++) {
                if (i > 0) pdf.addPage();
                pdf.addImage(scannedPages[i], 'JPEG', 0, 0, 210, 297);
            }
            blobToUpload = pdf.output('blob');
            filename = `${title}.pdf`;
        } else {
            const res = await fetch(scannedPages[0]);
            blobToUpload = await res.blob();
            filename = `${title}.jpg`;
        }

        uploadProgressBar.style.width = '65%';
        uploadProgressLabel.textContent = 'Uploading to cloud storage...';

        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        formData.append('title', title);
        formData.append('folder_id', folderId || '');
        formData.append('description', description || '');
        formData.append('file', blobToUpload, filename);

        const response = await fetch('{{ route("documents.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        uploadProgressBar.style.width = '100%';

        if (response.ok) {
            showAlert('success', `✅ Document "${title}" saved and uploaded to cloud!`);
            uploadProgressLabel.textContent = 'Upload complete!';
            setTimeout(() => {
                window.location.href = '{{ route("documents.index") }}';
            }, 800);
        } else {
            const err = await response.json();
            throw new Error(err.message || 'Upload failed. Please check file size.');
        }

    } catch (err) {
        console.error(err);
        showAlert('error', 'Upload Error: ' + err.message);
        btnSaveUpload.disabled = false;
        btnSaveUpload.innerHTML = '💾 Save & Upload to Cloud';
        uploadProgressBox.style.display = 'none';
    }
}
</script>
@endsection
