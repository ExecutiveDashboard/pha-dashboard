<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - PHAF I-16/3 Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Noto+Nastaliq+Urdu:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0; padding: 0; font-family: 'Inter', sans-serif;
            background-color: #ffffff; min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            overflow-x: hidden; position: relative;
        }
        /* The Purple Background Ring */
        .bg-ring {
            position: fixed;
            top: 2%;
            right: 15%;
            width: 320px;
            height: 320px;
            border: 45px solid #c4b5fd;
            border-radius: 50%;
            z-index: 0;
            opacity: 0.8;
        }
        
        .login-card {
            position: relative; z-index: 1;
            width: 100%; max-width: 400px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 40px 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        /* Faint Watermark inside card */
        .card-watermark {
            position: absolute;
            top: 55%; left: 50%;
            transform: translate(-50%, -50%);
            width: 480px; height: 480px;
            background: url('{{ asset('images/logos/pha-logo.svg') }}') center/contain no-repeat;
            opacity: 0.15; z-index: -1;
        }

        .header-logo {
            width: 90px; height: 90px;
            margin: 0 auto 16px; display: block;
            object-fit: contain;
        }
        
        .card-title {
            text-align: center; font-size: 16px; font-weight: 800; color: #000; margin-bottom: 4px;
        }
        .card-subtitle {
            text-align: center; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 24px;
        }

        /* Minimal Input Fields */
        .custom-input {
            width: 100%; border: none; border-bottom: 1.5px solid #cbd5e1;
            padding: 10px 4px; margin-bottom: 20px; font-size: 14px; color: #1e293b;
            background: transparent; outline: none; transition: border-color 0.2s;
        }
        .custom-input:focus { border-bottom-color: #10b981; }

        /* Buttons */
        .btn-pill {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%; border-radius: 50px; border: none;
            padding: 10px; font-weight: 700; font-size: 15px;
            transition: all 0.2s; cursor: pointer; text-decoration: none;
        }
        .btn-signin {
            background: #10b981; color: #fff; margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        .btn-signin:hover { background: #059669; color: #fff; transform: translateY(-1px); }
        
        .no-account-text {
            text-align: center; color: #64748b; font-size: 12.5px;
            margin-bottom: 6px; font-weight: 500; display: flex; flex-direction: column;
        }
        .urdu-text { font-family: 'Noto Nastaliq Urdu', serif; font-size: 15px; font-weight: 700; line-height: 1; }
        
        .btn-signup {
            background: #b4d36e; color: #fff; margin-bottom: 40px;
            box-shadow: 0 4px 12px rgba(180, 211, 110, 0.3);
        }
        .btn-signup:hover { background: #a3cc4e; color: #fff; transform: translateY(-1px); }

        /* Bottom Icons */
        .bottom-icons {
            display: flex; justify-content: center; gap: 30px;
        }
        .icon-item {
            display: flex; flex-direction: column; align-items: center; gap: 8px; text-decoration: none;
        }
        .icon-circle {
            width: 44px; height: 44px; border-radius: 50%;
            background: #10b981; color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
            transition: transform 0.2s;
        }
        .icon-item:hover .icon-circle { transform: translateY(-3px); background: #059669; }
        .icon-label {
            color: #475569; font-size: 10.5px; font-weight: 600;
        }

        .alert-error {
            background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 8px; font-size: 13px; margin-bottom: 15px; text-align: center;
        }
    </style>
</head>
<body>
    <!-- Background Design -->
    <div class="bg-ring"></div>

    <div class="login-card">
        <div class="card-watermark"></div>

        <img src="{{ asset('images/logos/pha-logo.svg') }}" alt="PHA Logo" class="header-logo">
        <h3 class="card-title">PHAF Maintenance Services</h3>
        <p class="card-subtitle">Ministry of Housing and Works</p>

        @if($errors->any())
            <div class="alert-error">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <!-- Inputs -->
            <input type="email" name="email" class="custom-input" placeholder="Email Address" required autofocus>
            <input type="password" name="password" class="custom-input" placeholder="Password" required>

            <!-- Sign In -->
            <button type="submit" class="btn-pill btn-signin">
                Sign In / <span class="urdu-text">سائن ان کریں</span>
            </button>
        </form>

        <!-- Sign Up Link (Admin doesn't have signup, so we link to portal or keep as visual placeholder) -->
        <div class="no-account-text">
            <span>Don't have an account?</span>
            <span class="urdu-text" style="color: #64748b; margin-top: 6px; font-size: 14px;">اکاؤنٹ نہیں ہے؟</span>
        </div>
        
        <a href="{{ route('portal.login') }}" class="btn-pill btn-signup">
            Sign Up / <span class="urdu-text">سائن اپ کریں</span>
        </a>

        <!-- Icons -->
        <div class="bottom-icons">
            <a href="#" class="icon-item">
                <div class="icon-circle"><i class="bi bi-calendar2-check"></i></div>
                <div class="icon-label">News/Events</div>
            </a>
            <a href="#" class="icon-item">
                <div class="icon-circle"><i class="bi bi-building"></i></div>
                <div class="icon-label">Projects</div>
            </a>
            <a href="#" class="icon-item">
                <div class="icon-circle"><i class="bi bi-telephone"></i></div>
                <div class="icon-label">Contact</div>
            </a>
        </div>
    </div>
</body>
</html>
