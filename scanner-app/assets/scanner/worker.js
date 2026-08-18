/**
 * worker.js — detection + perspective-warp + enhancement pipeline for the
 * document scanner. Pure Canvas API inside a Web Worker; no OpenCV.
 *
 * Imports the shared math/image primitives from utils.js.
 *
 * Message protocol (in):
 *   { id, type:'ping' }
 *   { id, type:'detect', imageBitmap }                         -> corners only
 *   { id, type:'scan',   imageBitmap }                         -> detect + warp
 *   { id, type:'warp',   imageBitmap, corners }                -> warp with given corners
 *   { id, type:'enhance', imageData, preset }                  -> filter an ImageData
 * Message protocol (out):
 *   { id, type:'pong' }
 *   { id, type:'detected', corners|null, stats }               (corners in input coords)
 *   { id, type:'result', corners, imageBitmap, stats }         (scan / warp)
 *   { id, type:'enhanced', imageData }                         (enhance)
 *   { id, type:'error', message }
 */
'use strict';

importScripts('utils.js');

self.onmessage = async (event) => {
  const msg = event.data;
  try {
    if (msg.type === 'ping') {
      return self.postMessage({ id: msg.id, type: 'pong' });
    }

    if (msg.type === 'enhance') {
      const out = enhance(msg.imageData, msg.preset);
      return self.postMessage({ id: msg.id, type: 'enhanced', imageData: out });
    }

    if (msg.type === 'detect') {
      const src = imageDataFromBitmap(msg.imageBitmap);
      const corners = detectDocument(src);
      return self.postMessage({
        id: msg.id, type: 'detected',
        corners: corners,
        stats: { cornersFound: !!corners, timeMs: 0 },
      });
    }

    if (msg.type === 'scan') {
      const src = imageDataFromBitmap(msg.imageBitmap);
      const t0 = performance.now();
      const corners = detectDocument(src);
      if (!corners) {
        return self.postMessage({
          id: msg.id, type: 'result',
          corners: null, imageBitmap: null,
          stats: { cornersFound: false, timeMs: performance.now() - t0 },
        });
      }
      const size = pageSizeFromQuad(corners, 1800);
      const page = new OffscreenCanvas(size.W, size.H);
      const pageCtx = page.getContext('2d');
      const out = warpIntoImageData(src, corners, size.W, size.H);
      pageCtx.putImageData(out, 0, 0);
      const pageBitmap = page.transferToImageBitmap();
      return self.postMessage(
        {
          id: msg.id, type: 'result',
          corners: corners, imageBitmap: pageBitmap,
          stats: { cornersFound: true, timeMs: performance.now() - t0 },
        },
        [pageBitmap]
      );
    }

    if (msg.type === 'warp') {
      const src = imageDataFromBitmap(msg.imageBitmap);
      const corners = msg.corners;
      const size = pageSizeFromQuad(corners, 1800);
      const page = new OffscreenCanvas(size.W, size.H);
      const pageCtx = page.getContext('2d');
      const out = warpIntoImageData(src, corners, size.W, size.H);
      pageCtx.putImageData(out, 0, 0);
      const pageBitmap = page.transferToImageBitmap();
      return self.postMessage(
        { id: msg.id, type: 'result', corners: corners, imageBitmap: pageBitmap, stats: { cornersFound: true } },
        [pageBitmap]
      );
    }
  } catch (err) {
    self.postMessage({ id: msg.id, type: 'error', message: err.message });
  }
};

/* ------------------------------------------------------------------ *
 * Input helpers
 * ------------------------------------------------------------------ */

/** Read a transferred ImageBitmap into an ImageData (closes the bitmap). */
function imageDataFromBitmap(bmp) {
  const c = new OffscreenCanvas(bmp.width, bmp.height);
  const ctx = c.getContext('2d');
  ctx.drawImage(bmp, 0, 0);
  const img = ctx.getImageData(0, 0, c.width, c.height);
  bmp.close();
  return img;
}

/* ------------------------------------------------------------------ *
 * Detection
 * ------------------------------------------------------------------ */

/**
 * Detect the 4 document corners on a potentially textured background.
 *
 * This is the web port of the OSS-DocumentScanner detector
 * (cpp/src/DocumentDetector.cpp). The same ideas are reproduced in plain JS:
 *   1. Downscale so the longest side is <= DETECT_MAX (their resizeThreshold).
 *   2. Multi-threshold edge sweep, strict -> lenient (their Canny loop over
 *      low thresholds 60..10). Strong edges first, weaker edges only as a
 *      fallback for low-contrast paper. Each pass runs morphological close
 *      (their MORPH_CLOSE / dilate) then traces connected edge components
 *      (their findContours) and approximates the largest one to a quad
 *      (their approxPolyDP). Stop early once an "optimal" quad is found
 *      (their expectedOptimalMaxCosine + expectedAreaFactor early exit).
 *   3. Bright-paper candidate: local-mean adaptive threshold isolates the
 *      paper as a bright connected blob (handles shadows + tiled floors),
 *      then hull -> quad (their threshold-based channel pass).
 *   4. Selection via their scoring: `area + weight * (1 - maxCosine)` — prefers
 *      big right-angled quads, with strict-threshold passes weighted higher.
 *      Rectangle-angle gate (expectedMaxCosine=0.4) and near-border rejection
 *      (their borderSize) filter frame-hugging background quads.
 *   5. Shi-Tomasi refine + angular corner ordering on the downscaled
 *      gradients, then scale back to the ORIGINAL input-image coordinates.
 *
 * @param {ImageData} img
 * @returns {number[4][]|null} [TL,TR,BR,BL] or null
 */
function detectDocument(img) {
  const fullW = img.width, fullH = img.height;
  if (fullW < 40 || fullH < 40) return null;

  // --- downscale ---
  const scale = Math.min(1, DETECT_MAX / Math.max(fullW, fullH));
  const w = Math.max(32, Math.round(fullW * scale));
  const h = Math.max(32, Math.round(fullH * scale));
  const small = new OffscreenCanvas(w, h);
  const sctx = small.getContext('2d', { willReadFrequently: true });
  sctx.drawImage(createCanvasFromImageData(img), 0, 0, w, h);
  const sImg = sctx.getImageData(0, 0, w, h);

  // --- shared luminance / gradients ---
  const lum = toLuminance(sImg.data, w, h);
  boxBlur(lum, w, h, 2); // suppress floor-tile texture (their median blur)
  const { mag, gx, gy } = sobel(lum, w, h);
  const nms = cannyNms(mag, gx, gy, w, h); // thin edges, kill background noise

  // --- edge sweep (strict -> lenient), like their Canny threshold loop ---
  const candidates = [];
  let optimalFound = false;
  for (let i = 0; i < EDGE_SWEEP.length; i++) {
    if (optimalFound) break;
    const quad = contourQuadFromMag(lum, nms, w, h, EDGE_SWEEP[i]);
    if (!quad) continue;
    candidates.push({ quad });
    // Early exit once a clean, reasonably large quad is found.
    if (quadMaxCosine(quad) < EDGE_OPTIMAL_MAX_COS &&
        quadArea(quad) / (w * h) > EDGE_OPTIMAL_AREA) optimalFound = true;
  }

  // --- bright-paper candidate (their threshold-based channel pass) ---
  const brights = brightPaperCandidate(lum, mag, w, h);
  for (const q of brights) candidates.push({ quad: q });

  if (!candidates.length) return null;

  // --- score = area + weight*(1-maxCosine); strict passes weigh more ---
  let best = null, bestScore = -Infinity;
  for (let i = 0; i < candidates.length; i++) {
    const quad = candidates[i].quad;
    const maxCos = quadMaxCosine(quad);
    if (maxCos > EDGE_MAX_COS) continue;        // rectangle-angle gate
    if (nearBorder(quad, w, h, MARGIN)) continue; // frame-hugging background
    const weight = i < EDGE_SWEEP.length ? EDGE_WEIGHT0 - i : EDGE_WEIGHT0 / 2;
    const score = quadArea(quad) + weight * (1 - maxCos);
    if (score > bestScore) { bestScore = score; best = { quad }; }
  }
  if (!best) return null;

  // --- refine to exact pixel corners on the downscaled gradients ---
  const refined = refineCorners(gx, gy, w, h, best.quad, 16, 2);
  const ordered = orderCorners(refined);

  // --- scale back to full resolution ---
  const inv = 1 / scale;
  return ordered.map(([x, y]) => [clamp(Math.round(x * inv), 0, fullW - 1), clamp(Math.round(y * inv), 0, fullH - 1)]);
}

/** Downscale target for detection (their resizeThreshold). */
const DETECT_MAX = 1000;
/** Canny-equivalent magnitude sweep: strict (clean) edges first, lenient last. */
const EDGE_SWEEP = [150, 110, 75, 45];
/** their expectedOptimalMaxCosine: a quad this square-ish triggers early exit. */
const EDGE_OPTIMAL_MAX_COS = 0.30;
/** their expectedAreaFactor: early-exit quads must cover >= 20% of the frame. */
const EDGE_OPTIMAL_AREA = 0.20;
/** their expectedMaxCosine (0.4) + a little slack for perspective. */
const EDGE_MAX_COS = 0.45;
/** their findSquares area weight: right angles outweigh raw area. */
const EDGE_WEIGHT0 = 3000000;
/** their borderSize: quads touching the frame are background, not paper. */
const MARGIN = 6;

/**
 * Largest |cos| of the four interior angles of a quad. 0 means perfect right
 * angles; near 1 means a sliver / collapsed shape.
 */
function quadMaxCosine(quad) {
  let mx = 0;
  for (let i = 0; i < 4; i++) {
    const a = quad[i], b = quad[(i + 1) % 4], c = quad[(i + 2) % 4];
    const ux = a.x - b.x, uy = a.y - b.y;
    const vx = c.x - b.x, vy = c.y - b.y;
    const den = (Math.sqrt(ux * ux + uy * uy) * Math.sqrt(vx * vx + vy * vy)) || 1;
    const ac = Math.abs((ux * vx + uy * vy) / den);
    if (ac > mx) mx = ac;
  }
  return mx;
}

/** True if any corner lies within `m` px of the image border. */
function nearBorder(quad, w, h, m) {
  for (const p of quad) {
    if (p.x < m || p.y < m || p.x >= w - m || p.y >= h - m) return true;
  }
  return false;
}

/**
 * Quad from a single edge-magnitude threshold, emulating one Canny pass +
 * findContours + approxPolyDP:
 *   1. threshold the Sobel magnitude,
 *   2. morphological close (dilate + erode) to bridge gaps in the paper edge,
 *   3. label connected components; ignore any hugging >= 3 image borders,
 *   4. take the largest remaining component, hull it and approximate a quad.
 * This is why a tiled floor with grout lines does NOT defeat it: grout edges
 * are separate small components; only the closed paper outline survives.
 * @returns {Array<{x,y}>|null}
 */
function contourQuadFromMag(lum, mag, w, h, threshold) {
  const n = w * h;
  const mask = new Uint8Array(n);
  for (let i = 0; i < n; i++) mask[i] = mag[i] > threshold ? 1 : 0;
  morphClose(mask, w, h, 1);

  const comps = connectedComponents(mask, w, h);
  let bestQ = null;
  for (const comp of comps) {
    if (comp.count < n * 0.004) break; // sorted by count desc
    const hits = (comp.minX === 0 ? 1 : 0) + (comp.maxX === w - 1 ? 1 : 0) +
                 (comp.minY === 0 ? 1 : 0) + (comp.maxY === h - 1 ? 1 : 0);
    if (hits >= 3) continue; // the background itself
    const pixels = floodFillPixels(mask, w, h, comp.id);
    const hull = convexHull(pixels);
    const quad = approxQuad(hull);
    if (!validateAspect(quad, w, h)) continue;
    if (!bestQ) bestQ = quad;
    break; // first valid (largest) component is the best at this threshold
  }
  return bestQ;
}

/**
 * Canny non-maximum suppression: keep only local maxima of the gradient
 * magnitude along the gradient direction. Thins edges to single pixels and
 * eliminates the diffuse low-magnitude noise of textured backgrounds (floor
 * tiles, wood grain), which is what lets strict thresholds isolate the paper
 * outline (their Canny(t, 2t)).
 * @returns {Float32Array} NMS'd magnitudes (0 where suppressed)
 */
function cannyNms(mag, gx, gy, w, h) {
  const nms = new Float32Array(w * h);
  for (let y = 1; y < h - 1; y++) {
    for (let x = 1; x < w - 1; x++) {
      const i = y * w + x;
      const m = mag[i];
      const ax = Math.abs(gx[i]), ay = Math.abs(gy[i]);
      let m1, m2;
      if (ax > ay) {
        if (gx[i] > 0) { m1 = mag[i + 1]; m2 = mag[i - 1]; }
        else { m1 = mag[i - 1]; m2 = mag[i + 1]; }
      } else {
        if (gy[i] > 0) { m1 = mag[i + w]; m2 = mag[i - w]; }
        else { m1 = mag[i - w]; m2 = mag[i + w]; }
      }
      if (m >= m1 && m >= m2) nms[i] = m;
    }
  }
  return nms;
}

/**
 * Morphological close: dilate 3x3 `iter` times then erode 3x3. Bridges small
 * gaps in edge pixels (their morphologyAnchorSize=4 / dilateAnchorSize=3)
 * so a slightly broken paper outline still forms one connected component.
 */
function morphClose(mask, w, h, iter) {
  const n = w * h;
  let tmp = new Uint8Array(n);
  let src = mask;
  for (let it = 0; it < iter; it++) {
    for (let y = 0; y < h; y++) {
      const ym = y > 0 ? -w : 0, yp = y < h - 1 ? w : 0;
      for (let x = 0; x < w; x++) {
        const i = y * w + x;
        const xm = x > 0 ? -1 : 0, xp = x < w - 1 ? 1 : 0;
        tmp[i] = (src[i + ym + xm] || src[i + ym] || src[i + ym + xp] ||
                  src[i + xm] || src[i] || src[i + xp] ||
                  src[i + yp + xm] || src[i + yp] || src[i + yp + xp]) ? 1 : 0;
      }
    }
    const t = src; src = tmp; tmp = t; // dilate again on the result
  }
  // erode: keep pixels whose full 3x3 neighborhood survived the dilation
  for (let y = 1; y < h - 1; y++) {
    for (let x = 1; x < w - 1; x++) {
      const i = y * w + x;
      tmp[i] = (src[i - w - 1] && src[i - w] && src[i - w + 1] &&
                src[i - 1] && src[i] && src[i + 1] &&
                src[i + w - 1] && src[i + w] && src[i + w + 1]) ? 1 : 0;
    }
  }
  for (let x = 0; x < w; x++) { tmp[x] = 0; tmp[(h - 1) * w + x] = 0; }
  for (let y = 0; y < h; y++) { tmp[y * w] = 0; tmp[y * w + w - 1] = 0; }
  mask.set(tmp);
}

/** Build an OffscreenCanvas from an ImageData (for resizing). */
function createCanvasFromImageData(img) {
  const c = new OffscreenCanvas(img.width, img.height);
  c.getContext('2d').putImageData(img, 0, 0);
  return c;
}

/** Area of a quad (shoelace), in the given pixel space. */
function quadArea(quad) {
  let area = 0;
  for (let i = 0; i < 4; i++) {
    const [x1, y1] = [quad[i].x, quad[i].y];
    const [x2, y2] = [quad[(i + 1) % 4].x, quad[(i + 1) % 4].y];
    area += x1 * y2 - x2 * y1;
  }
  return Math.abs(area) / 2;
}

/**
 * Candidate A: bright paper blob(s) = document.
 * Adaptive threshold "brighter than local surroundings" (integral image) marks
 * the paper as a bright ring around the page boundary. The ring can split into
 * separate components where ink rows touch the paper edges, so we union the
 * largest components incrementally and take the hull of every prefix — each
 * prefix that yields a valid quad is returned as a candidate. The convex hull
 * bridges the gaps, so a split ring still produces the full page quad.
 * @param {Float32Array} lum
 * @param {Float32Array} mag (unused, kept for symmetry)
 * @returns {Array<Array<{x,y}>>} list of candidate quads
 */
function brightPaperCandidate(lum, mag, w, h) {
  const sum = integralImage(lum, w, h);
  const radius = Math.max(8, Math.round(Math.max(w, h) * 0.08));
  const mean = localMean(lum, w, h, sum, radius);

  const mask = new Uint8Array(w * h);
  for (let i = 0; i < mask.length; i++) {
    mask[i] = lum[i] > mean[i] + 10 ? 1 : 0;
  }

  const minCount = w * h * 0.01;
  const comps = connectedComponents(mask, w, h).filter((c) => c.count >= minCount).slice(0, 6);
  const quads = [];
  let unionPixels = [];
  for (const comp of comps) {
    // Reject components hugging >= 3 image borders (the background itself).
    const hits = (comp.minX === 0 ? 1 : 0) + (comp.maxX === w - 1 ? 1 : 0) +
                 (comp.minY === 0 ? 1 : 0) + (comp.maxY === h - 1 ? 1 : 0);
    if (hits >= 3) continue;
    unionPixels = unionPixels.concat(floodFillPixels(mask, w, h, comp.id));
    const hull = convexHull(unionPixels);
    const quad = approxQuad(hull);
    if (validateAspect(quad, w, h)) quads.push(quad);
  }
  return quads;
}

/* ------------------------------------------------------------------ *
 * Enhancement presets (Arabic-text friendly)
 * ------------------------------------------------------------------ */

/**
 * Apply an enhancement preset to an ImageData (mutates it).
 *
 * For Arabic documents the 'clean' preset is recommended: it removes uneven
 * lighting / shadows while KEEPING grayscale anti-aliasing, which preserves
 * thin strokes and the diacritic dots. 'bw' additionally applies Otsu for a
 * hard black-on-white scan.
 *
 * @param {ImageData} img
 * @param {'original'|'gray'|'contrast'|'clean'|'color'|'bw'} preset
 * @returns {ImageData} same object (mutated)
 */
function enhance(img, preset) {
  const d = img.data;
  const w = img.width, h = img.height;

  if (preset === 'original') return img;

  if (preset === 'gray') {
    for (let i = 0; i < d.length; i += 4) {
      const g = 0.299 * d[i] + 0.587 * d[i + 1] + 0.114 * d[i + 2];
      d[i] = d[i + 1] = d[i + 2] = g;
    }
    return img;
  }

  if (preset === 'contrast') {
    const lum = toLuminance(d, w, h);
    const { lo, hi } = percentileBounds(lum, 0.02, 0.98);
    const range = Math.max(1, hi - lo);
    for (let i = 0; i < d.length; i += 4) {
      d[i] = (d[i] - lo) / range * 255;
      d[i + 1] = (d[i + 1] - lo) / range * 255;
      d[i + 2] = (d[i + 2] - lo) / range * 255;
    }
    return img;
  }

  if (preset === 'clean') {
    // White-paper pipeline (web port of WhitePaperTransform.cpp):
    //   1. DoG high-pass: wide box blur as the illumination model, then
    //      v = 255 + (lum - wide) * gain. Flat paper (including smooth
    //      shadows/gradients) maps to exactly 255, ink is pushed to 0.
    const lum = toLuminance(d, w, h);
    const wide = lum.slice();
    const rW = Math.max(16, Math.round(Math.max(w, h) * 0.12)); // their dogSigma1
    boxBlur(wide, w, h, rW);
    const gain = 1.6;
    for (let i = 0; i < lum.length; i++) {
      const v = clamp(255 + (lum[i] - wide[i]) * gain, 0, 255);
      const o = i * 4;
      d[o] = d[o + 1] = d[o + 2] = v;
    }

    //   2. Safe percentile stretch (their contrastStretch 2%/99.5%) that can
    //      never turn the paper cluster black: the black point is floored at
    //      hi - 48, so sparse-ink pages (paper pinned at 255) still map to
    //      white while faint gray copies get a real contrast boost.
    const cs = percentileBoundsChannel(d, w, h, 0.02, 0.995);
    for (let i = 0; i < d.length; i += 4) {
      for (let ch = 0; ch < 3; ch++) {
        const hi = cs.hi[ch];
        const lo = Math.min(cs.lo[ch], hi - 48);
        const scale = 255 / Math.max(hi - lo, 1);
        d[i + ch] = clamp((d[i + ch] - lo) * scale, 0, 255);
      }
    }

    //   3. Light unsharp mask to crisp Arabic strokes/dots without halos.
    const lum2 = toLuminance(d, w, h);
    const blurred = lum2.slice();
    boxBlur(blurred, w, h, 2);
    const amount = 0.45;
    for (let i = 0; i < lum2.length; i++) {
      const g = clamp(lum2[i] + amount * (lum2[i] - blurred[i]), 0, 255);
      const o = i * 4;
      d[o] = d[o + 1] = d[o + 2] = g;
    }

    //   4. Gentle gamma (their gammaValue=1.1) to lift midtones.
    const gammaInv = 1 / 1.1;
    for (let i = 0; i < d.length; i += 4) {
      for (let ch = 0; ch < 3; ch++) {
        d[i + ch] = clamp(Math.pow(d[i + ch] / 255, gammaInv) * 255, 0, 255);
      }
    }
    return img;
  }

  if (preset === 'color') {
    // SimpleScanner-style color preservation: whiten the paper first, then
    // restore only clearly saturated ink/stamps/photos from the original.
    const original = new Uint8ClampedArray(d);
    enhance(img, 'clean');
    for (let i = 0; i < d.length; i += 4) {
      const r = original[i], g = original[i + 1], b = original[i + 2];
      const hi = Math.max(r, g, b), lo = Math.min(r, g, b);
      if (hi - lo >= 45 && hi >= 50 && hi <= 245) {
        d[i] = r; d[i + 1] = g; d[i + 2] = b;
      }
    }
    return img;
  }

  if (preset === 'bw') {
    enhance(img, 'clean');
    const lum = toLuminance(d, w, h);
    const t = otsuThreshold(lum);
    for (let i = 0, p = 0; i < d.length; i += 4, p++) {
      const v = lum[p] >= t ? 255 : 0;
      d[i] = d[i + 1] = d[i + 2] = v;
    }
    return img;
  }

  return img;
}

/** Low/high percentile of a luminance plane. */
function percentileBounds(lum, plo, phi) {
  const n = lum.length;
  const step = Math.max(1, Math.floor(n / 20000)); // bounded sampling for speed
  const samples = new Float32Array(Math.ceil(n / step));
  for (let i = 0, p = 0; i < n; i += step, p++) samples[p] = lum[i];
  samples.sort();
  return {
    lo: samples[clamp(Math.floor(samples.length * plo), 0, samples.length - 1)],
    hi: samples[clamp(Math.floor(samples.length * phi), 0, samples.length - 1)],
  };
}

/** Per-channel low/high percentiles of RGBA data (their contrastStretch). */
function percentileBoundsChannel(data, w, h, plo, phi) {
  const n = data.length / 4;
  const step = Math.max(1, Math.floor(n / 20000));
  const count = Math.ceil(n / step);
  const ch = [
    new Float32Array(count),
    new Float32Array(count),
    new Float32Array(count),
  ];
  for (let p = 0, o = 0; o < data.length; o += step * 4, p++) {
    ch[0][p] = data[o];
    ch[1][p] = data[o + 1];
    ch[2][p] = data[o + 2];
  }
  const lo = [], hi = [];
  for (let c = 0; c < 3; c++) {
    ch[c].sort();
    lo.push(ch[c][clamp(Math.floor(count * plo), 0, count - 1)]);
    hi.push(ch[c][clamp(Math.floor(count * phi), 0, count - 1)]);
  }
  return { lo, hi };
}
