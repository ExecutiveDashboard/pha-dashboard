<?php

namespace App\Http\Controllers;

use App\Models\Allottee;
use App\Models\Setting;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use ZipArchive;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BillController extends Controller
{
    /* ── shared: build all bill data for one allottee ── */
    private function billData(Allottee $allottee): array
    {
        $activeProject = \App\Models\Project::active();
        $rate = (float) Setting::getValue('maintenance_rate_per_sqft', 3.07);
        $wwAmount = (float) Setting::getValue('watch_ward_amount', 10000);
        $wwCutoff = Setting::getValue('watch_ward_cutoff_date', '2023-07-23');
        $delayPct = (float) Setting::getValue('delay_charge_percent', 10);
        
        if ($activeProject) {
            $rate = $activeProject->maintenance_rate;
            $wwAmount = $activeProject->ww_amount;
            $wwCutoff = $activeProject->ww_cutoff_date;
            $delayPct = $activeProject->delay_percent;
        }

        $bankAccNo = Setting::getValue('bank_account_no', 'PHA-0001-0001-001');
        $bankName = Setting::getValue('bank_name', 'National Bank of Pakistan');
        $bankBranch = Setting::getValue('bank_branch', 'Islamabad Main Branch');

        $monthlyRate = $allottee->covered_area * $rate;

        // Calculate maximum allowed month dynamically
        $currentBillingMonthSetting = Setting::getValue('current_billing_month', '2026-07');
        $allowFutureBilling = (bool) Setting::getValue('allow_future_billing', 0);
        $maxMonthsAhead = (int) Setting::getValue('max_billing_months_ahead', 1);

        if ($allowFutureBilling) {
            $maxAllowedDate = Carbon::createFromFormat('Y-m', $currentBillingMonthSetting)->addMonths($maxMonthsAhead);
            $maxAllowedMonth = $maxAllowedDate->format('Y-m');
        } else {
            $maxAllowedMonth = $currentBillingMonthSetting;
        }

        // Check if there is a generated bill snapshot in the database (restricted to allowed month)
        $latestBill = \App\Models\Bill::withoutGlobalScopes()
            ->where('allottee_id', $allottee->id)
            ->where('bill_month', '<=', $maxAllowedMonth)
            ->orderByDesc('bill_month')
            ->first();

        $advanceAmount = 0.00;

        if ($latestBill) {
            $maintenance   = (float)$latestBill->maintenance_amount;
            $ww            = (float)$latestBill->ww_amount;
            $wwMonths      = $wwAmount > 0 ? (float)($ww / $wwAmount) : 0;
            $fine          = (float)$latestBill->fine_amount;
            $total         = $maintenance + $ww + $fine;
            $paid          = (float)$latestBill->paid_amount;
            $pending       = max(0.00, (float)($latestBill->total_amount - $paid));
            $dueMonths     = $allottee->overdue_months ?? 0;
            $billMonth     = Carbon::createFromFormat('Y-m', $latestBill->bill_month)->format('F Y');
            $status        = $latestBill->status;
            $displayStatus = $latestBill->display_status;
            $paymentDate   = $latestBill->payment_date;
            $paymentMode   = $latestBill->payment_mode;

            // Calculate advance credit: if total paid exceeds total generated cumulative charges
            $cumulativeGenerated = (float)($latestBill->maintenance_amount + $latestBill->ww_amount + $latestBill->fine_amount);
            if ((float)$allottee->amount_paid > $cumulativeGenerated) {
                $advanceAmount = (float)($allottee->amount_paid - $cumulativeGenerated);
            }
        } else {
            $maintenance   = $allottee->maintenance_charges;
            $wwStartDate   = Carbon::create(2023, 7, 1);
            $wwEndDate     = $allottee->possession_date ? clone $allottee->possession_date : Carbon::now();
            $wwMonths      = 0;
            if ($wwEndDate->gt($wwStartDate)) {
                $wwMonths = $wwStartDate->diffInMonths($wwEndDate);
            }
            $ww            = $wwMonths * $wwAmount;
            $fine          = $allottee->fine;
            $total         = $maintenance + $ww + $fine;
            $paid          = (float)$allottee->amount_paid;
            $pending       = max(0.00, $total - $paid);
            $dueMonths     = $allottee->overdue_months ?? 0;
            $billMonth     = Carbon::createFromFormat('Y-m', $currentBillingMonthSetting)->format('F Y');
            $status        = 'unpaid';
            $displayStatus = 'Unpaid';
            $paymentDate   = $allottee->payment_date;
            $paymentMode   = $allottee->payment_mode;

            if ((float)$allottee->amount_paid > $total) {
                $advanceAmount = (float)($allottee->amount_paid - $total);
            }
        }

        // Build list of all payment allocations from historical bills
        $paymentsHistory = \App\Models\Bill::withoutGlobalScopes()
            ->where('allottee_id', $allottee->id)
            ->where('paid_amount', '>', 0)
            ->orderByDesc('payment_date')
            ->orderByDesc('bill_month')
            ->get()
            ->map(function ($b) {
                return [
                    'date' => $b->payment_date ? $b->payment_date->format('d M Y') : '—',
                    'amount' => (float)$b->paid_amount,
                    'mode' => ucfirst($b->payment_mode ?? 'N/A'),
                    'ref' => $b->payment_ref ?? '—',
                    'month' => Carbon::createFromFormat('Y-m', $b->bill_month)->format('M Y'),
                    'status' => $b->display_status,
                ];
            });

        // Generate official Raast EMVCo (TLV) QR code payload
        $formatTlv = function ($id, $val) {
            return sprintf("%02s%02d%s", $id, strlen($val), $val);
        };
        $amountStr = number_format(max(0, $pending), 2, '.', '');
        $tlv  = $formatTlv('00', '01'); // Payload Format Indicator
        $tlv .= $formatTlv('01', '12'); // Point of Initiation Method (12 = Dynamic)
        $sub28 = $formatTlv('00', 'pk.com.raast') . $formatTlv('01', $bankAccNo ?: 'PK00RAAST00000000000000');
        $tlv .= $formatTlv('28', $sub28); // Raast Merchant Account Info
        $tlv .= $formatTlv('52', '8699'); // MCC: Government Services
        $tlv .= $formatTlv('53', '586');  // Currency: 586 = PKR
        $tlv .= $formatTlv('54', $amountStr); // Transaction Amount
        $tlv .= $formatTlv('58', 'PK');   // Country Code
        $tlv .= $formatTlv('59', substr('PHA Foundation', 0, 25)); // Merchant Name
        $tlv .= $formatTlv('60', substr('Islamabad', 0, 15));      // Merchant City
        $sub62 = $formatTlv('01', substr($allottee->file_no ?? 'INV', 0, 25));
        $tlv .= $formatTlv('62', $sub62); // Additional Data: Bill Reference

        // Project I-16/3 identification extension (Tag 80 Unreserved Template in safe ASCII pipe format)
        $projName = strtoupper($allottee->project?->name ?? '');
        $projCode = strtoupper($allottee->project?->code ?? '');
        if ($allottee->project_id == 1 || str_contains($projName, 'I-16/3') || str_contains($projCode, 'I163')) {
            $cleanAscii = function ($str) {
                $cleaned = preg_replace('/[^\x20-\x7E]/', '', (string)$str);
                $cleaned = str_replace(['{', '}', '"', "'", '|', "\r", "\n"], '', $cleaned);
                return trim(preg_replace('/\s+/', ' ', $cleaned));
            };

            $parts = [];
            $name = $cleanAscii($allottee->name ?? '');
            if ($name !== '') $parts[] = "name={$name}";
            $parts[] = "project=I-16/3";
            $block = $cleanAscii($allottee->block_no ?? '');
            if ($block !== '') $parts[] = "block={$block}";
            $floor = $cleanAscii($allottee->floor ?? '');
            if ($floor !== '') $parts[] = "floor={$floor}";
            $flat = $cleanAscii($allottee->flat_no ?? '');
            if ($flat !== '') $parts[] = "flat={$flat}";

            $asciiPayload = implode('|', $parts);
            if (!empty($asciiPayload) && strlen($asciiPayload) <= 99) {
                $tlv .= $formatTlv('80', $asciiPayload);
            }
        }

        $payloadForCrc = $tlv . '6304';
        $crc = 0xFFFF;
        $bytes = unpack('C*', $payloadForCrc);
        foreach ($bytes as $byte) {
            $crc ^= ($byte << 8);
            for ($i = 0; $i < 8; $i++) {
                if (($crc & 0x8000) > 0) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }
        $qrData = $payloadForCrc . strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));

        // Generate QR code as SVG (no Imagick required), embed as base64 data URI
        try {
            $qrSvgRaw = (string) QrCode::format('svg')
                ->size(110)
                ->margin(1)
                ->color(15, 68, 35)
                ->generate($qrData);
            $qrSvg    = $qrSvgRaw; // keep raw for web view
            $qrCodeB64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvgRaw);
        } catch (\Exception $e) {
            $qrSvg     = '';
            $qrCodeB64 = '';
        }

        // Logos as base64 for DomPDF
        $govtLogoB64  = $this->logoBase64(public_path('images/logos/govt-pk.svg'),  'image/svg+xml');
        $phaLogoB64   = $this->logoBase64(public_path('images/logos/pha-logo.svg'), 'image/svg+xml');
        $oneLinkB64   = $this->logoBase64(public_path('images/logos/1link-logo.png'), 'image/png');

        return compact(
            'allottee',
            'rate', 'wwAmount', 'wwCutoff', 'delayPct', 'wwMonths',
            'monthlyRate', 'maintenance', 'ww', 'fine', 'total',
            'paid', 'pending', 'dueMonths', 'billMonth', 'paymentsHistory', 'status', 'displayStatus',
            'bankAccNo', 'bankName', 'bankBranch', 'qrData',
            'qrSvg', 'qrCodeB64', 'govtLogoB64', 'phaLogoB64', 'oneLinkB64',
            'paymentDate', 'paymentMode', 'advanceAmount'
        );
    }

    /* ── encode a local image file to base64 data URI ── */
    private function logoBase64(string $path, string $mime): string
    {
        if (!file_exists($path)) return '';
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }

    /* ── GET /bills/{allottee}  — web view ── */
    public function show(Allottee $allottee)
    {
        if (!auth()->check() && session('portal_allottee_id') !== $allottee->id) {
            abort(403, 'Unauthorized action.');
        }
        return view('bills.show', $this->billData($allottee));
    }

    /* ── GET /bills/{allottee}/pdf  — download PDF ── */
    public function pdf(Allottee $allottee)
    {
        if (!auth()->check() && session('portal_allottee_id') !== $allottee->id) {
            abort(403, 'Unauthorized action.');
        }

        $data = $this->billData($allottee);
        $pdf = Pdf::loadView('bills.pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', true)
            ->setOption('margin_top', 5)
            ->setOption('margin_bottom', 5)
            ->setOption('margin_left', 7)
            ->setOption('margin_right', 7);

        $monthStr = Carbon::now()->format('F_Y');
        $cleanFileNo = str_replace('/', '-', $allottee->file_no);
        $filename = "Maintenance_Bill_{$cleanFileNo}_{$monthStr}.pdf";

        $output = $pdf->output();

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => strlen($output)
        ]);
    }

    /* ── GET /bills/search  — quick search by CNIC/Mobile/Name/FileNo ── */
    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));
        $allottees = collect();
        $searched = false;

        if (strlen($q) >= 3) {
            $searched = true;
            $allottees = Allottee::where('name', 'like', "%{$q}%")
                ->orWhere('cnic', 'like', "%{$q}%")
                ->orWhere('file_no', 'like', "%{$q}%")
                ->orWhere('membership_no', 'like', "%{$q}%")
                ->orWhere('cell', 'like', "%{$q}%")
                ->orderBy('name')
                ->limit(30)
                ->get();
        }

        return view('bills.search', compact('q', 'allottees', 'searched'));
    }

    /* ── GET /bills/bulk-pdf  — ZIP of all allottee PDFs (selected) ── */
    public function bulkPdf(Request $request)
    {
        set_time_limit(300);
        $ids = $request->get('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'No allottees selected for bulk PDF.');
        }

        $allottees = Allottee::whereIn('id', $ids)->get();
        $zipPath = storage_path('app/pha_bills_bulk.zip');
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($allottees as $a) {
            $data = $this->billData($a);
            $pdf = Pdf::loadView('bills.pdf', $data)->setPaper('a4', 'portrait');
            $fname = 'PHA-Bill-' . str_replace('/', '-', $a->file_no) . '.pdf';
            $zip->addFromString($fname, $pdf->output());
        }
        $zip->close();

        return response()->download($zipPath, 'PHA_Bills_Bulk.zip')->deleteFileAfterSend(true);
    }

    /* ── GET /bills/{allottee}/challan  — download 4-part landscape challan ── */
    public function challan(Allottee $allottee)
    {
        $data = $this->billData($allottee);
        
        // Add amount in words
        $data['amountInWords'] = $this->numberToWords(floor($data['total']));

        $pdf = Pdf::loadView('bills.challan', $data)
            ->setPaper('a4', 'landscape')
            ->setOption('isRemoteEnabled', true)
            ->setOption('margin_top', 10)
            ->setOption('margin_bottom', 10)
            ->setOption('margin_left', 10)
            ->setOption('margin_right', 10);
            
        $filename = 'PHA-Challan-' . str_replace('/', '-', $allottee->file_no) . '.pdf';
        return $pdf->stream($filename); // Stream instead of download for easy printing
    }

    /* ── Helper: Number to Words ── */
    private function numberToWords($number)
    {
        if ($number == 0) return 'Zero';

        $words = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
            7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
            13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen',
            18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty', 40 => 'Forty',
            50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
        ];

        if ($number < 20) return $words[$number];
        if ($number < 100) return $words[floor($number / 10) * 10] . ' ' . $words[$number % 10];
        if ($number < 1000) return $words[floor($number / 100)] . ' Hundred ' . ($number % 100 == 0 ? '' : 'and ' . $this->numberToWords($number % 100));
        if ($number < 100000) return $this->numberToWords(floor($number / 1000)) . ' Thousand ' . ($number % 1000 == 0 ? '' : ' ' . $this->numberToWords($number % 1000));
        if ($number < 10000000) return $this->numberToWords(floor($number / 100000)) . ' Lakh ' . ($number % 100000 == 0 ? '' : ' ' . $this->numberToWords($number % 100000));
        
        return $this->numberToWords(floor($number / 10000000)) . ' Crore ' . ($number % 10000000 == 0 ? '' : ' ' . $this->numberToWords($number % 10000000));
    }
}
