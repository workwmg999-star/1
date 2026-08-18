/**
 * build.js — Bundles the scanner into a single self-contained HTML file.
 * Run: node build.js
 * Output: assets/scanner/app.html
 */
const fs = require('fs');
const path = require('path');

const src = path.join(__dirname, '..', 'public', 'scanner');
const out = path.join(__dirname, 'assets', 'scanner', 'app.html');

const html = fs.readFileSync(path.join(src, 'index.html'), 'utf8');
let utils = fs.readFileSync(path.join(src, 'utils.js'), 'utf8');
let worker = fs.readFileSync(path.join(src, 'worker.js'), 'utf8');
let main = fs.readFileSync(path.join(src, 'main.js'), 'utf8');

// Remove 'use strict' from utils (it will be inside the worker blob)
utils = utils.replace(/^'use strict';\n/m, '');

// Remove importScripts from worker — utils will be prepended
worker = worker.replace(/importScripts\('utils\.js'\);/, '');

// Build the worker source: utils + worker code combined
const workerSource = utils + '\n' + worker;

// Modify main.js: replace new Worker('worker.js') with Blob-based worker
main = main.replace(
  /state\.worker = new Worker\('worker\.js'\);/,
  `const blob = new Blob([${JSON.stringify(workerSource)}], {type:'text/javascript'});
   state.worker = new Worker(URL.createObjectURL(blob));`
);

// Remove the three <script> references and replace with inline
let result = html;
result = result.replace(
  /<script src="utils\.js"><\/script>\s*<script src="main\.js"><\/script>/,
  `<script>\n${main}\n</script>`
);

// Remove the "window.parent.history.back()" back button (crashes in WebView)
result = result.replace(
  /onclick="window\.parent\.history\.back\(\)"/,
  `onclick="history.back()"`
);

// Fix the back button: also remove the reference in main.js goBack
// The scan-top back button should use Android back, not history.back
// history.back() is fine in a single-page app context

fs.mkdirSync(path.dirname(out), { recursive: true });
fs.writeFileSync(out, result);
console.log(`Built ${out} (${(result.length / 1024).toFixed(1)} KB)`);
