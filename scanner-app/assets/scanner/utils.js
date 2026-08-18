/**
 * utils.js — shared math + image primitives for the browser document scanner.
 *
 * Loaded BOTH by the main thread (classic <script> tag) and by the Web Worker
 * (importScripts('utils.js')). Pure functions only — no DOM access, no globals
 * beyond the exported names, so the same code runs identically in both worlds.
 *
 * Sections:
 *   1. Scalar / vector helpers
 *   2. Grayscale + blur + gradients + integral images
 *   3. Connected components (union-find) for background segmentation
 *   4. Convex hull, quad approximation, aspect validation, corner ordering
 *   5. Shi-Tomasi corner refinement
 *   6. Homography (DLT + Gaussian elimination) and perspective warp
 *   7. Otsu threshold (for the Arabic binarization preset)
 */
'use strict';

/* ------------------------------------------------------------------ *
 * 1. Scalar / vector helpers
 * ------------------------------------------------------------------ */

/**
 * Clamp v into [lo, hi].
 * @param {number} v
 * @param {number} lo
 * @param {number} hi
 * @returns {number}
 */
function clamp(v, lo, hi) {
  return v < lo ? lo : v > hi ? hi : v;
}

/**
 * Squared Euclidean distance (avoids Math.sqrt when only comparing).
 * @returns {number}
 */
function dist2(ax, ay, bx, by) {
  const dx = ax - bx, dy = ay - by;
  return dx * dx + dy * dy;
}

/**
 * Euclidean distance.
 * @returns {number}
 */
function dist(ax, ay, bx, by) {
  return Math.sqrt(dist2(ax, ay, bx, by));
}

/** Perpendicular distance from point P to infinite line through A-B. */
function pointLineDist(px, py, ax, ay, bx, by) {
  const dx = bx - ax, dy = by - ay;
  const len = Math.hypot(dx, dy) || 1e-6;
  return Math.abs(dx * (py - ay) - dy * (px - ax)) / len;
}

/** Project point P onto the infinite line through A-B. @returns {{x:number,y:number}} */
function projectOnLine(px, py, ax, ay, bx, by) {
  const dx = bx - ax, dy = by - ay;
  const t = ((px - ax) * dx + (py - ay) * dy) / (dx * dx + dy * dy || 1e-6);
  return { x: ax + t * dx, y: ay + t * dy };
}

/* ------------------------------------------------------------------ *
 * 2. Grayscale, blur, gradients, integral images
 * ------------------------------------------------------------------ */

/**
 * Convert an RGBA byte array (Uint8ClampedArray) to a luminance plane using
 * Rec.601 weights. @returns {Float32Array} length w*h, values 0..255.
 */
function toLuminance(data, w, h) {
  const lum = new Float32Array(w * h);
  for (let i = 0, p = 0; i < data.length; i += 4, p++) {
    lum[p] = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
  }
  return lum;
}

/**
 * In-place box blur, two separable passes. O(n) sliding-window means it is
 * very cheap even at 1080p, and it suppresses fine background texture (e.g.
 * floor tiles) before edge detection.
 * @param {Float32Array} lum mutated in place
 * @param {number} w
 * @param {number} h
 * @param {number} radius blur radius in pixels
 * @returns {Float32Array} same array
 */
function boxBlur(lum, w, h, radius) {
  const r = Math.max(1, Math.round(radius));
  const tmp = new Float32Array(w * h);
  const span = 2 * r + 1;

  // Horizontal pass.
  for (let y = 0; y < h; y++) {
    const row = y * w;
    let acc = 0;
    for (let x = -r; x <= r; x++) acc += lum[row + clamp(x, 0, w - 1)];
    for (let x = 0; x < w; x++) {
      tmp[row + x] = acc / span;
      acc += lum[row + clamp(x + r + 1, 0, w - 1)] - lum[row + clamp(x - r, 0, w - 1)];
    }
  }
  // Vertical pass.
  for (let x = 0; x < w; x++) {
    let acc = 0;
    for (let y = -r; y <= r; y++) acc += tmp[clamp(y, 0, h - 1) * w + x];
    for (let y = 0; y < h; y++) {
      lum[y * w + x] = acc / span;
      acc += tmp[clamp(y + r + 1, 0, h - 1) * w + x] - tmp[clamp(y - r, 0, h - 1) * w + x];
    }
  }
  return lum;
}

/**
 * Sobel gradients (3x3, border-safe). Used both for edge evidence and as the
 * input to the Shi-Tomasi corner refinement.
 * @param {Float32Array} lum
 * @param {number} w
 * @param {number} h
 * @returns {{mag:Float32Array, gx:Float32Array, gy:Float32Array}}
 */
function sobel(lum, w, h) {
  const mag = new Float32Array(w * h);
  const gx = new Float32Array(w * h);
  const gy = new Float32Array(w * h);
  for (let y = 1; y < h - 1; y++) {
    for (let x = 1; x < w - 1; x++) {
      const i = y * w + x;
      const sx =
        -lum[i - w - 1] + lum[i - w + 1] -
        2 * lum[i - 1] + 2 * lum[i + 1] -
        lum[i + w - 1] + lum[i + w + 1];
      const sy =
        -lum[i - w - 1] - 2 * lum[i - w] - lum[i - w + 1] +
        lum[i + w - 1] + 2 * lum[i + w] + lum[i + w + 1];
      gx[i] = sx;
      gy[i] = sy;
      // sqrt is ~3x faster than Math.hypot at this scale
      mag[i] = Math.sqrt(sx * sx + sy * sy);
    }
  }
  return { mag, gx, gy };
}

/**
 * Integral image for O(1) local-window sums (used by local-mean shadow
 * removal and by the adaptive "bright = paper" mask).
 * @returns {Float64Array} w*h running sums
 */
function integralImage(lum, w, h) {
  const sum = new Float64Array(w * h);
  for (let y = 0; y < h; y++) {
    for (let x = 0; x < w; x++) {
      const i = y * w + x;
      const a = x > 0 ? sum[i - 1] : 0;
      const b = y > 0 ? sum[i - w] : 0;
      const c = (x > 0 && y > 0) ? sum[i - w - 1] : 0;
      sum[i] = lum[i] + a + b - c;
    }
  }
  return sum;
}

/**
 * Local mean of luminance inside a square window of `radius` around every
 * pixel, computed from an integral image. This is the lighting model used by
 * the shadow-removal step: paper brightness varies slowly, shadows are wide,
 * so a ~15%-of-image window separates ink from lighting.
 * @returns {Float32Array} w*h local means
 */
function localMean(lum, w, h, sum, radius) {
  const mean = new Float32Array(w * h);
  const r = Math.max(1, Math.round(radius));
  for (let y = 0; y < h; y++) {
    const y0 = clamp(y - r, 0, h - 1);
    const y1 = clamp(y + r, 0, h - 1);
    const rowH = y1 - y0 + 1;
    for (let x = 0; x < w; x++) {
      const x0 = clamp(x - r, 0, w - 1);
      const x1 = clamp(x + r, 0, w - 1);
      const i = y * w + x;
      const s =
        sum[y1 * w + x1] -
        (x0 > 0 ? sum[y1 * w + x0 - 1] : 0) -
        (y0 > 0 ? sum[(y0 - 1) * w + x1] : 0) +
        ((x0 > 0 && y0 > 0) ? sum[(y0 - 1) * w + x0 - 1] : 0);
      mean[i] = s / ((x1 - x0 + 1) * rowH);
    }
  }
  return mean;
}

/* ------------------------------------------------------------------ *
 * 3. Connected components (8-connectivity, union-find)
 * ------------------------------------------------------------------ */

/**
 * Label connected foreground pixels in a 0/1 mask and return the metadata of
 * every component. Used to isolate the document from a textured background
 * (tiled floor, desk, hands): the paper is usually the largest bright blob.
 * @param {Uint8Array} mask 0/1, length w*h
 * @param {number} w
 * @param {number} h
 * @returns {Array<{id:number, count:number, minX:number, minY:number, maxX:number, maxY:number}>}
 */
function connectedComponents(mask, w, h) {
  const n = w * h;
  const parent = new Int32Array(n).fill(-1);

  const find = (a) => {
    while (parent[a] !== a) {
      parent[a] = parent[parent[a]];
      a = parent[a];
    }
    return a;
  };
  const union = (a, b) => {
    const ra = find(a), rb = find(b);
    if (ra !== rb) parent[ra] = rb;
  };

  // First pass: label + union with already-scanned neighbors.
  for (let y = 0; y < h; y++) {
    for (let x = 0; x < w; x++) {
      const i = y * w + x;
      if (!mask[i]) continue;
      parent[i] = i;
      if (x > 0 && mask[i - 1]) union(i, i - 1);
      if (y > 0 && mask[i - w]) union(i, i - w);
      if (y > 0 && x > 0 && mask[i - w - 1]) union(i, i - w - 1);
      if (y > 0 && x < w - 1 && mask[i - w + 1]) union(i, i - w + 1);
    }
  }

  // Second pass: aggregate stats per root.
  const stats = new Map();
  for (let y = 0; y < h; y++) {
    for (let x = 0; x < w; x++) {
      const i = y * w + x;
      if (!mask[i]) continue;
      const root = find(i);
      let s = stats.get(root);
      if (!s) {
        s = { id: root, count: 0, minX: x, minY: y, maxX: x, maxY: y };
        stats.set(root, s);
      }
      s.count++;
      if (x < s.minX) s.minX = x;
      if (x > s.maxX) s.maxX = x;
      if (y < s.minY) s.minY = y;
      if (y > s.maxY) s.maxY = y;
    }
  }
  return Array.from(stats.values()).sort((a, b) => b.count - a.count);
}

/**
 * BFS flood fill on the mask starting from any pixel belonging to the target
 * component (seeded by its root index). Returns the component's pixels —
 * feeding them to convexHull() yields the document's outline even when the
 * paper touches a textured background.
 * @param {Uint8Array} mask 0/1, length w*h
 * @param {number} w
 * @param {number} h
 * @param {number} rootId index of a pixel inside the target component
 * @returns {Array<{x:number,y:number}>}
 */
function floodFillPixels(mask, w, h, rootId) {
  // Recompute labels minimally: union-find again but only to check membership.
  // To avoid duplicating union-find, we just seed from the bbox center and
  // flood while pixel value is 1.
  const n = w * h;
  const seed = rootId;
  const pts = [];
  const visited = new Uint8Array(n);
  const stack = [seed];
  visited[seed] = 1;
  while (stack.length) {
    const i = stack.pop();
    pts.push({ x: i % w, y: (i / w) | 0 });
    const x = i % w, y = (i / w) | 0;
    if (x > 0) { const j = i - 1; if (!visited[j] && mask[j]) { visited[j] = 1; stack.push(j); } }
    if (x < w - 1) { const j = i + 1; if (!visited[j] && mask[j]) { visited[j] = 1; stack.push(j); } }
    if (y > 0) { const j = i - w; if (!visited[j] && mask[j]) { visited[j] = 1; stack.push(j); } }
    if (y < h - 1) { const j = i + w; if (!visited[j] && mask[j]) { visited[j] = 1; stack.push(j); } }
    if (y > 0 && x > 0) { const j = i - w - 1; if (!visited[j] && mask[j]) { visited[j] = 1; stack.push(j); } }
    if (y > 0 && x < w - 1) { const j = i - w + 1; if (!visited[j] && mask[j]) { visited[j] = 1; stack.push(j); } }
    if (y < h - 1 && x > 0) { const j = i + w - 1; if (!visited[j] && mask[j]) { visited[j] = 1; stack.push(j); } }
    if (y < h - 1 && x < w - 1) { const j = i + w + 1; if (!visited[j] && mask[j]) { visited[j] = 1; stack.push(j); } }
  }
  return pts;
}

/* ------------------------------------------------------------------ *
 * 4. Convex hull, quad approximation, validation, ordering
 * ------------------------------------------------------------------ */

/**
 * Andrew's monotone-chain convex hull.
 * @param {Array<{x:number,y:number}>} pts
 * @returns {Array<{x:number,y:number}>} hull vertices in CCW order
 */
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
 * Approximate a convex hull to a quadrilateral. For a document the hull is a
 * near-rectangle, so the two hull points farthest apart are a diagonal pair
 * and the farthest point from that diagonal on each chain is one of the other
 * two corners. Falls back to the bounding box for degenerate hulls.
 * @param {Array<{x:number,y:number}>} hull
 * @returns {Array<{x:number,y:number}>} 4 corners, order follows hull
 */
function approxQuad(hull) {
  const n = hull.length;
  if (n <= 4) return hull;

  let best = -1, A = 0, B = 0;
  for (let i = 0; i < n; i++) {
    for (let j = i + 1; j < n; j++) {
      const d = dist2(hull[i].x, hull[i].y, hull[j].x, hull[j].y);
      if (d > best) { best = d; A = i; B = j; }
    }
  }

  const lineDist = (k) => {
    const ax = hull[A].x, ay = hull[A].y, bx = hull[B].x, by = hull[B].y;
    return Math.abs((bx - ax) * (hull[k].y - ay) - (by - ay) * (hull[k].x - ax)) /
           (Math.hypot(bx - ax, by - ay) || 1);
  };

  let cIdx = A, dC = -1;
  for (let k = (A + 1) % n; k !== B; k = (k + 1) % n) {
    const d = lineDist(k);
    if (d > dC) { dC = d; cIdx = k; }
  }
  let dIdx = B, dD = -1;
  for (let k = (B + 1) % n; k !== A; k = (k + 1) % n) {
    const d = lineDist(k);
    if (d > dD) { dD = d; dIdx = k; }
  }

  if (dC <= 0 || dD <= 0) {
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
 * Aspect-ratio guard: rejects slivers and frame-hugging diagonals caused by
 * bad lighting or a background that dominates the image.
 * @param {Array<{x:number,y:number}>} quad
 * @param {number} W image width
 * @param {number} H image height
 * @returns {boolean}
 */
function validateAspect(quad, W, H) {
  if (!quad || quad.length !== 4) return false;
  const s = (a, b) => dist(a.x, a.y, b.x, b.y);
  const sides = [s(quad[0], quad[1]), s(quad[1], quad[2]), s(quad[2], quad[3]), s(quad[3], quad[0])];
  const long = Math.max(...sides), short = Math.min(...sides);
  if (short === 0) return false;
  const ratio = long / short;
  return ratio > 0.9 && ratio < 9 && short > Math.min(W, H) * 0.25;
}

/**
 * Order four points as [TL, TR, BR, BL] by angle around the centroid
 * (robust to rotation up to any angle).
 * @param {Array<{x:number,y:number}>} quad
 * @returns {number[4][]} array of [x, y]
 */
function orderCorners(quad) {
  const cx = quad.reduce((s, p) => s + p.x, 0) / 4;
  const cy = quad.reduce((s, p) => s + p.y, 0) / 4;
  return quad
    .map((p) => ({ p, a: Math.atan2(p.y - cy, p.x - cx) }))
    .sort((x, y) => x.a - y.a)
    .map((o) => o.p)
    .map((p) => [p.x, p.y]);
}

/* ------------------------------------------------------------------ *
 * 5. Shi-Tomasi corner refinement
 * ------------------------------------------------------------------ */

/**
 * Snap each approximate corner to the strongest corner response inside a
 * small search window. Uses the Shi-Tomasi score (min eigenvalue of the
 * structure tensor). Unlike |gx|*|gy| this stays ~0 along a straight edge and
 * peaks only where two edges meet, so it locks onto the true corner even for
 * rotated or perspective-distorted documents.
 * @param {Float32Array} gx sobel output
 * @param {Float32Array} gy sobel output
 * @param {number} width
 * @param {number} height
 * @param {Array<{x:number,y:number}>} corners approximate corners
 * @param {number} win search window half-size
 * @param {number} N tensor neighborhood half-size (2 => 5x5)
 * @returns {Array<{x:number,y:number}>}
 */
function refineCorners(gx, gy, width, height, corners, win = 16, N = 2) {
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
        const disc = Math.sqrt(((a - c) / 2) ** 2 + b * b);
        const resp = (a + c) / 2 - disc;
        if (resp > best) { best = resp; bx = x; by = y; }
      }
    }
    return { x: bx, y: by };
  });
}

/* ------------------------------------------------------------------ *
 * 6. Homography + perspective warp
 * ------------------------------------------------------------------ */

/**
 * Solve the 3x3 homography mapping src -> dst from 4 correspondences via an
 * 8x8 linear system + Gaussian elimination (partial pivoting).
 * @param {number[4][]} src four source points [x,y]
 * @param {number[4][]} dst four destination points [x,y]
 * @returns {number[]} 9 coefficients, row-major, h[8] === 1
 */
function solveHomography(src, dst) {
  const A = [], b = [];
  for (let i = 0; i < 4; i++) {
    const [x, y] = src[i], [u, v] = dst[i];
    A.push([x, y, 1, 0, 0, 0, -u * x, -u * y]); b.push(u);
    A.push([0, 0, 0, x, y, 1, -v * x, -v * y]); b.push(v);
  }
  const h = gaussSolve(A, b);
  return [h[0], h[1], h[2], h[3], h[4], h[5], h[6], h[7], 1];
}

/** Gaussian elimination with partial pivoting for an n x n system. */
function gaussSolve(A, b) {
  const n = A.length;
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

/** Invert a 3x3 matrix (adjugate / determinant). */
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
 * Warp the source-quad region into a rectangular ImageData via inverse
 * mapping + bilinear interpolation (no holes, sub-pixel quality).
 * @param {ImageData} src source pixels
 * @param {number[4][]} corners [TL,TR,BR,BL] in source coordinates
 * @param {number} outW destination width
 * @param {number} outH destination height
 * @returns {ImageData}
 */
function warpIntoImageData(src, corners, outW, outH) {
  const dstQuad = [[0, 0], [outW, 0], [outW, outH], [0, outH]];
  const H = solveHomography(corners, dstQuad);
  const Hi = invert3x3(H);

  const out = new ImageData(outW, outH);
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
  return out;
}

/**
 * Page size from the detected quad, capped at `maxDim` pixels on the long
 * side (1800 is a good balance of quality vs. mobile memory).
 * @param {number[4][]} corners
 * @param {number} [maxDim]
 * @returns {{W:number, H:number}}
 */
function pageSizeFromQuad(corners, maxDim = 1800) {
  const [tl, tr, br, bl] = corners;
  const top = dist(tl[0], tl[1], tr[0], tr[1]);
  const bottom = dist(br[0], br[1], bl[0], bl[1]);
  const left = dist(bl[0], bl[1], tl[0], tl[1]);
  const right = dist(br[0], br[1], tr[0], tr[1]);
  let W = (top + bottom) / 2;
  let H = (left + right) / 2;
  const scale = Math.min(1, maxDim / Math.max(W, H, 1));
  return { W: Math.max(2, Math.round(W * scale)), H: Math.max(2, Math.round(H * scale)) };
}

/* ------------------------------------------------------------------ *
 * 7. Otsu threshold (binarization preset)
 * ------------------------------------------------------------------ */

/**
 * Otsu's method on a luminance histogram. Returns the threshold that splits
 * the histogram into two classes minimizing within-class variance.
 * @param {Float32Array} lum
 * @returns {number}
 */
function otsuThreshold(lum) {
  const hist = new Uint32Array(256);
  for (let i = 0; i < lum.length; i++) hist[clamp(lum[i], 0, 255) | 0]++;
  const total = lum.length;
  let sum = 0;
  for (let t = 0; t < 256; t++) sum += t * hist[t];

  let sumB = 0, wB = 0, maxVar = 0, threshold = 128;
  for (let t = 0; t < 256; t++) {
    wB += hist[t];
    if (wB === 0) continue;
    const wF = total - wB;
    if (wF === 0) break;
    sumB += t * hist[t];
    const mB = sumB / wB;
    const mF = (sum - sumB) / wF;
    const varBetween = wB * wF * (mB - mF) * (mB - mF);
    if (varBetween > maxVar) { maxVar = varBetween; threshold = t; }
  }
  return threshold;
}
