@extends('layouts.app')
@section('title', 'Bill — '.$allottee->name)
@section('page-title', 'Maintenance Bill — '.$allottee->file_no)

@push('styles')
<style>
/* ── BILL WRAPPER ── */
.bill-container {
    max-width: 820px;
    margin: 0 auto;
    background: #fff;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    color: #1a2332;
    box-shadow: 0 4px 24px rgba(0,0,0,0.10);
}

/* ── BILL HEADER ── */
.bill-header {
    background: linear-gradient(135deg, #0f4423 0%, #1B6B35 60%, #2d8a4e 100%);
    padding: 0;
    border-radius: 4px 4px 0 0;
}
.bill-header-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 24px 12px;
    border-bottom: 1px solid rgba(255,255,255,0.15);
}
.bill-logos {
    display: flex;
    align-items: center;
    gap: 14px;
}
.bill-logos img {
    height: 52px;
    width: 52px;
    object-fit: contain;
    filter: brightness(0) invert(1);
}
.bill-org-name {
    color: #fff;
    text-align: center;
    flex: 1;
}
.bill-org-name h2 {
    font-size: 18px;
    font-weight: 800;
    margin: 0;
    letter-spacing: 0.3px;
}
.bill-org-name p {
    font-size: 11px;
    margin: 2px 0 0;
    opacity: 0.85;
}
.bill-badge {
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.3);
    border-radius: 8px;
    padding: 8px 14px;
    color: #fff;
    text-align: center;
    min-width: 110px;
}
.bill-badge .lbl { font-size: 9px; opacity: 0.8; letter-spacing: 1px; text-transform: uppercase; }
.bill-badge .val { font-size: 15px; font-weight: 800; line-height: 1.2; }

.bill-header-strip {
    background: rgba(0,0,0,0.25);
    padding: 6px 24px;
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: rgba(255,255,255,0.85);
}

/* ── ALLOTTEE INFO STRIP ── */
.allottee-strip {
    background: #f8f9fa;
    border-bottom: 2px solid #1B6B35;
    padding: 14px 24px;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
}
.a-item .lbl { font-size: 9px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
.a-item .val { font-size: 13px; font-weight: 700; color: #1a2332; margin-top: 1px; }

/* ── CHARGES SECTION ── */
.bill-body { padding: 0 24px 20px; }
.bill-section-title {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #1B6B35;
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 4px;
    margin: 18px 0 10px;
}
.charges-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.charges-table thead tr { background: #1B6B35; color: #fff; }
.charges-table thead th { padding: 7px 12px; font-weight: 600; font-size: 11px; }
.charges-table tbody tr:nth-child(even) { background: #f9fafb; }
.charges-table tbody tr:hover { background: #f0f9f4; }
.charges-table tbody td { padding: 8px 12px; border-bottom: 1px solid #f0f0f0; }
.charges-table .total-row { background: #0f4423 !important; color: #fff; font-weight: 800; font-size: 13px; }
.charges-table .total-row td { padding: 10px 12px; border: none; }
.charges-table .subtotal-row { background: #f0f9f4 !important; font-weight: 700; }

/* ── PAYMENT HISTORY TABLE ── */
.hist-table { width: 100%; border-collapse: collapse; font-size: 11px; }
.hist-table thead tr { background: #374151; color: #fff; }
.hist-table thead th { padding: 6px 10px; font-weight: 600; }
.hist-table tbody td { padding: 6px 10px; border-bottom: 1px solid #f0f0f0; }
.hist-table tbody tr:nth-child(even) { background: #f9fafb; }

/* ── PAYMENT STATUS ── */
.status-box {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-top: 14px;
}
.status-card {
    border-radius: 8px;
    padding: 12px 16px;
    text-align: center;
}
.status-card .s-lbl { font-size: 10px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; }
.status-card .s-val { font-size: 22px; font-weight: 900; margin-top: 2px; }
.s-paid   { background: #dcfce7; color: #166534; }
.s-due    { background: #fee2e2; color: #991b1b; }

/* ── AMOUNT DUE BOX (IESCO style) ── */
.amount-due-box {
    background: linear-gradient(135deg, #0f4423, #1B6B35);
    color: #fff;
    border-radius: 10px;
    padding: 16px 20px;
    margin-top: 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.amount-due-box .ad-label { font-size: 12px; opacity: 0.85; font-weight: 600; }
.amount-due-box .ad-value { font-size: 28px; font-weight: 900; }
.amount-due-box .ad-notice { font-size: 10px; opacity: 0.7; margin-top: 2px; }

/* ── PAYMENT METHODS ── */
.payment-methods {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-top: 14px;
}
.pay-method {
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 12px 14px;
}
.pay-method.online-pay { border-color: #1B6B35; }
.pay-method .pm-title { font-size: 10px; font-weight: 700; color: #1B6B35; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
.pay-method .pm-info { font-size: 11px; color: #374151; line-height: 1.7; }

/* ── QR CODE ── */
.qr-section {
    display: flex;
    align-items: center;
    gap: 14px;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 12px 16px;
    margin-top: 10px;
}
.qr-section canvas, .qr-section img { border: 1px solid #d1d5db; border-radius: 4px; }
.qr-section .qr-label { font-size: 10px; color: #6b7280; }
.qr-section .qr-title { font-size: 13px; font-weight: 700; color: #1a2332; margin-bottom: 2px; }
.qr-section .qr-inst  { font-size: 11px; color: #374151; line-height: 1.6; }

/* ── BILL FOOTER ── */
.bill-footer {
    background: #f8f9fa;
    border-top: 2px solid #1B6B35;
    padding: 12px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 10px;
    color: #6b7280;
    border-radius: 0 0 4px 4px;
}
.bill-footer strong { color: #1a2332; }

/* ── LATE PAYMENT NOTICE ── */
.late-notice {
    background: #fff7ed;
    border: 1px solid #fed7aa;
    border-left: 4px solid #f97316;
    border-radius: 0 6px 6px 0;
    padding: 8px 12px;
    margin-top: 10px;
    font-size: 11px;
    color: #7c2d12;
}

/* ── PRINT BUTTON BAR ── */
.action-bar {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 16px;
    flex-wrap: wrap;
}
.btn-print {
    background: #1B6B35;
    color: #fff;
    border: none;
    padding: 9px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}
.btn-print:hover { background: #0f4423; color: #fff; }
.btn-pdf {
    background: #dc2626;
    color: #fff;
    border: none;
    padding: 9px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}
.btn-pdf:hover { background: #b91c1c; color: #fff; }

@media print {
    .sidebar, .topbar, .action-bar, .main-content > .topbar { display: none !important; }
    .main-content { margin-left: 0 !important; }
    .page-body { padding: 0 !important; }
    .bill-container { box-shadow: none; border: none; max-width: 100%; }
    body { background: #fff !important; }
}
</style>
@endpush

@section('content')

{{-- Action Bar --}}
<div class="action-bar">
    <a href="{{ route('allottees.show', $allottee) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
    <button class="btn-print" onclick="window.print()">
        <i class="bi bi-printer-fill"></i> Print Bill
    </button>
    <a href="{{ route('bills.pdf', $allottee) }}" class="btn-pdf">
        <i class="bi bi-file-earmark-pdf-fill"></i> Download PDF
    </a>
    <span style="font-size:12px;color:#64748b;">
        <i class="bi bi-info-circle me-1"></i>
        Bill for: <strong>{{ $billMonth }}</strong>
    </span>
</div>

{{-- THE BILL --}}
<div class="bill-container">

    {{-- ── HEADER ── --}}
    <div class="bill-header">
        <div class="bill-header-top">
            <div class="bill-logos" style="width: 130px;">
                <img src="{{ asset('images/logos/pha-logo.svg') }}" alt="PHAF Logo" style="height: 70px; width: auto;">
            </div>
            <div class="bill-org-name">
                <h2>GOVERNMENT OF PAKISTAN</h2>
                <p>Pakistan Housing Authority Foundation (PHAF)</p>
                <p style="font-size:10px;opacity:0.7;">Maintenance Billing System — I-16/3 Islamabad</p>
            </div>
            <div class="bill-badge" style="width: 130px; text-align: right; padding: 6px; background: transparent; border: none;">
                <img src="{{ asset('images/logos/govt-pk.svg') }}" alt="Govt" style="height: 60px; width: auto; filter: brightness(0) invert(1);">
            </div>
        </div>
        <div class="bill-header-strip">
            <span><i class="bi bi-calendar3 me-1"></i>Bill Month: <strong>{{ $billMonth }}</strong></span>
            <span><i class="bi bi-hash me-1"></i>File No: <strong>{{ $allottee->file_no }}</strong></span>
            <span><i class="bi bi-clock me-1"></i>Issue Date: <strong>{{ now()->format('d M Y') }}</strong></span>
            <span><i class="bi bi-clock-history me-1"></i>Due Date: <strong>{{ now()->endOfMonth()->format('d M Y') }}</strong></span>
        </div>
    </div>

    {{-- ── ALLOTTEE INFO STRIP ── --}}
    <div class="allottee-strip">
        <div class="a-item">
            <div class="lbl">Allottee Name</div>
            <div class="val">{{ $allottee->display_name }}</div>
        </div>
        <div class="a-item">
            <div class="lbl">CNIC</div>
            <div class="val">{{ $allottee->display_cnic }}</div>
        </div>
        <div class="a-item">
            <div class="lbl">Cell / Mobile</div>
            <div class="val">{{ $allottee->cell ?? '—' }}</div>
        </div>
        <div class="a-item">
            <div class="lbl">Category / Area</div>
            <div class="val">Cat-{{ $allottee->category }} / {{ $allottee->covered_area }} Sq Ft</div>
        </div>
        <div class="a-item">
            <div class="lbl">Block / Floor / Flat</div>
            <div class="val">Blk {{ $allottee->block_no ?? '—' }} / Flr {{ $allottee->floor ?? '—' }} / Flat {{ $allottee->flat_no ?? '—' }}</div>
        </div>
        <div class="a-item">
            <div class="lbl">Membership No.</div>
            <div class="val">{{ $allottee->membership_no ?? '—' }}</div>
        </div>
        <div class="a-item">
            <div class="lbl">Possession Date</div>
            <div class="val">{{ $allottee->possession_date?->format('d M Y') ?? 'Not Recorded' }}</div>
        </div>
        <div class="a-item">
            <div class="lbl">BPS Grade</div>
            <div class="val">{{ $allottee->bps ? 'BPS-'.$allottee->bps : '—' }}</div>
        </div>
    </div>

    {{-- ── BILL BODY ── --}}
    <div class="bill-body">

        {{-- CURRENT CHARGES --}}
        <div class="bill-section-title">Current Month Charges — {{ $billMonth }}</div>
        <table class="charges-table">
            <thead>
                <tr>
                    <th style="width:40%">Description</th>
                    <th class="text-center">Rate</th>
                    <th class="text-center">Duration / Basis</th>
                    <th class="text-end">Amount (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                @if($dueMonths > 1)
                <tr>
                    <td>
                        <strong>Previous Arrears (Maintenance)</strong>
                        <div style="font-size:10px;color:#6b7280;">Past due balance for {{ $dueMonths - 1 }} month(s)</div>
                    </td>
                    <td class="text-center">Rs. {{ number_format($rate, 2) }}/Sq Ft</td>
                    <td class="text-center">{{ $dueMonths - 1 }} month(s)</td>
                    <td class="text-end"><strong>{{ number_format($maintenance - $monthlyRate, 2) }}</strong></td>
                </tr>
                @endif
                @if($dueMonths > 0)
                <tr>
                    <td>
                        <strong>Current Month Maintenance</strong>
                        <div style="font-size:10px;color:#6b7280;">{{ $allottee->covered_area }} Sq Ft × Rs. {{ number_format($rate, 2) }}/Sq Ft</div>
                    </td>
                    <td class="text-center">Rs. {{ number_format($rate, 2) }}/Sq Ft</td>
                    <td class="text-center">1 month</td>
                    <td class="text-end"><strong>{{ number_format($monthlyRate, 2) }}</strong></td>
                </tr>
                @endif
                @if($ww > 0)
                <tr>
                    <td>
                        <strong>Watch & Ward Charges</strong>
                        <div style="font-size:10px;color:#6b7280;">Applicable (Possession ≥ 23-Jul-2023 or not recorded)</div>
                    </td>
                    <td class="text-center">Rs. {{ number_format($wwAmount) }}/-</td>
                    <td class="text-center">One-time</td>
                    <td class="text-end"><strong>{{ number_format($ww, 2) }}</strong></td>
                </tr>
                @endif
                <tr class="subtotal-row">
                    <td colspan="3"><strong>Sub-Total (Maintenance + W&W)</strong></td>
                    <td class="text-end"><strong>{{ number_format($maintenance + $ww, 2) }}</strong></td>
                </tr>
                @if($fine > 0)
                <tr>
                    <td style="color:#dc2626;">
                        <strong>Delay Surcharge ({{ $delayPct }}% Fine)</strong>
                        <div style="font-size:10px;color:#ef4444;">Late payment penalty — Please pay before due date to avoid this charge</div>
                    </td>
                    <td class="text-center" style="color:#dc2626;">{{ $delayPct }}%</td>
                    <td class="text-center" style="color:#dc2626;">On sub-total</td>
                    <td class="text-end" style="color:#dc2626;"><strong>{{ number_format($fine, 2) }}</strong></td>
                </tr>
                @endif
                <tr class="total-row">
                    <td colspan="3">GROSS TOTAL PAYABLE</td>
                    <td class="text-end">Rs. {{ number_format($total, 2) }}</td>
                </tr>
            </tbody>
        </table>

        @if($fine > 0)
        <div class="late-notice">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            <strong>IMPORTANT:</strong> A delay surcharge of <strong>{{ $delayPct }}%</strong> has been applied due to overdue payment.
            Paying before <strong>{{ now()->endOfMonth()->format('d M Y') }}</strong> will help you avoid additional penalties.
            Total months overdue: <strong>{{ $dueMonths }}</strong>
        </div>
        @endif

        {{-- PAYMENT STATUS --}}
        <div class="bill-section-title">Payment Status</div>
        <div class="status-box">
            <div class="status-card s-paid">
                <div class="s-lbl">Amount Paid</div>
                <div class="s-val">Rs. {{ number_format($paid, 2) }}</div>
                @if($allottee->payment_date)<div style="font-size:11px;margin-top:2px;">Last paid: {{ $allottee->payment_date->format('d M Y') }}</div>@endif
            </div>
            <div class="status-card s-due">
                <div class="s-lbl">Amount Pending (Due)</div>
                <div class="s-val">Rs. {{ number_format($pending, 2) }}</div>
                <div style="font-size:11px;margin-top:2px;">Due Date: {{ now()->endOfMonth()->format('d M Y') }}</div>
            </div>
        </div>

        {{-- AMOUNT DUE BOX (SNGPL style big box) --}}
        <div class="amount-due-box">
            <div>
                <div class="ad-label">AMOUNT DUE — PAYABLE IMMEDIATELY</div>
                <div class="ad-value">Rs. {{ number_format($pending, 2) }}</div>
                <div class="ad-notice">
                    <i class="bi bi-calendar-check me-1"></i>Pay before {{ now()->endOfMonth()->format('d M Y') }} to avoid additional surcharge
                </div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:11px;opacity:0.8;margin-bottom:4px;">File No.</div>
                <div style="font-size:22px;font-weight:900;">{{ $allottee->file_no }}</div>
                <div style="font-size:10px;opacity:0.7;">{{ $allottee->category }} Type — {{ $allottee->covered_area }} Sq Ft</div>
            </div>
        </div>

        {{-- PREVIOUS PAYMENT HISTORY --}}
        <div class="bill-section-title">Previous Payment History</div>
        @if($lastPayment)
        <table class="hist-table">
            <thead>
                <tr>
                    <th>Payment Date</th>
                    <th class="text-end">Amount Paid (Rs.)</th>
                    <th class="text-center">Payment Mode</th>
                    <th>Reference No.</th>
                    <th class="text-end">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $lastPayment['date'] }}</td>
                    <td class="text-end"><strong>{{ number_format($lastPayment['amount'], 2) }}</strong></td>
                    <td class="text-center">{{ $lastPayment['mode'] }}</td>
                    <td>{{ $lastPayment['ref'] }}</td>
                    <td class="text-end"><span style="background:#dcfce7;color:#166534;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:700;">RECEIVED</span></td>
                </tr>
            </tbody>
        </table>
        @else
        <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:12px;text-align:center;color:#6b7280;font-size:12px;">
            <i class="bi bi-clock-history me-1"></i> No previous payment records found. Please contact PHA office if you have already paid.
        </div>
        @endif

        {{-- HOW TO PAY --}}
        <div class="bill-section-title">How To Pay — 1Bill e-Voucher Instructions</div>
        
        <div style="background:#f0fdf4; border:1px solid #16a34a; border-radius:8px; padding:14px; margin-bottom:14px; text-align:center;">
            <div style="font-size:11px; font-weight:700; color:#166534; text-transform:uppercase; margin-bottom:4px;">1-Bill Consumer No.</div>
            <div style="font-size:22px; font-weight:900; letter-spacing:2px; color:#1a2332;">PHAF-{{ preg_replace('/[^A-Za-z0-9]/', '', $allottee->block_no ?? 'X') }}{{ str_pad(preg_replace('/[^0-9]/', '', $allottee->flat_no ?? '0'), 3, '0', STR_PAD_LEFT) }}-{{ date('Ym') }}</div>
        </div>

        <div style="background:#1B6B35; color:#fff; font-size:12px; font-weight:bold; text-align:center; padding:10px; border-radius:8px 8px 0 0;">
            You can pay online using 1-Bill Consumer No. through following payment channels
        </div>
        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-top:none; text-align:center; font-size:11px; font-weight:bold; color:#475569; padding:8px; margin-bottom:14px; border-radius:0 0 8px 8px;">
            easyPaisa &middot; JazzCash &middot; Mobile Banking &middot; Internet Banking &middot; Over the Counter
        </div>

        <div class="payment-methods" style="margin-top:0;">
            <div class="pay-method" style="padding:0; overflow:hidden;">
                <div style="background:#1B6B35; color:#fff; padding:10px; font-size:12px; font-weight:bold; text-align:center;">
                    Paying Over the Counter (Cash Payment)
                </div>
                <div class="pm-info" style="padding:14px;">
                    <strong>1.</strong> Present this Invoice to Bank Representative specifying 1-Bill Consumer No. to be paid through 1-Bill - Invoice.<br><br>
                    <strong>2.</strong> Hand-over Cash to Representative.<br><br>
                    <strong>3.</strong> Collect relevant Payment Receipt (Computer Printed Receipt or manual receipt with Bank Stamp) and its <strong>DONE.</strong>
                </div>
            </div>
            
            <div class="pay-method online-pay" style="padding:0; overflow:hidden;">
                <div style="background:#1B6B35; color:#fff; padding:10px; font-size:12px; font-weight:bold; text-align:center;">
                    Pay via Mobile Wallets / Internet / Mobile Banking
                </div>
                <div class="pm-info" style="padding:14px;">
                    <strong>1.</strong> Login to your Mobile Wallet / Internet Banking / Mobile Banking Account.<br><br>
                    <strong>2.</strong> Tap/Select <strong>1-Bill - Invoice</strong> option.<br><br>
                    <strong>3.</strong> Enter 1-Bill Consumer No. and complete Transaction. You are <strong>DONE.</strong>
                </div>
            </div>
        </div>

        {{-- MAILING ADDRESS --}}
        @if($allottee->mailing_address)
        <div style="background:#f9fafb;border:1px dashed #d1d5db;border-radius:6px;padding:10px 14px;margin-top:14px;display:flex;justify-content:space-between;align-items:flex-start;font-size:11px;">
            <div>
                <div style="font-size:10px;font-weight:700;color:#6b7280;margin-bottom:3px;">MAILING ADDRESS</div>
                <div style="font-weight:600;color:#1a2332;">{{ $allottee->display_name }}</div>
                <div style="color:#374151;">{{ $allottee->mailing_address }}</div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:10px;font-weight:700;color:#6b7280;margin-bottom:3px;">PROPERTY ADDRESS</div>
                <div style="font-weight:600;color:#1a2332;">Flat {{ $allottee->flat_no ?? '—' }}, Floor {{ $allottee->floor ?? '—' }},
                Block {{ $allottee->block_no ?? '—' }}</div>
                <div>I-16/3, Islamabad</div>
            </div>
        </div>
        @endif

    </div>{{-- end bill-body --}}

    {{-- ── FOOTER ── --}}
    <div class="bill-footer">
        <div>
            <strong>PHA Foundation</strong> — Ministry of Housing & Works, Government of Pakistan<br>
            I-16/3 Apartments, Islamabad | maintenance@pha.gov.pk
        </div>
        <div style="text-align:center;">
            <div>Generated: {{ now()->format('d M Y, h:i A') }}</div>
            <div>This is a computer-generated bill. No signature required.</div>
        </div>
        <div style="text-align:right;">
            <strong>Helpline:</strong> PHA Office<br>
            <strong>Web:</strong> www.pha.gov.pk
        </div>
    </div>

</div>{{-- end bill-container --}}

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // QR Code generation
    const qrData = @json($qrData);
    const canvas = document.getElementById('qrCanvas');
    if (canvas && typeof QRCode !== 'undefined') {
        new QRCode(canvas, {
            text: qrData,
            width: 100,
            height: 100,
            colorDark: '#0f4423',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        });
    }
});
</script>
@endpush
