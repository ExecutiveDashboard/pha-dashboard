<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Details — PHAF Portal</title>
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
        .page-body { padding: 28px; max-width: 700px; margin: 0 auto; }
        .bill-card { background: #fff; border-radius: 16px; padding: 0; border: 1px solid #e8edf3; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .bill-header { background: #1B6B35; color: #fff; padding: 24px; text-align: center; }
        .bill-header h3 { margin: 0; font-weight: 800; }
        .bill-body { padding: 24px; }
        .bill-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f1f5f9; }
        .bill-row.total { border-bottom: none; font-size: 18px; font-weight: 800; padding-top: 16px; margin-top: 8px; border-top: 2px dashed #e2e8f0; }
        .psid-box { background: #f0f9f4; border: 2px dashed #1B6B35; border-radius: 12px; padding: 20px; text-align: center; margin-top: 24px; }
        .psid-box .lbl { font-size: 12px; color: #1B6B35; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
        .psid-box .psid-val { font-size: 28px; font-weight: 900; color: #1a2332; letter-spacing: 2px; margin: 8px 0; }
        .btn-pay { background: #2563eb; color: #fff; border: none; padding: 12px 24px; border-radius: 10px; font-weight: 700; width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 15px; margin-top: 16px; }
        .btn-pay:hover { background: #1d4ed8; color: #fff; }
    </style>
</head>
<body>

<div class="portal-topbar">
    <div class="brand">
        <a href="{{ route('portal.dashboard') }}" class="text-white text-decoration-none me-2"><i class="bi bi-arrow-left"></i></a>
        <img src="{{ asset('images/logos/pha-logo.svg') }}" alt="PHAF">
        <div class="brand-text">
            <div class="t1">PHAF Maintenance Portal</div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="bill-card">
        <div class="bill-header">
            <h3>Bill for {{ \Carbon\Carbon::parse($bill->bill_month)->format('F Y') }}</h3>
            <div style="font-size: 13px; opacity: 0.8; mt-1">File No: {{ $allottee->file_no }}</div>
        </div>
        <div class="bill-body">
            
            <div class="text-center mb-4">
                @if($bill->status === 'paid' || $bill->status === 'settled')
                    <div style="display:inline-block; padding:8px 24px; background:#dcfce7; color:#166534; font-weight:800; border-radius:20px; font-size:14px; border:2px solid #166534;">
                        <i class="bi bi-check-circle-fill me-1"></i> PAID / SETTLED
                    </div>
                @else
                    <div style="display:inline-block; padding:8px 24px; background:#fee2e2; color:#991b1b; font-weight:800; border-radius:20px; font-size:14px; border:2px solid #991b1b;">
                        <i class="bi bi-exclamation-circle-fill me-1"></i> UNPAID
                    </div>
                @endif
            </div>

            @php
                $monthlyRate = $allottee->covered_area * $rate;
                $arrears = max(0, $bill->maintenance_amount - $monthlyRate);
            @endphp

            @if($arrears > 0)
            <div class="bill-row">
                <span style="color:#64748b;">Previous Arrears (Maintenance)</span>
                <span style="font-weight:600;">Rs. {{ number_format($arrears) }}</span>
            </div>
            @endif
            <div class="bill-row">
                <span style="color:#64748b;">Current Month Maintenance</span>
                <span style="font-weight:600;">Rs. {{ number_format($monthlyRate) }}</span>
            </div>
            @if($bill->ww_amount > 0)
            <div class="bill-row">
                <span style="color:#64748b;">Watch & Ward Charges</span>
                <span style="font-weight:600;">Rs. {{ number_format($bill->ww_amount) }}</span>
            </div>
            @endif
            @if($bill->fine_amount > 0)
            <div class="bill-row text-danger">
                <span>Delay Surcharge (Fine)</span>
                <span style="font-weight:600;">Rs. {{ number_format($bill->fine_amount) }}</span>
            </div>
            @endif
            <div class="bill-row total">
                <span>Total Amount</span>
                <span>Rs. {{ number_format($bill->total_amount) }}</span>
            </div>

            @if($bill->status !== 'paid' && $bill->status !== 'settled')
                <div class="psid-box">
                    <div class="lbl"><img src="{{ asset('images/logos/1link.png') }}" alt="1Link" style="height:20px; margin-right:8px; vertical-align:middle; filter:grayscale(1) opacity(0.6); display:none;">1Bill Payment (PSID)</div>
                    <div class="psid-val">{{ $bill->psid }}</div>
                    <div style="font-size:11px; color:#64748b;">Use this 1Bill Invoice ID in any banking app to pay instantly.</div>
                    
                    <button type="button" class="btn-pay" onclick="simulatePayment()">
                        <i class="bi bi-phone"></i> Simulate Payment via App
                    </button>
                </div>
            @else
                <div class="mt-4 p-3 bg-light rounded text-center border">
                    <i class="bi bi-receipt me-2 text-success"></i>
                    <strong>Payment Recorded:</strong> Rs. {{ number_format($bill->paid_amount) }}
                    @if($bill->payment_date)<br><small class="text-muted">Paid on: {{ \Carbon\Carbon::parse($bill->payment_date)->format('d M Y') }}</small>@endif
                </div>
            @endif
            
            <div class="text-center mt-4">
                <a href="{{ route('bills.pdf', $allottee) }}" class="text-decoration-none" style="color:#1B6B35; font-size:13px; font-weight:600;">
                    <i class="bi bi-download me-1"></i> Download Original Bill PDF
                </a>
            </div>

        </div>
    </div>
</div>

<!-- Simulation Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="modal-body p-4" id="modalBody">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <h6 class="fw-bold">Processing...</h6>
                <p class="text-muted small">Simulating payment via 1Bill API for PSID <br>{{ $bill->psid }}</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function simulatePayment() {
        const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
        modal.show();

        setTimeout(() => {
            const body = document.getElementById('modalBody');
            body.innerHTML = `
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h5 class="mt-3 fw-bold text-success">Payment Successful!</h5>
                <p class="text-muted small">Your payment of Rs. {{ number_format($bill->total_amount) }} was received successfully.</p>
                <button type="button" class="btn btn-outline-success btn-sm w-100" onclick="window.location.reload()">Return to Bill</button>
            `;
            
            // Actually record the payment in the backend using an AJAX call
            fetch("{{ route('monthly-bills.pay', $bill->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    paid_amount: {{ $bill->total_amount }},
                    payment_mode: 'psid',
                    payment_date: new Date().toISOString().split('T')[0]
                })
            });
            
        }, 2500);
    }
</script>
</body>
</html>
