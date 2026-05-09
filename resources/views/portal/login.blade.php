<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHA Allottee Portal — Sign In</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            background: linear-gradient(145deg, #0f4423 0%, #1B6B35 50%, #2d8a4e 100%);
            min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center;
        }
        /* Government header bar */
        .gov-header {
            width: 100%; background: rgba(255,255,255,0.08); backdrop-filter: blur(10px);
            padding: 10px 24px; display: flex; align-items: center; justify-content: center;
            gap: 20px; position: fixed; top: 0; left: 0; border-bottom: 1px solid rgba(255,255,255,0.15);
        }
        .gov-header img { height: 40px; width: 40px; object-fit: contain; }
        .gov-header .gov-text { color: #fff; text-align: center; }
        .gov-header .gov-text .line1 { font-size: 13px; font-weight: 700; }
        .gov-header .gov-text .line2 { font-size: 10px; opacity: 0.8; }

        /* Login card */
        .portal-wrap { padding-top: 80px; width: 100%; max-width: 420px; padding-left: 16px; padding-right: 16px; }
        .portal-card {
            background: #fff; border-radius: 20px; padding: 36px 32px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .logo-circle {
            width: 80px; height: 80px; border-radius: 50%;
            background: #f0f9f4; border: 3px solid #1B6B35;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px; overflow: hidden;
        }
        .logo-circle img { width: 56px; height: 56px; object-fit: contain; }
        .portal-title { text-align: center; margin-bottom: 24px; }
        .portal-title h4 { font-weight: 800; color: #1a2332; font-size: 18px; margin-bottom: 4px; }
        .portal-title .urdu { font-size: 14px; color: #1B6B35; font-weight: 600; direction: rtl; }
        .form-label { font-weight: 600; font-size: 13px; color: #374151; margin-bottom: 4px; }
        .form-label .urdu-label { color: #1B6B35; font-size: 12px; direction: rtl; }
        .form-control { border-radius: 10px; border: 1.5px solid #d1d5db; padding: 10px 14px; font-size: 14px; }
        .form-control:focus { border-color: #1B6B35; box-shadow: 0 0 0 3px rgba(27,107,53,0.15); }
        .btn-portal {
            background: linear-gradient(135deg, #1B6B35, #2d8a4e);
            color: #fff; border: none; border-radius: 12px;
            padding: 12px; font-size: 15px; font-weight: 700; width: 100%;
            transition: all 0.3s;
        }
        .btn-portal:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(27,107,53,0.4); color: #fff; }
        .divider { text-align: center; color: #9ca3af; font-size: 12px; margin: 16px 0; }
        .no-account { text-align: center; color: #6b7280; font-size: 12px; }
        .no-account .urdu { direction: rtl; display: block; color: #9ca3af; font-size: 11px; }

        /* Bottom nav */
        .bottom-nav {
            display: flex; gap: 24px; justify-content: center;
            margin-top: 24px; padding-bottom: 24px;
        }
        .bottom-nav-item { text-align: center; color: rgba(255,255,255,0.8); text-decoration: none; }
        .bottom-nav-item .icon-circle {
            width: 52px; height: 52px; border-radius: 50%;
            background: #1B6B35; border: 2px solid rgba(255,255,255,0.3);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 6px; font-size: 22px; color: #fff;
            transition: all 0.2s;
        }
        .bottom-nav-item:hover .icon-circle { background: #2d8a4e; transform: translateY(-2px); }
        .bottom-nav-item span { font-size: 11px; font-weight: 500; }

        .alert { border-radius: 10px; font-size: 13px; }
    </style>
</head>
<body>

    <!-- Government Header -->
    <div class="gov-header">
        <img src="{{ asset('images/logos/govt-pk.svg') }}" alt="Govt of Pakistan">
        <div class="gov-text">
            <div class="line1">Government of Pakistan — Ministry of Housing & Works</div>
            <div class="line2">Punjab Housing Authority Foundation — I-16/3 Islamabad</div>
        </div>
        <img src="{{ asset('images/logos/pha-logo.svg') }}" alt="PHA Foundation">
    </div>

    <div class="portal-wrap">
        <div class="portal-card">
            <!-- Logo -->
            <div class="logo-circle">
                <img src="{{ asset('images/logos/pha-logo.svg') }}" alt="PHA">
            </div>

            <div class="portal-title">
                <h4>PHA Maintenance Services</h4>
                <div class="urdu">پی ایچ اے مینٹیننس سروسز</div>
            </div>

            @if(session('success'))
                <div class="alert alert-success mb-3">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('portal.login.post') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">
                        CNIC <span class="urdu-label">/ قومی شناختی کارڈ نمبر</span>
                    </label>
                    <input type="text" name="cnic" class="form-control @error('cnic') is-invalid @enderror"
                           placeholder="e.g. 3740512345678" value="{{ old('cnic') }}" required>
                    @error('cnic')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label">
                        Mobile Number <span class="urdu-label">/ موبائل نمبر</span>
                    </label>
                    <input type="text" name="cell" class="form-control @error('cell') is-invalid @enderror"
                           placeholder="e.g. 03001234567" value="{{ old('cell') }}" required>
                    @error('cell')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn-portal">
                    Sign In / سائن ان کریں —
                </button>
            </form>

            <div class="divider">— OR —</div>
            <div class="no-account">
                Don't have an account? Contact PHA Office
                <span class="urdu">اکاؤنٹ نہیں ہے؟ PHA دفتر سے رابطہ کریں</span>
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
                <span>Contact</span>
            </a>
        </div>
    </div>

</body>
</html>
