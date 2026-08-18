<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Create Company Account — DocuScan Pro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --primary: hsl(231, 80%, 60%); --primary-dark: hsl(231, 80%, 50%);
            --primary-glow: hsl(231, 80%, 60%, 0.22); --accent: hsl(193, 87%, 55%);
            --success: hsl(162, 76%, 42%); --danger: hsl(350, 88%, 55%);
            --bg: hsl(220, 20%, 97%); --card: #fff; --border: hsl(220, 13%, 91%);
            --text: hsl(222, 47%, 11%); --text-2: hsl(215, 16%, 30%);
            --text-muted: hsl(215, 16%, 47%); --text-light: hsl(215, 16%, 72%);
            --font: 'Plus Jakarta Sans', sans-serif;
            --shadow-primary: 0 8px 24px hsl(231 80% 60% / 0.35);
        }
        [data-theme="dark"] {
            --bg: hsl(222,47%,8%); --card: hsl(222,47%,12%);
            --border: hsl(222,30%,20%); --text: hsl(220,20%,96%);
            --text-2: hsl(220,15%,80%); --text-muted: hsl(215,16%,60%);
            --text-light: hsl(215,16%,45%);
        }
        body { font-family: var(--font); background: var(--bg); color: var(--text); min-height: 100vh; display: flex; -webkit-font-smoothing: antialiased; }

        .register-left {
            width: 380px; flex-shrink: 0;
            background: linear-gradient(160deg, hsl(231,80%,10%) 0%, hsl(222,47%,7%) 60%, hsl(193,87%,8%) 100%);
            display: flex; flex-direction: column; padding: 48px 40px;
            position: relative; overflow: hidden;
        }
        .register-left::before {
            content: ''; position: absolute;
            width: 400px; height: 400px;
            background: radial-gradient(circle, hsl(231,80%,60%,0.12) 0%, transparent 65%);
            top: -80px; left: -60px; pointer-events: none;
        }

        .brand { display: flex; align-items: center; gap: 12px; position: relative; z-index: 1; }
        .brand-icon {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, hsl(231,80%,60%), hsl(193,87%,55%));
            border-radius: 12px; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 16px hsl(231,80%,60%,0.4);
        }
        .brand-name { font-size: 20px; font-weight: 800; color: #fff; }
        .brand-sub { font-size: 10px; color: hsl(231,80%,72%); font-weight: 600; letter-spacing: 0.8px; text-transform: uppercase; }

        .left-content { flex: 1; display: flex; flex-direction: column; justify-content: center; gap: 28px; position: relative; z-index: 1; margin-top: 32px; }
        .left-content h2 { font-size: 28px; font-weight: 800; color: #fff; line-height: 1.25; letter-spacing: -0.5px; }
        .left-content h2 span { color: hsl(193,87%,65%); }
        .left-content p { font-size: 14px; color: hsl(220,15%,60%); line-height: 1.7; }

        .onboard-steps { display: flex; flex-direction: column; gap: 16px; }
        .onboard-step { display: flex; align-items: flex-start; gap: 12px; }
        .step-num {
            width: 28px; height: 28px;
            background: hsl(231,80%,60%,0.2);
            border: 1px solid hsl(231,80%,60%,0.35);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; color: hsl(231,80%,72%); flex-shrink: 0; margin-top: 1px;
        }
        .step-text { font-size: 13px; color: hsl(220,15%,65%); font-weight: 500; }
        .step-text strong { color: hsl(220,15%,82%); display: block; margin-bottom: 2px; font-size: 13.5px; }

        .left-footer { position: relative; z-index: 1; font-size: 11px; color: hsl(215,16%,38%); }

        /* RIGHT */
        .register-right {
            flex: 1; display: flex; align-items: flex-start; justify-content: center;
            padding: 40px 48px; overflow-y: auto;
        }
        .auth-box { width: 100%; max-width: 480px; }

        /* Progress */
        .progress-steps { display: flex; align-items: center; margin-bottom: 32px; gap: 0; }
        .p-step { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px; position: relative; }
        .p-step:not(:last-child)::after {
            content: ''; position: absolute;
            top: 16px; left: calc(50% + 20px); right: calc(-50% + 20px);
            height: 2px; background: var(--border); z-index: 0;
            transition: background 0.3s;
        }
        .p-step.done:not(:last-child)::after { background: var(--success); }
        .p-step.active:not(:last-child)::after { background: hsl(231,80%,60%,0.3); }
        .p-circle {
            width: 32px; height: 32px;
            border-radius: 50%;
            border: 2px solid var(--border);
            background: var(--card);
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; color: var(--text-muted);
            z-index: 1; transition: all 0.25s;
        }
        .p-step.active .p-circle { border-color: var(--primary); background: var(--primary); color: #fff; box-shadow: 0 0 0 4px var(--primary-glow); }
        .p-step.done .p-circle { border-color: var(--success); background: var(--success); color: #fff; }
        .p-label { font-size: 10px; font-weight: 600; color: var(--text-light); white-space: nowrap; text-align: center; }
        .p-step.active .p-label { color: var(--primary); }
        .p-step.done .p-label { color: var(--success); }

        /* Header */
        .step-header { margin-bottom: 24px; }
        .step-header h1 { font-size: 26px; font-weight: 800; color: var(--text); letter-spacing: -0.4px; margin-bottom: 5px; }
        .step-header p { font-size: 14px; color: var(--text-muted); }

        /* Forms */
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--text-2); margin-bottom: 6px; }
        .form-label span { color: var(--danger); }
        .form-control {
            width: 100%; padding: 10px 14px;
            border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 14px; color: var(--text); background: var(--card);
            outline: none; transition: all 0.18s; font-family: var(--font); -webkit-appearance: none;
        }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-glow); }
        .form-control::placeholder { color: var(--text-light); }
        .is-invalid { border-color: var(--danger) !important; }
        .invalid-feedback { color: var(--danger); font-size: 12px; margin-top: 4px; font-weight: 500; }
        .input-wrap { position: relative; }
        .input-wrap .input-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 17px; height: 17px; color: var(--text-light); pointer-events: none; }
        .input-wrap .form-control { padding-left: 40px; }
        .form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

        select.form-control { padding-right: 32px; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; cursor: pointer; }

        /* Buttons */
        .btn-submit { width: 100%; padding: 13px 20px; background: linear-gradient(135deg, var(--primary), hsl(231,80%,55%)); color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; transition: all 0.18s; font-family: var(--font); display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: var(--shadow-primary); }
        .btn-submit:hover { box-shadow: 0 12px 32px hsl(231,80%,60%,0.45); transform: translateY(-1px); }
        .btn-prev { padding: 11px 20px; background: var(--card); border: 1.5px solid var(--border); border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; color: var(--text-2); font-family: var(--font); display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.18s; }
        .btn-prev:hover { border-color: var(--primary); color: var(--primary); }
        .action-row { display: flex; gap: 12px; margin-top: 4px; }
        .action-row .btn-submit { flex: 1; }

        .alert { padding: 12px 16px; border-radius: 8px; font-size: 13px; font-weight: 500; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .alert-error { background: hsl(350,88%,97%); color: hsl(350,88%,38%); border: 1px solid hsl(350,88%,55%,0.2); }
        .alert svg { width: 16px; height: 16px; flex-shrink: 0; }

        .auth-footer { text-align: center; margin-top: 20px; font-size: 13px; color: var(--text-muted); }
        .auth-footer a { color: var(--primary); font-weight: 700; text-decoration: none; }

        .terms-text { font-size: 12px; color: var(--text-muted); text-align: center; margin-top: 12px; line-height: 1.6; }
        .terms-text a { color: var(--primary); text-decoration: none; font-weight: 600; }

        @media (max-width: 900px) { .register-left { display: none; } }
        @media (max-width: 600px) { .form-row-2 { grid-template-columns: 1fr; } .register-right { padding: 24px 20px; } }
    </style>
</head>
<body>

<div class="register-left">
    <div class="brand">
        <div class="brand-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5" width="22" height="22">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div>
            <div class="brand-name">DocuScan</div>
            <div class="brand-sub">Pro SaaS</div>
        </div>
    </div>

    <div class="left-content">
        <div>
            <h2>Start scanning<br>in <span>2 minutes</span></h2>
            <p>Set up your company workspace and start digitising documents instantly.</p>
        </div>

        <div class="onboard-steps">
            <div class="onboard-step">
                <div class="step-num">1</div>
                <div class="step-text"><strong>Company Setup</strong>Create your company workspace</div>
            </div>
            <div class="onboard-step">
                <div class="step-num">2</div>
                <div class="step-text"><strong>Personal Profile</strong>Set up your admin account</div>
            </div>
            <div class="onboard-step">
                <div class="step-num">3</div>
                <div class="step-text"><strong>Start Scanning</strong>Digitise your first document</div>
            </div>
        </div>
    </div>

    <div class="left-footer">© {{ date('Y') }} DocuScan Pro. All rights reserved.</div>
</div>

<div class="register-right">
    <div class="auth-box">

        <!-- Progress Steps -->
        <div class="progress-steps" id="progressSteps">
            <div class="p-step active" id="pstep1">
                <div class="p-circle">1</div>
                <div class="p-label">Company</div>
            </div>
            <div class="p-step" id="pstep2">
                <div class="p-circle">2</div>
                <div class="p-label">Personal</div>
            </div>
            <div class="p-step" id="pstep3">
                <div class="p-circle">✓</div>
                <div class="p-label">Done</div>
            </div>
        </div>

        @if($errors->any())
        <div class="alert alert-error">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/></svg>
            {{ $errors->first() }}
        </div>
        @endif

        <form action="{{ route('register.post') }}" method="POST" id="registerForm">
            @csrf

            <!-- Step 1: Company -->
            <div id="step1">
                <div class="step-header">
                    <h1>Company Details</h1>
                    <p>Tell us about your company to set up your workspace.</p>
                </div>

                <div class="form-group">
                    <label class="form-label">Company Name <span>*</span></label>
                    <div class="input-wrap">
                        <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <input type="text" name="company_name" class="form-control {{ $errors->has('company_name') ? 'is-invalid' : '' }}"
                               placeholder="Acme Import & Export Co." value="{{ old('company_name') }}" required>
                        @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label class="form-label">Country <span>*</span></label>
                        <select name="country" class="form-control {{ $errors->has('country') ? 'is-invalid' : '' }}" required>
                            <option value="">Select country</option>
                            <option value="DZ" {{ old('country') === 'DZ' ? 'selected' : '' }}>🇩🇿 Algeria</option>
                            <option value="MA" {{ old('country') === 'MA' ? 'selected' : '' }}>🇲🇦 Morocco</option>
                            <option value="TN" {{ old('country') === 'TN' ? 'selected' : '' }}>🇹🇳 Tunisia</option>
                            <option value="EG" {{ old('country') === 'EG' ? 'selected' : '' }}>🇪🇬 Egypt</option>
                            <option value="FR" {{ old('country') === 'FR' ? 'selected' : '' }}>🇫🇷 France</option>
                            <option value="AE" {{ old('country') === 'AE' ? 'selected' : '' }}>🇦🇪 UAE</option>
                            <option value="SA" {{ old('country') === 'SA' ? 'selected' : '' }}>🇸🇦 Saudi Arabia</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Industry</label>
                        <select name="industry" class="form-control">
                            <option value="">Select industry</option>
                            <option value="import_export">Import / Export</option>
                            <option value="customs">Customs & Douane</option>
                            <option value="logistics">Logistics</option>
                            <option value="finance">Finance</option>
                            <option value="legal">Legal</option>
                            <option value="healthcare">Healthcare</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Company Size</label>
                    <select name="company_size" class="form-control">
                        <option value="1-10">1–10 employees</option>
                        <option value="11-50">11–50 employees</option>
                        <option value="51-200">51–200 employees</option>
                        <option value="201+">201+ employees</option>
                    </select>
                </div>

                <button type="button" class="btn-submit" onclick="goToStep2()">
                    Continue
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>

            <!-- Step 2: Personal Info -->
            <div id="step2" style="display:none;">
                <div class="step-header">
                    <h1>Your Profile</h1>
                    <p>Create your admin account credentials.</p>
                </div>

                <div class="form-group">
                    <label class="form-label">Full Name <span>*</span></label>
                    <div class="input-wrap">
                        <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               placeholder="Your full name" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address <span>*</span></label>
                    <div class="input-wrap">
                        <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                               placeholder="admin@company.com" value="{{ old('email') }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <div class="input-wrap">
                        <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <input type="tel" name="phone" class="form-control" placeholder="+213 XX XX XX XX" value="{{ old('phone') }}">
                    </div>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label class="form-label">Password <span>*</span></label>
                        <input type="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                               placeholder="Min. 8 characters" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password <span>*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat password" required>
                    </div>
                </div>

                <div class="action-row">
                    <button type="button" class="btn-prev" onclick="goToStep1()">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        Back
                    </button>
                    <button type="submit" class="btn-submit">
                        Create Account
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </button>
                </div>

                <p class="terms-text">
                    By creating an account, you agree to our
                    <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.
                </p>
            </div>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="{{ route('login') }}">Sign in →</a>
        </div>

    </div>
</div>

<script>
(function() {
    const saved = localStorage.getItem('docuscan-theme') || 'light';
    document.documentElement.setAttribute('data-theme', saved);
})();

function goToStep2() {
    const companyName = document.querySelector('[name="company_name"]').value.trim();
    if (!companyName) { alert('Please enter your company name.'); return; }

    document.getElementById('step1').style.display = 'none';
    document.getElementById('step2').style.display = 'block';

    document.getElementById('pstep1').className = 'p-step done';
    document.getElementById('pstep2').className = 'p-step active';
}

function goToStep1() {
    document.getElementById('step2').style.display = 'none';
    document.getElementById('step1').style.display = 'block';

    document.getElementById('pstep1').className = 'p-step active';
    document.getElementById('pstep2').className = 'p-step';
}

// Show step 2 if there were validation errors on step 2 fields
@if($errors->has('name') || $errors->has('email') || $errors->has('password'))
goToStep2();
@endif
</script>
</body>
</html>
