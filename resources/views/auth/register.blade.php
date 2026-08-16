<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Company — DocuScan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --primary: #4f46e5; --primary-dark: #3730a3; --danger: #ef4444; --border: #e2e8f0; --text: #1e293b; --text-muted: #64748b; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; min-height: 100vh; display: flex; align-items: flex-start; justify-content: center; padding: 40px 20px; }
        .container { width: 100%; max-width: 680px; }
        .brand { display: flex; align-items: center; gap: 10px; margin-bottom: 28px; text-decoration: none; }
        .brand-icon { width: 38px; height: 38px; background: linear-gradient(135deg, #4f46e5, #06b6d4); border-radius: 10px; display: flex; align-items: center; justify-content: center; }
        .brand-name { font-size: 20px; font-weight: 800; color: var(--text); }
        .card { background: #fff; border: 1px solid var(--border); border-radius: 14px; padding: 36px; box-shadow: 0 4px 6px -1px rgba(0,0,0,.06); }
        .card h1 { font-size: 24px; font-weight: 800; color: var(--text); margin-bottom: 4px; }
        .card .subtitle { font-size: 14px; color: var(--text-muted); margin-bottom: 28px; }
        .section-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .7px; color: var(--text-muted); margin: 24px 0 14px; padding-bottom: 8px; border-bottom: 1px solid var(--border); }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-group { margin-bottom: 14px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 6px; }
        .form-control { width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: 8px; font-size: 14px; color: var(--text); outline: none; transition: border-color .15s; font-family: inherit; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79,70,229,.1); }
        .form-control::placeholder { color: #94a3b8; }
        .is-invalid { border-color: var(--danger) !important; }
        .invalid-feedback { color: var(--danger); font-size: 12px; margin-top: 4px; }
        .btn-full { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 12px 20px; background: var(--primary); color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; transition: background .15s; font-family: inherit; margin-top: 24px; }
        .btn-full:hover { background: var(--primary-dark); }
        .alert { padding: 12px 16px; border-radius: 8px; font-size: 13.5px; font-weight: 500; margin-bottom: 20px; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        @media (max-width: 600px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<div class="container">
    <a href="{{ route('landing') }}" class="brand">
        <div class="brand-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <span class="brand-name">DocuScan</span>
    </a>

    <div class="card">
        <h1>Create Company Account</h1>
        <p class="subtitle">Set up your workspace — free to start, no credit card required.</p>

        @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
            <div>• {{ $error }}</div>
            @endforeach
        </div>
        @endif

        <form action="{{ route('register.post') }}" method="POST">
            @csrf

            <div class="section-title">🏢 Company Information</div>
            <div class="grid">
                <div class="form-group">
                    <label class="form-label">Company Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="company_name" class="form-control {{ $errors->has('company_name') ? 'is-invalid' : '' }}"
                           placeholder="Global Import & Export SARL" value="{{ old('company_name') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Company Email <span style="color:var(--danger)">*</span></label>
                    <input type="email" name="company_email" class="form-control {{ $errors->has('company_email') ? 'is-invalid' : '' }}"
                           placeholder="contact@company.com" value="{{ old('company_email') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="company_phone" class="form-control" placeholder="+212 600-000000" value="{{ old('company_phone') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Country</label>
                    <input type="text" name="company_country" class="form-control" placeholder="Morocco" value="{{ old('company_country') }}">
                </div>
            </div>

            <div class="section-title">👤 Owner Account</div>
            <div class="grid">
                <div class="form-group">
                    <label class="form-label">Full Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                           placeholder="Ahmed Benali" value="{{ old('name') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address <span style="color:var(--danger)">*</span></label>
                    <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                           placeholder="ahmed@company.com" value="{{ old('email') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password <span style="color:var(--danger)">*</span></label>
                    <input type="password" name="password" class="form-control" placeholder="Min. 8 characters" required minlength="8">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password <span style="color:var(--danger)">*</span></label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat password" required>
                </div>
            </div>

            <button type="submit" class="btn-full">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Create Account — It's Free!
            </button>
        </form>

        <p style="text-align:center;font-size:14px;color:var(--text-muted);margin-top:20px;">
            Already have an account?
            <a href="{{ route('login') }}" style="color:var(--primary);font-weight:600;text-decoration:none;">Sign in →</a>
        </p>
    </div>
</div>

</body>
</html>
