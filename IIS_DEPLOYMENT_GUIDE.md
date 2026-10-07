# PHA-Maintenance — IIS Production Deployment & Configuration Guide

## Architecture Overview
* **Server IP:** `10.200.200.53`
* **Application Path:** `C:\inetpub\PHA-Maintenance`
* **Web Root (Physical Path):** `C:\inetpub\PHA-Maintenance\public`
* **Web Server:** Microsoft Internet Information Services (IIS 10.0)
* **Application Pool:** `PHA-MaintenancePool` (No Managed Code, 64-bit)
* **PHP Engine:** PHP 8.2.12 via FastCGI (`C:\xampp\php\php-cgi.exe`)
* **Binding:** HTTPS on `*:8082` (TLS 1.2 / TLS 1.3)
* **Firewall:** Port 8082 Inbound TCP Allowed (`PHA-Maintenance-8082`)
* **Client URL:** `https://10.200.200.53:8082/` &rarr; `https://10.200.200.53:8082/login`

---

## 1. IIS Site & Application Pool Configuration
1. **Application Pool:**
   * Name: `PHA-MaintenancePool`
   * .NET CLR Version: `No Managed Code`
   * Managed Pipeline Mode: `Integrated`
   * Start Mode: `AlwaysRunning`
   * Identity: `ApplicationPoolIdentity`
2. **Website:**
   * Name: `PHA-Maintenance` (Site ID: `2`)
   * Physical Path: `C:\inetpub\PHA-Maintenance\public`
   * Binding: `https://*:8082`
   * AutoStart: `True`

---

## 2. SSL/TLS Certificate Specification
To prevent `ERR_SSL_KEY_USAGE_INCOMPATIBLE` in Microsoft Edge and Google Chrome, the SSL certificate must be generated with explicit `DigitalSignature` and `KeyEncipherment` flags:

```powershell
$cert = New-SelfSignedCertificate `
    -DnsName "10.200.200.53", "WIN-M1U3101O5F8", "pha-maintenance", "localhost" `
    -CertStoreLocation "cert:\LocalMachine\My" `
    -KeyUsage DigitalSignature, KeyEncipherment `
    -Type SSLServerAuthentication `
    -NotAfter (Get-Date).AddYears(5)

# Bind to IIS site
$binding = Get-WebBinding -Name "PHA-Maintenance" -Protocol "https"
$binding.AddSslCertificate($cert.Thumbprint, "My")
```

---

## 3. FastCGI & PHP Configuration
In `applicationHost.config`:
```xml
<fastCgi>
    <application fullPath="C:\xampp\php\php-cgi.exe" monitorChangesTo="C:\xampp\php\php.ini" activityTimeout="600" requestTimeout="600" instanceMaxRequests="10000">
        <environmentVariables>
            <environmentVariable name="PHPRC" value="C:\xampp\php" />
            <environmentVariable name="PHP_FCGI_MAX_REQUESTS" value="10000" />
        </environmentVariables>
    </application>
</fastCgi>
```

In `C:\xampp\php\php.ini`:
```ini
cgi.force_redirect = 0
fastcgi.impersonate = 1
fastcgi.logging = 0
display_errors = Off
log_errors = On
```

---

## 4. NTFS Access Permissions
The following directories require Modify permissions for `IIS_IUSRS` and `IUSR`:
```powershell
icacls "C:\inetpub\PHA-Maintenance\storage" /grant "IIS_IUSRS:(OI)(CI)M" /T /Q
icacls "C:\inetpub\PHA-Maintenance\storage" /grant "IUSR:(OI)(CI)M" /T /Q
icacls "C:\inetpub\PHA-Maintenance\bootstrap\cache" /grant "IIS_IUSRS:(OI)(CI)M" /T /Q
icacls "C:\inetpub\PHA-Maintenance\bootstrap\cache" /grant "IUSR:(OI)(CI)M" /T /Q
icacls "C:\inetpub\PHA-Maintenance\database" /grant "IIS_IUSRS:(OI)(CI)M" /T /Q
icacls "C:\inetpub\PHA-Maintenance\database" /grant "IUSR:(OI)(CI)M" /T /Q
```

---

## 5. Windows Firewall
```powershell
New-NetFirewallRule -DisplayName "PHA-Maintenance-8082" -Direction Inbound -LocalPort 8082 -Protocol TCP -Action Allow -Profile Any
```

---

## 6. Co-existence with PHA-HRMS
`PHA-HRMS` resides in `C:\pha-hrms` on `Default Web Site` (Port 80 HTTP) and remains isolated and untouched.
