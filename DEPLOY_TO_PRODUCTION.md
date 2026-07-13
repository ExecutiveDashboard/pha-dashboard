# Production Deployment Guide: I16-Maintenance-service.com

This guide explains how to deploy your Laravel Maintenance System to the cloud so it is active 24/7/365 and linked to your custom domain: **`I16-Maintenance-service.com`**.

We will use **Render.com** (or **Railway.app**), which are modern, free/low-cost platforms. The project is already fully configured with a `Dockerfile` and `nixpacks.toml` to support these cloud servers.

---

## 🛠️ Step 1: Push the Project to GitHub
1. Open the project folder `c:\AI Agentic\Maintenance System-MIS` in Windows Explorer.
2. Double-click the **`CONFIGURE_GITHUB.bat`** script we created.
3. Follow the prompts to configure Git with your email **`nadeemseventy3@gmail.com`** and push the code to a new repository on your GitHub (e.g., `pha-dashboard`).

---

## 🚀 Step 2: Deploy to Render.com (Cloud Hosting)
1. Go to [Render.com](https://render.com) and click **Sign Up** (use the "Sign in with GitHub" option).
2. On your Render Dashboard, click the green **`New +`** button in the top-right and select **`Web Service`**.
3. Under "Connect a repository", search for your GitHub repository (`pha-dashboard`) and click **`Connect`**.
4. Configure the settings:
   *   **Name**: `pha-dashboard`
   *   **Region**: Select the region closest to you (e.g., Singapore or US East)
   *   **Branch**: `main`
   *   **Runtime**: **`Docker`** (Render will automatically detect this because we created a `Dockerfile`)
   *   **Instance Type**: Choose the Free instance (or a paid one if you want faster performance)
5. Scroll down to **Environment Variables** (or click Advanced) and add these keys:
   *   `APP_KEY` = *(Copy the `APP_KEY` from your local `.env` file)*
   *   `APP_ENV` = `production`
   *   `APP_DEBUG` = `false`
6. Click **`Deploy Web Service`**. 
   *   Render will now build your container, run migrations, and deploy it.
   *   Once complete, it will give you a public URL (e.g., `https://pha-dashboard-xxxx.onrender.com`).

---

## 🌐 Step 3: Link your Custom Domain (I16-Maintenance-service.com)
Once the service is running, follow these steps to route your custom domain to your new cloud server:

1.  **Add Domain in Render**:
    *   In your Render Web Service dashboard, click **`Settings`** on the left menu.
    *   Scroll down to the **Custom Domains** section.
    *   Click **`Add Custom Domain`**, enter **`I16-Maintenance-service.com`**, and click **`Save`**.
    *   Click **`Add Custom Domain`** again, enter **`www.I16-Maintenance-service.com`**, and click **`Save`**.
2.  **Add DNS Records at your Registrar** (e.g., GoDaddy, Namecheap, or Cloudflare where you bought the domain):
    *   Log in to your domain registrar and open the **DNS Settings** for `I16-Maintenance-service.com`.
    *   Add a **CNAME** record:
        *   **Type**: `CNAME`
        *   **Name/Host**: `www`
        *   **Value/Target**: *(Enter your Render URL, e.g., `pha-dashboard-xxxx.onrender.com`)*
        *   **TTL**: `Auto` or `1 hour`
    *   Add an **A** record (for the root domain):
        *   **Type**: `A`
        *   **Name/Host**: `@`
        *   **Value/Target**: *(Enter the Render IP address displayed in your settings page, e.g., `216.24.57.x`)*
        *   **TTL**: `Auto` or `1 hour`
3.  **Wait for SSL & Activation**:
    *   Render will automatically check your DNS records, verify ownership, and provision a free **SSL certificate (HTTPS)**.
    *   Within 10-30 minutes, your application will be live and secure at **`https://I16-Maintenance-service.com`**!
