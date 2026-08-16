/**
 * pipeline-worker.js — DocuScan document scanning pipeline.
 * Pure Canvas API. No OpenCV. No external libraries.
 *
 * Messages (in):
 *   { id, type:'ping' }
 *   { id, type:'scan',    imageBitmap }        -> detect corners + warp (clean page)
 *   { id, type:'enhance', imageData, preset }  -> filter an ImageData
 *   { id, type:'analyze', imageData }          -> edge stats only
 * Messages (out):
 *   { id, type:'pong' }
 *   { id, type:'result', corners, imageBitmap, stats }   (scan)
 *   { id, type:'enhanced', imageData }                    (enhance)
 *   { id, type:'analyzed', stats }                        (analyze)
 *   { id, type:'error', message }
 */

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

    if (msg.type === 'analyze') {
      const stats = analyze(msg.imageData);
      return self.postMessage({ id: msg.id, type: 'analyzed', stats: stats });
    }

    if (msg.type === 'warp') {
      const src = new OffscreenCanvas(msg.imageBitmap.width, msg.imageBitmap.height);
      const srcCtx = src.getContext('2d');
      srcCtx.drawImage(msg.imageBitmap, 0, 0);
      msg.imageBitmap.close();

      const imageData = srcCtx.getImageData(0, 0, src.width, src.height);
      const corners = msg.corners;
      const size = pageSizeFromQuad(corners);
      const page = new OffscreenCanvas(size.W, size.H);
      const pageCtx = page.getContext('2d');
      warp(imageData, corners, pageCtx, size.W, size.H);

      const pageBitmap = page.transferToImageBitmap();
      return self.postMessage(
        { id: msg.id, type: 'result', corners: corners, imageBitmap: pageBitmap, stats: { cornersFound: true } },
        [pageBitmap]
      );
    }

    if (msg.type === 'scan') {
      const src = new OffscreenCanvas(msg.imageBitmap.width, msg.imageBitmap.height);
      const srcCtx = src.getContext('2d');
      srcCtx.drawImage(msg.imageBitmap, 0, 0);
      msg.imageBitmap.close();

      const imageData = srcCtx.getImageData(0, 0, src.width, src.height);
      const corners = detectDocument(imageData);

      if (!corners) {
        return self.postMessage({
          id: msg.id, type: 'result',
          corners: null, imageBitmap: null,
          stats: { cornersFound: false },
        });
      }

      const size = pageSizeFromQuad(corners);
      const page = new OffscreenCanvas(size.W, size.H);
      const pageCtx = page.getContext('2d');
      warp(imageData, corners, pageCtx, size.W, size.H);

      const pageBitmap = page.transferToImageBitmap();
      self.postMessage(
        { id: msg.id, type: 'result', corners: corners, imageBitmap: pageBitmap, stats: { cornersFound: true } },
        [pageBitmap]
      );
    }
  } catch (err) {
    self.postMessage({ id: msg.id, type: 'error', message: err.message });
  }
};

/** Convert an ImageData to a transferable ImageBitmap. */
function toBitmap(imageData) {
  const c = new OffscreenCanvas(imageData.width, imageData.height);
  c.getContext('2d').putImageData(imageData, 0, 0);
  return c.transferToImageBitmap();
}

/**
 * Analyze edge coverage + sharpness of an ImageData.
 * @param {ImageData} img
 * @returns {{coveragePct:number, qualityPct:number}}
 */
function analyze(img) {
  const { width, height } = img;
  const lum = grayscale(img);
  const { mag } = sobel(lum, width, height);
  let strong = 0, sum = 0;
  for (let i = 0; i < mag.length; i++) {
    sum += mag[i];
    if (mag[i] > 60) strong++;
  }
  const total = width * height;
  return {
    coveragePct: +((strong / total) * 100).toFixed(1),
    qualityPct: Math.min(100, Math.round((sum / total / 120) * 100)),
  };
}

/**
 * Convert RGBA to a luminance plane (Rec.601 weights).
 * @param {ImageData} img
 * @returns {Float32Array} width*height values 0..255
 */
function grayscale(img) {
  const { data, width, height } = img;
  const lum = new Float32Array(width * height);
  for (let i = 0, p = 0; i < data.length; i += 4, p++) {
    lum[p] = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
  }
  return lum;
}

/**
 * Sobel gradients (border-safe). Returns magnitude plus gx/gy so corners can
 * be refined with a Harris-style response.
 * @param {Float32Array} lum
 * @param {number} width
 * @param {number} height
 * @returns {{mag:Float32Array, gx:Float32Array, gy:Float32Array}}
 */
function sobel(lum, width, height) {
  const mag = new Float32Array(width * height);
  const gx = new Float32Array(width * height);
  const gy = new Float32Array(width * height);
  for (let y = 1; y < height - 1; y++) {
    for (let x = 1; x < width - 1; x++) {
      const i = y * width + x;
      const sx =
        -lum[i - width - 1] + lum[i - width + 1] -
         2 * lum[i - 1] + 2 * lum[i + 1] -
        lum[i + width - 1] + lum[i + width + 1];
      const sy =
        -lum[i - width - 1] - 2 * lum[i - width] - lum[i - width + 1] +
        lum[i + width - 1] + 2 * lum[i + width] + lum[i + width + 1];
      gx[i] = sx;
      gy[i] = sy;
      mag[i] = Math.hypot(sx, sy);
    }
  }
  return { mag, gx, gy };
}

/**
 * Detect the 4 document corners on a contrasting background.
 * Edges -> sample -> convex hull -> simplify to quad -> validate aspect.
 * @param {ImageData} img
 * @returns {number[4][]|null} corners [TL,TR,BR,BL] or null
 */
function detectDocument(img) {
  const { width, height } = img;
  const lum = grayscale(img);
  const { mag, gx, gy } = sobel(lum, width, height);

  const points = [];
  const stride = 4, threshold = 48;
  for (let y = 0; y < height; y += stride) {
    for (let x = 0; x < width; x += stride) {
      if (mag[y * width + x] > threshold) points.push({ x, y });
    }
  }
  if (points.length < 8) return null;

  const hull = convexHull(points);
  const quad = approxQuad(hull);
  if (!validateAspect(quad, width, height)) return null;

  // Refine each corner to the exact pixel (Harris-style response).
  const refined = refineCorners(gx, gy, width, height, quad);

  return orderCorners(refined);
}

/**
 * Snap each approximate corner to the strongest corner response within a
 * small search window. Uses the Shi-Tomasi score (min eigenvalue of the
 * structure tensor): unlike |gx|*|gy| it stays ~0 along straight edges and
 * peaks only where two edges meet, so it snaps to the true corner even on
 * diagonal documents.
 * @param {Float32Array} gx
 * @param {Float32Array} gy
 * @param {number} width
 * @param {number} height
 * @param {Array<{x:number,y:number}>} corners
 * @returns {Array<{x:number,y:number}>}
 */
function refineCorners(gx, gy, width, height, corners, win = 16) {
  const N = 2; // structure-tensor neighborhood half-size (5x5)
  return corners.map((c) => {
    const cx = Math.round(c.x), cy = Math.round(c.y);
    let bx = cx, by = cy, best = -1;
    const y0 = Math.max(N, cy - win), y1 = Math.min(height - N - 1, cy + win);
    const x0 = Math.max(N, cx - win), x1 = Math.min(width - N - 1, cx + win);
    for (let y = y0; y <= y1; y++) {
      for (let x = x0; x <= x1; x++) {
        let a = 0, b = 0, c = 0;
        for (let yy = y - N; yy <= y + N; yy++) {
          for (let xx = x - N; xx <= x + N; xx++) {
            const i = yy * width + xx;
            const gxx = gx[i], gyy = gy[i];
            a += gxx * gxx;
            b += gxx * gyy;
            c += gyy * gyy;
          }
        }
        // Shi-Tomasi: min eigenvalue of [[a,b],[b,c]]
        const disc = Math.sqrt(((a - c) / 2) ** 2 + b * b);
        const resp = (a + c) / 2 - disc;
        if (resp > best) { best = resp; bx = x; by = y; }
      }
    }
    return { x: bx, y: by };
  });
}

/** Andrew's monotone-chain convex hull. */
function convexHull(pts) {
  const p = pts.slice().sort((a, b) => a.x - b.x || a.y - b.y);
  const cross = (o, a, b) => (a.x - o.x) * (b.y - o.y) - (a.y - o.y) * (b.x - o.x);
  const lower = [], upper = [];
  for (const pt of p) {
    while (lower.length >= 2 && cross(lower[lower.length - 2], lower[lower.length - 1], pt) <= 0) lower.pop();
    lower.push(pt);
  }
  for (let i = p.length - 1; i >= 0; i--) {
    const pt = p[i];
    while (upper.length >= 2 && cross(upper[upper.length - 2], upper[upper.length - 1], pt) <= 0) upper.pop();
    upper.push(pt);
  }
  lower.pop(); upper.pop();
  return lower.concat(upper);
}

/**
 * Approximate the convex hull to a quadrilateral using the farthest-point
 * technique (robust for near-rectangular documents):
 *   1. The two hull points farthest apart are two opposite corners.
 *   2. The point farthest from their chord on each connecting chain is
 *      one of the other two corners.
 * Falls back to the bounding box for degenerate hulls.
 * @param {Array<{x:number,y:number}>} hull
 * @returns {Array<{x:number,y:number}>} 4 corners in hull order
 */
function approxQuad(hull) {
  const n = hull.length;
  if (n <= 4) return hull;

  // 1. Opposite corners = farthest pair (squared distance).
  let best = -1, A = 0, B = 0;
  for (let i = 0; i < n; i++) {
    for (let j = i + 1; j < n; j++) {
      const d = (hull[i].x - hull[j].x) ** 2 + (hull[i].y - hull[j].y) ** 2;
      if (d > best) { best = d; A = i; B = j; }
    }
  }

  const lineDist = (k) => {
    const ax = hull[A].x, ay = hull[A].y, bx = hull[B].x, by = hull[B].y;
    return Math.abs((bx - ax) * (hull[k].y - ay) - (by - ay) * (hull[k].x - ax)) /
           (Math.hypot(bx - ax, by - ay) || 1);
  };

  // 2a. Farthest from chord AB along chain A -> B.
  let cIdx = A, dC = -1;
  for (let k = (A + 1) % n; k !== B; k = (k + 1) % n) {
    const d = lineDist(k);
    if (d > dC) { dC = d; cIdx = k; }
  }
  // 2b. Farthest from chord AB along chain B -> A.
  let dIdx = B, dD = -1;
  for (let k = (B + 1) % n; k !== A; k = (k + 1) % n) {
    const d = lineDist(k);
    if (d > dD) { dD = d; dIdx = k; }
  }

  if (dC <= 0 || dD <= 0) {
    // Degenerate hull (A and B adjacent): use bounding box corners.
    const xs = hull.map((p) => p.x), ys = hull.map((p) => p.y);
    return [
      { x: Math.min(...xs), y: Math.min(...ys) },
      { x: Math.max(...xs), y: Math.min(...ys) },
      { x: Math.max(...xs), y: Math.max(...ys) },
      { x: Math.min(...xs), y: Math.max(...ys) },
    ];
  }

  return [hull[A], hull[cIdx], hull[B], hull[dIdx]];
}

/**
 * Aspect-ratio guard. Rejects slivers/diagonals from bad lighting.
 * @returns {boolean}
 */
function validateAspect(quad, W, H) {
  const s = (a, b) => Math.hypot(b.x - a.x, b.y - a.y);
  const sides = [s(quad[0], quad[1]), s(quad[1], quad[2]), s(quad[2], quad[3]), s(quad[3], quad[0])];
  const long = Math.max(...sides), short = Math.min(...sides);
  if (short === 0) return false;
  const ratio = long / short;
  return ratio > 0.9 && ratio < 9 && short > Math.min(W, H) * 0.25;
}

/** Order corners as TL, TR, BR, BL by angle around centroid. */
function orderCorners(quad) {
  const cx = quad.reduce((s, p) => s + p.x, 0) / 4;
  const cy = quad.reduce((s, p) => s + p.y, 0) / 4;
  return quad
    .map((p) => ({ p, a: Math.atan2(p.y - cy, p.x - cx) }))
    .sort((x, y) => x.a - y.a)
    .map((o) => o.p)
    .map((p) => [p.x, p.y]);
}

/** Compute a page canvas size from the detected quad, capped at 1800px. */
function pageSizeFromQuad(corners) {
  const [tl, tr, br, bl] = corners;
  const top = Math.hypot(tr[0] - tl[0], tr[1] - tl[1]);
  const bottom = Math.hypot(br[0] - bl[0], br[1] - bl[1]);
  const left = Math.hypot(bl[0] - tl[0], bl[1] - tl[1]);
  const right = Math.hypot(br[0] - tr[0], br[1] - tr[1]);
  let W = (top + bottom) / 2;
  let H = (left + right) / 2;
  const scale = Math.min(1, 1800 / Math.max(W, H, 1));
  return { W: Math.max(2, Math.round(W * scale)), H: Math.max(2, Math.round(H * scale)) };
}

/**
 * Solve the 3x3 homography H mapping src -> dst (4 correspondences)
 * via an 8x8 linear system + Gaussian elimination.
 */
function solveHomography(src, dst) {
  const A = [], b = [];
  for (let i = 0; i < 4; i++) {
    const [x, y] = src[i], [u, v] = dst[i];
    A.push([x, y, 1, 0, 0, 0, -u * x, -u * y]); b.push(u);
    A.push([0, 0, 0, x, y, 1, -v * x, -v * y]); b.push(v);
  }
  const h = gauss(A, b);
  return [h[0], h[1], h[2], h[3], h[4], h[5], h[6], h[7], 1];
}

/** Gaussian elimination with partial pivoting (8x8). */
function gauss(A, b) {
  const n = 8;
  for (let col = 0; col < n; col++) {
    let pivot = col;
    for (let r = col + 1; r < n; r++) if (Math.abs(A[r][col]) > Math.abs(A[pivot][col])) pivot = r;
    [A[col], A[pivot]] = [A[pivot], A[col]];
    [b[col], b[pivot]] = [b[pivot], b[col]];
    for (let r = col + 1; r < n; r++) {
      const f = A[r][col] / A[col][col];
      for (let c = col; c < n; c++) A[r][c] -= f * A[col][c];
      b[r] -= f * b[col];
    }
  }
  const x = new Array(n);
  for (let r = n - 1; r >= 0; r--) {
    let s = b[r];
    for (let c = r + 1; c < n; c++) s -= A[r][c] * x[c];
    x[r] = s / A[r][r];
  }
  return x;
}

/** Invert a 3x3 matrix (adjugate method). */
function invert3x3(m) {
  const [a, b, c, d, e, f, g, h, i] = m;
  const A = e * i - f * h, B = -(d * i - f * g), C = d * h - e * g;
  const D = -(b * i - c * h), E = a * i - c * g, F = -(a * h - b * g);
  const G = b * f - c * e, H = -(a * f - c * d), I = a * e - b * d;
  const det = a * A + b * B + c * C;
  if (!det) return m;
  return [A / det, D / det, G / det, B / det, E / det, H / det, C / det, F / det, I / det];
}

/**
 * Warp the detected quad into a rectangular page on dstCtx.
 * Inverse mapping + bilinear interpolation (no holes).
 * @param {ImageData} src
 * @param {number[4][]} corners - [TL,TR,BR,BL] in source
 * @param {CanvasRenderingContext2D} dstCtx
 * @param {number} outW
 * @param {number} outH
 */
function warp(src, corners, dstCtx, outW, outH) {
  const dstQuad = [[0, 0], [outW, 0], [outW, outH], [0, outH]];
  const H = solveHomography(corners, dstQuad);
  const Hi = invert3x3(H);

  const out = dstCtx.createImageData(outW, outH);
  const d = out.data, s = src.data;
  const sw = src.width, sh = src.height;

  for (let v = 0; v < outH; v++) {
    for (let u = 0; u < outW; u++) {
      const w = 1 / (Hi[6] * u + Hi[7] * v + Hi[8]);
      const x = (Hi[0] * u + Hi[1] * v + Hi[2]) * w;
      const y = (Hi[3] * u + Hi[4] * v + Hi[5]) * w;
      if (x < 0 || y < 0 || x > sw - 1 || y > sh - 1) continue;

      const x0 = Math.floor(x), y0 = Math.floor(y);
      const dx = x - x0, dy = y - y0;
      const i00 = (y0 * sw + x0) * 4;
      const i10 = i00 + 4, i01 = i00 + sw * 4, i11 = i01 + 4;
      const o = (v * outW + u) * 4;
      for (let ch = 0; ch < 3; ch++) {
        d[o + ch] = (s[i00 + ch] * (1 - dx) * (1 - dy)) +
                    (s[i10 + ch] * dx * (1 - dy)) +
                    (s[i01 + ch] * (1 - dx) * dy) +
                    (s[i11 + ch] * dx * dy);
      }
      d[o + 3] = 255;
    }
  }
  dstCtx.putImageData(out, 0, 0);
}

/**
 * Apply an enhancement preset.
 * @param {ImageData} img
 * @param {'magic'|'enhanced'|'grayscale'|'contrast'|'original'} preset
 * @returns {ImageData}
 */
function enhance(img, preset) {
  const d = img.data;

  if (preset === 'original') return img;

  if (preset === 'grayscale') {
    for (let i = 0; i < d.length; i += 4) {
      const g = 0.299 * d[i] + 0.587 * d[i + 1] + 0.114 * d[i + 2];
      d[i] = d[i + 1] = d[i + 2] = g;
    }
    return img;
  }

  if (preset === 'magic') {
    for (let i = 0; i < d.length; i += 4) {
      const gray = 0.299 * d[i] + 0.587 * d[i + 1] + 0.114 * d[i + 2];
      const v = gray > 140 ? 255 : (gray < 70 ? 0 : (gray - 70) * (255 / 70));
      d[i] = d[i + 1] = d[i + 2] = v;
    }
    return img;
  }

  if (preset === 'enhanced') {
    for (let i = 0; i < d.length; i += 4) {
      d[i]     = Math.min(255, (d[i] - 128) * 1.3 + 128 + 20);
      d[i + 1] = Math.min(255, (d[i + 1] - 128) * 1.3 + 128 + 20);
      d[i + 2] = Math.min(255, (d[i + 2] - 128) * 1.3 + 128 + 20);
    }
    return img;
  }

  if (preset === 'contrast') {
    // Percentile contrast stretch: map [p2,p98] -> [0,255].
    const n = d.length / 4;
    const samples = new Uint8Array(n);
    for (let i = 0, p = 0; i < d.length; i += 4, p++) {
      samples[p] = 0.299 * d[i] + 0.587 * d[i + 1] + 0.114 * d[i + 2];
    }
    const sorted = new Uint8Array(samples).sort();
    const lo = sorted[Math.floor(n * 0.02)];
    const hi = sorted[Math.floor(n * 0.98)];
    const range = Math.max(1, hi - lo);
    for (let i = 0; i < d.length; i += 4) {
      d[i]     = ((d[i] - lo) / range * 255);
      d[i + 1] = ((d[i + 1] - lo) / range * 255);
      d[i + 2] = ((d[i + 2] - lo) / range * 255);
    }
    return img;
  }

  return img;
}
