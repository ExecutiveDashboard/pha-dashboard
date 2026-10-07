# ============================================================
#  PHA MAINTENANCE APPLICATION - IIS DEPLOYMENT SCRIPT
#  Server   : 10.200.200.53
#  Site Name: PHA-Maintenance
#  Port     : 8082
#  App Root : C:\inetpub\PHA-Maintenance
#  Doc Root : C:\inetpub\PHA-Maintenance\public  (Laravel)
#  PHP      : C:\xampp\php\php.exe  (from XAMPP)
#
#  SAFE: Does NOT touch PHA-HRMS or any other existing site.
#  Run this script as Administrator via PowerShell on the server.
# ============================================================

$ErrorActionPreference = "Continue"

# Configuration
$AppPath    = "C:\inetpub\PHA-Maintenance"
$PublicPath = "$AppPath\public"
$PhpExe     = "C:\xampp\php\php.exe"
$SiteName   = "PHA-Maintenance"
$Port       = 8082
$AppURL     = "http://10.200.200.53:8082"
$IISUser    = "IIS_IUSRS"

function Write-Step($n, $msg) { Write-Host "`n=== Step $n : $msg ===" -ForegroundColor Cyan }
function Write-OK($msg)   { Write-Host "  [OK]  $msg" -ForegroundColor Green }
function Write-WARN($msg) { Write-Host "  [!!]  $msg" -ForegroundColor Yellow }
function Write-ERR($msg)  { Write-Host "  [ERR] $msg" -ForegroundColor Red }

Write-Host "`n=============================================" -ForegroundColor Magenta
Write-Host "  PHA MAINTENANCE - IIS DEPLOYMENT v1.0     " -ForegroundColor Magenta
Write-Host "  Target: http://10.200.200.53:8082         " -ForegroundColor Magenta
Write-Host "=============================================" -ForegroundColor Magenta

# STEP 1 : Verify application files
Write-Step 1 "Verifying Application Files"
if (-not (Test-Path $AppPath)) { Write-ERR "App folder NOT found at: $AppPath"; exit 1 }
Write-OK "App folder found: $AppPath"

if (-not (Test-Path "$PublicPath\index.php")) { Write-ERR "index.php missing from public folder"; exit 1 }
Write-OK "index.php found in public folder"

if (-not (Test-Path "$AppPath\.env")) { Write-ERR ".env file NOT found"; exit 1 }
Write-OK ".env file found"

$dbPath = "$AppPath\database\database.sqlite"
if (Test-Path $dbPath) {
    $dbMB = [math]::Round((Get-Item $dbPath).Length / 1MB, 2)
    Write-OK "SQLite database found (${dbMB} MB) - existing data will be preserved"
} else {
    Write-WARN "database.sqlite NOT found - fresh migration will run later"
}

# STEP 2 : Locate PHP executable
Write-Step 2 "Locating PHP"
$phpCandidates = @("C:\xampp\php\php.exe","C:\PHP\php.exe","C:\php8\php.exe","C:\PHP8\php.exe","C:\php\php.exe")
$phpFound = $false
foreach ($candidate in $phpCandidates) {
    if (Test-Path $candidate) { $PhpExe = $candidate; $phpFound = $true; break }
}
if (-not $phpFound) {
    $phpInPath = Get-Command php -ErrorAction SilentlyContinue
    if ($phpInPath) { $PhpExe = $phpInPath.Source; $phpFound = $true }
}
if (-not $phpFound) { Write-ERR "PHP not found! Check XAMPP installation."; exit 1 }
$phpVersion = (& $PhpExe -v 2>&1) | Select-Object -First 1
Write-OK "PHP found at : $PhpExe"
Write-OK "PHP version  : $phpVersion"

# STEP 3 : Enable IIS CGI Feature
Write-Step 3 "Enabling IIS CGI Feature (FastCGI)"
$cgiFeature = Get-WindowsOptionalFeature -Online -FeatureName IIS-CGI -ErrorAction SilentlyContinue
if ($cgiFeature -and $cgiFeature.State -eq "Enabled") {
    Write-OK "IIS-CGI already enabled"
} else {
    Enable-WindowsOptionalFeature -Online -FeatureName IIS-CGI -NoRestart -WarningAction SilentlyContinue | Out-Null
    Write-OK "IIS-CGI feature enabled"
}

# STEP 4 : Configure PHP FastCGI in IIS
Write-Step 4 "Configuring PHP FastCGI in IIS"
try { Import-Module WebAdministration -ErrorAction Stop; Write-OK "WebAdministration module loaded" }
catch { Write-ERR "Cannot load WebAdministration. Is IIS installed?"; exit 1 }

$existingFcgi = Get-WebConfiguration "system.webServer/fastCgi/application" | Where-Object { $_.fullPath -eq $PhpExe }
if (-not $existingFcgi) {
    Add-WebConfiguration -Filter "system.webServer/fastCgi" -Value @{ fullPath = $PhpExe; arguments = "" }
    Write-OK "FastCGI application registered for PHP"
} else {
    Write-OK "PHP FastCGI already registered"
}

# STEP 5 : Check URL Rewrite Module
Write-Step 5 "Checking URL Rewrite Module"
$rewriteModule = Get-WebGlobalModule -Name "RewriteModule" -ErrorAction SilentlyContinue
if ($rewriteModule) {
    Write-OK "URL Rewrite Module is installed"
} else {
    Write-WARN "URL Rewrite Module NOT detected! Laravel routes WILL FAIL."
    Write-Host ""
    Write-Host "  DOWNLOAD: https://www.iis.net/downloads/microsoft/url-rewrite" -ForegroundColor Red
    Write-Host "  Install it, then re-run this script." -ForegroundColor Red
    Write-Host ""
    $continue = Read-Host "  Type YES to continue anyway, or Enter to exit"
    if ($continue -ne "YES") { exit 1 }
}

# STEP 6 : Create IIS Website on port 8082
Write-Step 6 "Creating IIS Website '$SiteName' on Port $Port"

# Check for port conflicts with OTHER sites
$portConflict = Get-Website | Where-Object {
    ($_.Bindings.Collection | Where-Object { $_.bindingInformation -like "*:${Port}:*" }) -and ($_.Name -ne $SiteName)
}
if ($portConflict) {
    Write-ERR "Port $Port is already in use by site: '$($portConflict.Name)'"; exit 1
}

# Remove old version of OUR site safely
if (Get-Website -Name $SiteName -ErrorAction SilentlyContinue) {
    Stop-Website -Name $SiteName -ErrorAction SilentlyContinue
    Remove-Website -Name $SiteName
    Write-WARN "Removed old '$SiteName' site (recreating)"
}
if (Get-WebAppPool -Name $SiteName -ErrorAction SilentlyContinue) {
    Stop-WebAppPool -Name $SiteName -ErrorAction SilentlyContinue
    Remove-WebAppPool -Name $SiteName
    Write-WARN "Removed old '$SiteName' app pool (recreating)"
}

# Create App Pool (No Managed Code for PHP)
New-WebAppPool -Name $SiteName | Out-Null
Set-ItemProperty "IIS:\AppPools\$SiteName" managedRuntimeVersion ""
Set-ItemProperty "IIS:\AppPools\$SiteName" enable32BitAppOnWin64 $false
Write-OK "App pool '$SiteName' created (No Managed Code)"

# Create Website
New-Website -Name $SiteName -PhysicalPath $PublicPath -Port $Port -ApplicationPool $SiteName -Force | Out-Null
Write-OK "IIS Site '$SiteName' created on port $Port"
Write-OK "Document root: $PublicPath"

# STEP 7 : Add PHP Handler Mapping
Write-Step 7 "Adding PHP Handler Mapping"
$handlerName = "PHP_FastCGI_PHA"
$sitePSPath  = "IIS:\Sites\$SiteName"
try { Remove-WebHandler -Name $handlerName -PSPath $sitePSPath -ErrorAction SilentlyContinue } catch {}
Add-WebHandler -Name $handlerName -Path "*.php" -Verb "*" -Modules "FastCgiModule" -ScriptProcessor $PhpExe -PSPath $sitePSPath
Write-OK "PHP handler '$handlerName' added for *.php"

# STEP 8 : Set Folder Permissions
Write-Step 8 "Setting Folder Permissions"
$writableFolders = @(
    "$AppPath\storage",
    "$AppPath\storage\app",
    "$AppPath\storage\app\public",
    "$AppPath\storage\framework",
    "$AppPath\storage\framework\cache",
    "$AppPath\storage\framework\sessions",
    "$AppPath\storage\framework\views",
    "$AppPath\storage\logs",
    "$AppPath\bootstrap\cache",
    "$AppPath\database"
)
foreach ($folder in $writableFolders) {
    if (-not (Test-Path $folder)) {
        New-Item -ItemType Directory -Path $folder -Force | Out-Null
        Write-WARN "Created missing folder: $folder"
    }
    icacls $folder /grant "${IISUser}:(OI)(CI)M" /T /Q 2>&1 | Out-Null
    icacls $folder /grant "IUSR:(OI)(CI)M"       /T /Q 2>&1 | Out-Null
    Write-OK "Permissions set: $folder"
}

# STEP 9 : Update .env
Write-Step 9 "Updating .env Configuration"
$envFile    = "$AppPath\.env"
$envContent = Get-Content $envFile -Raw
$envContent = $envContent -replace '(?m)^APP_URL=.*$',   "APP_URL=$AppURL"
$envContent = $envContent -replace '(?m)^APP_ENV=.*$',   "APP_ENV=production"
$envContent = $envContent -replace '(?m)^APP_DEBUG=.*$', "APP_DEBUG=false"
Set-Content $envFile $envContent -Encoding UTF8 -NoNewline
Write-OK "APP_URL   = $AppURL"
Write-OK "APP_ENV   = production"
Write-OK "APP_DEBUG = false"

# STEP 10 : Laravel Artisan Commands
Write-Step 10 "Running Laravel Artisan Commands"
Set-Location $AppPath
Write-Host "  -> config:clear"  -ForegroundColor Gray; & $PhpExe artisan config:clear  2>&1
Write-Host "  -> cache:clear"   -ForegroundColor Gray; & $PhpExe artisan cache:clear   2>&1
Write-Host "  -> view:clear"    -ForegroundColor Gray; & $PhpExe artisan view:clear    2>&1
Write-Host "  -> config:cache"  -ForegroundColor Gray; & $PhpExe artisan config:cache  2>&1
Write-Host "  -> route:cache"   -ForegroundColor Gray; & $PhpExe artisan route:cache   2>&1
Write-Host "  -> view:cache"    -ForegroundColor Gray; & $PhpExe artisan view:cache    2>&1
Write-OK "All artisan cache commands completed"

# STEP 11 : Database Check
Write-Step 11 "Database Verification"
if (Test-Path $dbPath) {
    $dbMB = [math]::Round((Get-Item $dbPath).Length / 1MB, 2)
    Write-OK "SQLite database intact (${dbMB} MB) - existing data preserved"
    icacls $dbPath /grant "${IISUser}:M" /Q 2>&1 | Out-Null
    icacls $dbPath /grant "IUSR:M"      /Q 2>&1 | Out-Null
    Write-OK "IIS write permissions granted on database.sqlite"
} else {
    Write-WARN "database.sqlite not found - running fresh migration..."
    & $PhpExe artisan migrate --force 2>&1
    Write-OK "Migration complete"
}

# STEP 12 : Open Firewall Port
Write-Step 12 "Opening Windows Firewall Port $Port"
$fwRule = Get-NetFirewallRule -DisplayName "PHA-Maintenance-8082" -ErrorAction SilentlyContinue
if ($fwRule) {
    Write-OK "Firewall rule already exists for port 8082"
} else {
    New-NetFirewallRule -DisplayName "PHA-Maintenance-8082" -Direction Inbound -Protocol TCP -LocalPort 8082 -Action Allow | Out-Null
    Write-OK "Firewall rule created: Allow TCP inbound port 8082"
}

# STEP 13 : Start the Website
Write-Step 13 "Starting IIS Site"
Start-WebAppPool -Name $SiteName -ErrorAction SilentlyContinue
Start-Website    -Name $SiteName -ErrorAction SilentlyContinue
$site = Get-Website -Name $SiteName
Write-OK "Site state: $($site.State)"

# STEP 14 : Smoke Test
Write-Step 14 "Smoke Test"
Start-Sleep -Seconds 2
try {
    $response = Invoke-WebRequest -Uri "http://localhost:$Port" -UseBasicParsing -TimeoutSec 10 -ErrorAction Stop
    Write-OK "HTTP $($response.StatusCode) received - APPLICATION IS LIVE!"
} catch {
    $code = $_.Exception.Response.StatusCode.value__
    if ($code -eq 302 -or $code -eq 301) {
        Write-OK "HTTP $code Redirect received - App is running (redirecting to login page)"
    } else {
        Write-WARN "Response: $($_.Exception.Message)"
        Write-WARN "If 500 error, check: $AppPath\storage\logs\laravel.log"
    }
}

# FINAL SUMMARY
Write-Host "`n=============================================" -ForegroundColor Magenta
Write-Host "  DEPLOYMENT COMPLETE                       " -ForegroundColor Green
Write-Host "=============================================" -ForegroundColor Magenta
Write-Host "  URL      : http://10.200.200.53:8082" -ForegroundColor White
Write-Host "  IIS Site : $SiteName"                 -ForegroundColor White
Write-Host "  App Root : $AppPath"                  -ForegroundColor White
Write-Host "  PHP      : $PhpExe"                   -ForegroundColor White
Write-Host "  Database : $dbPath"                   -ForegroundColor White
Write-Host ""
Write-Host "  If 500 error persists, check:" -ForegroundColor Yellow
Write-Host "  1. IIS URL Rewrite Module installed?" -ForegroundColor Yellow
Write-Host "  2. Laravel log: $AppPath\storage\logs\laravel.log" -ForegroundColor Yellow
Write-Host "  3. IIS log: C:\inetpub\logs\LogFiles\" -ForegroundColor Yellow
Write-Host ""
Write-Host "  PHA-HRMS + all other sites: UNTOUCHED" -ForegroundColor Green
Write-Host "=============================================" -ForegroundColor Magenta
