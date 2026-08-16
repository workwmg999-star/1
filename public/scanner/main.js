/**
 * main.js — UI orchestration for the document scanner.
 *
 * Responsibilities:
 *   - Camera access (getUserMedia) with graceful fallback to a file input
 *     (HTTP-from-phone does not allow the camera API, so we fall back to the
 *     native capture UI).
 *   - Live detection loop (throttled) that renders the dark overlay, green
 *     quadrilateral and four circular draggable handles.
 *   - Touch / mouse dragging of the handles with snap-to-edge assistance.
 *   - Floating action button (pencil) to confirm -> perspective warp in the
 *     worker -> result screen with enhancement presets + PNG download.
 *
 * Geometry model: corners are kept NORMALIZED (0..1) relative to the source
 * frame, so live low-res detection and full-res capture share one coordinate
 * system. Actual pixel corners are derived at capture / warp time.
 */
'use strict';

/* ------------------------------------------------------------------ *
 * State
 * ------------------------------------------------------------------ */
const state = {
  worker: null,
  stream: null,
  rafId: null,

  live: false,                 // camera preview is running
  sourceCanvas: null,          // offscreen full-res copy of the current frame/photo
  sourceW: 0,
  sourceH: 0,

  normCorners: null,           // Array<[nx, ny]> 0..1 — [TL, TR, BR, BL]
  lastLines: null,             // Array<{ax,ay,bx,by}> normalized edge lines (for snapping)
  cornersEdited: false,

  activeHandle: -1,            // index of the handle being dragged
  detecting: false,            // a detection request is in flight
  lastDetectAt: 0,
  seq: 0,                      // request token to drop stale results

  // Auto-capture on stability (OSS AutoScanHandler behavior).
  lastStableCorners: null,     // normalized corners of the previous detection
  stableSince: 0,              // ms timestamp when the corners went stable
  autoCapFired: false,         // one-shot guard so we capture once per hold
};

const SNAP_PX = 16;            // snap radius on the overlay canvas, in px
const HANDLE_R = 18;           // handle radius on the overlay canvas, in px
const LIVE_DETECT_MS = 220;    // throttle between live detections
const STABLE_MS = 900;         // how long the quad must stay still before auto-capture
const STABLE_DISP = 0.02;      // max normalized corner movement to count as "still"

/* ------------------------------------------------------------------ *
 * DOM refs
 * ------------------------------------------------------------------ */
const $ = (id) => document.getElementById(id);
const el = {
  stage: $('stage'),
  cam: $('cam'),
  still: $('still'),
  overlay: $('overlay'),
  empty: $('empty'),
  status: $('status'),
  btnDetect: $('btnDetect'),
  btnFile: $('btnFile'),
  fileInput: $('fileInput'),
  btnRedo: $('btnRedo'),
  fab: $('fab'),
  fabIcon: $('fabIcon'),

  result: $('result'),
  out: $('out'),
  btnBack: $('btnBack'),
  btnGray: $('btnGray'),
  btnClean: $('btnClean'),
  btnBw: $('btnBw'),
  btnDownload: $('btnDownload'),
};

const overlayCtx = el.overlay.getContext('2d');
const stillCtx = el.still.getContext('2d');

/* ------------------------------------------------------------------ *
 * Worker messaging
 * ------------------------------------------------------------------ */
initWorker();

/** Create the worker and expose a promisified send. */
function initWorker() {
  state.worker = new Worker('worker.js');
  const pending = new Map();
  state.worker.onmessage = (e) => {
    const { id, type, message } = e.data;
    const p = pending.get(id);
    if (!p) return;
    pending.delete(id);
    if (type === 'error') p.reject(new Error(message));
    else p.resolve(e.data);
  };
  state.worker.onerror = (e) => {
    for (const p of pending.values()) p.reject(new Error(e.message || 'Worker error'));
    pending.clear();
  };
  state._pending = pending;
}

/**
 * Send a message to the worker and await the response.
 * @param {object} msg message payload (id auto-assigned)
 * @param {Transferable[]} [transfer] objects to transfer
 * @returns {Promise<object>}
 */
function sendToWorker(msg, transfer) {
  return new Promise((resolve, reject) => {
    const id = ++state.seq;
    const payload = Object.assign({ id }, msg);
    state._pending.set(id, { resolve, reject });
    state.worker.postMessage(payload, transfer || []);
  });
}

/* ------------------------------------------------------------------ *
 * Camera / source setup
 * ------------------------------------------------------------------ */
init();

async function init() {
  bindUI();
  try {
    await startCamera();
  } catch (err) {
    console.warn('Camera unavailable:', err);
    // HTTP from a phone, or permission denied -> fall back to capture/file UI.
    enterEmptyMode();
  }
}

/** Request the rear camera and start the live loop. */
async function startCamera() {
  const stream = await navigator.mediaDevices.getUserMedia({
    video: { facingMode: 'environment', width: { ideal: 1920 }, height: { ideal: 1080 } },
    audio: false,
  });
  state.stream = stream;
  state.live = true;
  el.cam.srcObject = stream;
  await el.cam.play();
  el.cam.hidden = false;
  el.still.hidden = true;
  el.empty.hidden = true;
  setStatus('جارِ الكشف…', 'busy');
  liveLoop();
}

/** Stop the camera stream and cancel the live loop. */
function stopCamera() {
  if (state.stream) {
    state.stream.getTracks().forEach((t) => t.stop());
    state.stream = null;
  }
  if (state.rafId) cancelAnimationFrame(state.rafId);
  state.rafId = null;
  state.live = false;
  el.cam.srcObject = null;
  el.cam.hidden = true;
}

/** Show the empty state (camera not usable) and rely on the file input. */
function enterEmptyMode() {
  el.empty.hidden = false;
  setStatus('الكاميرا غير متاحة — اختر صورة', 'warn');
}

/** Capture the current video frame into the offscreen full-res source. */
function freezeFrame() {
  const v = el.cam;
  if (!v.videoWidth) return false;
  const c = document.createElement('canvas');
  c.width = v.videoWidth;
  c.height = v.videoHeight;
  c.getContext('2d').drawImage(v, 0, 0);
  setSource(c);
  return true;
}

/**
 * Set the offscreen full-res source (video frame or uploaded photo) and
 * mirror it on the "still" canvas for the overlay/result flow.
 * @param {HTMLCanvasElement} canvas
 */
function setSource(canvas) {
  state.sourceCanvas = canvas;
  state.sourceW = canvas.width;
  state.sourceH = canvas.height;
  el.still.width = canvas.width;
  el.still.height = canvas.height;
  stillCtx.drawImage(canvas, 0, 0);
  el.still.hidden = false;
  sizeOverlay(canvas.width, canvas.height);
}

/** Size the overlay canvas to the displayed image resolution. */
function sizeOverlay(w, h) {
  el.overlay.width = w;
  el.overlay.height = h;
}

/* ------------------------------------------------------------------ *
 * Live detection loop
 * ------------------------------------------------------------------ */

/** rAF loop: draws nothing itself, but schedules throttled detections. */
function liveLoop() {
  state.rafId = requestAnimationFrame(liveLoop);
  const v = el.cam;
  if (!v.videoWidth || state.detecting) return;
  if (state.cornersEdited) return; // manual mode: stop overwriting the user's corners
  if (performance.now() - state.lastDetectAt < LIVE_DETECT_MS) return;
  if (!state.btnDetect.classList.contains('on')) return;
  state.lastDetectAt = performance.now();
  runDetectFromLive();
}

/**
 * Grab a downscaled frame, ask the worker for corners, normalize the result
 * and repaint the overlay. Low-resolution input keeps this fast enough for
 * "live frame" feedback on a mid-range phone.
 */
async function runDetectFromLive() {
  state.detecting = true;
  const mySeq = state.seq;
  try {
    const v = el.cam;
    const scale = Math.min(1, 720 / Math.max(v.videoWidth, v.videoHeight));
    const w = Math.max(64, Math.round(v.videoWidth * scale));
    const h = Math.max(64, Math.round(v.videoHeight * scale));
    const small = document.createElement('canvas');
    small.width = w; small.height = h;
    small.getContext('2d').drawImage(v, 0, 0, w, h);
    const bmp = await createImageBitmap(small);

    const res = await sendToWorker({ type: 'detect', imageBitmap: bmp }, [bmp]);
    if (mySeq !== state.seq || !state.live) return;

    if (res.corners) {
      applyCorners(res.corners, w, h);
      updateStability(state.normCorners);
      setStatus('✓ الإطار محدد', 'ok');
      el.fab.classList.add('ready');
    } else {
      state.normCorners = null;
      state.lastLines = null;
      resetStability();
      setStatus('لا يوجد إطار — حرّك الورقة', 'warn');
      el.fab.classList.remove('ready');
    }
    drawOverlay();
  } catch (err) {
    console.warn('live detect failed:', err);
  } finally {
    state.detecting = false;
  }
}

/**
 * Store normalized corners + derived edge lines from a detection that ran at
 * (w, h) resolution.
 * @param {number[4][]} corners
 * @param {number} w
 * @param {number} h
 */
function applyCorners(corners, w, h) {
  state.normCorners = corners.map(([x, y]) => [x / w, y / h]);
  state.lastLines = [];
  for (let i = 0; i < 4; i++) {
    const [ax, ay] = state.normCorners[i];
    const [bx, by] = state.normCorners[(i + 1) % 4];
    state.lastLines.push({ ax, ay, bx, by });
  }
}

/* ------------------------------------------------------------------ *
 * Stability auto-capture (OSS AutoScanHandler behavior)
 * ------------------------------------------------------------------ */

/** Forget the stability bookkeeping (camera restarted, no frame, manual edit). */
function resetStability() {
  state.lastStableCorners = null;
  state.stableSince = 0;
}

/**
 * Track how still the detected quad is between consecutive live detections.
 * Once the normalized corners stay within STABLE_DISP of the previous frame
 * for STABLE_MS, auto-capture (the OSS app's "scans when you stop").
 * @param {number[4][]} cornersNorm normalized corners [TL, TR, BR, BL]
 */
function updateStability(cornersNorm) {
  if (!state.live || state.cornersEdited || state.autoCapFired) {
    resetStability();
    return;
  }
  const prev = state.lastStableCorners;
  state.lastStableCorners = cornersNorm;
  if (!prev) return; // first sample — need two frames to compare

  let maxD = 0;
  for (let i = 0; i < 4; i++) {
    maxD = Math.max(maxD, dist(prev[i][0], prev[i][1], cornersNorm[i][0], cornersNorm[i][1]));
  }
  if (maxD > STABLE_DISP) {
    state.stableSince = 0;
    return;
  }
  if (!state.stableSince) state.stableSince = performance.now();
  if (performance.now() - state.stableSince >= STABLE_MS) {
    state.autoCapFired = true;
    resetStability();
    onConfirm();
  }
}

/* ------------------------------------------------------------------ *
 * Overlay rendering (dark outside, green quad, circular handles)
 * ------------------------------------------------------------------ */

/** Repaint the overlay canvas. */
function drawOverlay() {
  const ctx = overlayCtx;
  const W = el.overlay.width, H = el.overlay.height;
  ctx.clearRect(0, 0, W, H);
  const corners = state.normCorners;
  if (!corners) return;

  const c = corners.map(([nx, ny]) => [nx * W, ny * H]);

  // Dark semi-transparent overlay OUTSIDE the quad (even-odd fill).
  ctx.fillStyle = 'rgba(8, 10, 18, 0.55)';
  ctx.beginPath();
  ctx.rect(0, 0, W, H);
  ctx.moveTo(c[0][0], c[0][1]);
  ctx.lineTo(c[1][0], c[1][1]);
  ctx.lineTo(c[2][0], c[2][1]);
  ctx.lineTo(c[3][0], c[3][1]);
  ctx.closePath();
  ctx.fill('evenodd');

  // Green quadrilateral boundary.
  ctx.strokeStyle = '#22c55e';
  ctx.lineWidth = 3;
  ctx.lineJoin = 'round';
  ctx.beginPath();
  ctx.moveTo(c[0][0], c[0][1]);
  for (let i = 1; i < 4; i++) ctx.lineTo(c[i][0], c[i][1]);
  ctx.closePath();
  ctx.stroke();

  // Four circular handles: white fill, green border, dark center dot.
  for (let i = 0; i < 4; i++) {
    const [x, y] = c[i];
    ctx.beginPath();
    ctx.arc(x, y, HANDLE_R, 0, Math.PI * 2);
    ctx.fillStyle = '#ffffff';
    ctx.fill();
    ctx.strokeStyle = i === state.activeHandle ? '#16a34a' : '#22c55e';
    ctx.lineWidth = 3;
    ctx.stroke();
    ctx.beginPath();
    ctx.arc(x, y, 3.5, 0, Math.PI * 2);
    ctx.fillStyle = '#16a34a';
    ctx.fill();
  }

  // Auto-capture progress: translucent fill inside the quad + a ring that
  // fills up as the frame stabilizes.
  if (state.stableSince && state.live && !state.cornersEdited && !state.autoCapFired) {
    const progress = clamp((performance.now() - state.stableSince) / STABLE_MS, 0, 1);
    if (progress > 0) {
      ctx.beginPath();
      ctx.moveTo(c[0][0], c[0][1]);
      for (let i = 1; i < 4; i++) ctx.lineTo(c[i][0], c[i][1]);
      ctx.closePath();
      ctx.fillStyle = 'rgba(34, 197, 94, ' + (0.16 * progress).toFixed(3) + ')';
      ctx.fill();

      const cx = (c[0][0] + c[1][0] + c[2][0] + c[3][0]) / 4;
      const cy = (c[0][1] + c[1][1] + c[2][1] + c[3][1]) / 4;
      const R = 26;
      ctx.beginPath();
      ctx.arc(cx, cy, R, -Math.PI / 2, -Math.PI / 2 + Math.PI * 2 * progress);
      ctx.strokeStyle = '#22c55e';
      ctx.lineWidth = 5;
      ctx.lineCap = 'round';
      ctx.stroke();
    }
  }
}

/* ------------------------------------------------------------------ *
 * Pointer interaction: drag handles with snap-to-edge
 * ------------------------------------------------------------------ */

/** Map a pointer event to overlay-canvas coordinates. @returns {{x:number,y:number}} */
function toOverlayCoords(e) {
  const r = el.overlay.getBoundingClientRect();
  return {
    x: (e.clientX - r.left) * (el.overlay.width / r.width),
    y: (e.clientY - r.top) * (el.overlay.height / r.height),
  };
}

/** Distance from a point to a normalized edge line, in overlay px. */
function distToLinePx(px, py, line, W, H) {
  return pointLineDist(px, py, line.ax * W, line.ay * H, line.bx * W, line.by * H);
}

/**
 * Snap-to-edge: project a dragged corner onto the nearest stored edge line
 * when the pointer is within SNAP_PX of it. Returns the snapped point in
 * overlay px, or the original point.
 */
function snapPoint(px, py, ignoreLine, W, H) {
  let best = null, bestD = SNAP_PX;
  for (const line of state.lastLines || []) {
    if (line === ignoreLine) continue;
    const d = distToLinePx(px, py, line, W, H);
    if (d <= bestD) {
      bestD = d;
      best = projectOnLine(px, py, line.ax * W, line.ay * H, line.bx * W, line.by * H);
    }
  }
  return best || { x: px, y: py };
}

el.overlay.addEventListener('pointerdown', (e) => {
  if (!state.normCorners) return;
  const p = toOverlayCoords(e);
  for (let i = 0; i < 4; i++) {
    const [nx, ny] = state.normCorners[i];
    const cx = nx * el.overlay.width, cy = ny * el.overlay.height;
    if (dist2(p.x, p.y, cx, cy) <= (HANDLE_R + 8) ** 2) {
      state.activeHandle = i;
      el.overlay.setPointerCapture(e.pointerId);
      e.preventDefault();
      drawOverlay();
      return;
    }
  }
});

el.overlay.addEventListener('pointermove', (e) => {
  if (state.activeHandle < 0) return;
  const p = toOverlayCoords(e);
  const W = el.overlay.width, H = el.overlay.height;

  // Snap to the two edges that DO NOT meet at this corner.
  const edgeInto = state.lastLines && state.lastLines[state.activeHandle];
  const edgeOut = state.lastLines && state.lastLines[(state.activeHandle + 3) % 4];
  let snapped = { x: p.x, y: p.y };
  const candidates = [edgeInto, edgeOut].filter(Boolean);
  for (const line of candidates) {
    const proj = snapPoint(p.x, p.y, line, W, H);
    if (proj.x !== p.x || proj.y !== p.y) { snapped = proj; break; }
  }

  state.normCorners[state.activeHandle] = [
    clamp(snapped.x / W, 0, 1),
    clamp(snapped.y / H, 0, 1),
  ];
  state.cornersEdited = true;
  drawOverlay();
});

const endDrag = () => {
  if (state.activeHandle < 0) return;
  state.activeHandle = -1;
  state.cornersEdited = true;
  drawOverlay();
};
el.overlay.addEventListener('pointerup', endDrag);
el.overlay.addEventListener('pointercancel', endDrag);

/* ------------------------------------------------------------------ *
 * FAB: confirm crop -> warp in the worker -> result screen
 * ------------------------------------------------------------------ */
el.fab.addEventListener('click', onConfirm);

/** Confirm: freeze/ensure a source, derive full-res corners, warp. */
async function onConfirm() {
  el.fab.classList.add('busy');
  setStatus('جاري المعالجة…', 'busy');
  try {
    if (state.live) {
      if (!freezeFrame()) throw new Error('No camera frame yet');
      stopCamera();
    }
    if (!state.sourceCanvas) return;

    // Full-res corners from normalized state (or the whole frame as fallback).
    const w = state.sourceW, h = state.sourceH;
    let corners = state.normCorners;
    if (!corners) {
      corners = [[0, 0], [w, 0], [w, h], [0, h]];
    } else {
      corners = corners.map(([nx, ny]) => [nx * w, ny * h]);
    }

    const bmp = await createImageBitmap(state.sourceCanvas);
    const res = await sendToWorker({ type: 'warp', imageBitmap: bmp, corners }, [bmp]);
    if (!res.imageBitmap) throw new Error('Warp produced no image');

    const page = res.imageBitmap;
    el.out.width = page.width;
    el.out.height = page.height;
    el.out.getContext('2d').drawImage(page, 0, 0);
    page.close();

    state.normCorners = null; // allow re-scan on retake
    showResult();
  } catch (err) {
    console.error(err);
    state.autoCapFired = false; // let auto-capture re-arm if capture failed
    setStatus('✗ تعذرت المعالجة: ' + err.message, 'warn');
  } finally {
    el.fab.classList.remove('busy');
  }
}

/** Re-run full-res detection on the frozen source (retake helper). */
async function onRedetect() {
  if (!state.sourceCanvas) return;
  setStatus('جارِ الكشف من جديد…', 'busy');
  const w = state.sourceW, h = state.sourceH;
  const scale = Math.min(1, 1000 / Math.max(w, h));
  const sw = Math.round(w * scale), sh = Math.round(h * scale);
  const small = document.createElement('canvas');
  small.width = sw; small.height = sh;
  small.getContext('2d').drawImage(state.sourceCanvas, 0, 0, sw, sh);
  const bmp = await createImageBitmap(small);
  const res = await sendToWorker({ type: 'detect', imageBitmap: bmp }, [bmp]);
  if (res.corners) {
    applyCorners(res.corners, sw, sh);
    setStatus('✓ الإطار محدد', 'ok');
  } else {
    setStatus('✗ لا يوجد إطار', 'warn');
  }
  drawOverlay();
}

/* ------------------------------------------------------------------ *
 * Result screen + enhancement + download
 * ------------------------------------------------------------------ */

/** Show the result screen. */
function showResult() {
  el.stage.hidden = true;
  el.result.hidden = false;
}

/** Back to the scan stage (re-open camera if possible). */
async function goBack() {
  el.result.hidden = true;
  el.stage.hidden = false;
  state.normCorners = null;
  state.cornersEdited = false;
  state.sourceCanvas = null;
  state.autoCapFired = false; // re-arm auto-capture for the next hold
  resetStability();
  el.still.hidden = true;
  el.fab.classList.remove('ready');
  try {
    await startCamera();
  } catch (err) {
    enterEmptyMode();
  }
}

/**
 * Apply an enhancement preset on the result canvas through the worker.
 * @param {'original'|'gray'|'contrast'|'clean'|'bw'} preset
 */
async function enhanceResult(preset) {
  const c = el.out;
  const ctx = c.getContext('2d');
  const id = ctx.getImageData(0, 0, c.width, c.height);
  try {
    const res = await sendToWorker(
      { type: 'enhance', preset, imageData: { width: id.width, height: id.height, data: id.data } },
      [id.data.buffer]
    );
    const out = new ImageData(res.imageData.data, res.imageData.width, res.imageData.height);
    ctx.putImageData(out, 0, 0);
  } catch (err) {
    console.warn('enhance failed:', err);
  }
}

/** Download the result canvas as a PNG. */
function downloadResult() {
  el.out.toBlob((blob) => {
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'scan-' + Date.now() + '.png';
    a.click();
    setTimeout(() => URL.revokeObjectURL(a.href), 4000);
  }, 'image/png');
}

/* ------------------------------------------------------------------ *
 * File input fallback (camera not available)
 * ------------------------------------------------------------------ */
el.btnFile.addEventListener('click', () => el.fileInput.click());
el.fileInput.addEventListener('change', async (e) => {
  const file = e.target.files && e.target.files[0];
  if (!file) return;
  const url = URL.createObjectURL(file);
  const img = new Image();
  img.onload = async () => {
    const c = document.createElement('canvas');
    c.width = img.naturalWidth;
    c.height = img.naturalHeight;
    c.getContext('2d').drawImage(img, 0, 0);
    URL.revokeObjectURL(url);
    setSource(c);
    el.empty.hidden = true;
    await onRedetect();
  };
  img.src = url;
});

/* ------------------------------------------------------------------ *
 * Small UI helpers
 * ------------------------------------------------------------------ */

/** Status chip text + style. */
function setStatus(text, kind) {
  el.status.textContent = text;
  el.status.className = 'status ' + (kind || '');
}

/** Re-detect toggle + buttons. */
el.btnDetect.addEventListener('click', () => {
  el.btnDetect.classList.toggle('on');
  const on = el.btnDetect.classList.contains('on');
  el.btnDetect.textContent = on ? '● الكشف التلقائي' : '○ الكشف التلقائي';
  if (on) {
    state.cornersEdited = false; // re-arm live detection + auto-capture
    state.lastDetectAt = 0;
  } else {
    resetStability();
  }
});
el.btnRedo.addEventListener('click', onRedetect);
el.btnBack.addEventListener('click', goBack);
el.btnGray.addEventListener('click', () => enhanceResult('gray'));
el.btnClean.addEventListener('click', () => enhanceResult('clean'));
el.btnBw.addEventListener('click', () => enhanceResult('bw'));
el.btnDownload.addEventListener('click', downloadResult);

/** Wire everything (called once at load). */
function bindUI() {
  // All listeners are attached above; keep this as a no-op hook for clarity.
}
