<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account — PHA Maintenance Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f0f4f8; }
        .portal-topbar {
            background: linear-gradient(135deg, #0f4423, #1B6B35);
            padding: 12px 24px; display: flex; align-items: center; justify-content: space-between;
        }
        .portal-topbar .brand { display: flex; align-items: center; gap: 12px; }
        .portal-topbar .brand img { height: 36px; }
        .portal-topbar .brand-text .t1 { color: #fff; font-weight: 700; font-size: 14px; }
        .portal-topbar .brand-text .t2 { color: rgba(255,255,255,0.6); font-size: 11px; }
        .page-body { padding: 28px; max-width: 900px; margin: 0 auto; }
        .allottee-card { background: #fff; border-radius: 16px; padding: 24px; border: 1px solid #e8edf3; margin-bottom: 20px; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; }
        .status-unpaid { background: #fee2e2; color: #dc2626; }
        .status-partial { background: #fef3c7; color: #92400e; }
        .status-paid    { background: #dcfce7; color: #166534; }
        .bill-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
        .bill-row:last-child { border-bottom: none; }
        .bill-label { font-size: 13px; color: #64748b; }
        .bill-value { font-size: 14px; font-weight: 700; color: #1a2332; }
        .total-box { background: linear-gradient(135deg, #1B6B35, #0f4423); border-radius: 12px; padding: 16px 20px; color: #fff; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .info-item { background: #f8fafc; border-radius: 10px; padding: 10px 14px; }
        .info-item .lbl { font-size: 11px; color: #94a3b8; font-weight: 500; }
        .info-item .val { font-size: 13px; color: #1a2332; font-weight: 600; }
    </style>
</head>
<body>

<div class="portal-topbar">
    <div class="brand">
        <img src="{{ asset('images/logos/govt-pk.svg') }}" alt="Govt">
        <img src="{{ asset('images/logos/pha-logo.svg') }}" alt="PHA">
        <div class="brand-text">
            <div class="t1">PHA Maintenance Services Portal</div>
            <div class="t2">Government of Pakistan — Ministry of Housing & Works</div>
        </div>
    </div>
    <form method="POST" action="{{ route('portal.logout') }}">
        @csrf
        <button class="btn btn-sm btn-outline-light" style="font-size:12px;"><i class="bi bi-box-arrow-right me-1"></i>Sign Out</button>
    </form>
</div>

<div class="page-body">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible" style="border-radius:10px;">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <!-- Allottee Header -->
    <div class="allottee-card">
        <div class="d-flex align-items-center gap-4">
            <div style="width:64px;height:64px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;font-size:28px;color:#1B6B35;flex-shrink:0;">
                <i class="bi bi-person-fill"></i>
            </div>
            <div>
                <h4 style="font-weight:800;margin:0;">{{ $allottee->display_name }}</h4>
                <div style="font-size:13px;color:#64748b;">CNIC: {{ $allottee->display_cnic }} &nbsp;|&nbsp; Cell: {{ $allottee->cell ?? '—' }}</div>
                <div class="mt-1">
                    <span class="badge" style="background:#dbeafe;color:#1d4ed8;">Category {{ $allottee->category }}</span>
                    <span class="badge ms-1" style="background:#f0f9f4;color:#1B6B35;">{{ $allottee->covered_area }} Sq Ft</span>
                    @php
                        $pStatus = $allottee->payment_status;
                    @endphp
                    <span class="status-badge ms-1 status-{{ $pStatus }}">
                        {{ strtoupper($pStatus) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Bill Breakdown / History -->
        <div class="col-md-6">
            <div class="allottee-card h-100">
                @if($hasMonthlyBills)
                    <h6 style="font-weight:700;margin-bottom:16px;"><i class="bi bi-calendar3 me-2" style="color:#1B6B35;"></i>Monthly Bills History</h6>
                    <div class="table-responsive">
                        <table class="table mb-0" style="font-size: 12px; vertical-align: middle;">
                            <thead>
                                <tr>
                                    <th class="text-muted border-0 pb-2">Month</th>
                                    <th class="text-muted border-0 pb-2 text-end">Amount</th>
                                    <th class="text-muted border-0 pb-2">Status</th>
                                    <th class="text-muted border-0 pb-2 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($monthlyBills as $mb)
                                    <tr>
                                        <td class="fw-bold">{{ \Carbon\Carbon::parse($mb->bill_month)->format('M Y') }}</td>
                                        <td class="text-end fw-bold">Rs. {{ number_format($mb->total_amount) }}</td>
                                        <td>
                                            @if($mb->status === 'paid' || $mb->status === 'settled')
                                                <span class="badge bg-success bg-opacity-25 text-success border border-success">Paid</span>
                                            @else
                                                <span class="badge bg-danger bg-opacity-25 text-danger border border-danger">Unpaid</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('portal.bill.monthly', $mb->bill_month) }}" class="btn btn-sm" style="background:#1B6B35;color:#fff;font-size:10px;padding:4px 8px;">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <h6 style="font-weight:700;margin-bottom:16px;"><i class="bi bi-receipt me-2" style="color:#1B6B35;"></i>Your Bill Breakdown</h6>
                    <div class="bill-row">
                        <span class="bill-label">Maintenance Charges</span>
                        <span class="bill-value">Rs. {{ number_format($allottee->maintenance_charges) }}</span>
                    </div>
                    <div class="bill-row">
                        <span class="bill-label">Watch & Ward Charges</span>
                        <span class="bill-value">Rs. {{ number_format($allottee->watch_ward_charges) }}</span>
                    </div>
                    <div class="bill-row">
                        <span class="bill-label">Delay Charges (10% Fine)</span>
                        <span class="bill-value" style="color:#dc2626;">Rs. {{ number_format($allottee->fine) }}</span>
                    </div>
                    <div class="total-box mt-3">
                        <div style="font-size:11px;opacity:0.8;">TOTAL PAYABLE</div>
                        <div style="font-size:24px;font-weight:900;">Rs. {{ number_format($allottee->total_maintenance_charges) }}</div>
                        <div style="font-size:11px;opacity:0.7;">{{ $allottee->due_months ?? 0 }} months overdue</div>
                    </div>

                    <!-- Payment Status -->
                    <div class="row g-2 mt-3">
                        <div class="col-6">
                            <div style="background:#dcfce7;border-radius:10px;padding:12px;text-align:center;">
                                <div style="font-size:10px;font-weight:600;color:#166534;">AMOUNT PAID</div>
                                <div style="font-size:18px;font-weight:800;color:#1B6B35;">Rs. {{ number_format($allottee->amount_paid) }}</div>
                                @if($allottee->payment_date)
                                    <div style="font-size:10px;color:#166534;">{{ $allottee->payment_date->format('d M Y') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-6">
                            <div style="background:#fee2e2;border-radius:10px;padding:12px;text-align:center;">
                                <div style="font-size:10px;font-weight:600;color:#991b1b;">AMOUNT PENDING</div>
                                <div style="font-size:18px;font-weight:800;color:#dc2626;">Rs. {{ number_format($allottee->amount_pending) }}</div>
                                @if($allottee->payment_mode)
                                    <div style="font-size:10px;color:#991b1b;">via {{ ucfirst($allottee->payment_mode) }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Property & Personal Info -->
        <div class="col-md-6">
            <div class="allottee-card h-100">
                <h6 style="font-weight:700;margin-bottom:16px;"><i class="bi bi-house-fill me-2" style="color:#2563eb;"></i>Property & Personal Details</h6>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="lbl">File No.</div>
                        <div class="val">{{ $allottee->file_no ?? '—' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="lbl">Membership No.</div>
                        <div class="val">{{ $allottee->membership_no ?? '—' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="lbl">Block No.</div>
                        <div class="val">{{ $allottee->block_no ?? '—' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="lbl">Flat No.</div>
                        <div class="val">{{ $allottee->flat_no ?? '—' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="lbl">Floor</div>
                        <div class="val">{{ $allottee->floor ?? '—' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="lbl">BPS Grade</div>
                        <div class="val">{{ $allottee->bps ? 'BPS-'.$allottee->bps : '—' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="lbl">Possession Date</div>
                        <div class="val">{{ $allottee->possession_date?->format('d M Y') ?? 'Not Recorded' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="lbl">City</div>
                        <div class="val">{{ $allottee->city ?? '—' }}</div>
                    </div>
                </div>
                <div class="info-item mt-2">
                    <div class="lbl">Mailing Address</div>
                    <div class="val" style="font-size:12px;">{{ $allottee->mailing_address ?? '—' }}</div>
                </div>
                @if($allottee->office_name)
                <div class="info-item mt-2">
                    <div class="lbl">Office / Department</div>
                    <div class="val" style="font-size:12px;">{{ $allottee->office_name }} — {{ $allottee->post_held }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>


    <div class="mt-3 text-center">
        <a href="{{ route('bills.pdf', $allottee) }}" target="_blank"
           style="display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff;padding:12px 28px;border-radius:10px;font-weight:700;font-size:14px;text-decoration:none;box-shadow:0 4px 12px rgba(220,38,38,0.3);">
            <svg xmlns='http://www.w3.org/2000/svg' width='18' height='18' fill='currentColor' viewBox='0 0 16 16'><path d='M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z'/><path d='M4.603 14.087a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.697 19.697 0 0 0 1.062-2.227 7.269 7.269 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .477.365c.088.164.12.356.127.538.007.188-.012.396-.047.614-.084.51-.27 1.134-.52 1.794a10.954 10.954 0 0 0 .98 1.686 5.753 5.753 0 0 1 1.334.05c.364.066.734.195.96.465.12.144.193.32.2.518.007.192-.047.382-.138.563a1.04 1.04 0 0 1-.354.416.856.856 0 0 1-.51.138c-.331-.014-.654-.196-.933-.417a5.712 5.712 0 0 1-.911-.95 11.651 11.651 0 0 0-1.997.406 11.307 11.307 0 0 1-1.02 1.51c-.292.35-.609.656-.927.787a.793.793 0 0 1-.58.029zm1.379-1.901c-.166.076-.32.156-.459.238-.328.194-.541.383-.647.547-.094.145-.096.25-.04.361.01.022.02.036.026.044a.266.266 0 0 0 .035-.012c.137-.056.355-.235.635-.572a8.18 8.18 0 0 0 .45-.606zm1.64-1.33a12.71 12.71 0 0 1 1.01-.193 11.744 11.744 0 0 1-.51-.858 20.801 20.801 0 0 1-.5 1.05zm2.446.45c.15.163.296.3.435.41.24.19.407.253.498.256a.107.107 0 0 0 .07-.015.307.307 0 0 0 .094-.125.436.436 0 0 0 .059-.2.095.095 0 0 0-.026-.063c-.052-.062-.2-.152-.518-.209a3.876 3.876 0 0 0-.612-.053zM8.078 7.8a6.7 6.7 0 0 0 .2-.828c.031-.188.043-.343.038-.465a.613.613 0 0 0-.032-.198.517.517 0 0 0-.145.04c-.087.035-.158.106-.196.283-.04.192-.03.469.046.822.024.111.054.227.09.346z'/></svg>
            Download My Bill (PDF)
        </a>
        <a href="{{ route('bills.show', $allottee) }}" target="_blank"
           style="display:inline-flex;align-items:center;gap:8px;background:#1B6B35;color:#fff;padding:12px 24px;border-radius:10px;font-weight:600;font-size:13px;text-decoration:none;margin-left:10px;">
            <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' viewBox='0 0 16 16'><path d='M1.5 0h11.586L15 1.914V14.5a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 1 14.5v-13A1.5 1.5 0 0 1 1.5 0zm0 1a.5.5 0 0 0-.5.5v13a.5.5 0 0 0 .5.5h11a.5.5 0 0 0 .5-.5V2.5H11V1H1.5zM12 1v1.5h1.586L12 1z'/><path d='M4 6h8v1H4V6zm0 2h8v1H4V8zm0 2h5v1H4v-1z'/></svg>
            View Bill
        </a>
    </div>
    <div class="mt-3 text-center" style="font-size:12px;color:#94a3b8;">
        For payment queries, contact PHA Office: Ministry of Housing &amp; Works, Islamabad
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
