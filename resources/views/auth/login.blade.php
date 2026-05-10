<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - PHAF I-16/3 Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            min-height: 100vh;
            background: url('{{ asset('images/bg/login-bg.png') }}') center/cover no-repeat fixed;
            display: flex; align-items: center; justify-content: center;
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
        .login-header h2 { font-weight: 800; font-size: 28px; margin-bottom: 6px; }
        .login-header p { color: rgba(255,255,255,0.8); font-size: 14px; font-weight: 500; }
        .login-card {
            background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px);
            border-radius: 24px; padding: 40px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.4);
            border: 1px solid rgba(255,255,255,0.6);
        }
        .login-card h5 { font-weight: 700; color: #1a2332; font-size: 18px; margin-bottom: 6px; }
        .login-card .subtitle { color: #64748b; font-size: 13.5px; margin-bottom: 28px; }
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
            transition: all 0.2s; letter-spacing: 0.3px;
        }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(27,107,53,0.35); color: #fff; }
        .footer-note { text-align: center; margin-top: 24px; color: rgba(255,255,255,0.6); font-size: 12px; }
        .input-group-text { background: #f8fafc; border: 1.5px solid #e2e8f0; border-right: none; border-radius: 10px 0 0 10px; }
        .input-group .form-control { border-left: none; border-radius: 0 10px 10px 0; }
        .error-msg { background: #fee2e2; color: #991b1b; border-radius: 10px; padding: 12px 14px; font-size: 13px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-header">
            <div style="display:flex;justify-content:center;align-items:center;gap:20px;margin-bottom:20px;">
                <div style="background:#fff;border-radius:50%;width:64px;height:64px;display:flex;align-items:center;justify-content:center;padding:5px;box-shadow:0 4px 15px rgba(0,0,0,0.2);">
                    <img src="{{ asset('images/logos/pha-logo.svg') }}" alt="PHA Logo" style="width:50px;height:50px;object-fit:contain;">
                </div>
                <div style="background:#fff;border-radius:50%;width:64px;height:64px;display:flex;align-items:center;justify-content:center;padding:5px;box-shadow:0 4px 15px rgba(0,0,0,0.2);">
                    <img src="{{ asset('images/logos/govt-pk.svg') }}" alt="Govt Logo" style="width:50px;height:50px;object-fit:contain;">
                </div>
            </div>
            <h2 style="font-weight:900;letter-spacing:1px;font-size:32px;">PHA Foundation</h2>
            <div class="pha-badge" style="margin-top:10px;">MAINTENANCE DASHBOARD</div>
            <p>Ministry of Housing & Works — I-16/3 Islamabad</p>
        </div>

        <div class="login-card">
            <h5>Welcome Back</h5>
            <p class="subtitle">Sign in to access the maintenance dashboard</p>

            @if($errors->any())
                <div class="error-msg"><i class="bi bi-exclamation-circle me-2"></i>{{ $errors->first() }}</div>
            @endif

            <form action="/login" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope" style="color:#64748b;"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="admin@pha.gov.pk"
                               value="{{ old('email') }}" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock" style="color:#64748b;"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                    </div>
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember" style="font-size:13px;color:#64748b;">Remember me</label>
                </div>
                <button type="submit" class="btn-login btn">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </button>
            </form>
        </div>

        <p class="footer-note">© {{ date('Y') }} PHA Foundation. All rights reserved.</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
