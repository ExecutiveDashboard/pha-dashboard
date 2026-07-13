<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>PHA Bill — {{ $allottee->file_no }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
@page { margin: 5mm; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size:8px; color:#1a2332; background:#fff; line-height: 1.25; }

.hdr { background:#0f4423; color:#fff; padding:6px 12px; }
.hdr table { width:100%; border-collapse:collapse; }
.hdr td { vertical-align:middle; }
.hdr .org { font-size:11px; font-weight:bold; }
.hdr .sub { font-size:7px; opacity:0.75; }
.hdr .title { font-size:14px; font-weight:900; text-align:center; letter-spacing:0.5px; }
.hdr .sub2  { font-size:8px; text-align:center; opacity:0.8; }
.mbox { border:1px solid rgba(255,255,255,0.3); border-radius:3px; padding:3px 6px; text-align:center; }
.mbox .ml { font-size:6.5px; opacity:0.7; text-transform:uppercase; }
.mbox .mv { font-size:10px; font-weight:bold; }

.hstrip { background:rgba(0,0,0,0.2); padding:3px 12px; }
.hstrip table { width:100%; border-collapse:collapse; }
.hstrip td { font-size:7.5px; color:rgba(255,255,255,0.9); }

.astrip { background:#f8fafc; border-bottom:1.5px solid #1B6B35; padding:6px 12px; }
.astrip table { width:100%; border-collapse:collapse; }
.astrip td { vertical-align:top; padding:2px 4px; }
.al { font-size:6.5px; color:#6b7280; font-weight:bold; text-transform:uppercase; }
.av { font-size:8.5px; font-weight:bold; color:#1a2332; margin-top:1px; }

.body-wrap { padding:6px 12px 2px; }
.cols { width:100%; border-collapse:separate; border-spacing:8px 0; }
.col-l { vertical-align:top; width:52%; }
.col-r { vertical-align:top; width:48%; }

.st { font-size:7.5px; font-weight:bold; letter-spacing:0.5px; text-transform:uppercase;
      color:#1B6B35; border-bottom:1px solid #d1d5db; padding-bottom:1px; margin:4px 0 3px; }

.ct { width:100%; border-collapse:collapse; font-size:8px; }
.ct thead tr { background:#1B6B35; color:#fff; }
.ct thead th { padding:3px 4px; font-size:7.5px; font-weight:bold; }
.ct tbody td { padding:3px 4px; border-bottom:1px solid #f1f5f9; }
.ct tbody tr:nth-child(even) { background:#f8fafc; }
.ct .tot-r { background:#0f4423; color:#fff; font-weight:bold; font-size:9.5px; }
.ct .tot-r td { padding:4px 4px; }
.ct .fine td { color:#b91c1c; }
.tr { text-align:right; }
.tc { text-align:center; }

.ln { background:#fff7ed; border-left:3px solid #f97316; padding:3px 6px; margin-top:3px;
      font-size:7px; color:#7c2d12; border-radius:0 2px 2px 0; }

.pst { width:100%; border-collapse:separate; border-spacing:4px 0; margin-top:3px; }
.s-paid { background:#dcfce7; color:#166534; border-radius:3px; padding:4px; text-align:center; }
.s-due  { background:#fee2e2; color:#991b1b; border-radius:3px; padding:4px; text-align:center; }
.slbl { font-size:7px; font-weight:bold; text-transform:uppercase; }
.sval { font-size:12px; font-weight:900; margin-top:1px; }
.ssub { font-size:7px; }

.ht { width:100%; border-collapse:collapse; font-size:7.5px; margin-top:3px; }
.ht thead tr { background:#374151; color:#fff; }
.ht thead th { padding:2px 4px; }
.ht tbody td { padding:3px 4px; border-bottom:1px solid #f1f5f9; }
.no-h { background:#f9fafb; border:1px solid #e5e7eb; border-radius:3px;
        padding:4px; text-align:center; color:#6b7280; font-size:7.5px; margin-top:3px; }

.notes-box { background:#f8fafc; border:1px solid #cbd5e1; border-radius:3px;
             padding:4px 6px; margin-top:4px; }
.notes-box .nt { font-size:7px; font-weight:bold; color:#475569; text-transform:uppercase; margin-bottom:2px; }
.notes-box ul { padding-left:10px; }
.notes-box li { font-size:7px; color:#64748b; line-height:1.3; }

.amt-box { background:#0f4423; color:#fff; border-radius:3px; padding:6px 8px; }
.amt-box table { width:100%; border-collapse:collapse; }
.abl { font-size:7.5px; font-weight:bold; opacity:0.8; }
.abv { font-size:18px; font-weight:900; margin:1px 0; }
.abn { font-size:7px; opacity:0.7; }
.abmo .mv { font-size:18px; font-weight:900; }
.abmo .ml { font-size:7px; opacity:0.7; }

.pm { border:1px solid #cbd5e1; border-radius:3px; padding:4px 6px; margin-top:4px; }
.pm.on { border-color:#1B6B35; }
.pmt { font-size:7.5px; font-weight:bold; color:#1B6B35; text-transform:uppercase; margin-bottom:2px; }
.pml { font-size:7.5px; line-height:1.3; color:#374151; }

.tearoff { border-top:1.5px dashed #94a3b8; margin:4px 12px 0; padding-top:4px; }
.tearoff table { width:100%; border-collapse:collapse; }
.tearoff td { font-size:7.5px; vertical-align:middle; padding:0 3px; }
.to-lbl { font-size:6.5px; color:#6b7280; text-transform:uppercase; font-weight:bold; }
.to-val { font-size:8.5px; font-weight:bold; color:#1a2332; }
.to-amount { font-size:12px; font-weight:900; color:#0f4423; }

.ftr { background:#f2f4f6; border-top:1.5px solid #1B6B35; padding:3px 12px; margin-top:4px; }
.ftr table { width:100%; border-collapse:collapse; }
.ftr td { font-size:7.5px; color:#6b7280; vertical-align:middle; }

.fw { font-weight:bold; }
.gr { color:#1B6B35; }
.rd { color:#b91c1c; }
</style>
</head>
<body>

<!-- HEADER -->
<div class="hdr">
  <table>
    <tr>
      <td width="45%">
        <table style="width:100%;border-collapse:collapse;">
          <tr>
            <td width="45" style="vertical-align:middle;padding-right:6px;">
              @if($phaLogoB64)
                <div style="width:36px;height:36px;border-radius:3px;background:#fff;padding:2px;">
                  <img src="{{ $phaLogoB64 }}" style="width:32px;height:32px;display:block;" alt="PHA">
                </div>
              @endif
            </td>
            <td style="vertical-align:middle;">
              <div class="org">PHA Foundation</div>
              <div class="sub">Ministry of Housing &amp; Works, Government of Pakistan</div>
            </td>
          </tr>
        </table>
      </td>
      <td width="30%">
        <div class="title">MAINTENANCE BILL</div>
        <div class="sub2" style="font-weight:bold; color:#dcfce7; margin-top:2px;">Month: {{ strtoupper($billMonth) }}</div>
      </td>
      <td width="25%" style="text-align:right;">
        <table style="width:100%;border-collapse:collapse;">
          <tr>
            <td style="vertical-align:middle;text-align:right;padding-right:6px;">
              @if($govtLogoB64)
                <div style="width:28px;height:28px;border-radius:50%;background:#fff;padding:2px;margin-left:auto;">
                  <img src="{{ $govtLogoB64 }}" style="width:24px;height:24px;display:block;" alt="Govt">
                </div>
              @endif
            </td>
            <td style="vertical-align:middle;width:80px;">
              <div class="mbox">
                <div class="ml">File No</div>
                <div class="mv" style="font-size:8.5px;">{{ $allottee->file_no }}</div>
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  <div class="hstrip">
    <table><tr>
      <td>Issue Date: <strong>{{ now()->format('d M Y') }}</strong></td>
      <td>Due Date: <strong>{{ now()->endOfMonth()->format('d M Y') }}</strong></td>
      <td>Category: <strong>{{ $allottee->category }}</strong></td>
      <td>Area: <strong>{{ $allottee->covered_area }} Sq Ft</strong></td>
      <td>Due Months: <strong>{{ $dueMonths }}</strong></td>
    </tr></table>
  </div>
</div>

<!-- ALLOTTEE & OCCUPANT STRIP -->
<div class="astrip">
  <table style="width:100%;">
    <tr>
      <td width="35%"><div class="al">Project Name</div><div class="av">PHA Apartments I-16/3 Islamabad</div></td>
      <td width="35%"><div class="al">Current Owner (Allottee)</div><div class="av">{{ $allottee->name }} (CNIC: {{ $allottee->cnic }})</div></td>
      <td width="30%"><div class="al">Occupancy Status</div><div class="av" style="color:#1B6B35; font-weight:bold;">{{ $allottee->occupancy_status == 'tenant_occupied' ? 'Tenant Occupied' : 'Owner Occupied' }}</div></td>
    </tr>
    @if($allottee->occupancy_status == 'tenant_occupied' && ($activeTenant = $allottee->activeTenant()))
    <tr>
      <td><div class="al">Tenant Name</div><div class="av">{{ $activeTenant->tenant_name }} (CNIC: {{ $activeTenant->tenant_cnic }})</div></td>
      <td><div class="al">Tenant Contact</div><div class="av">{{ $activeTenant->mobile_no }}</div></td>
      <td><div class="al">Tenancy Agreement Period</div><div class="av">{{ $activeTenant->agreement_start_date?->format('d M Y') ?? '—' }} to {{ $activeTenant->agreement_expiry_date?->format('d M Y') ?? '—' }}</div></td>
    </tr>
    @endif
    <tr>
      <td><div class="al">Apartment Details</div><div class="av">Block: {{ $allottee->block_no ?? '—' }} | Floor: {{ $allottee->floor ?? '—' }} | Flat: {{ $allottee->flat_no ?? '—' }}</div></td>
      @if($allottee->transfer_type)
      <td><div class="al">Ownership Details</div><div class="av" style="font-size:7.5px;">{{ ucfirst($allottee->transfer_type) }} | Since: {{ $allottee->ownership_start_date?->format('d M Y') ?? '—' }}</div></td>
      @else
      <td><div class="al">Office / Department</div><div class="av" style="font-size:7.5px;">{{ Str::limit($allottee->office_name ?? '—', 45) }}</div></td>
      @endif
      <td><div class="al">Cell / Mobile</div><div class="av">{{ $allottee->cell ?? '—' }}</div></td>
    </tr>
  </table>
</div>

<!-- BODY -->
<div class="body-wrap">
<table class="cols" style="margin-bottom: 4px;"><tr>

<!-- LEFT: BILL SUMMARY & STATUS -->
<td class="col-l">
  <div class="st" style="margin-top:2px;">Billing & Financial Summary</div>
  <table class="ct">
    <thead>
      <tr><th style="width:70%;">Description</th><th class="tr">Amount (Rs.)</th></tr>
    </thead>
    <tbody>
      @php
        $pCharge = $allottee->has_parking ? ($allottee->parking_charges ?: 500) : 0;
        $wCharge = $allottee->has_water ? ($allottee->water_charges ?: 1000) : 0;
        $currentCharges = ($allottee->covered_area * $rate) + $pCharge + $wCharge;
        $prevDues = max(0.00, $maintenance - $currentCharges);
      @endphp
      <tr>
        <td>
          <span class="fw">Previous Outstanding Balance</span><br>
          <span style="font-size:7px;color:#64748b;">Unpaid dues from prior billing cycles</span>
        </td>
        <td class="tr fw">{{ number_format($prevDues, 2) }}</td>
      </tr>
      <tr>
        <td>
          <span class="fw">Current Month Maintenance Charges</span><br>
          <span style="font-size:7px;color:#334155;">
            {{ $allottee->covered_area }} Sq Ft @ Rs. {{ number_format($rate, 2) }}/sq ft
            @if($pCharge > 0) + Parking Rs. {{ number_format($pCharge) }} @endif
            @if($wCharge > 0) + Water Rs. {{ number_format($wCharge) }} @endif
          </span>
        </td>
        <td class="tr fw">{{ number_format($currentCharges, 2) }}</td>
      </tr>
      @if($ww > 0)
      <tr>
        <td>
          <span class="fw">Watch &amp; Ward Charges</span><br>
          <span style="font-size:7px;color:#334155;">
            Accrued security fee (From {{ \Carbon\Carbon::parse($wwCutoff)->format('d M Y') }})
          </span>
        </td>
        <td class="tr fw">{{ number_format($ww, 2) }}</td>
      </tr>
      @endif
      @if($fine > 0)
      <tr class="fine">
        <td>
          <span class="fw">Delay Payment Charges (DPC)</span><br>
          <span style="font-size:7px;color:#ef4444;">
            {{ $delayPct }}% delay surcharge penalty applied on overdue balance
          </span>
        </td>
        <td class="tr fw rd">{{ number_format($fine, 2) }}</td>
      </tr>
      @endif
      <tr class="tot-r"><td>GROSS TOTAL PAYABLE</td><td class="tr">Rs. {{ number_format($total, 2) }}</td></tr>
      @if(($advanceAmount ?? 0) > 0)
      <tr style="background:#f0fdf4;">
        <td><span class="fw" style="color:#16a34a;">Advance Credit Carried Forward</span><br><span style="font-size:7px;color:#166534;">Prepaid surplus balance remaining for future bills</span></td>
        <td class="tr fw" style="color:#16a34a;">+Rs. {{ number_format($advanceAmount, 2) }}</td>
      </tr>
      @endif
    </tbody>
  </table>

  @if($fine > 0)
  <div class="ln"><span class="fw">NOTE:</span> Surcharge applied — {{ $dueMonths }} months overdue. Status: {{ strtoupper($displayStatus) }}.</div>
  @endif

  <div class="st">Account Balance Status</div>
  <table class="pst">
    <tr>
      <td class="s-paid">
        <div class="slbl">Amount Paid</div>
        <div class="sval">Rs. {{ number_format($paid, 2) }}</div>
        @if($paymentDate)
          <div class="ssub">{{ $paymentDate->format('d M Y') }} · {{ ucfirst($paymentMode ?? 'N/A') }}</div>
        @else
          <div class="ssub">No payment on record</div>
        @endif
      </td>
      <td width="4"></td>
      <td class="s-due">
        <div class="slbl">Amount Pending</div>
        <div class="sval">Rs. {{ number_format($pending, 2) }}</div>
        <div class="ssub">Status: {{ strtoupper($displayStatus) }}</div>
      </td>
    </tr>
  </table>

  <div class="st">Previous Payment History</div>
  @if($paymentsHistory->isNotEmpty())
  <table class="ht">
    <thead><tr><th>Date</th><th>Billing Month</th><th class="tr">Amount (Rs.)</th><th class="tc">Mode</th><th class="tr">Status</th></tr></thead>
    <tbody>
      @foreach($paymentsHistory->take(3) as $payment)
      <tr>
        <td>{{ $payment['date'] }}</td>
        <td>{{ $payment['month'] }}</td>
        <td class="tr fw">{{ number_format($payment['amount'], 2) }}</td>
        <td class="tc">{{ $payment['mode'] }}</td>
        <td class="tr fw gr">{{ $payment['status'] }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @else
  <div class="no-h">No previous payment records found.</div>
  @endif
</td>

<!-- RIGHT: PAYMENT PORTAL & INSTRUCTIONS -->
<td class="col-r">
  <div class="amt-box" style="margin-bottom:4px; @if($pending <= 0) background: linear-gradient(135deg, #15803d, #166534); @endif">
    <table>
      <tr>
        <td style="vertical-align:middle;" width="65%">
          @if($pending <= 0)
            <div class="abl" style="color:#dcfce7;">BILL STATUS</div>
            <div class="abv" style="color:#ffffff; font-size:14px;">PAID IN FULL</div>
            <div class="abn" style="color:#dcfce7; opacity:0.9;">No balance outstanding</div>
          @else
            <div class="abl">AMOUNT PAYABLE NOW</div>
            <div class="abv">Rs. {{ number_format($pending,2) }}</div>
            <div class="abn">Pay before {{ now()->endOfMonth()->format('d M Y') }}</div>
          @endif
        </td>
        <td style="vertical-align:middle;text-align:right;" width="35%">
          <div class="abmo">
            <div class="ml" style="opacity:0.7;font-size:7px;">OVERDUE</div>
            <div class="mv">{{ $dueMonths }}</div>
            <div class="ml" style="opacity:0.7;font-size:7px;">months</div>
          </div>
        </td>
      </tr>
    </table>
  </div>

  <div style="background:#f0fdf4; border:1px solid #16a34a; border-radius:4px; padding:5px; margin-bottom:4px; text-align:center;">
    <div style="margin-bottom:2px;">
      <img src="{{ $oneLinkB64 }}" style="height: 18px; vertical-align: middle; margin-right: 4px;">
      <span style="font-size:8px; font-weight:800; color:#166534; text-transform:uppercase; vertical-align: middle;">1-Bill Consumer No.</span>
    </div>
    <div style="font-size:11px; font-weight:900; letter-spacing:0.5px; color:#1a2332;">PHAF-{{ preg_replace('/[^A-Za-z0-9]/', '', $allottee->block_no ?? 'X') }}{{ str_pad(preg_replace('/[^0-9]/', '', $allottee->flat_no ?? '0'), 3, '0', STR_PAD_LEFT) }}-{{ date('Ym') }}</div>
  </div>

  <div style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:4px; padding:5px; margin-bottom:4px; text-align:center;">
    <div style="font-size:7.5px; font-weight:800; color:#475569; text-transform:uppercase; margin-bottom:2px;">Or Manual Bank Deposit</div>
    <div style="font-size:9px; font-weight:bold; color:#0f172a;">JS Bank Ltd</div>
    <div style="font-size:11px; font-weight:900; color:#0f4423; margin:1px 0;">A/C# 1490108</div>
    <div style="font-size:7.5px; font-weight:bold; color:#334155;">Title: PHA-F I-16/3 Maintenance Services</div>
  </div>

  <div class="pm" style="margin-bottom:4px;">
    <div class="pmt">Paying Over the Counter (Cash)</div>
    <div class="pml">
      Present this bill to JS Bank representative specifying the 1-Bill Consumer No. above.
    </div>
  </div>

  <div class="pm on">
    <div class="pmt">Pay via Mobile Wallet / Banking App</div>
    <div class="pml">
      Select <strong>1-Bill - Invoice</strong>, enter the Consumer No. above, and pay.
    </div>
  </div>

  @php
    $raastConfigured = (!empty($qrCodeB64) || !empty($qrData)) && !empty($bankAccNo);
  @endphp
  @if($raastConfigured)
  <div class="pm" style="border-color:#16a34a; background:#f0fdf4; padding:4px 6px;">
    <div class="pmt" style="color:#166534; font-size:7.5px; margin-bottom:3px; font-weight:bold;">RAAST QR PAYMENT</div>
    <table style="width:100%; border-collapse:collapse;">
      <tr>
        <td style="width:55px; vertical-align:middle; text-align:center; padding-right:4px;">
          @if(!empty($qrCodeB64))
            <img src="{{ $qrCodeB64 }}" style="width:48px; height:48px; border:1px solid #16a34a; border-radius:2px; padding:1px; background:#fff;">
          @endif
        </td>
        <td style="vertical-align:middle;">
          <div class="pml" style="line-height:1.2; font-size:7px; color:#374151;">
            Scan QR via any Raast-enabled mobile application to pay instantly.
          </div>
        </td>
      </tr>
    </table>
  </div>
  @endif

  <div class="notes-box">
    <div class="nt">&#9432; Important Instructions</div>
    <ul style="padding-left:10px;">
      <li>Pay before due date to avoid <strong>{{ $delayPct }}%</strong> late surcharge penalty.</li>
      <li>This is a computer-generated bill, signature or stamp not required.</li>
    </ul>
  </div>
</td>

</tr></table>
</div>

<!-- TEAR-OFF STRIP -->
<div class="tearoff">
  <table>
    <tr>
      <td width="3%" style="text-align:center;font-size:10px;color:#94a3b8;">✂</td>
      <td width="20%"><div class="to-lbl">Owner Name</div><div class="to-val">{{ Str::limit($allottee->name, 20) }}</div></td>
      <td width="15%"><div class="to-lbl">File No.</div><div class="to-val">{{ $allottee->file_no }}</div></td>
      <td width="15%"><div class="to-lbl">Bill Month</div><div class="to-val">{{ $billMonth }}</div></td>
      <td width="15%"><div class="to-lbl">Due Date</div><div class="to-val">{{ now()->endOfMonth()->format('d M Y') }}</div></td>
      <td width="32%" style="text-align:right;"><div class="to-lbl">Amount Payable</div><div class="to-amount" style="font-size:12px;">Rs. {{ number_format($pending, 2) }}</div></td>
    </tr>
  </table>
</div>

<!-- FOOTER -->
<div class="ftr">
  <table style="width:100%;">
    <tr>
      <td width="40%"><strong>PHA Foundation</strong> — Ministry of Housing &amp; Works, GoP</td>
      <td width="30%" style="text-align:center;">Generated: {{ now()->format('d M Y, h:i A') }}</td>
      <td width="30%" style="text-align:right;">Helpline: PHA Office, Islamabad</td>
    </tr>
  </table>
</div>

</body>
</html>
