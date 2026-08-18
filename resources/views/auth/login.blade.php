<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Sign In — DocuScan Pro</title>
    <meta name="description" content="Sign in to DocuScan Pro — Professional Document Scanner & Cloud Manager">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary: hsl(231, 80%, 60%);
            --primary-dark: hsl(231, 80%, 50%);
            --primary-glow: hsl(231, 80%, 60%, 0.25);
            --accent: hsl(193, 87%, 55%);
            --danger: hsl(350, 88%, 55%);
            --success: hsl(162, 76%, 42%);
            --bg: hsl(220, 20%, 97%);
            --card: #ffffff;
            --border: hsl(220, 13%, 91%);
            --text: hsl(222, 47%, 11%);
            --text-2: hsl(215, 16%, 30%);
            --text-muted: hsl(215, 16%, 47%);
            --text-light: hsl(215, 16%, 72%);
            --font: 'Plus Jakarta Sans', sans-serif;
            --r-sm: 8px;
            --r: 12px;
            --r-md: 16px;
            --r-lg: 20px;
            --r-full: 9999px;
            --shadow-md: 0 8px 24px -4px hsl(220 13% 11% / 0.12);
            --shadow-primary: 0 8px 24px -4px hsl(231 80% 60% / 0.35);
        }
        [data-theme="dark"] {
            --bg: hsl(222, 47%, 8%);
            --card: hsl(222, 47%, 12%);
            --border: hsl(222, 30%, 20%);
            --text: hsl(220, 20%, 96%);
            --text-2: hsl(220, 15%, 80%);
            --text-muted: hsl(215, 16%, 60%);
        }

        body {
            font-family: var(--font);
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            -webkit-font-smoothing: antialiased;
        }

        /* ─── LEFT PANEL ──────────────────────────────────────────────── */
        .auth-left {
            width: 460px;
            flex-shrink: 0;
            background: linear-gradient(160deg, hsl(231,80%,10%) 0%, hsl(222,47%,7%) 50%, hsl(193,87%,8%) 100%);
            display: flex;
            flex-direction: column;
            padding: 48px;
            position: relative;
            overflow: hidden;
        }
        .auth-left::before {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            background: radial-gradient(circle, hsl(231,80%,60%,0.15) 0%, transparent 70%);
            top: -100px; left: -100px;
            pointer-events: none;
        }
        .auth-left::after {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            background: radial-gradient(circle, hsl(193,87%,55%,0.1) 0%, transparent 70%);
            bottom: -100px; right: -80px;
            pointer-events: none;
        }

        .left-brand { display: flex; align-items: center; gap: 12px; position: relative; z-index: 1; }
        .brand-icon {
            width: 46px; height: 46px;
            background: linear-gradient(135deg, hsl(231,80%,60%), hsl(193,87%,55%));
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 6px 20px hsl(231,80%,60%,0.4);
        }
        .brand-name { font-size: 22px; font-weight: 800; color: #fff; letter-spacing: -0.5px; }
        .brand-sub  { font-size: 10px; color: hsl(231,80%,72%); font-weight: 600; letter-spacing: 0.8px; text-transform: uppercase; }

        .left-hero { flex: 1; display: flex; flex-direction: column; justify-content: center; position: relative; z-index: 1; }
        .left-hero h2 {
            font-size: 34px; font-weight: 800;
            color: #fff; line-height: 1.2;
            letter-spacing: -1px;
            margin-bottom: 16px;
        }
        .left-hero h2 span { color: hsl(193,87%,65%); }
        .left-hero p { font-size: 15px; color: hsl(220,15%,65%); line-height: 1.7; max-width: 320px; }

        .features-list { margin-top: 36px; display: flex; flex-direction: column; gap: 14px; }
        .feature-row { display: flex; align-items: center; gap: 12px; }
        .feature-icon-wrap {
            width: 36px; height: 36px;
            background: hsl(231,80%,60%,0.15);
            border: 1px solid hsl(231,80%,60%,0.25);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .feature-icon-wrap svg { width: 17px; height: 17px; stroke: hsl(231,80%,72%); }
        .feature-text { font-size: 13.5px; color: hsl(220,15%,70%); font-weight: 500; }

        .left-stats {
            display: flex; gap: 24px;
            padding-top: 32px;
            border-top: 1px solid hsl(222,47%,18%);
            position: relative; z-index: 1;
        }
        .left-stat .val { font-size: 24px; font-weight: 800; color: #fff; letter-spacing: -0.5px; }
        .left-stat .lbl { font-size: 11px; color: hsl(215,16%,45%); margin-top: 2px; }

        /* ─── RIGHT PANEL ─────────────────────────────────────────────── */
        .auth-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 48px;
            overflow-y: auto;
        }
        .auth-box { width: 100%; max-width: 400px; }

        .auth-header { margin-bottom: 32px; }
        .auth-header h1 { font-size: 28px; font-weight: 800; color: var(--text); letter-spacing: -0.5px; margin-bottom: 6px; }
        .auth-header p { font-size: 14px; color: var(--text-muted); }

        /* Form */
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--text-2); margin-bottom: 6px; }
        .form-control {
            width: 100%; padding: 11px 14px;
            border: 1.5px solid var(--border);
            border-radius: var(--r-sm);
            font-size: 14px; color: var(--text);
            background: var(--card);
            outline: none;
            transition: all 0.18s;
            font-family: var(--font);
            -webkit-appearance: none;
        }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-glow); }
        .form-control::placeholder { color: var(--text-light); }
        .is-invalid { border-color: var(--danger) !important; }
        .invalid-feedback { color: var(--danger); font-size: 12px; margin-top: 4px; font-weight: 500; }

        .input-wrap { position: relative; }
        .input-wrap .input-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: var(--text-light); pointer-events: none; }
        .input-wrap .form-control { padding-left: 40px; }
        .input-wrap .toggle-pw { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-light); padding: 4px; }

        .form-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .checkbox-wrap { display: flex; align-items: center; gap: 8px; cursor: pointer; }
        .checkbox-wrap input { accent-color: var(--primary); width: 16px; height: 16px; cursor: pointer; }
        .checkbox-wrap span { font-size: 13px; color: var(--text-2); font-weight: 500; }
        .forgot-link { font-size: 13px; color: var(--primary); font-weight: 600; text-decoration: none; }
        .forgot-link:hover { text-decoration: underline; }

        /* Buttons */
        .btn-submit {
            width: 100%; padding: 13px 20px;
            background: linear-gradient(135deg, var(--primary), hsl(231,80%,55%));
            color: #fff;
            border: none; border-radius: var(--r-sm);
            font-size: 15px; font-weight: 700;
            cursor: pointer;
            transition: all 0.18s;
            font-family: var(--font);
            display: flex; align-items: center; justify-content: center; gap: 8px;
            box-shadow: var(--shadow-primary);
            letter-spacing: 0.1px;
        }
        .btn-submit:hover { box-shadow: 0 12px 32px hsl(231,80%,60%,0.45); transform: translateY(-1px); }
        .btn-submit:active { transform: scale(0.98); }

        .btn-google {
            width: 100%; padding: 11px 20px;
            background: var(--card);
            border: 1.5px solid var(--border);
            border-radius: var(--r-sm);
            font-size: 14px; font-weight: 600;
            cursor: pointer; color: var(--text);
            font-family: var(--font);
            display: flex; align-items: center; justify-content: center; gap: 10px;
            transition: all 0.18s;
        }
        .btn-google:hover { border-color: hsl(231,80%,60%,0.4); background: hsl(231,80%,60%,0.03); transform: translateY(-1px); }

        /* Divider */
        .divider { text-align: center; position: relative; margin: 24px 0; color: var(--text-light); font-size: 12px; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase; }
        .divider::before, .divider::after { content: ''; position: absolute; top: 50%; height: 1px; background: var(--border); width: calc(50% - 28px); }
        .divider::before { left: 0; } .divider::after { right: 0; }

        /* Alert */
        .alert { padding: 12px 16px; border-radius: var(--r-sm); font-size: 13.5px; font-weight: 500; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .alert-error { background: hsl(350,88%,97%); color: hsl(350,88%,38%); border: 1px solid hsl(350,88%,55%,0.25); }
        .alert-success { background: hsl(162,76%,96%); color: hsl(162,76%,28%); border: 1px solid hsl(162,76%,42%,0.25); }
        .alert svg { width: 17px; height: 17px; flex-shrink: 0; }

        /* Footer */
        .auth-footer { text-align: center; margin-top: 24px; font-size: 14px; color: var(--text-muted); }
        .auth-footer a { color: var(--primary); font-weight: 700; text-decoration: none; }
        .auth-footer a:hover { text-decoration: underline; }

        /* Demo box */
        .demo-box { margin-top: 24px; padding: 14px 16px; background: hsl(231,80%,60%,0.04); border: 1px solid hsl(231,80%,60%,0.15); border-radius: var(--r); }
        .demo-box-title { font-size: 10px; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 8px; }
        .demo-box-row { font-size: 13px; color: var(--text-2); margin-bottom: 4px; }
        .demo-box-row strong { color: var(--text); }

        /* Theme toggle */
        .theme-toggle { position: absolute; top: 20px; right: 20px; background: var(--card); border: 1px solid var(--border); width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-muted); transition: all 0.18s; }
        .theme-toggle:hover { color: var(--primary); border-color: var(--primary); }

        @media (max-width: 900px) {
            .auth-left { display: none; }
            .auth-right { padding: 24px; }
        }
        @media (max-width: 480px) {
            .auth-right { padding: 20px; align-items: flex-start; padding-top: 40px; }
        }
    </style>
</head>
<body>

<div class="auth-left">
    <div class="left-brand">
        <div class="brand-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5" width="24" height="24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div>
            <div class="brand-name">DocuScan</div>
            <div class="brand-sub">Pro SaaS</div>
        </div>
    </div>

    <div class="left-hero">
        <h2>Scan. Organise.<br><span>Access Anywhere.</span></h2>
        <p>The professional document scanning & management platform built for businesses and importers.</p>

        <div class="features-list">
            <div class="feature-row">
                <div class="feature-icon-wrap">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
                </div>
                <div class="feature-text">Instant camera scanning with auto edge detection</div>
            </div>
            <div class="feature-row">
                <div class="feature-icon-wrap">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div class="feature-text">Multi-page PDF compilation in seconds</div>
            </div>
            <div class="feature-row">
                <div class="feature-icon-wrap">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
                </div>
                <div class="feature-text">Cloud storage: Factures, Douane, Contrats</div>
            </div>
            <div class="feature-row">
                <div class="feature-icon-wrap">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <div class="feature-text">Secure multi-tenant company isolation</div>
            </div>
        </div>
    </div>

    <div class="left-stats">
        <div class="left-stat"><div class="val">10K+</div><div class="lbl">Companies</div></div>
        <div class="left-stat"><div class="val">5M+</div><div class="lbl">Documents</div></div>
        <div class="left-stat"><div class="val">99.9%</div><div class="lbl">Uptime</div></div>
    </div>
</div>

<div class="auth-right" style="position:relative;">
    <button class="theme-toggle" onclick="toggleTheme()" title="Toggle theme">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
        </svg>
    </button>

    <div class="auth-box">
        <div class="auth-header">
            <h1>Welcome back</h1>
            <p>Sign in to your DocuScan account</p>
        </div>

        @if($errors->any())
        <div class="alert alert-error">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/></svg>
            {{ $errors->first() }}
        </div>
        @endif
        @if(session('success'))
        <div class="alert alert-success">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <div class="input-wrap">
                    <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                           placeholder="you@company.com"
                           value="{{ old('email', 'owner@docuscan.test') }}" required autofocus>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-wrap">
                    <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <input type="password" name="password" id="pwField" class="form-control" placeholder="••••••••" value="password123" required>
                    <button type="button" class="toggle-pw" onclick="togglePw()">
                        <svg id="pwEye" width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
            </div>

            <div class="form-row">
                <label class="checkbox-wrap">
                    <input type="checkbox" name="remember" checked>
                    <span>Remember me</span>
                </label>
                <a href="#" class="forgot-link">Forgot password?</a>
            </div>

            <button type="submit" class="btn-submit">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                Sign In
            </button>
        </form>

        <div class="divider">or continue with</div>

        <button class="btn-google">
            <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
            Continue with Google
        </button>

        <div class="auth-footer">
            Don't have an account?
            <a href="{{ route('register') }}">Create company account →</a>
        </div>

        <div class="demo-box">
            <div class="demo-box-title">🧪 Demo Credentials</div>
            <div class="demo-box-row">Email: <strong>owner@docuscan.test</strong></div>
            <div class="demo-box-row">Password: <strong>password123</strong></div>
        </div>
    </div>
</div>

<script>
(function() {
    const saved = localStorage.getItem('docuscan-theme') || 'light';
    document.documentElement.setAttribute('data-theme', saved);
})();
function toggleTheme() {
    const c = document.documentElement.getAttribute('data-theme');
    const n = c === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', n);
    localStorage.setItem('docuscan-theme', n);
}
function togglePw() {
    const f = document.getElementById('pwField');
    f.type = f.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
