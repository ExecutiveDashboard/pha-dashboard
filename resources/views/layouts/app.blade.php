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
            background: #0b2114; /* Deep premium forest green */
            background-image: radial-gradient(circle at top right, rgba(27, 107, 53, 0.15) 0%, transparent 40%),
                              radial-gradient(circle at bottom left, rgba(201, 168, 76, 0.05) 0%, transparent 40%);
            border-right: 1px solid rgba(255,255,255,0.05);
            display: flex; flex-direction: column; z-index: 1000;
            box-shadow: 4px 0 24px rgba(0,0,0,0.25);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        body.sidebar-collapsed .sidebar { transform: translateX(-100%); }
        .sidebar-brand {
            padding: 28px 20px 22px;
            background: linear-gradient(135deg, rgba(27, 107, 53, 0.28) 0%, rgba(15, 68, 35, 0.45) 100%);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.18);
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            gap: 14px;
            transition: all 0.3s ease;
        }
        .sidebar-logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 50px;
            padding: 8px 18px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2), inset 0 1px 2px rgba(255, 255, 255, 0.8);
            gap: 12px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar-logo-container:hover {
            transform: scale(1.02);
            background: #ffffff;
            box-shadow: 0 6px 20px rgba(27, 107, 53, 0.35), 0 0 12px rgba(255, 255, 255, 0.25);
            border-color: #ffffff;
        }
        .sidebar-logo-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
        }
        .sidebar-logo-wrapper img {
            width: 32px;
            height: 32px;
            object-fit: contain;
            display: block;
        }
        .sidebar-logo-divider {
            width: 1px;
            height: 20px;
            background-color: rgba(0, 0, 0, 0.15);
        }
        .sidebar-brand h6 { 
            color: #ffffff; 
            font-weight: 700; 
            font-size: 15.5px; 
            margin: 0; 
            letter-spacing: 1.2px; 
            text-transform: uppercase;
            text-align: center; 
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        .sidebar-brand small { 
            color: rgba(255, 255, 255, 0.65); 
            font-size: 10.5px; 
            text-align: center; 
            font-weight: 500; 
            letter-spacing: 0.5px;
            margin-top: 2px;
        }
        
        .project-switcher-btn {
            background: rgba(255,255,255,0.06) !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            color: #f8fafc !important;
            border-radius: 8px !important;
            font-size: 11.5px !important;
            padding: 8px 12px !important;
            transition: all 0.2s ease !important;
        }
        .project-switcher-btn:hover {
            background: rgba(255,255,255,0.12) !important;
            border-color: rgba(255,255,255,0.2) !important;
        }

        .sidebar-nav { flex: 1; padding: 20px 14px; overflow-y: auto; }
        .nav-section-title {
            font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase;
            color: #64748b; padding: 12px 10px 6px; font-weight: 700;
        }
        .sidebar-nav .nav-link {
            color: #cbd5e1; border-radius: 10px; padding: 10px 14px;
            font-size: 13px; font-weight: 500; display: flex; align-items: center;
            gap: 12px; transition: all 0.2s ease; margin-bottom: 4px;
            border: 1px solid transparent;
        }
        .sidebar-nav .nav-link:hover {
            background: rgba(255,255,255,0.06); color: #fff;
            transform: translateX(4px);
        }
        .sidebar-nav .nav-link.active {
            background: linear-gradient(90deg, rgba(201, 168, 76, 0.15) 0%, rgba(201, 168, 76, 0.02) 100%);
            border-left: 3px solid #C9A84C;
            border-radius: 0 10px 10px 0;
            color: #C9A84C; font-weight: 600;
        }
        .sidebar-nav .nav-link i { font-size: 16px; width: 22px; text-align: center; opacity: 0.8; }
        .sidebar-nav .nav-link.active i { opacity: 1; text-shadow: 0 0 12px rgba(201, 168, 76, 0.4); }

        .sidebar-footer { padding: 16px 20px; background: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.05); }
        .sidebar-footer .user-info { color: #64748b; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .sidebar-footer .user-name { color: #f8fafc; font-weight: 600; font-size: 14px; margin-top: 2px; }
        .sidebar-footer .btn-logout { background: rgba(220,38,38,0.1) !important; color: #fca5a5 !important; border: 1px solid rgba(220,38,38,0.2) !important; }
        .sidebar-footer .btn-logout:hover { background: rgba(220,38,38,0.2) !important; color: #fff !important; }

        /* ── MAIN ── */
        .main-content { 
            margin-left: var(--sidebar-w); min-height: 100vh; 
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        body.sidebar-collapsed .main-content { margin-left: 0; }
        
        .topbar {
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(10px);
            padding: 12px 26px;
            border-bottom: 1px solid rgba(226,232,240,0.9);
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 1px 10px rgba(15,68,35,0.06);
        }
        .topbar h5 { margin: 0; font-weight: 800; color: #1a2332; font-size: 16px; letter-spacing: -0.2px; }
        .sidebar-toggle-btn {
            background: transparent; border: none; font-size: 22px; color: #475569;
            cursor: pointer; padding: 4px; border-radius: 6px; transition: all 0.2s;
            display: flex; align-items: center; justify-content: center; width: 36px; height: 36px;
        }
        .sidebar-toggle-btn:hover { background: rgba(0,0,0,0.06); color: #1a2332; }
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
        .data-table { border-collapse: separate; border-spacing: 0; width: 100%; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
        .data-table thead th {
            background: #f8fafc;
            color: #475569; font-size: 11px; font-weight: 700;
            letter-spacing: 0.5px; padding: 12px 16px; 
            border-bottom: 2px solid #e2e8f0; text-transform: uppercase;
        }
        .data-table tbody td { padding: 12px 16px; font-size: 12px; vertical-align: middle; border-bottom: 1px solid #e2e8f0; background: #fff; color: #334155; }
        .data-table tbody tr { transition: background 0.15s ease; }
        .data-table tbody tr:hover td { background: #f1f5f9; }
        .data-table tbody tr:last-child td { border-bottom: none; }
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

        /* ── PAGINATION — modern & minimalist ── */
        .pha-pagination { display:flex; align-items:center; flex-wrap:wrap; gap:6px; margin-top:20px; }
        .pha-btn-page {
            display:inline-flex; align-items:center; gap:6px; padding:0 18px; height: 38px;
            border-radius:20px; font-size:13px; font-weight:600; text-decoration:none !important;
            background:#fff; color:#475569 !important; border:1.5px solid #e2e8f0;
            transition:all 0.2s ease; cursor:pointer;
        }
        .pha-btn-page:hover:not(.disabled) { background:#f8fafc; color:#1a2332 !important; border-color:#cbd5e1; transform:translateY(-1px); box-shadow: 0 4px 10px rgba(0,0,0,0.04); }
        .pha-btn-page.disabled { background:#f8fafc; color:#cbd5e1 !important; border-color:#f1f5f9; cursor:not-allowed; box-shadow:none; opacity: 0.7; }
        .pha-pg {
            display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px;
            border-radius:50%; font-size:13px; font-weight:600;
            text-decoration:none !important; border:1.5px solid transparent; background:transparent;
            color:#475569 !important; transition:all 0.2s ease;
        }
        .pha-pg:hover:not(.pha-pg-active) { background:#f1f5f9; color:#1a2332 !important; transform:translateY(-1px); }
        .pha-pg-active { background: linear-gradient(135deg, #1B6B35, #0f4423) !important; color:#fff !important; box-shadow:0 4px 12px rgba(15,68,35,0.3); cursor:default; }
        .pha-pg-dot { display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; color:#94a3b8; font-weight:600; letter-spacing: 2px; }
        .pha-pagination-meta { font-size:12.5px; color:#64748b; margin-top:12px; font-weight: 500; }
        .pha-pagination-meta strong { color:#0f4423; font-weight:700; }


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
            <div class="sidebar-logo-container">
                <div class="sidebar-logo-wrapper">
                    <img src="{{ asset('images/logos/pha-logo.svg') }}" alt="PHA Logo">
                </div>
                <div class="sidebar-logo-divider"></div>
                <div class="sidebar-logo-wrapper">
                    <img src="{{ asset('images/logos/govt-pk.svg') }}" alt="Govt of Pakistan">
                </div>
            </div>
            <div class="text-center mt-1">
                <h6>PHA Foundation</h6>
                <small class="d-block">Ministry of Housing & Works</small>
            </div>

            <!-- Project Switcher -->
            <div class="dropdown w-100 px-3 mt-3">
                @php $activeProject = \App\Models\Project::active(); @endphp
                <button class="btn btn-sm w-100 text-start dropdown-toggle project-switcher-btn" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-building me-1"></i> {{ $activeProject ? $activeProject->name : 'I-16/3 Islamabad' }}
                </button>
                <ul class="dropdown-menu w-100 shadow" style="font-size: 12px; border:none; border-radius: 10px;">
                    <li class="dropdown-header text-muted" style="font-size: 10px;">SWITCH PROJECT</li>
                    @foreach(\App\Models\Project::all() as $proj)
                        <li>
                            <form action="{{ route('projects.switch') }}" method="POST">
                                @csrf
                                <input type="hidden" name="project_id" value="{{ $proj->id }}">
                                <button type="submit" class="dropdown-item {{ $proj->is_active ? 'active fw-bold' : '' }}">
                                    {{ $proj->name }}
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section-title">Main</div>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i> Overview
            </a>
            
            @if(in_array(auth()->user()->role, ['super_admin', 'data_entry', 'viewer']))
            <a href="{{ route('allottees.index') }}" class="nav-link {{ request()->routeIs('allottees.*') && !request()->routeIs('allottees.*defaulter*') && !request()->routeIs('blocks.visual') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> Allottees Detail
            </a>
            <a href="{{ route('blocks.visual') }}" class="nav-link {{ request()->routeIs('blocks.visual') ? 'active' : '' }}">
                <i class="bi bi-building-fill"></i> Block Visuals
            </a>
            <a href="{{ route('allottees.index', ['defaulter' => 1]) }}" class="nav-link {{ request()->get('defaulter') == 1 ? 'active' : '' }}">
                <i class="bi bi-exclamation-triangle-fill"></i> Defaulters
            </a>
            @endif

            @if(in_array(auth()->user()->role, ['super_admin', 'data_entry']))
            <div class="nav-section-title mt-3">Billing & Payments</div>
            <a href="{{ route('monthly-bills.index') }}" class="nav-link {{ request()->routeIs('monthly-bills.*') && !request()->routeIs('monthly-bills-e.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-check-fill"></i> Monthly Bills (Cat B)
            </a>
            <a href="{{ route('monthly-bills-e.index') }}" class="nav-link {{ request()->routeIs('monthly-bills-e.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-check-fill"></i> Monthly Bills (Cat E)
            </a>
            <a href="{{ route('bills.search') }}" class="nav-link {{ request()->routeIs('bills.search') ? 'active' : '' }}">
                <i class="bi bi-search"></i> Bill Search
            </a>
            @endif

            @if(in_array(auth()->user()->role, ['super_admin', 'whatsapp_sender']))
            <div class="nav-section-title mt-3">Communications</div>
            <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                <i class="bi bi-whatsapp"></i> Bulk SMS / WhatsApp
            </a>
            @endif

            <!-- ── COMPLAINT SYSTEM ── -->
            @if(in_array(auth()->user()->role, ['super_admin', 'data_entry', 'viewer', 'maintenance_staff', 'maintenance_supervisor']))
            <div class="nav-section-title mt-3">Complaint System</div>
            
            @if(in_array(auth()->user()->role, ['super_admin', 'data_entry', 'viewer', 'maintenance_supervisor']))
            <a href="{{ route('admin.complaints.dashboard') }}" class="nav-link {{ request()->routeIs('admin.complaints.dashboard') ? 'active' : '' }}">
                <i class="bi bi-chat-left-text-fill"></i> Complaints Dashboard
            </a>
            @endif
            
            <a href="{{ route('admin.complaints.index') }}" class="nav-link {{ request()->routeIs('admin.complaints.index') || request()->routeIs('admin.complaints.show') ? 'active' : '' }}">
                <i class="bi bi-list-task"></i> Manage Complaints
            </a>
            
            @if(in_array(auth()->user()->role, ['super_admin', 'maintenance_supervisor']))
            <a href="{{ route('admin.complaints.categories.index') }}" class="nav-link {{ request()->routeIs('admin.complaints.categories.*') ? 'active' : '' }}">
                <i class="bi bi-tags-fill"></i> Categories
            </a>
            <a href="{{ route('admin.complaints.staff.index') }}" class="nav-link {{ request()->routeIs('admin.complaints.staff.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> Maintenance Staff
            </a>
            <a href="{{ route('admin.complaints.reports') }}" class="nav-link {{ request()->routeIs('admin.complaints.reports') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line-fill"></i> CMS Reports
            </a>
            @endif
            @endif

            {{-- ── Staff HR Management ──────────────────────────────────────── --}}
            @if(in_array(auth()->user()->role, ['super_admin', 'data_entry', 'viewer', 'maintenance_supervisor']))
            <div class="nav-section-title mt-3">Staff HR Management</div>
            <a href="{{ route('admin.staff.attendance.index') }}" class="nav-link {{ request()->routeIs('admin.staff.attendance.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-check-fill"></i> Attendance
            </a>
            <a href="{{ route('admin.staff.payroll.index') }}" class="nav-link {{ request()->routeIs('admin.staff.payroll.*') ? 'active' : '' }}">
                <i class="bi bi-cash-coin"></i> Payroll & Salary
            </a>
            <a href="{{ route('admin.staff.performance.index') }}" class="nav-link {{ request()->routeIs('admin.staff.performance.*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line"></i> Staff Performance
            </a>
            @endif

            <div class="nav-section-title mt-3">Portal</div>
            <a href="{{ route('portal.login') }}" class="nav-link {{ request()->routeIs('portal.*') ? 'active' : '' }}" target="_blank">

                <i class="bi bi-person-badge-fill"></i> Allottee Portal <i class="bi bi-box-arrow-up-right ms-auto" style="font-size: 10px; width: auto;"></i>
            </a>

            @if(auth()->user()->role === 'super_admin')
            <div class="nav-section-title mt-3">Management</div>
            <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="bi bi-person-gear"></i> User Management
            </a>
            <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <i class="bi bi-sliders2"></i> Settings & Criteria
            </a>
            <a href="{{ route('projects.index') }}" class="nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                <i class="bi bi-building-gear"></i> Projects Setup
            </a>
            @endif
        </nav>
        <div class="sidebar-footer">
            <div class="user-info">Logged in as</div>
            <div class="user-name">{{ Auth::user()->name }}</div>
            <form action="{{ route('logout') }}" method="POST" class="mt-2">
                @csrf
                <button type="submit" class="btn btn-sm btn-logout w-100" style="font-size:11px; font-weight:600;">
                    <i class="bi bi-box-arrow-right"></i> Sign Out
                </button>
            </form>
            <div class="text-center mt-3 pt-2" style="border-top: 1px solid rgba(255,255,255,0.06); font-size: 9.5px; color: rgba(255, 255, 255, 0.4); line-height: 1.4; letter-spacing: 0.3px;">
                &copy; {{ date('Y') }} PHA Foundation<br>
                Developed by IT Wing
            </div>
        </div>
    </aside>

    <!-- MAIN -->
    <div class="main-content">
        <div class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="sidebar-toggle-btn" onclick="document.body.classList.toggle('sidebar-collapsed')" title="Toggle Sidebar">
                    <i class="bi bi-list"></i>
                </button>
                <h5>@yield('page-title', 'Dashboard')</h5>
            </div>
            <div class="d-flex align-items-center gap-3">
                @php
                    $projects = \App\Models\Project::all();
                    $activeProject = \App\Models\Project::active();
                @endphp
                <form action="{{ route('projects.switch') }}" method="POST" class="d-flex align-items-center bg-white border rounded px-2 py-1 shadow-sm" style="min-width:200px;">
                    @csrf
                    <i class="bi bi-building ms-1 me-2 text-muted" style="font-size:12px;"></i>
                    <select name="project_id" class="form-select form-select-sm border-0 fw-bold" style="background: transparent; cursor: pointer; font-size:12px;" onchange="this.form.submit()">
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ $activeProject && $activeProject->id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </form>

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
