<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>PHA Bill — {{ $allottee->file_no }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size:10px; color:#1a2332; background:#fff; }

.hdr { background:#0f4423; color:#fff; padding:10px 14px 7px; }
.hdr table { width:100%; border-collapse:collapse; }
.hdr td { vertical-align:middle; padding:0 4px; }
.hdr .org { font-size:13px; font-weight:bold; }
.hdr .sub { font-size:8.5px; opacity:0.75; margin-top:2px; }
.hdr .title { font-size:18px; font-weight:900; text-align:center; letter-spacing:0.5px; }
.hdr .sub2  { font-size:8.5px; text-align:center; opacity:0.7; margin-top:2px; }
.mbox { border:1px solid rgba(255,255,255,0.35); border-radius:4px; padding:6px 10px; text-align:center; }
.mbox .ml { font-size:8px; opacity:0.7; text-transform:uppercase; }
.mbox .mv { font-size:13px; font-weight:bold; margin-top:1px; }

.hstrip { background:rgba(0,0,0,0.3); padding:4px 14px; margin-top:6px; }
.hstrip table { width:100%; border-collapse:collapse; }
.hstrip td { font-size:8.5px; color:rgba(255,255,255,0.85); padding:0 5px; }

.astrip { background:#f2f4f6; border-bottom:2px solid #1B6B35; padding:7px 14px; }
.astrip table { width:100%; border-collapse:collapse; }
.astrip td { vertical-align:top; padding:3px 5px; }
.al { font-size:7.5px; color:#6b7280; font-weight:bold; text-transform:uppercase; }
.av { font-size:10px; font-weight:bold; color:#1a2332; margin-top:1px; }

.body-wrap { padding:7px 14px 6px; }
.cols { width:100%; border-collapse:separate; border-spacing:10px 0; }
.col-l { vertical-align:top; width:54%; }
.col-r { vertical-align:top; width:46%; }

.st { font-size:8px; font-weight:bold; letter-spacing:0.8px; text-transform:uppercase;
      color:#1B6B35; border-bottom:1px solid #d1d5db; padding-bottom:3px; margin:9px 0 5px; }

.ct { width:100%; border-collapse:collapse; font-size:9.5px; }
.ct thead tr { background:#1B6B35; color:#fff; }
.ct thead th { padding:5px 8px; font-size:8.5px; font-weight:bold; }
.ct tbody td { padding:6px 8px; border-bottom:1px solid #f0f0f0; }
.ct tbody tr:nth-child(even) { background:#fafafa; }
.ct .sub-r { background:#e8f5ee !important; font-weight:bold; }
.ct .tot-r { background:#0f4423; color:#fff; font-weight:bold; font-size:11px; }
.ct .tot-r td { padding:7px 8px; }
.ct .fine td { color:#b91c1c; }
.tr { text-align:right; }
.tc { text-align:center; }

.ln { background:#fff7ed; border-left:4px solid #f97316; padding:5px 9px; margin-top:5px;
      font-size:8.5px; color:#7c2d12; border-radius:0 4px 4px 0; }

.pst { width:100%; border-collapse:separate; border-spacing:5px 0; margin-top:6px; }
.s-paid { background:#dcfce7; color:#166534; border-radius:5px; padding:8px 7px; text-align:center; }
.s-due  { background:#fee2e2; color:#991b1b; border-radius:5px; padding:8px 7px; text-align:center; }
.slbl { font-size:8px; font-weight:bold; text-transform:uppercase; }
.sval { font-size:15px; font-weight:900; line-height:1.1; margin-top:2px; }
.ssub { font-size:8px; margin-top:2px; }

.ht { width:100%; border-collapse:collapse; font-size:8.5px; margin-top:5px; }
.ht thead tr { background:#374151; color:#fff; }
.ht thead th { padding:4px 7px; }
.ht tbody td { padding:5px 7px; border-bottom:1px solid #f0f0f0; }
.no-h { background:#f9fafb; border:1px solid #e5e7eb; border-radius:4px;
        padding:8px; text-align:center; color:#6b7280; font-size:8.5px; margin-top:5px; }

/* NOTES SECTION — fills bottom of left col */
.notes-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:5px;
             padding:8px 10px; margin-top:9px; }
.notes-box .nt { font-size:8px; font-weight:bold; color:#475569; text-transform:uppercase;
                 letter-spacing:0.5px; margin-bottom:5px; }
.notes-box ul { padding-left:12px; }
.notes-box li { font-size:8.5px; color:#64748b; line-height:1.8; }

/* RIGHT COL */
.amt-box { background:#0f4423; color:#fff; border-radius:6px; padding:14px 14px; }
.amt-box table { width:100%; border-collapse:collapse; }
.abl { font-size:9px; font-weight:bold; opacity:0.8; }
.abv { font-size:26px; font-weight:900; line-height:1.1; margin:4px 0; }
.abn { font-size:8px; opacity:0.65; }
.abmo .mv { font-size:30px; font-weight:900; }
.abmo .ml { font-size:8px; opacity:0.7; }

.pm { border:1px solid #d1d5db; border-radius:4px; padding:8px 10px; margin-top:7px; }
.pm.on { border-color:#1B6B35; }
.pmt { font-size:8.5px; font-weight:bold; color:#1B6B35; text-transform:uppercase; margin-bottom:4px; }
.pml { font-size:9px; line-height:1.7; color:#374151; }

.qrb { border:1px solid #1B6B35; border-radius:4px; padding:8px 10px; margin-top:7px; background:#f9fafb; }
.qrp { width:72px; height:72px; border:2px dashed #1B6B35; border-radius:4px;
       margin:0 auto 4px; background:#fff; font-size:7px; color:#1B6B35;
       padding:6px 3px; line-height:1.5; text-align:center; }

/* TEAR-OFF STRIP */
.tearoff { border-top:2px dashed #94a3b8; margin:8px 14px 0; padding-top:6px; }
.tearoff table { width:100%; border-collapse:collapse; }
.tearoff td { font-size:8.5px; vertical-align:middle; padding:0 6px; }
.to-lbl { font-size:7.5px; color:#6b7280; text-transform:uppercase; font-weight:bold; }
.to-val { font-size:10px; font-weight:bold; color:#1a2332; }
.to-amount { font-size:16px; font-weight:900; color:#0f4423; }

.mbox2 { background:#f9fafb; border:1px dashed #d1d5db; border-radius:4px;
          padding:5px 9px; font-size:9px; vertical-align:top; }
.mlbl { font-size:7.5px; font-weight:bold; color:#6b7280; text-transform:uppercase; margin-bottom:1px; }

.ftr { background:#f2f4f6; border-top:2px solid #1B6B35; padding:5px 14px; }
.ftr table { width:100%; border-collapse:collapse; }
.ftr td { font-size:8.5px; color:#6b7280; vertical-align:middle; padding:0 4px; }

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
            <td width="55" style="vertical-align:middle;padding-right:8px;">
              @if($phaLogoB64)
                <div style="width:48px;height:48px;border-radius:4px;background:#fff;padding:3px;">
                  <img src="{{ $phaLogoB64 }}" style="width:42px;height:42px;display:block;" alt="PHA">
                </div>
              @endif
            </td>
            <td style="vertical-align:middle;">
              <div class="org" style="font-size:20px;">PHA Foundation</div>
              <div class="sub">Ministry of Housing &amp; Works, Government of Pakistan</div>
              <div class="sub">Maintenance Billing System — I-16/3 Islamabad</div>
            </td>
          </tr>
        </table>
      </td>
      <td width="30%">
        <div class="title">MAINTENANCE BILL</div>
        <div class="sub2" style="font-size:10px; font-weight:bold; color:#dcfce7; margin-top:4px;">BILL MONTH: {{ strtoupper($billMonth) }}</div>
      </td>
      <td width="25%" style="text-align:right;">
        <table style="width:100%;border-collapse:collapse;">
          <tr>
            <td style="vertical-align:middle;text-align:right;padding-right:8px;">
              @if($govtLogoB64)
                <div style="width:38px;height:38px;border-radius:50%;background:#fff;padding:3px;margin-left:auto;">
                  <img src="{{ $govtLogoB64 }}" style="width:32px;height:32px;display:block;" alt="Govt">
                </div>
              @endif
            </td>
            <td style="vertical-align:middle;">
              <div class="mbox">
                <div class="ml">File No</div>
                <div class="mv">{{ $allottee->file_no }}</div>
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
      <td>Cat: <strong>{{ $allottee->category }}-Type</strong></td>
      <td>Area: <strong>{{ $allottee->covered_area }} Sq Ft</strong></td>
      <td>Due Months: <strong>{{ $dueMonths }}</strong></td>
      <td>Membership: <strong>{{ $allottee->membership_no ?? '—' }}</strong></td>
    </tr></table>
  </div>
</div>

<!-- ALLOTTEE STRIP -->
<div class="astrip">
  <table>
    <tr>
      <td width="28%"><div class="al">Allottee Name</div><div class="av">{{ $allottee->display_name }}</div></td>
      <td width="20%"><div class="al">CNIC</div><div class="av">{{ $allottee->display_cnic }}</div></td>
      <td width="16%"><div class="al">Cell / Mobile</div><div class="av">{{ $allottee->cell ?? '—' }}</div></td>
      <td width="20%"><div class="al">Block / Floor / Flat</div><div class="av">Blk {{ $allottee->block_no ?? '—' }} / Fl {{ $allottee->floor ?? '—' }} / Flat {{ $allottee->flat_no ?? '—' }}</div></td>
      <td width="16%"><div class="al">BPS / Possession</div><div class="av">{{ $allottee->bps ? 'BPS-'.$allottee->bps : '—' }} / {{ $allottee->possession_date?->format('d-m-Y') ?? 'N/A' }}</div></td>
    </tr>
    <tr>
      <td colspan="3"><div class="al">Office / Department</div><div class="av" style="font-size:9px;">{{ Str::limit($allottee->office_name ?? '—', 55) }} — {{ Str::limit($allottee->post_held ?? '', 30) }}</div></td>
      <td colspan="2"><div class="al">City</div><div class="av">{{ $allottee->city ?? '—' }}</div></td>
    </tr>
  </table>
</div>

<!-- BODY -->
<div class="body-wrap">
<table class="cols"><tr>

<!-- LEFT -->
<td class="col-l">
  <div class="st">Current Bill — {{ strtoupper($billMonth) }}</div>
  <table class="ct">
    <thead>
      <tr><th style="width:44%;">Description</th><th class="tc">Rate</th><th class="tc">Months</th><th class="tr">Amount (Rs.)</th></tr>
    </thead>
    <tbody>
      @if($dueMonths > 1)
      <tr>
        <td><span class="fw">Previous Arrears (Maintenance)</span><br><span style="font-size:8px;color:#6b7280;">Past due balance for {{ $dueMonths - 1 }} month(s)</span></td>
        <td class="tc">{{ number_format($rate,2) }}</td>
        <td class="tc">{{ $dueMonths - 1 }}</td>
        <td class="tr fw">{{ number_format($maintenance - $monthlyRate,2) }}</td>
      </tr>
      @endif
      @if($dueMonths > 0)
      <tr>
        <td><span class="fw">Current Month Maintenance</span><br><span style="font-size:8px;color:#6b7280;">{{ $allottee->covered_area }} Sq Ft × Rs.{{ number_format($rate,2) }}/Sq Ft</span></td>
        <td class="tc">{{ number_format($rate,2) }}</td>
        <td class="tc">1</td>
        <td class="tr fw">{{ number_format($monthlyRate,2) }}</td>
      </tr>
      @endif
      @if($ww > 0)
      <tr>
        <td><span class="fw">Watch &amp; Ward Charges</span><br><span style="font-size:8px;color:#6b7280;">From max(Possession Date, 01-Jul-2023)</span></td>
        <td class="tc">{{ number_format($wwAmount) }}</td>
        <td class="tc">{{ $wwMonths }} month(s)</td>
        <td class="tr fw">{{ number_format($ww,2) }}</td>
      </tr>
      @endif
      <tr class="sub-r"><td colspan="3" class="fw">Sub-Total (Maintenance + W&amp;W)</td><td class="tr fw">{{ number_format($maintenance+$ww,2) }}</td></tr>
      @if($fine > 0)
      <tr class="fine">
        <td><span class="fw">Delay Surcharge ({{ $delayPct }}% Fine)</span><br><span style="font-size:8px;">Late payment penalty</span></td>
        <td class="tc rd">{{ $delayPct }}%</td><td class="tc rd">On sub-total</td>
        <td class="tr fw rd">{{ number_format($fine,2) }}</td>
      </tr>
      @endif
      <tr class="tot-r"><td colspan="3">GROSS TOTAL PAYABLE</td><td class="tr">Rs. {{ number_format($total,2) }}</td></tr>
    </tbody>
  </table>

  @if($fine > 0)
  <div class="ln"><span class="fw">NOTE:</span> {{ $delayPct }}% surcharge applied — {{ $dueMonths }} months overdue. Pay by {{ now()->endOfMonth()->format('d M Y') }} to avoid further penalties.</div>
  @endif

  <div class="st">Payment Status</div>
  <table class="pst">
    <tr>
      <td class="s-paid"><div class="slbl">Amount Paid</div><div class="sval">Rs. {{ number_format($paid,2) }}</div>@if($allottee->payment_date)<div class="ssub">{{ $allottee->payment_date->format('d M Y') }} · {{ ucfirst($allottee->payment_mode ?? '') }}</div>@else<div class="ssub">No payment on record</div>@endif</td>
      <td width="6"></td>
      <td class="s-due"><div class="slbl">Amount Due</div><div class="sval">Rs. {{ number_format($pending,2) }}</div><div class="ssub">Due: {{ now()->endOfMonth()->format('d M Y') }}</div></td>
    </tr>
  </table>

  <div class="st">Previous Payment History</div>
  @if($lastPayment)
  <table class="ht">
    <thead><tr><th>Date</th><th class="tr">Amount (Rs.)</th><th class="tc">Mode</th><th>Reference</th><th class="tr">Status</th></tr></thead>
    <tbody><tr>
      <td>{{ $lastPayment['date'] }}</td>
      <td class="tr fw">{{ number_format($lastPayment['amount'],2) }}</td>
      <td class="tc">{{ $lastPayment['mode'] }}</td>
      <td>{{ $lastPayment['ref'] }}</td>
      <td class="tr fw gr">RECEIVED</td>
    </tr></tbody>
  </table>
  @else
  <div class="no-h">No previous payment records found. Contact PHA office if you have already paid.</div>
  @endif

  @if($allottee->mailing_address)
  <div class="st">Addresses</div>
  <table style="width:100%;border-collapse:separate;border-spacing:5px 0;">
    <tr>
      <td class="mbox2" width="50%"><div class="mlbl">Mailing Address</div><div class="fw" style="font-size:9.5px;">{{ $allottee->display_name }}</div><div>{{ $allottee->mailing_address }}</div></td>
      <td class="mbox2" width="50%"><div class="mlbl">Property Address</div><div class="fw" style="font-size:9.5px;">Flat {{ $allottee->flat_no ?? '—' }}, Floor {{ $allottee->floor ?? '—' }}, Block {{ $allottee->block_no ?? '—' }}</div><div>I-16/3, Islamabad</div></td>
    </tr>
  </table>
  @endif

  <!-- IMPORTANT NOTES - fills remaining left-column space -->
  <div class="notes-box">
    <div class="nt">&#9432; Important Instructions</div>
    <ul>
      <li>Payment must be made before the due date to avoid additional surcharge.</li>
      <li>A <strong>{{ $delayPct }}%</strong> late payment surcharge applies on unpaid balances.</li>
      <li>Keep payment receipt / bank slip as proof of payment.</li>
      <li>For queries, contact PHA office: Ministry of Housing &amp; Works, Islamabad.</li>
      <li>Online payment via Raast/1Link: use File No. <strong>{{ $allottee->file_no }}</strong> as reference.</li>
      <li>This is a computer-generated bill. No signature or stamp is required.</li>
      <li>Watch &amp; Ward charges apply if possession date is on/after 23-Jul-2023 or not recorded.</li>
    </ul>
  </div>
</td>

<!-- RIGHT -->
<td class="col-r">
  <div class="amt-box" style="margin-bottom:8px;">
    <table>
      <tr>
        <td style="vertical-align:middle;" width="62%">
          <div class="abl">AMOUNT PAYABLE NOW</div>
          <div class="abv">Rs. {{ number_format($pending,2) }}</div>
          <div class="abn">Pay before {{ now()->endOfMonth()->format('d M Y') }}</div>
        </td>
        <td style="vertical-align:middle;text-align:right;" width="38%">
          <div class="abmo">
            <div class="ml" style="opacity:0.7;font-size:8px;">OVERDUE</div>
            <div class="mv">{{ $dueMonths }}</div>
            <div class="ml" style="opacity:0.7;font-size:8px;">months</div>
          </div>
        </td>
      </tr>
    </table>
  </div>

  <div style="background:#f0fdf4; border:1px solid #16a34a; border-radius:6px; padding:8px; margin-bottom:8px; text-align:center;">
    <div style="margin-bottom:4px;">
      <img src="{{ $oneLinkB64 }}" style="height: 28px; vertical-align: middle; margin-right: 6px;">
      <span style="font-size:10px; font-weight:800; color:#166534; text-transform:uppercase; vertical-align: middle;">1-Bill Consumer No.</span>
    </div>
    <div style="font-size:14px; font-weight:900; letter-spacing:1px; color:#1a2332;">PHAF-{{ preg_replace('/[^A-Za-z0-9]/', '', $allottee->block_no ?? 'X') }}{{ str_pad(preg_replace('/[^0-9]/', '', $allottee->flat_no ?? '0'), 3, '0', STR_PAD_LEFT) }}-{{ date('Ym') }}</div>
  </div>

  <div style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:6px; padding:8px; margin-bottom:8px; text-align:center;">
    <div style="font-size:9px; font-weight:800; color:#475569; text-transform:uppercase; margin-bottom:4px;">Or Manual Bank Deposit</div>
    <div style="font-size:11px; font-weight:bold; color:#0f172a;">JS Bank Ltd</div>
    <div style="font-size:13px; font-weight:900; letter-spacing:1px; color:#0f4423; margin:2px 0;">A/C# 1490108</div>
    <div style="font-size:9px; font-weight:bold; color:#334155;">Title: PHA-F I-16/3 Maintenance Services</div>
  </div>

  <div style="background:#1B6B35; color:#fff; font-size:8px; font-weight:bold; text-align:center; padding:5px; border-radius:4px 4px 0 0;">
    PAY ONLINE VIA 1-BILL THROUGH
  </div>
  <div style="background:#f8fafc; border:1px solid #e2e8f0; border-top:none; text-align:center; font-size:7.5px; font-weight:bold; color:#475569; padding:4px; margin-bottom:8px; border-radius:0 0 4px 4px;">
    easypaisa &middot; JazzCash &middot; Mobile Banking &middot; Internet Banking &middot; Over the Counter
  </div>

  <div class="pm" style="margin-bottom:6px;">
    <div class="pmt" style="background:#1B6B35;">Paying Over the Counter (Cash Payment)</div>
    <div class="pml" style="line-height:1.4;">
      <strong>1.</strong> Present this Invoice to Bank Representative specifying 1-Bill Consumer No. to be paid through 1-Bill - Invoice.<br>
      <strong>2.</strong> Hand-over Cash to Representative.<br>
      <strong>3.</strong> Collect relevant Payment Receipt (Computer Printed Receipt or manual receipt with Bank Stamp) and its <strong>DONE.</strong>
    </div>
  </div>

  <div class="pm on">
    <div class="pmt" style="background:#1B6B35;">Pay via Mobile Wallets / Internet / Mobile Banking</div>
    <div class="pml" style="line-height:1.4;">
      <strong>1.</strong> Login to your Mobile Wallet / Internet Banking / Mobile Banking Account.<br>
      <strong>2.</strong> Tap/Select <strong>1-Bill - Invoice</strong> option.<br>
      <strong>3.</strong> Enter 1-Bill Consumer No. and complete Transaction. You are <strong>DONE.</strong>
    </div>
  </div>
</td>

</tr></table>
</div>

<!-- TEAR-OFF STRIP -->
<div class="tearoff">
  <table>
    <tr>
      <td width="4%" style="text-align:center;font-size:14px;color:#94a3b8;">✂</td>
      <td width="15%"><div class="to-lbl">Allottee Name</div><div class="to-val">{{ Str::limit($allottee->display_name, 20) }}</div></td>
      <td width="14%"><div class="to-lbl">File No.</div><div class="to-val">{{ $allottee->file_no }}</div></td>
      <td width="14%"><div class="to-lbl">CNIC</div><div class="to-val" style="font-size:9px;">{{ $allottee->display_cnic }}</div></td>
      <td width="13%"><div class="to-lbl">Bill Month</div><div class="to-val">{{ $billMonth }}</div></td>
      <td width="14%"><div class="to-lbl">Due Date</div><div class="to-val">{{ now()->endOfMonth()->format('d M Y') }}</div></td>
      <td width="26%" style="text-align:right;"><div class="to-lbl">Amount Due</div><div class="to-amount">Rs. {{ number_format($pending,2) }}</div></td>
    </tr>
  </table>
</div>

<!-- FOOTER -->
<div class="ftr" style="margin-top:6px;">
  <table>
    <tr>
      <td width="38%"><strong>PHA Foundation</strong> — Ministry of Housing &amp; Works, GoP<br>I-16/3 Islamabad · maintenance@pha.gov.pk</td>
      <td width="30%" style="text-align:center;"><strong>Computer Generated — No Signature Required</strong><br>Generated: {{ now()->format('d M Y, h:i A') }}</td>
      <td width="32%" style="text-align:right;">Helpline: PHA Office, Islamabad<br>Web: www.pha.gov.pk</td>
    </tr>
  </table>
</div>

</body>
</html>
