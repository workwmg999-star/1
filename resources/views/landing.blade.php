<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocuScan — Professional Document Scanner for Businesses</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --primary: #4f46e5; --accent: #06b6d4; --text: #0f172a; --text-muted: #64748b; }
        body { font-family: 'Inter', sans-serif; background: #fff; color: var(--text); }

        nav { display: flex; align-items: center; justify-content: space-between; padding: 18px 48px; border-bottom: 1px solid #f1f5f9; position: sticky; top: 0; background: rgba(255,255,255,.95); backdrop-filter: blur(8px); z-index: 100; }
        .nav-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .nav-brand-icon { width: 36px; height: 36px; background: linear-gradient(135deg, #4f46e5, #06b6d4); border-radius: 9px; display: flex; align-items: center; justify-content: center; }
        .nav-brand-name { font-size: 18px; font-weight: 800; color: var(--text); }
        .nav-links { display: flex; align-items: center; gap: 8px; }
        .btn-nav { padding: 8px 18px; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; border: none; font-family: inherit; transition: all .15s; }
        .btn-ghost { color: var(--text-muted); background: transparent; }
        .btn-ghost:hover { color: var(--text); background: #f8fafc; }
        .btn-filled { background: var(--primary); color: #fff; }
        .btn-filled:hover { background: #3730a3; }

        .hero { text-align: center; padding: 80px 24px 60px; background: linear-gradient(180deg, #fafafa 0%, #fff 100%); }
        .hero-badge { display: inline-flex; align-items: center; gap: 6px; background: #ede9fe; color: #5b21b6; font-size: 12px; font-weight: 700; padding: 5px 14px; border-radius: 20px; margin-bottom: 24px; }
        .hero h1 { font-size: clamp(38px, 6vw, 68px); font-weight: 900; line-height: 1.1; letter-spacing: -2px; color: var(--text); max-width: 800px; margin: 0 auto 20px; }
        .hero h1 span { background: linear-gradient(135deg, #4f46e5, #06b6d4); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .hero p { font-size: 18px; color: var(--text-muted); max-width: 560px; margin: 0 auto 36px; line-height: 1.7; }
        .hero-btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .btn-hero { padding: 14px 30px; border-radius: 10px; font-size: 15px; font-weight: 700; text-decoration: none; transition: all .15s; }
        .btn-hero-primary { background: var(--primary); color: #fff; box-shadow: 0 4px 14px rgba(79,70,229,.4); }
        .btn-hero-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(79,70,229,.5); }
        .btn-hero-outline { border: 2px solid #e2e8f0; color: var(--text); background: #fff; }
        .btn-hero-outline:hover { border-color: var(--primary); color: var(--primary); }

        .features { padding: 80px 48px; background: #fafafa; }
        .features-title { text-align: center; margin-bottom: 52px; }
        .features-title h2 { font-size: 36px; font-weight: 800; letter-spacing: -1px; margin-bottom: 10px; }
        .features-title p { color: var(--text-muted); font-size: 16px; }
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; max-width: 1100px; margin: 0 auto; }
        .feature-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 28px; transition: transform .15s, box-shadow .15s; }
        .feature-card:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,.08); }
        .feature-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; }
        .feature-card h3 { font-size: 16px; font-weight: 700; margin-bottom: 8px; }
        .feature-card p { font-size: 14px; color: var(--text-muted); line-height: 1.6; }

        .cta { text-align: center; padding: 80px 24px; background: linear-gradient(135deg, #0f172a, #1e1b4b); }
        .cta h2 { font-size: 40px; font-weight: 900; color: #fff; margin-bottom: 12px; letter-spacing: -1.5px; }
        .cta p { color: #94a3b8; font-size: 16px; margin-bottom: 32px; }

        footer { text-align: center; padding: 24px; color: var(--text-muted); font-size: 13px; border-top: 1px solid #f1f5f9; }
    </style>
</head>
<body>

<nav>
    <a href="{{ route('landing') }}" class="nav-brand">
        <div class="nav-brand-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <span class="nav-brand-name">DocuScan</span>
    </a>
    <div class="nav-links">
        <a href="{{ route('login') }}" class="btn-nav btn-ghost">Sign In</a>
        <a href="{{ route('register') }}" class="btn-nav btn-filled">Get Started Free</a>
    </div>
</nav>

<div class="hero">
    <div class="hero-badge">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        Built for Importers & Businesses
    </div>
    <h1>Scan, Organise &<br><span>Manage Documents</span><br>Instantly</h1>
    <p>The fastest way to digitize your paper documents. Scan, auto-crop, enhance, and store Factures, Douane, Contracts and more — all in one place.</p>
    <div class="hero-btns">
        <a href="{{ route('register') }}" class="btn-hero btn-hero-primary">Start Free — No Credit Card</a>
        <a href="{{ route('login') }}" class="btn-hero btn-hero-outline">Sign In →</a>
    </div>
</div>

<div class="features">
    <div class="features-title">
        <h2>Everything you need to go paperless</h2>
        <p>Designed for businesses that deal with high volumes of paper documents daily.</p>
    </div>
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon" style="background:#ede9fe;">
                <svg fill="none" viewBox="0 0 24 24" stroke="#7c3aed" stroke-width="2" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
            </div>
            <h3>Smart Camera Scanning</h3>
            <p>Auto edge detection, perspective correction, and image enhancement directly from your Android camera.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:#dbeafe;">
                <svg fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </div>
            <h3>PDF Compilation</h3>
            <p>Combine multiple scanned pages into a single organized PDF file in seconds.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:#d1fae5;">
                <svg fill="none" viewBox="0 0 24 24" stroke="#059669" stroke-width="2" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
            </div>
            <h3>Smart Folders</h3>
            <p>Organise by Factures, Documents Douane, Fournisseurs, Transport, and Contrats — instantly.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:#fef3c7;">
                <svg fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="2" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
            </div>
            <h3>Cloud Storage</h3>
            <p>Secure cloud backup with local and S3 support. Access your documents from any device, anytime.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:#fee2e2;">
                <svg fill="none" viewBox="0 0 24 24" stroke="#dc2626" stroke-width="2" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
            </div>
            <h3>Team Management</h3>
            <p>Add employees, assign roles (Owner / Admin / Employee), and control document access per company.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:#f0fdf4;">
                <svg fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <h3>Dashboard & Analytics</h3>
            <p>Real-time storage usage, document counts, and activity logs in a clear visual dashboard.</p>
        </div>
    </div>
</div>

<div class="cta">
    <h2>Ready to go paperless?</h2>
    <p>Join businesses already scanning with DocuScan. Start free, scale as you grow.</p>
    <a href="{{ route('register') }}" style="display:inline-block;padding:16px 36px;background:linear-gradient(135deg,#4f46e5,#06b6d4);color:#fff;border-radius:12px;font-size:16px;font-weight:800;text-decoration:none;box-shadow:0 8px 24px rgba(79,70,229,.5);transition:transform .15s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
        Create Free Account →
    </a>
</div>

<footer>
    © {{ date('Y') }} DocuScan SaaS. Built with Laravel 12. All rights reserved.
</footer>

</body>
</html>
