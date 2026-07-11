@echo off
TITLE PHA Dashboard - Local Server & Remote Tunnel
color 0A

echo =====================================================================
echo  PHA FOUNDATION - MAINTENANCE SYSTEM AUTOMATED STARTUP SCRIPT
echo =====================================================================
echo.

REM Step 1: Check for .env file
if not exist ".env" (
    echo [INFO] .env file not found. Copying from .env.example...
    copy .env.example .env
    echo [INFO] Generating application key...
    php artisan key:generate
)

REM Step 2: Check for vendor directory
if not exist "vendor" (
    echo [INFO] vendor directory not found. Running composer install...
    composer install
)

REM Step 3: Check for node_modules directory
if not exist "node_modules" (
    echo [INFO] node_modules directory not found. Running npm install...
    npm install
)

REM Step 4: Build assets
echo.
echo [INFO] Compiling frontend assets (Vite)...
echo =====================================================================
call npm run build
echo =====================================================================
echo.

REM Step 5: Start PHP server in the background
echo [INFO] Starting Laravel Development Server in a minimized window...
start "PHA Laravel Server" /min cmd /c "php artisan serve --port=8000"

echo [INFO] Server started locally at http://127.0.0.1:8000
echo.
REM Step 5.5: Check and generate SSH keys if missing (prevents password prompts)
if not exist "%USERPROFILE%\.ssh\id_rsa" (
    if not exist "%USERPROFILE%\.ssh\id_ed25519" (
        echo [INFO] SSH key not found. Generating a key to bypass the password prompt...
        mkdir "%USERPROFILE%\.ssh" >nul 2>&1
        ssh-keygen -t ed25519 -N "" -f "%USERPROFILE%\.ssh\id_ed25519"
    )
)

echo.

REM Step 6: Start remote SSH tunnel (Pinggy)
echo =====================================================================
echo  STARTING REMOTE TUNNEL (ANYWHERE ACCESSIBLE)
echo =====================================================================
echo  This service (Pinggy) will expose your local server to the internet.
echo  It will generate a public URL (e.g. http://....pinggy.link).
echo  You can copy and paste that URL in any browser on any device!
echo.
echo  Press Ctrl+C to terminate both the server and the tunnel when done.
echo =====================================================================
echo.

ssh -o StrictHostKeyChecking=accept-new -p 443 -R0:127.0.0.1:8000 a.pinggy.io

REM Cleanup background processes on exit
echo.
echo [INFO] Shutting down Laravel server...
taskkill /FI "WINDOWTITLE eq PHA Laravel Server*" /F >nul 2>&1
echo [INFO] Done.
pause
