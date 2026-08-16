<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — DocuScan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --primary: #4f46e5; --primary-dark: #3730a3; --danger: #ef4444; --border: #e2e8f0; --text: #1e293b; --text-muted: #64748b; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; display: flex; min-height: 100vh; }

        .auth-left {
            width: 480px;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: space-between;
            padding: 48px;
            position: relative;
            overflow: hidden;
        }
        .auth-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%234f46e5' fill-opacity='0.06'%3E%3Ccircle cx='30' cy='30' r='30'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .auth-left .brand { display: flex; align-items: center; gap: 12px; position: relative; }
        .auth-left .brand-icon {
            width: 44px; height: 44px;
            background: linear-gradient(135deg, #4f46e5, #06b6d4);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
        }
        .auth-left .brand-name { font-size: 22px; font-weight: 800; color: #fff; }
        .auth-left .brand-tagline { font-size: 11px; color: #818cf8; font-weight: 500; }

        .auth-left .hero { position: relative; }
        .auth-left .hero h2 { font-size: 32px; font-weight: 800; color: #fff; line-height: 1.25; margin-bottom: 16px; }
        .auth-left .hero p { font-size: 15px; color: #94a3b8; line-height: 1.7; }
        .auth-left .features { display: flex; flex-direction: column; gap: 12px; position: relative; margin-top: 32px; }
        .feature-item { display: flex; align-items: center; gap: 10px; color: #c7d2fe; font-size: 14px; }
        .feature-dot { width: 8px; height: 8px; border-radius: 50%; background: #818cf8; flex-shrink: 0; }

        .auth-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px;
        }
        .auth-box { width: 100%; max-width: 420px; }
        .auth-box h1 { font-size: 26px; font-weight: 800; color: var(--text); margin-bottom: 6px; }
        .auth-box .subtitle { font-size: 14px; color: var(--text-muted); margin-bottom: 32px; }

        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 7px; }
        .form-control {
            width: 100%; padding: 11px 14px;
            border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 14px; color: var(--text); outline: none;
            transition: border-color .15s; font-family: inherit;
        }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79,70,229,.1); }
        .form-control::placeholder { color: #94a3b8; }
        .is-invalid { border-color: var(--danger) !important; }
        .invalid-feedback { color: var(--danger); font-size: 12px; margin-top: 4px; }

        .btn-full {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%; padding: 12px 20px;
            background: var(--primary); color: #fff;
            border: none; border-radius: 8px;
            font-size: 15px; font-weight: 700;
            cursor: pointer; transition: background .15s;
            font-family: inherit;
        }
        .btn-full:hover { background: var(--primary-dark); }

        .divider { text-align: center; position: relative; margin: 24px 0; color: var(--text-muted); font-size: 13px; }
        .divider::before, .divider::after { content: ''; position: absolute; top: 50%; width: 40%; height: 1px; background: var(--border); }
        .divider::before { left: 0; } .divider::after { right: 0; }

        .alert { padding: 12px 16px; border-radius: 8px; font-size: 13.5px; font-weight: 500; margin-bottom: 20px; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }

        @media (max-width: 768px) { .auth-left { display: none; } }
    </style>
</head>
<body>

<div class="auth-left">
    <div class="brand">
        <div class="brand-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2" width="22" height="22">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div>
            <div class="brand-name">DocuScan</div>
            <div class="brand-tagline">SaaS Platform</div>
        </div>
    </div>

    <div class="hero">
        <h2>Scan. Organise.<br>Access Anywhere.</h2>
        <p>The professional document scanning & management platform for businesses and importers.</p>
        <div class="features">
            <div class="feature-item"><span class="feature-dot"></span>Instant camera scanning with auto edge detection</div>
            <div class="feature-item"><span class="feature-dot"></span>Multi-page PDF compilation in seconds</div>
            <div class="feature-item"><span class="feature-dot"></span>Cloud storage for Factures, Douane, Contrats</div>
            <div class="feature-item"><span class="feature-dot"></span>Secure multi-tenant company isolation</div>
        </div>
    </div>

    <div style="color:#475569;font-size:12px;position:relative;">
        © {{ date('Y') }} DocuScan SaaS. All rights reserved.
    </div>
</div>

<div class="auth-right">
    <div class="auth-box">
        <h1>Welcome back</h1>
        <p class="subtitle">Sign in to your DocuScan account</p>

        @if($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif
        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                       placeholder="owner@company.com" value="{{ old('email', 'owner@docuscan.test') }}" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" value="password123" required>
            </div>

            <button type="submit" class="btn-full">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                Sign In
            </button>
        </form>

        <div class="divider">or</div>

        <p style="text-align:center;font-size:14px;color:var(--text-muted);">
            Don't have an account?
            <a href="{{ route('register') }}" style="color:var(--primary);font-weight:600;text-decoration:none;">Create company account →</a>
        </p>

        <div style="margin-top:28px;padding:16px;background:#f8fafc;border-radius:8px;border:1px solid var(--border);">
            <div style="font-size:11.5px;font-weight:600;color:var(--text-muted);margin-bottom:8px;text-transform:uppercase;letter-spacing:.5px;">Demo Credentials</div>
            <div style="font-size:13px;color:var(--text);">Email: <strong>owner@docuscan.test</strong></div>
            <div style="font-size:13px;color:var(--text);">Password: <strong>password123</strong></div>
        </div>
    </div>
</div>

</body>
</html>
