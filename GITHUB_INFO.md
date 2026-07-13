# Saved Project & GitHub Configuration Details

For future reference and automated deployments, here are the configurations set up for this project:

### 👤 GitHub Account Settings
*   **Git User Email:** `nadeemseventy3@gmail.com`
*   **Git User Name:** `Nadeem`

### 🌐 Domain & Hosting Settings
*   **Target Custom Domain:** `I16-Maintenance-service.com`
*   **Target GitHub Repository:** `https://github.com/nadeemseventy3/pha-dashboard.git` (or the repository name you create on your GitHub)

---

### ⚡ Quick Reference Commands

#### 1. Local Server & Remote Tunnel
To start the server and get a temporary public link at any time, run:
```powershell
.\START_SERVER_AND_TUNNEL.bat
```
Or run this manually in a new terminal window:
```powershell
ssh -o StrictHostKeyChecking=accept-new -p 443 -R0:127.0.0.1:8000 a.pinggy.io
```

#### 2. Push Updates to GitHub
To push your latest workspace changes to your GitHub account:
```powershell
.\CONFIGURE_GITHUB.bat
```
Or run manually in Command Prompt/PowerShell:
```powershell
git config user.email "nadeemseventy3@gmail.com"
git config user.name "Nadeem"
git add .
git commit -m "Save local modifications"
git branch -M main
git push -u origin main
```
