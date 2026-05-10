<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bank Challan - {{ $allottee->file_no }}</title>
    <style>
        @page { size: a4 landscape; margin: 10px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9px; line-height: 1.2; color: #000; margin: 0; padding: 0; }
        
        .main-table { width: 100%; table-layout: fixed; border-collapse: collapse; }
        .main-table > tbody > tr > td { 
            width: 25%; 
            vertical-align: top; 
            padding: 5px 10px;
            border-right: 1px dashed #666;
        }
        .main-table > tbody > tr > td:last-child { border-right: none; }
        
        /* Inner Copy Structure */
        .header { text-align: center; margin-bottom: 8px; }
        .logo { width: 35px; height: 35px; margin-bottom: 2px; }
        .org-name { font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .project-name { font-size: 9px; font-weight: bold; margin: 3px 0; }
        .copy-type { font-size: 10px; font-weight: bold; margin: 4px 0; }
        .date-line { text-align: right; font-size: 8px; margin-bottom: 8px; }
        
        .psid-box { 
            border: 1px solid #ccc; padding: 4px; margin-bottom: 8px; 
            display: table; width: 100%; border-radius: 4px;
        }
        .psid-box td { vertical-align: middle; }
        .psid-label { font-size: 8px; font-weight: bold; color: #f59e0b; }
        .psid-val { font-size: 10px; font-weight: bold; letter-spacing: 1px; }
        .qr-td { text-align: right; width: 35px; }
        .qr-img { width: 35px; height: 35px; }
        
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .info-table td { padding: 3px 0; font-size: 8px; vertical-align: top; }
        .info-lbl { font-weight: bold; width: 45px; }
        .info-val { border-bottom: 1px solid #ccc; padding-left: 5px; }
        
        .charges-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .charges-table th { border: 1px solid #000; padding: 4px; font-size: 8px; background: #f3f4f6; text-align: left; }
        .charges-table th.amt { text-align: right; width: 50px; }
        .charges-table td { border: 1px solid #000; padding: 4px; font-size: 8px; }
        .charges-table td.amt { text-align: right; }
        
        .totals-row td { font-weight: bold; }
        
        .words-lbl { font-weight: bold; font-size: 8px; margin-top: 5px; }
        .words-val { font-size: 8px; font-style: italic; border-bottom: 1px solid #ccc; padding-bottom: 2px; min-height: 12px; }
        
        .bank-use { border: 1px solid #000; margin-top: 10px; height: 75px; position: relative; }
        .bank-use-title { text-align: center; font-size: 8px; font-weight: bold; border-bottom: 1px solid #000; padding: 3px; background: #f3f4f6; }
        .bank-collected { font-size: 8px; padding: 4px; }
        .bank-stamp { position: absolute; bottom: 4px; left: 4px; font-size: 8px; font-weight: bold; }
        .bank-manager { position: absolute; bottom: 4px; right: 4px; font-size: 8px; font-weight: bold; border-top: 1px solid #000; width: 60px; text-align: center; padding-top: 2px; }
        
        .footer { font-size: 7px; text-align: center; margin-top: 8px; font-weight: bold; color: #1B6B35; }
        
        /* Helpers */
        .fw { font-weight: bold; }
    </style>
</head>
<body>

@php
    $copies = ['Office Copy', 'Customer Copy', 'Revenue Copy', 'Bank Copy'];
    
    // Calculate breakdown
    $currentMaint = round($rate * $allottee->covered_area, 2);
    $arrears = max(0, $allottee->maintenance_charges - $currentMaint);
    
    // PSID logic
    $psid = App\Models\Bill::generatePsid($allottee, date('Y-m'));
    
    // Due Date
    $dueDate = \Carbon\Carbon::now()->endOfMonth()->format('d M Y');
@endphp

<table class="main-table">
    <tbody>
        <tr>
            @foreach($copies as $copyName)
            <td>
                <!-- Header -->
                <div class="header">
                    <img src="{{ $phaLogoB64 }}" class="logo" alt="PHA">
                    <div class="org-name">Pakistan Housing Authority</div>
                    <div class="project-name">Maintenance Charges Challan</div>
                    <div class="copy-type">{{ $copyName }}</div>
                </div>
                
                <div class="date-line">
                    <span class="fw">Date:</span> {{ date('d-M-Y') }}
                </div>
                
                <!-- PSID Box -->
                <table class="psid-box">
                    <tr>
                        <td>
                            <div class="psid-label">1-BILL INVOICE ID:</div>
                            <div class="psid-val">{{ $psid }}</div>
                        </td>
                        <td class="qr-td">
                            <img src="{{ $qrCodeB64 }}" class="qr-img">
                        </td>
                    </tr>
                </table>
                
                <!-- Info -->
                <table class="info-table">
                    <tr>
                        <td class="info-lbl">Name:</td>
                        <td class="info-val">{{ $allottee->display_name }}</td>
                    </tr>
                    <tr>
                        <td class="info-lbl">Address:</td>
                        <td class="info-val">Flat {{ $allottee->flat_no }}, Blk {{ $allottee->block_no }}</td>
                    </tr>
                    <tr>
                        <td class="info-lbl">Project:</td>
                        <td class="info-val">{{ $allottee->city }} Sector</td>
                    </tr>
                    <tr>
                        <td class="info-lbl">File No:</td>
                        <td class="info-val">{{ $allottee->file_no }}</td>
                    </tr>
                    <tr>
                        <td class="info-lbl mt-2">Due Date:</td>
                        <td class="info-val fw" style="text-align: right;">{{ $dueDate }}</td>
                    </tr>
                </table>
                
                <!-- Charges -->
                <table class="charges-table">
                    <thead>
                        <tr>
                            <th>Payment Description</th>
                            <th class="amt">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($arrears > 0)
                        <tr>
                            <td>Previous Arrears ({{ max(0, $allottee->due_months - 1) }} months)</td>
                            <td class="amt">{{ number_format($arrears) }}</td>
                        </tr>
                        @endif
                        @if($currentMaint > 0)
                        <tr>
                            <td>Current Maintenance (1 month)</td>
                            <td class="amt">{{ number_format($currentMaint) }}</td>
                        </tr>
                        @endif
                        @if($allottee->watch_ward_charges > 0)
                        <tr>
                            <td>Watch & Ward Charges</td>
                            <td class="amt">{{ number_format($allottee->watch_ward_charges) }}</td>
                        </tr>
                        @endif
                        @if($allottee->fine > 0)
                        <tr>
                            <td>Delay Surcharge (Fine)</td>
                            <td class="amt">{{ number_format($allottee->fine) }}</td>
                        </tr>
                        @endif
                        
                        <tr class="totals-row">
                            <td style="text-align: right;">Sub-Total:</td>
                            <td class="amt">{{ number_format($total) }}</td>
                        </tr>
                        <tr class="totals-row">
                            <td style="text-align: right; background: #f3f4f6;">Total Payable:</td>
                            <td class="amt" style="background: #f3f4f6;">{{ number_format($total) }}</td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- Amount in words -->
                <div class="words-lbl">Amount (in words):</div>
                <div class="words-val">Rupees {{ $amountInWords }} Only</div>
                
                <!-- Bank Use -->
                <div class="bank-use">
                    <div class="bank-use-title">For Bank Use Only</div>
                    <div class="bank-collected">Fee collected By:</div>
                    
                    <div class="bank-stamp">Stamp</div>
                    <div class="bank-manager">Bank Manager</div>
                </div>
                
                <!-- Footer -->
                <div class="footer" style="padding-top: 4px;">
                    <img src="{{ $oneLinkB64 }}" style="height: 25px; vertical-align: middle; margin-right: 4px;">
                    <span style="vertical-align: middle;">1Link Payment Gateway - PHA</span>
                </div>
            </td>
            @endforeach
        </tr>
    </tbody>
</table>

</body>
</html>
