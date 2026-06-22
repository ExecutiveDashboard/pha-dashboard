$instance = ".\SQLEXPRESS01"
$dbName = "PHA_DB_N"
$connectionString = "Server=$instance;Database=$dbName;Integrated Security=True;TrustServerCertificate=True"
$connection = New-Object System.Data.SqlClient.SqlConnection
$connection.ConnectionString = $connectionString

$sqlFilePath = Join-Path $PSScriptRoot "import_data.sql"
$writer = New-Object System.IO.StreamWriter($sqlFilePath, $false, [System.Text.Encoding]::UTF8)

function Get-SqlValue($val) {
    if ($val -eq $null -or $val -eq [DBNull]::Value -or $val.ToString().Trim() -eq "") {
        return "NULL"
    }
    $clean = $val.ToString().Replace("'", "''")
    return "'$clean'"
}

function Get-SqlNumber($val, $default = 0) {
    if ($val -eq $null -or $val -eq [DBNull]::Value -or $val.ToString().Trim() -eq "") {
        return $default
    }
    $num = 0
    if ([double]::TryParse($val.ToString(), [ref]$num)) {
        return $num
    }
    return $default
}

try {
    $connection.Open()
    $cmd = $connection.CreateCommand()
    
    $writer.WriteLine("BEGIN TRANSACTION;")
    $writer.WriteLine("DELETE FROM allottees WHERE project_id IN (3, 5);")
    
    # Ensure Peshawar project exists in projects table
    $writer.WriteLine(@"
INSERT INTO projects (id, name, full_name, code, city, maintenance_rate, ww_amount, delay_percent, total_units, is_active, created_at, updated_at)
VALUES (5, 'PHA Peshawar Residencia', 'PHA Residencia Peshawar (Surizai)', 'PHA-702', 'Peshawar', 1.50, 5000, 10, 24311, 0, datetime('now'), datetime('now'))
ON CONFLICT(id) DO UPDATE SET name=excluded.name, full_name=excluded.full_name, code=excluded.code, city=excluded.city, total_units=excluded.total_units;
"@)

    # 1. Update I-16 BPS (Project ID 1)
    Write-Host "Fetching I-16 BPS updates..."
    $cmd.CommandText = "SELECT MemNo, CNIC, BPS FROM I16"
    $reader = $cmd.ExecuteReader()
    while ($reader.Read()) {
        $memNo = Get-SqlValue $reader["MemNo"]
        $cnic = Get-SqlValue $reader["CNIC"]
        $bps = Get-SqlValue $reader["BPS"]
        if ($bps -ne "NULL") {
            $writer.WriteLine("UPDATE allottees SET bps = $bps WHERE project_id = 1 AND (membership_no = $memNo OR cnic = $cnic);")
        }
    }
    $reader.Close()

    # 2. Update Kurri BPS (Project ID 4)
    Write-Host "Fetching Kurri Road Houses BPS updates..."
    $cmd.CommandText = "SELECT RegistrationNo, CNIC, BPS FROM Kurri"
    $reader = $cmd.ExecuteReader()
    while ($reader.Read()) {
        $regNo = Get-SqlValue $reader["RegistrationNo"]
        $cnic = Get-SqlValue $reader["CNIC"]
        $bps = Get-SqlValue $reader["BPS"]
        if ($bps -ne "NULL") {
            $writer.WriteLine("UPDATE allottees SET bps = $bps WHERE project_id = 4 AND (membership_no = $regNo OR cnic = $cnic);")
        }
    }
    $reader.Close()

    # 3. Import Apartments I-12 (Project ID 3)
    Write-Host "Fetching Apartments I-12 allottees..."
    $cmd.CommandText = "SELECT * FROM I12"
    $reader = $cmd.ExecuteReader()
    $countI12 = 0
    while ($reader.Read()) {
        $fileNo = Get-SqlValue $reader["FIleNo"]
        $memNo = Get-SqlValue $reader["Mem_no"]
        $name = Get-SqlValue $reader["AllotteeName"]
        $cnic = Get-SqlValue $reader["CNIC"]
        $bps = Get-SqlValue $reader["BPS"]
        $cell = Get-SqlValue $reader["CellNo"]
        $block = Get-SqlValue $reader["BLOCK"]
        $floor = Get-SqlValue $reader["FLOOR"]
        $flat = Get-SqlValue $reader["FlatNO"]
        $address = Get-SqlValue $reader["PostalAddress"]
        $office = Get-SqlValue $reader["OfficeName"]
        $cadre = Get-SqlValue $reader["CaderGroup"]
        $post = Get-SqlValue $reader["PostHeld"]
        
        $category = "'B'"
        $coveredArea = 1000
        
        # Calculate approximate maintenance charge elements (defaults)
        $dueMonths = 0
        $maintCharges = 0.00
        $totalCharges = 0.00
        
        $writer.WriteLine(@"
INSERT INTO allottees (project_id, file_no, membership_no, name, cnic, bps, cell, block_no, floor, flat_no, mailing_address, office_name, cadre_group, post_held, category, covered_area, due_months, maintenance_charges, watch_ward_charges, fine, total_maintenance_charges, city, amount_paid, handed_over, temporary_occupancy, created_at, updated_at)
VALUES (3, $fileNo, $memNo, $name, $cnic, $bps, $cell, $block, $floor, $flat, $address, $office, $cadre, $post, $category, $coveredArea, $dueMonths, $maintCharges, 0, 0, $totalCharges, 'Islamabad', 0, 'No', 'No', datetime('now'), datetime('now'));
"@)
        $countI12++
    }
    $reader.Close()
    Write-Host "I-12 Allottees generated: $countI12"

    # 4. Import PHA Peshawar Residencia (Project ID 5)
    Write-Host "Fetching PHA Peshawar Residencia memberships..."
    $cmd.CommandText = "SELECT * FROM tblMemberships WHERE ProjectID IN (77, 78, 79)"
    $reader = $cmd.ExecuteReader()
    $countPesh = 0
    while ($reader.Read()) {
        $regNo = Get-SqlValue $reader["RegistrationNo"]
        $name = Get-SqlValue $reader["ApplicantName"]
        $cnic = Get-SqlValue $reader["CNIC"]
        $bps = Get-SqlValue $reader["BPS"]
        $cell = Get-SqlValue $reader["Cell"]
        $address = Get-SqlValue $reader["OfficerAddress"]
        if ($address -eq "NULL") {
            $address = Get-SqlValue $reader["PermanentAddress"]
        }
        $office = Get-SqlValue $reader["OfficeName"]
        $cadre = Get-SqlValue $reader["CadreGroup"]
        $post = Get-SqlValue $reader["Post"]
        
        # Peshawar defaults
        $category = "'A'"
        $coveredArea = 1200
        $dueMonths = 0
        $maintCharges = 0.00
        $totalCharges = 0.00
        
        $writer.WriteLine(@"
INSERT INTO allottees (project_id, file_no, membership_no, name, cnic, bps, cell, block_no, floor, flat_no, mailing_address, office_name, cadre_group, post_held, category, covered_area, due_months, maintenance_charges, watch_ward_charges, fine, total_maintenance_charges, city, amount_paid, handed_over, temporary_occupancy, created_at, updated_at)
VALUES (5, $regNo, $regNo, $name, $cnic, $bps, $cell, 'Phase 1', 'N/A', 'N/A', $address, $office, $cadre, $post, $category, $coveredArea, $dueMonths, $maintCharges, 0, 0, $totalCharges, 'Peshawar', 0, 'No', 'No', datetime('now'), datetime('now'));
"@)
        $countPesh++
    }
    $reader.Close()
    Write-Host "Peshawar Memberships generated: $countPesh"
    
    $writer.WriteLine("COMMIT;")
    $writer.Close()
    
    $connection.Close()
    
    # Execute sqlite3 import
    Write-Host "Running sqlite3 CLI to import generated updates..."
    $dbPath = Resolve-Path (Join-Path $PSScriptRoot "../database/database.sqlite")
    sqlite3 $dbPath ".read $sqlFilePath"
    
    Write-Host "Import completed successfully!"
    
} catch {
    Write-Host "Error occurred: $_"
    if ($writer) { $writer.Close() }
    if ($connection.State -eq "Open") { $connection.Close() }
}
