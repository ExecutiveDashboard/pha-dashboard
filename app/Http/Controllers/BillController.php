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
        $rate = (float) Setting::getValue('maintenance_rate_per_sqft', 3.07);
        $wwAmount = (float) Setting::getValue('watch_ward_amount', 10000);
        $wwCutoff = Setting::getValue('watch_ward_cutoff_date', '2023-07-23');
        $delayPct = (float) Setting::getValue('delay_charge_percent', 10);
        $bankAccNo = Setting::getValue('bank_account_no', 'PHA-0001-0001-001');
        $bankName = Setting::getValue('bank_name', 'National Bank of Pakistan');
        $bankBranch = Setting::getValue('bank_branch', 'Islamabad Main Branch');

        $monthlyRate = $allottee->covered_area * $rate;
        $maintenance = $allottee->maintenance_charges;
        $ww = $allottee->watch_ward_charges;
        $fine = $allottee->fine;
        $total = $allottee->total_maintenance_charges;
        $paid = (float) $allottee->amount_paid;
        $pending = max(0, $total - $paid);
        $dueMonths = $allottee->due_months ?? 0;
        $billMonth = Carbon::now()->format('F Y');

        // Previous payment history (single record we have)
        $lastPayment = null;
        if ($allottee->payment_date && $paid > 0) {
            $lastPayment = [
                'date' => $allottee->payment_date->format('d M Y'),
                'amount' => $paid,
                'mode' => ucfirst($allottee->payment_mode ?? 'N/A'),
                'ref' => $allottee->payment_ref ?? '—',
            ];
        }

        // QR code data string
        $qrData = "PHA|ACC:{$bankAccNo}|REF:{$allottee->file_no}|AMT:PKR " . number_format($pending, 2);

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
            'rate', 'wwAmount', 'wwCutoff', 'delayPct',
            'monthlyRate', 'maintenance', 'ww', 'fine', 'total',
            'paid', 'pending', 'dueMonths', 'billMonth', 'lastPayment',
            'bankAccNo', 'bankName', 'bankBranch', 'qrData',
            'qrSvg', 'qrCodeB64', 'govtLogoB64', 'phaLogoB64', 'oneLinkB64'
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
        return view('bills.show', $this->billData($allottee));
    }

    /* ── GET /bills/{allottee}/pdf  — download PDF ── */
    public function pdf(Allottee $allottee)
    {
        $data = $this->billData($allottee);
        $pdf = Pdf::loadView('bills.pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', true)
            ->setOption('margin_top', 5)
            ->setOption('margin_bottom', 5)
            ->setOption('margin_left', 7)
            ->setOption('margin_right', 7);
        $filename = 'PHA-Bill-' . str_replace('/', '-', $allottee->file_no) . '.pdf';
        return $pdf->download($filename);
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
