<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Allottee Portal Login - PHA Foundation</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            min-height: 100vh;
            background: url('{{ asset('images/bg/login-bg.png') }}') center/cover no-repeat fixed;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 20px;
            position: relative;
        }
        body::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, rgba(15,68,35,0.85) 0%, rgba(27,107,53,0.7) 100%);
            z-index: 1;
        }
        .login-wrapper { width: 100%; max-width: 440px; position: relative; z-index: 2; }
        .login-header { text-align: center; margin-bottom: 32px; color: #fff; }
        .login-header .pha-badge {
            background: rgba(255,255,255,0.15); backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.25);
            color: #fff; font-weight: 700; font-size: 13px; letter-spacing: 2px;
            padding: 6px 18px; border-radius: 50px; display: inline-block; margin-bottom: 16px;
        }
        .login-header h2 { font-weight: 900; font-size: 32px; margin-bottom: 6px; letter-spacing: 1px; }
        .login-header p { color: rgba(255,255,255,0.8); font-size: 14px; font-weight: 500; }
        .login-card {
            background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px);
            border-radius: 24px; padding: 40px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.4);
            border: 1px solid rgba(255,255,255,0.6);
        }
        .login-card h5 { font-weight: 800; color: #1a2332; font-size: 20px; margin-bottom: 6px; text-align: center; }
        .login-card .subtitle { color: #64748b; font-size: 13.5px; margin-bottom: 28px; text-align: center; }
        .form-label { font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
        .form-control {
            border: 1.5px solid #e2e8f0; border-radius: 10px;
            padding: 11px 14px; font-size: 14px; transition: all 0.2s;
        }
        .form-control:focus {
            border-color: #1B6B35; box-shadow: 0 0 0 3px rgba(27,107,53,0.12);
        }
        .btn-login {
            background: linear-gradient(135deg, #1B6B35, #2d8a4e);
            border: none; color: #fff; font-weight: 700; font-size: 15px;
            padding: 13px; border-radius: 10px; width: 100%;
            transition: all 0.2s; letter-spacing: 0.3px; margin-top: 10px;
        }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(27,107,53,0.35); color: #fff; }
        .input-group-text { background: #f8fafc; border: 1.5px solid #e2e8f0; border-right: none; border-radius: 10px 0 0 10px; }
        .input-group .form-control { border-left: none; border-radius: 0 10px 10px 0; }
        .error-msg { background: #fee2e2; color: #991b1b; border-radius: 10px; padding: 12px 14px; font-size: 13px; margin-bottom: 20px; }
        
        .divider { text-align: center; color: #9ca3af; font-size: 12px; margin: 24px 0 16px; position: relative; }
        .divider::before, .divider::after { content: ''; position: absolute; top: 50%; width: 35%; height: 1px; background: #e2e8f0; }
        .divider::before { left: 0; }
        .divider::after { right: 0; }
        .no-account { text-align: center; color: #6b7280; font-size: 13px; }

        /* Bottom nav */
        .bottom-nav {
            display: flex; gap: 32px; justify-content: center;
            margin-top: 40px;
        }
        .bottom-nav-item { text-align: center; color: rgba(255,255,255,0.8); text-decoration: none; }
        .bottom-nav-item .icon-circle {
            width: 48px; height: 48px; border-radius: 50%;
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 8px; font-size: 20px; color: #fff;
            transition: all 0.2s;
        }
        .bottom-nav-item:hover .icon-circle { background: rgba(255,255,255,0.2); transform: translateY(-2px); }
        .bottom-nav-item span { font-size: 12px; font-weight: 500; }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-header">
            <h2 style="font-weight:900;letter-spacing:1px;font-size:32px;">PHA Foundation</h2>
            <div class="pha-badge" style="margin-top:10px;">ALLOTTEE PORTAL</div>
            <p>Ministry of Housing & Works — I-16/3 Islamabad</p>
        </div>

        <div class="login-card">
            <div style="display:flex;justify-content:center;align-items:center;gap:16px;margin-bottom:24px;">
                <img src="{{ asset('images/logos/pha-logo.svg') }}" alt="PHA Logo" style="width:52px;height:52px;object-fit:contain; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
                <img src="{{ asset('images/logos/govt-pk.svg') }}" alt="Govt Logo" style="width:52px;height:52px;object-fit:contain; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
            </div>
            <h5>Welcome</h5>
            <p class="subtitle">Sign in to view your maintenance bills & status</p>

            @if(session('success'))
                <div class="alert alert-success mb-3" style="border-radius: 10px; font-size: 13px;">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="error-msg"><i class="bi bi-exclamation-circle me-2"></i>{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('portal.login.post') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">CNIC Number</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-vcard" style="color:#64748b;"></i></span>
                        <input type="text" name="cnic" class="form-control" placeholder="e.g. 3740512345678"
                               value="{{ old('cnic') }}" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Registered Mobile Number</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-phone" style="color:#64748b;"></i></span>
                        <input type="text" name="cell" class="form-control" placeholder="e.g. 03001234567"
                               value="{{ old('cell') }}" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-login">
                    Sign In
                </button>
            </form>

            <div class="divider">OR</div>
            <div class="no-account">
                Having trouble logging in?<br>
                Please contact the PHAF Office for assistance.
            </div>
        </div>

        <!-- Bottom Navigation -->
        <div class="bottom-nav">
            <a href="#" class="bottom-nav-item">
                <div class="icon-circle"><i class="bi bi-calendar-check"></i></div>
                <span>News/Events</span>
            </a>
            <a href="#" class="bottom-nav-item">
                <div class="icon-circle"><i class="bi bi-buildings"></i></div>
                <span>Projects</span>
            </a>
            <a href="#" class="bottom-nav-item">
                <div class="icon-circle"><i class="bi bi-telephone"></i></div>
                <span>Contact Us</span>
            </a>
        </div>
    </div>
</body>
</html>
