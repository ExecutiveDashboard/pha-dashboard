@echo off
TITLE PHA Dashboard - GitHub Configuration Utility
color 0E

echo =====================================================================
echo  PHA FOUNDATION - GITHUB CONFIGURATION & DEPLOYMENT UTILITY
echo =====================================================================
echo.
echo  This script will configure Git with your email: nadeemseventy3@gmail.com
echo  and help you push the project to your GitHub account so you can run
echo  it anywhere, any time as your server (using GitHub Codespaces).
echo.
echo =====================================================================
echo.

REM Step 1: Configure Git user details locally
echo [INFO] Configuring local Git user settings...
git config user.email "nadeemseventy3@gmail.com"
echo [SUCCESS] Git email set to: nadeemseventy3@gmail.com
echo.

REM Prompt for user's name
set /p gitname="Enter your name for Git commits [default: Nadeem]: "
if "%gitname%"=="" set gitname=Nadeem
git config user.name "%gitname%"
echo [SUCCESS] Git name set to: %gitname%
echo.

echo =====================================================================
echo  INSTRUCTIONS TO PUSH TO GITHUB:
echo =====================================================================
echo  1. Go to https://github.com and log in with nadeemseventy3@gmail.com
echo  2. Click "New" to create a new repository.
echo     - Repository Name: pha-dashboard
echo     - Keep it Public or Private (as you prefer).
echo     - Do NOT initialize with README, .gitignore, or license.
echo  3. Copy your repository URL (e.g. https://github.com/your-username/pha-dashboard.git).
echo =====================================================================
echo.

set /p repourl="Paste your GitHub repository URL: "
if "%repourl%"=="" (
    echo [WARNING] No URL pasted. Skipping remote URL setup.
    goto end
)

echo.
echo [INFO] Updating Git Remote Origin URL...
git remote set-url origin %repourl% 2>nul
if %ERRORLEVEL% NEQ 0 (
    git remote add origin %repourl%
)
echo [SUCCESS] Remote origin set to: %repourl%
echo.

echo [INFO] Preparing and staging files...
git add .

echo [INFO] Creating commit...
git commit -m "Setup devcontainer for GitHub Codespaces and add startup scripts"

echo [INFO] Pushing files to GitHub (main branch)...
echo (You may be prompted by GitHub to log in or authorize in your browser)
echo.
git branch -M main
git push -u origin main

if %ERRORLEVEL% EQU 0 (
    echo.
    echo [SUCCESS] Project pushed successfully to your GitHub repository!
    echo.
    echo =====================================================================
    echo  HOW TO RUN FROM GITHUB ANYWHERE, ANY TIME (CODESPACES):
    echo =====================================================================
    echo  1. Open your repository on GitHub in any web browser.
    echo  2. Click the green "<> Code" button.
    echo  3. Switch to the "Codespaces" tab and click "Create codespace on main".
    echo  4. GitHub will automatically spin up a container using our devcontainer
    echo     configuration, install dependencies, compile assets, and start the app.
    echo  5. A popup will appear in the bottom-right corner with a "Forwarded Port"
    echo     link (or go to Ports tab and click the browser icon next to port 8000).
    echo  6. This gives you a private, secure link and port that runs completely in the
    echo     cloud as your server!
    echo =====================================================================
) else (
    echo [ERROR] Git push failed. Please verify your internet connection or GitHub authorization.
)

:end
echo.
pause
