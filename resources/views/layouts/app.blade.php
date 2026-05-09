<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PHA Maintenance Dashboard') — I-16/3</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.0/dist/apexcharts.min.js"></script>
    <style>
        :root {
            --pha-green: #1B6B35;
            --pha-dark:  #0f4423;
            --pha-gold:  #C9A84C;
            --pha-light: #E8F5EE;
            --sidebar-w: 260px;
        }
        * { font-family: 'Inter', sans-serif; }
        body { background: #eef2f7; color: #1a2332; }

        /* ── SIDEBAR ── */
        .sidebar {
            position: fixed; top: 0; left: 0; height: 100vh;
            width: var(--sidebar-w);
            background: linear-gradient(175deg, #0a3018 0%, #0f4423 55%, #1a6332 100%);
            display: flex; flex-direction: column; z-index: 1000;
            box-shadow: 4px 0 24px rgba(0,0,0,0.25);
        }
        .sidebar-brand {
            padding: 18px 20px 14px;
            background: rgba(0,0,0,0.2);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            display: flex; flex-direction: column; align-items: center; gap: 8px;
        }
        .sidebar-brand .logos-row {
            display: flex; align-items: center; gap: 10px; justify-content: center;
        }
        .sidebar-brand .logos-row img {
            height: 38px; width: 38px; object-fit: contain;
            max-width: 38px; max-height: 38px; display: block; overflow: hidden;
        }
        /* Global SVG safety — prevent any uncontained SVG from going full-screen */
        svg:not([class]):not([style]) { max-width: 100%; }
        .pagination svg, nav svg { width: 14px !important; height: 14px !important; display: inline-block; vertical-align: middle; }
        .sidebar-brand h6 { color: #fff; font-weight: 700; font-size: 13px; margin: 0; line-height: 1.4; text-align: center; }
        .sidebar-brand small { color: rgba(255,255,255,0.5); font-size: 10px; text-align: center; }

        .sidebar-nav { flex: 1; padding: 14px 10px; overflow-y: auto; }
        .nav-section-title {
            font-size: 9.5px; letter-spacing: 1.8px; text-transform: uppercase;
            color: rgba(255,255,255,0.3); padding: 10px 10px 4px; font-weight: 600;
        }
        .sidebar-nav .nav-link {
            color: rgba(255,255,255,0.7); border-radius: 9px; padding: 9px 12px;
            font-size: 13px; font-weight: 500; display: flex; align-items: center;
            gap: 10px; transition: all 0.2s ease; margin-bottom: 2px;
        }
        .sidebar-nav .nav-link:hover {
            background: rgba(255,255,255,0.1); color: #fff; padding-left: 16px;
        }
        .sidebar-nav .nav-link.active {
            background: rgba(255,255,255,0.15);
            box-shadow: inset 3px 0 0 #C9A84C;
            color: #fff; font-weight: 700;
        }
        .sidebar-nav .nav-link i { font-size: 15px; width: 20px; text-align: center; }

        .sidebar-footer { padding: 14px 18px; border-top: 1px solid rgba(255,255,255,0.08); }
        .sidebar-footer .user-info { color: rgba(255,255,255,0.6); font-size: 11px; }
        .sidebar-footer .user-name { color: #fff; font-weight: 600; font-size: 13px; }

        /* ── MAIN ── */
        .main-content { margin-left: var(--sidebar-w); min-height: 100vh; }
        .topbar {
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(10px);
            padding: 12px 26px;
            border-bottom: 1px solid rgba(226,232,240,0.9);
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 1px 10px rgba(15,68,35,0.06);
        }
        .topbar h5 { margin: 0; font-weight: 800; color: #1a2332; font-size: 15px; letter-spacing: -0.2px; }
        .page-body { padding: 22px 24px; animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: none; } }

        /* ── KPI STRIP ── */
        .kpi-strip { display: flex; flex-wrap: wrap; gap: 8px; }
        .kpi-pill {
            flex: 1; min-width: 130px; border-radius: 12px;
            padding: 11px 14px; color: #fff; text-align: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.13);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .kpi-pill:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,0.2); }
        .kpi-pill .pill-val { font-size: 18px; font-weight: 800; line-height: 1.2; }
        .kpi-pill .pill-lbl { font-size: 10px; font-weight: 500; opacity: 0.9; margin-top: 2px; }
        .kpi-pill small { font-size: 9px; }
        .pill-blue   { background: #2563eb; }
        .pill-green  { background: #1B6B35; }
        .pill-teal   { background: #0d9488; }
        .pill-orange { background: #d97706; }
        .pill-purple { background: #7c3aed; }
        .pill-indigo { background: #4338ca; }
        .pill-red    { background: #dc2626; }

        /* ── KPI CARDS ── */
        .kpi-card {
            background: #fff; border-radius: 14px; padding: 20px 22px;
            border: 1px solid rgba(226,232,240,0.8);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.2s, box-shadow 0.2s; height: 100%;
        }
        .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(15,68,35,0.11); }
        .kpi-card .kpi-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 12px; }
        .kpi-card .kpi-value { font-size: 24px; font-weight: 800; color: #1a2332; line-height: 1; }
        .kpi-card .kpi-label { font-size: 12px; color: #64748b; font-weight: 500; margin-top: 4px; }
        .kpi-card .kpi-sub   { font-size: 11px; color: #94a3b8; margin-top: 5px; }
        .icon-green  { background: #dcfce7; color: var(--pha-green); }
        .icon-blue   { background: #dbeafe; color: #2563eb; }
        .icon-amber  { background: #fef3c7; color: #d97706; }
        .icon-red    { background: #fee2e2; color: #dc2626; }
        .icon-purple { background: #ede9fe; color: #7c3aed; }
        .icon-teal   { background: #ccfbf1; color: #0d9488; }

        /* ── CHART CARDS ── */
        .chart-card {
            background: #fff; border-radius: 14px; padding: 18px;
            border: 1px solid rgba(226,232,240,0.8);
            box-shadow: 0 1px 6px rgba(0,0,0,0.05), 0 4px 16px rgba(15,68,35,0.04);
            position: relative; overflow: hidden;
            transition: box-shadow 0.2s;
        }
        .chart-card:hover { box-shadow: 0 4px 20px rgba(15,68,35,0.10); }
        .chart-card h6 { font-size: 13px; font-weight: 700; color: #1a2332; margin-bottom: 2px; }
        .chart-card .chart-sub { font-size: 11px; color: #94a3b8; margin-bottom: 14px; }
        .section-title { font-size: 13px; font-weight: 700; color: #1a2332; margin-bottom: 10px; display: flex; align-items: center; gap: 6px; }
        .badge-policy { background: #f0f4f8; color: #64748b; font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 20px; }

        /* ── W&W BOXES ── */
        .ww-box { border-radius: 10px; padding: 10px 8px; text-align: center; border: 2px solid; }
        .ww-box-title { font-size: 9px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 4px; }
        .ww-box-sub   { font-size: 9px; margin-bottom: 6px; }
        .ww-box-count { font-size: 20px; font-weight: 900; }
        .ww-box-pct   { font-size: 11px; font-weight: 600; }
        .ww-box-amt   { font-size: 10px; font-weight: 700; margin-top: 4px; }
        .ww-before { background: #fef9c3; border-color: #f59e0b; color: #92400e; }
        .ww-after  { background: #dcfce7; border-color: #1B6B35; color: #1B6B35; }
        .ww-null   { background: #ede9fe; border-color: #7c3aed; color: #5b21b6; }

        /* ── FORMULA / GRAND TOTAL / POLICY BOXES ── */
        .formula-box { background: #f0f9f4; border-left: 3px solid #1B6B35; padding: 8px 12px; border-radius: 0 8px 8px 0; font-size: 11px; color: #1a2332; }
        .grand-total-box { background: linear-gradient(135deg, #1B6B35, #0f4423); border-radius: 10px; padding: 12px 16px; }
        .policy-box { background: #f8fafc; border-radius: 10px; padding: 12px; color: #334155; }
        .policy-box p { margin-bottom: 10px; }

        /* ── TABLE ── */
        .data-table { border-radius: 12px; overflow: hidden; }
        .data-table thead th {
            background: linear-gradient(90deg, #0f4423, #1B6B35);
            color: #fff; font-size: 11px; font-weight: 700;
            letter-spacing: 0.5px; padding: 11px 13px; border: none;
            text-transform: uppercase;
        }
        .data-table tbody td { padding: 10px 13px; font-size: 12px; vertical-align: middle; border-color: #f1f5f9; }
        .data-table tbody tr { transition: background 0.12s; }
        .data-table tbody tr:hover { background: #f0f9f4; box-shadow: inset 3px 0 0 #1B6B35; }
        .data-table tbody tr:nth-child(even) { background: #fafbfc; }
        .badge-b { background: #dbeafe; color: #1d4ed8; }
        .badge-e { background: #dcfce7; color: #166534; }
        .fw-600 { font-weight: 600; }
        .fw-700 { font-weight: 700; }

        /* ── DEFAULTER RANK ── */
        .defaulter-rank { width: 26px; height: 26px; border-radius: 50%; background: var(--pha-dark); color: #fff; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; }
        .defaulter-rank.rank-1 { background: #f59e0b; }
        .defaulter-rank.rank-2 { background: #94a3b8; }
        .defaulter-rank.rank-3 { background: #cd7c2f; }

        /* ── SECTION HEADING ── */
        .section-heading { font-size: 15px; font-weight: 800; color: #0f4423; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; letter-spacing: -0.2px; }
        .section-heading::after { content: ''; flex: 1; height: 2px; background: linear-gradient(90deg, rgba(27,107,53,0.2), transparent); }

        /* ── BUTTONS ── */
        .btn { border-radius: 8px; transition: all 0.18s ease; }
        .btn:hover { transform: translateY(-1px); }
        .btn:active { transform: scale(0.97); }
        .btn-success { box-shadow: 0 2px 8px rgba(27,107,53,0.22); }
        .btn-success:hover { box-shadow: 0 4px 14px rgba(27,107,53,0.35); }
        .btn-danger { box-shadow: 0 2px 8px rgba(220,38,38,0.18); }
        .btn-danger:hover { box-shadow: 0 4px 12px rgba(220,38,38,0.3); }
        .btn-outline-secondary { border-color: #d1d5db; color: #4b5563; }
        .btn-outline-secondary:hover { background: #f9fafb; border-color: #9ca3af; color: #1a2332; box-shadow: 0 2px 8px rgba(0,0,0,0.07); }
        .btn-outline-light:hover { background: rgba(255,255,255,0.1); }

        /* ── PAGINATION — custom, no Bootstrap dependency ── */
        .pha-pagination { display:flex; align-items:center; flex-wrap:wrap; gap:5px; margin-top:14px; }
        .pha-btn-page {
            display:inline-flex; align-items:center; gap:5px; padding:7px 16px;
            border-radius:8px; font-size:13px; font-weight:600; text-decoration:none !important;
            background:linear-gradient(135deg,#1B6B35,#0f4423); color:#fff !important; border:none;
            box-shadow:0 3px 10px rgba(15,68,35,0.28); transition:all 0.18s ease; cursor:pointer;
        }
        .pha-btn-page:hover:not(.disabled) { background:linear-gradient(135deg,#22853f,#1B6B35); box-shadow:0 5px 16px rgba(15,68,35,0.4); transform:translateY(-1px); color:#fff !important; }
        .pha-btn-page.disabled { background:#e9ecef; color:#9ca3af !important; box-shadow:none; cursor:not-allowed; opacity:0.65; }
        .pha-pg {
            display:inline-flex; align-items:center; justify-content:center; min-width:34px; height:34px;
            padding:0 6px; border-radius:7px; font-size:13px; font-weight:500;
            text-decoration:none !important; border:1.5px solid #e2e8f0; background:#fff;
            color:#374151 !important; transition:all 0.15s ease; box-shadow:0 1px 3px rgba(0,0,0,0.05);
        }
        .pha-pg:hover:not(.pha-pg-active) { background:#f0f9f4; border-color:#1B6B35; color:#1B6B35 !important; transform:translateY(-1px); box-shadow:0 3px 8px rgba(27,107,53,0.15); text-decoration:none !important; }
        .pha-pg-active { background:linear-gradient(135deg,#1B6B35,#0f4423) !important; border-color:#0f4423 !important; color:#fff !important; font-weight:700; box-shadow:0 3px 10px rgba(15,68,35,0.3); cursor:default; }
        .pha-pg-dot { color:#94a3b8; font-weight:700; padding:0 4px; font-size:14px; }
        .pha-pagination-meta { font-size:12px; color:#64748b; margin-top:6px; }
        .pha-pagination-meta strong { color:#1B6B35; font-weight:700; }


        /* ── FORM CONTROLS ── */
        .form-control, .form-select { border-radius: 8px; border: 1.5px solid #e2e8f0; transition: border 0.15s, box-shadow 0.15s; }
        .form-control:focus, .form-select:focus { border-color: #1B6B35 !important; box-shadow: 0 0 0 3px rgba(27,107,53,0.1) !important; outline: none; }

        /* ── ALERTS ── */
        .flash-success { background: linear-gradient(135deg,#f0fdf4,#dcfce7); color: #166534; border: 1px solid #86efac; border-radius: 10px; padding: 10px 14px; box-shadow: 0 2px 8px rgba(27,107,53,0.08); }

        /* ── SCROLLBAR ── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
        ::-webkit-scrollbar-thumb:hover { background: #1B6B35; }
    </style>
    @stack('styles')
</head>
<body>
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="logos-row">
                <img src="{{ asset('images/logos/govt-pk.svg') }}" alt="Govt of Pakistan" title="Government of Pakistan">
                <img src="{{ asset('images/logos/pha-logo.svg') }}" alt="PHA Foundation" title="PHA Foundation">
            </div>
            <h6>Punjab Housing Authority</h6>
            <small>Ministry of Housing & Works, Islamabad</small>
            <small style="color:rgba(255,255,255,0.35);">I-16/3 Maintenance Dashboard</small>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section-title">Main</div>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i> Overview
            </a>
            <a href="{{ route('allottees.index') }}" class="nav-link {{ request()->routeIs('allottees.*') && !request()->routeIs('allottees.*defaulter*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> Allottee List
            </a>
            <a href="{{ route('allottees.index', ['defaulter' => 1]) }}" class="nav-link {{ request()->get('defaulter') == 1 ? 'active' : '' }}">
                <i class="bi bi-exclamation-triangle-fill"></i> Defaulters
            </a>

            <div class="nav-section-title mt-3">Billing</div>
            <a href="{{ route('bills.search') }}" class="nav-link {{ request()->routeIs('bills.search') ? 'active' : '' }}">
                <i class="bi bi-search"></i> Bill Search
            </a>
            <a href="{{ route('allottees.index') }}" class="nav-link" title="Go to Allottees and click View to generate bill">
                <i class="bi bi-receipt"></i> Generate Bill
            </a>

            <div class="nav-section-title mt-3">Portal</div>
            <a href="{{ route('portal.login') }}" class="nav-link {{ request()->routeIs('portal.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge-fill"></i> Allottee Portal
            </a>

            <div class="nav-section-title mt-3">Management</div>
            <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <i class="bi bi-sliders2"></i> Settings & Criteria
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info">Logged in as</div>
            <div class="user-name">{{ Auth::user()->name }}</div>
            <form action="{{ route('logout') }}" method="POST" class="mt-2">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light w-100" style="font-size:11px;">
                    <i class="bi bi-box-arrow-right"></i> Sign Out
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN -->
    <div class="main-content">
        <div class="topbar">
            <h5>@yield('page-title', 'Dashboard')</h5>
            <div class="d-flex align-items-center gap-3">
                <span class="badge" style="background:#dcfce7;color:#166534;font-size:11px;padding:5px 10px;">
                    <i class="bi bi-circle-fill me-1" style="font-size:7px;"></i> Live Data
                </span>
                <span style="font-size:11px;color:#94a3b8;">Data as on: {{ now()->format('d M Y') }}</span>
            </div>
        </div>

        <div class="page-body">
            @if(session('success'))
                <div class="flash-success mb-3"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger mb-3" style="border-radius:10px;">{{ session('error') }}</div>
            @endif
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
