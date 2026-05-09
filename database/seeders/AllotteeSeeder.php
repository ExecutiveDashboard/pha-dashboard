<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Allottee;
use App\Models\Setting;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class AllotteeSeeder extends Seeder
{
    private string $excelPath = 'C:\\Users\\tim\\Documents\\sirnadeemdb\\Maintenance_Charges_I-16-3_Calculated.xlsx';

    public function run(): void
    {
        Allottee::truncate();

        $spreadsheet = IOFactory::load($this->excelPath);
        $total = 0;

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $sheetName = $sheet->getTitle();
            $isCatB = str_contains($sheetName, 'Cat-B');
            $isCatE = str_contains($sheetName, 'Cat-E');

            $rows = $sheet->toArray(null, true, true, false);
            array_shift($rows); // Remove header row

            foreach ($rows as $row) {
                if (empty(array_filter($row))) continue;

                if ($isCatB) {
                    $allottee = $this->mapCatB($row);
                } elseif ($isCatE) {
                    $allottee = $this->mapCatE($row);
                } else {
                    continue;
                }

                Allottee::create($allottee);
                $total++;
            }
        }

        $this->command->info("Imported {$total} allottees successfully.");
        $this->seedSettings();
    }

    /**
     * Cat-B has 40 columns:
     * 0=SerialNo, 1=FileNo/Membership, 2=FG, 3=Endorsed, 4=LoanMortgage,
     * 5=HandedOver, 6=TempOccupancy, 7=PossessionDate, 8=BookingDate,
     * 9=GP, 10=BlockNo, 11=Floor, 12=FlatNo, 13=BPS, 14=CNIC,
     * 15=Balloting, 16=PAL, 17=Transfer, 18=Verification, 19=Scanning,
     * 20=Name, 21=OfficeName, 22=CadreGroup, 23=DateOfJoining, 24=PostHeld,
     * 25=DOS, 26=DOB, 27=OfficeAddress, 28=MailingAddress, 29=OfficeTel,
     * 30=HomeTel, 31=Cell, 32=Category(B), 33=CoveredArea, 34=PossDate2,
     * 35=DueMonths, 36=Maintenance, 37=WatchWard, 38=Fine, 39=Total
     */
    private function mapCatB(array $row): array
    {
        return [
            'file_no'                   => $this->clean($row[1] ?? null),  // Membership#
            'membership_no'             => $this->clean($row[1] ?? null),
            'fg'                        => $this->clean($row[2] ?? null),
            'endorsed_files'            => $this->clean($row[3] ?? null),
            'loan_mortgage'             => $this->clean($row[4] ?? null),
            'handed_over'               => $this->clean($row[5] ?? null),
            'temporary_occupancy'       => $this->clean($row[6] ?? null),
            'possession_date'           => $this->parseDate($row[34] ?? $row[7] ?? null),
            'booking_transfer_date'     => $this->parseDate($row[8] ?? null),
            'gp'                        => $this->clean($row[9] ?? null),
            'block_no'                  => $this->clean($row[10] ?? null),
            'floor'                     => $this->clean($row[11] ?? null),
            'flat_no'                   => $this->clean($row[12] ?? null),
            'bps'                       => $this->clean($row[13] ?? null),
            'cnic'                      => $this->clean($row[14] ?? null),
            'balloting_fcfs'            => $this->clean($row[15] ?? null),
            'pal'                       => $this->clean($row[16] ?? null),
            'transfer'                  => $this->clean($row[17] ?? null),
            'verification'              => $this->clean($row[18] ?? null),
            'scanning'                  => $this->clean($row[19] ?? null),
            'name'                      => $this->clean($row[20] ?? null),
            'office_name'               => $this->clean($row[21] ?? null),
            'cadre_group'               => $this->clean($row[22] ?? null),
            'date_of_joining'           => $this->parseDate($row[23] ?? null),
            'post_held'                 => $this->clean($row[24] ?? null),
            'dos'                       => $this->parseDate($row[25] ?? null),
            'dob'                       => $this->parseDate($row[26] ?? null),
            'office_address'            => $this->clean($row[27] ?? null),
            'mailing_address'           => $this->clean($row[28] ?? null),
            'office_tel'                => $this->clean($row[29] ?? null),
            'home_tel'                  => $this->clean($row[30] ?? null),
            'cell'                      => $this->clean($row[31] ?? null),
            'category'                  => 'B',
            'covered_area'              => $this->toInt($row[33] ?? null),
            'due_months'                => $this->toInt($row[35] ?? null),
            'maintenance_charges'       => $this->toDecimal($row[36] ?? null),
            'watch_ward_charges'        => $this->toDecimal($row[37] ?? null),
            'fine'                      => $this->toDecimal($row[38] ?? null),
            'total_maintenance_charges' => $this->toDecimal($row[39] ?? null),
            'city'                      => $this->extractCity($row[28] ?? null),
        ];
    }

    /**
     * Cat-E has 28 columns:
     * 0=SerialNo, 1=Status, 2=Membership#, 3=BookingDate/FG,
     * 4=HandedOver, 5=TempOccupancy, 6=PossessionDate,
     * 7=BlockNo, 8=Floor, 9=FlatNo, 10=Verification,
     * 11=Transfer, 12=PAL, 13=Balloting, 14=Scanning,
     * 15=BPS, 16=Name, 17=PostalAddress, 18=CNIC, 19=Cell,
     * 20=Category(E), 21=CoveredArea, 22=PossDate2,
     * 23=DueMonths, 24=Maintenance, 25=WatchWard, 26=Fine, 27=Total
     */
    private function mapCatE(array $row): array
    {
        return [
            'file_no'                   => $this->clean($row[2] ?? null),  // Membership#
            'membership_no'             => $this->clean($row[2] ?? null),
            'fg'                        => $this->clean($row[3] ?? null),  // FG status in booking date col
            'endorsed_files'            => null,
            'loan_mortgage'             => null,
            'handed_over'               => $this->clean($row[4] ?? null),
            'temporary_occupancy'       => $this->clean($row[5] ?? null),
            'possession_date'           => $this->parseDate($row[22] ?? $row[6] ?? null),
            'booking_transfer_date'     => null,
            'gp'                        => null,
            'block_no'                  => $this->clean($row[7] ?? null),
            'floor'                     => $this->clean($row[8] ?? null),
            'flat_no'                   => $this->clean($row[9] ?? null),
            'bps'                       => $this->clean($row[15] ?? null),
            'cnic'                      => $this->clean($row[18] ?? null),
            'balloting_fcfs'            => $this->clean($row[13] ?? null),
            'pal'                       => $this->clean($row[12] ?? null),
            'transfer'                  => $this->clean($row[11] ?? null),
            'verification'              => $this->clean($row[10] ?? null),
            'scanning'                  => $this->clean($row[14] ?? null),
            'name'                      => $this->clean($row[16] ?? null),
            'office_name'               => null,
            'cadre_group'               => null,
            'date_of_joining'           => null,
            'post_held'                 => null,
            'dos'                       => null,
            'dob'                       => null,
            'office_address'            => null,
            'mailing_address'           => $this->clean($row[17] ?? null),
            'office_tel'                => null,
            'home_tel'                  => null,
            'cell'                      => $this->clean($row[19] ?? null),
            'category'                  => 'E',
            'covered_area'              => $this->toInt($row[21] ?? null),
            'due_months'                => $this->toInt($row[23] ?? null),
            'maintenance_charges'       => $this->toDecimal($row[24] ?? null),
            'watch_ward_charges'        => $this->toDecimal($row[25] ?? null),
            'fine'                      => $this->toDecimal($row[26] ?? null),
            'total_maintenance_charges' => $this->toDecimal($row[27] ?? null),
            'city'                      => $this->extractCity($row[17] ?? null),
        ];
    }

    private function seedSettings(): void
    {
        Setting::truncate();
        $defaults = [
            ['key' => 'defaulter_months_threshold', 'value' => '3',    'label' => 'Defaulter Threshold (Months)',   'type' => 'number', 'group' => 'defaulter'],
            ['key' => 'defaulter_top_count',        'value' => '10',   'label' => 'Top Defaulters Count',            'type' => 'number', 'group' => 'defaulter'],
            ['key' => 'maintenance_rate_per_sqft',  'value' => '3.07', 'label' => 'Maintenance Rate (Rs/Sq Ft)',     'type' => 'number', 'group' => 'billing'],
            ['key' => 'watch_ward_amount',          'value' => '10000','label' => 'Watch & Ward Charges (Rs)',       'type' => 'number', 'group' => 'billing'],
            ['key' => 'delay_charge_percent',       'value' => '10',   'label' => 'Delay Charges (%)',               'type' => 'number', 'group' => 'billing'],
            ['key' => 'watch_ward_cutoff_date',     'value' => '2023-07-23','label' => 'W&W Applicable After Date', 'type' => 'text',   'group' => 'billing'],
            ['key' => 'project_name',               'value' => 'I-16/3 Apartments', 'label' => 'Project Name',      'type' => 'text',   'group' => 'general'],
            ['key' => 'dashboard_title',            'value' => 'PHA Maintenance Dashboard','label' => 'Dashboard Title','type' => 'text','group' => 'general'],
        ];
        foreach ($defaults as $s) Setting::create($s);
        $this->command->info('Default settings seeded.');
    }

    private function clean(mixed $val): ?string
    {
        $v = trim((string)($val ?? ''));
        return $v === '' ? null : $v;
    }

    private function parseDate(mixed $val): ?string
    {
        if (empty($val)) return null;
        try {
            if (is_numeric($val)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val)->format('Y-m-d');
            }
            return Carbon::parse($val)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    private function toInt(mixed $val): ?int
    {
        $v = trim((string)($val ?? ''));
        return is_numeric($v) ? (int)$v : null;
    }

    private function toDecimal(mixed $val): ?float
    {
        $v = trim((string)($val ?? ''));
        return is_numeric($v) ? (float)$v : null;
    }

    private function extractCity(mixed $address): string
    {
        if (empty($address)) return 'Unknown';
        $address = strtolower((string)$address);
        $cities = [
            'islamabad'  => 'Islamabad',
            'rawalpindi' => 'Rawalpindi',
            'lahore'     => 'Lahore',
            'peshawar'   => 'Peshawar',
            'karachi'    => 'Karachi',
            'quetta'     => 'Quetta',
            'multan'     => 'Multan',
            'faisalabad' => 'Faisalabad',
            'hyderabad'  => 'Hyderabad',
            'abbottabad' => 'Abbottabad',
            'sialkot'    => 'Sialkot',
            'gujranwala' => 'Gujranwala',
        ];
        foreach ($cities as $key => $city) {
            if (str_contains($address, $key)) return $city;
        }
        return 'Others';
    }
}
