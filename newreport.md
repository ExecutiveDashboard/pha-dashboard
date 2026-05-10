# PHA Maintenance Dashboard — Complete Deep Project Report
> **Purpose:** This report is a complete reference for any AI agent or developer to fully understand the project without re-reading source files.
> **Last Updated:** 10 May 2026 | **Author:** Taimur Khan | **Version:** 2.0

---

## PART 1 — PROJECT IDENTITY & OVERVIEW

### What Is This Project?
A **Laravel 11 web application** built for **Punjab Housing Authority (PHA) Foundation**, under the **Government of Pakistan — Ministry of Housing & Works**. It manages maintenance billing for **1,584 government apartment allottees** at **I-16/3, Islamabad**.

### Two User Types
| User | Login Method | What They Can Do |
|------|-------------|-----------------|
| **Admin** (PHA staff) | Email + Password at `/login` | Full dashboard, manage allottees, record payments, generate bills |
| **Allottee** (apartment owner) | CNIC + Mobile at `/portal` | View own bill, download PDF |

### Admin Credentials (from seeder)
- **Email:** `admin@pha.gov.pk`
- **Password:** `pha@2026`

### How to Run Locally
```powershell
cd C:\Users\tim\Documents\sirnadeemdb\pha-dashboard
php artisan serve --port=8000
# Visit: http://127.0.0.1:8000
```

---

## PART 2 — TECHNOLOGY STACK

| Layer | Technology | Version |
|-------|-----------|---------|
| Backend | Laravel | 12.x (PHP ^8.2) |
| Database | SQLite | File: `database/database.sqlite` (647 KB) |
| CSS Framework | Bootstrap | 5.3.3 (CDN) |
| Charts | ApexCharts | 3.54.0 (CDN) |
| Font | Google Fonts — Inter | 300–800 weights |
| Icons | Bootstrap Icons | 1.11.3 (CDN) |
| PDF Generation | barryvdh/laravel-dompdf | ^3.1 |
| Excel Import | maatwebsite/excel + PhpSpreadsheet | ^3.1 |
| QR Code | simplesoftwareio/simple-qrcode | ^4.2 |
| Build Tool | Vite | (package.json) |

### Key composer.json dependencies
```json
"barryvdh/laravel-dompdf": "^3.1",
"maatwebsite/excel": "^3.1",
"simplesoftwareio/simple-qrcode": "^4.2",
"laravel/framework": "^12.0"
```

### Environment (.env key settings)
```
APP_NAME="PHA Maintenance Dashboard"
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=sqlite
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

---

## PART 3 — DIRECTORY STRUCTURE (Every File Explained)

```
pha-dashboard/
│
├── app/
│   ├── Http/Controllers/
│   │   ├── Controller.php              # Base controller (empty, extends Laravel base)
│   │   ├── AuthController.php          # Admin login/logout (email+password)
│   │   ├── DashboardController.php     # Main dashboard — all KPIs and charts data
│   │   ├── AllotteeController.php      # Allottee list (paginated, filtered) + show
│   │   ├── AllotteePortalController.php # Allottee self-service portal (CNIC+Mobile login)
│   │   ├── BillController.php          # Bill view, PDF download, search, bulk PDF ZIP
│   │   ├── PaymentController.php       # Record payment (admin only)
│   │   └── SettingController.php       # Settings CRUD
│   │
│   ├── Models/
│   │   ├── Allottee.php                # Main data model (1,584 rows, 35+ fields)
│   │   ├── Setting.php                 # Key-value settings store
│   │   └── User.php                    # Admin user (Laravel auth)
│   │
│   └── Providers/                      # Default Laravel providers
│
├── database/
│   ├── database.sqlite                 # THE DATABASE (647 KB, all data lives here)
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2026_05_05_221807_create_allottees_table.php   # Main allottees table
│   │   ├── 2026_05_05_221807_create_settings_table.php    # Settings key-value table
│   │   ├── 2026_05_07_000001_add_payment_fields_to_allottees.php  # amount_paid, mode, date, ref
│   │   └── 2026_05_08_000001_add_bank_settings.php        # Seeds bank/payment settings
│   │
│   └── seeders/
│       ├── DatabaseSeeder.php          # Runs AllotteeSeeder (+ creates test user)
│       └── AllotteeSeeder.php          # Reads Excel → imports 1,584 allottees + seeds settings
│
├── routes/
│   ├── web.php                         # ALL routes defined here (45 lines, well-organized)
│   └── console.php                     # Default Laravel console routes
│
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   └── app.blade.php           # MASTER LAYOUT: sidebar, topbar, CSS, all JS CDNs
│   │   ├── auth/
│   │   │   └── login.blade.php         # Admin login page (green gradient, PHA branding)
│   │   ├── dashboard/
│   │   │   └── index.blade.php         # MAIN DASHBOARD (31,083 bytes — largest file, 11 sections)
│   │   ├── allottees/
│   │   │   ├── index.blade.php         # Allottee list with search/filter + pagination
│   │   │   └── show.blade.php          # Single allottee detail + payment recording form
│   │   ├── bills/
│   │   │   ├── show.blade.php          # Professional SNGPL-style bill (web view, 22,405 bytes)
│   │   │   ├── pdf.blade.php           # PDF version of bill (DomPDF, 18,852 bytes)
│   │   │   └── search.blade.php        # Quick bill search by CNIC/Mobile/Name/FileNo
│   │   ├── portal/
│   │   │   ├── login.blade.php         # Allottee portal login (CNIC + Mobile, bilingual Urdu/English)
│   │   │   └── dashboard.blade.php     # Allottee personal bill dashboard + PDF download button
│   │   ├── settings/
│   │   │   └── index.blade.php         # Settings form (grouped, all configurable params)
│   │   └── welcome.blade.php           # Default Laravel welcome (82,568 bytes — unused)
│   │
│   ├── css/                            # (Vite managed, mostly empty — all CSS is inline in views)
│   └── js/                             # (Vite managed)
│
├── public/
│   ├── images/logos/
│   │   ├── govt-pk.svg                 # Government of Pakistan emblem (used in sidebar, bills, portal)
│   │   └── pha-logo.svg                # PHA Foundation diamond logo (used everywhere)
│   ├── index.php                       # Laravel entry point
│   ├── .htaccess                       # Apache rewrite rules
│   └── robots.txt
│
├── config/                             # Standard Laravel config (database, auth, session, etc.)
├── bootstrap/                          # Laravel bootstrap files
├── storage/                            # Logs, cache, sessions, uploaded files
├── tests/                              # PHPUnit tests (default Laravel, not customized)
├── vendor/                             # Composer packages (DO NOT EDIT)
│
├── .env                                # Environment variables (SQLite, debug=true)
├── .env.example                        # Template for .env
├── composer.json                       # PHP dependencies
├── package.json                        # Node/Vite dependencies
├── vite.config.js                      # Vite build config
├── artisan                             # Laravel CLI entry point
├── phpunit.xml                         # Test configuration
│
├── report.md                           # Old project report (v2.0, 340 lines)
├── README.md                           # Default Laravel README (not customized)
├── newreport.md                        # THIS FILE — comprehensive deep report
│
├── AllotteeSeeder.php (root)           # Diagnostic/helper script (check_data.php etc.)
├── check_data.php                      # Root-level debug script
├── check_cate.php                      # Root-level debug script
├── check_excel.php                     # Root-level debug script
├── out.txt                             # Large output log (2MB)
│
└── [PDF/Image files at root]           # Sample bills, logos, wallpapers (dev artifacts)
    ├── PHA-Bill-404-000002.pdf         # Sample generated bill PDF
    ├── Government_of_Pakistan.svg      # Source logo
    ├── PHA logo.svg                    # Source logo
    └── [other dev/test files]
```

---

## PART 4 — DATABASE SCHEMA (SQLite)

### Table: `allottees` (PRIMARY TABLE — 1,584 rows)

| Column | Type | Notes |
|--------|------|-------|
| `id` | INTEGER PK | Auto-increment |
| `file_no` | STRING | Allottee file/membership number (e.g. `404/000002`) |
| `membership_no` | STRING | Same as file_no (duplicate for both Cat B & E) |
| `fg` | STRING | FG status field |
| `endorsed_files` | STRING | Endorsed files status |
| `loan_mortgage` | STRING | Loan/mortgage status |
| `handed_over` | STRING | Whether flat was handed over (non-null/non-zero = yes) |
| `temporary_occupancy` | STRING | Temp occupancy status (non-null/non-zero = yes) |
| `possession_date` | DATE | KEY FIELD — determines Watch & Ward eligibility |
| `booking_transfer_date` | DATE | Date of booking transfer |
| `gp` | STRING | GP field |
| `block_no` | STRING | Block number (used for block-wise analytics) |
| `floor` | STRING | Floor number |
| `flat_no` | STRING | Flat number |
| `bps` | STRING | Basic Pay Scale (government grade, e.g. BPS-17) |
| `cnic` | STRING | National Identity Card number (used for portal login) |
| `balloting_fcfs` | STRING | Balloting/FCFS status |
| `pal` | STRING | PAL status |
| `transfer` | STRING | Transfer status (non-null/non-zero = transferred) |
| `verification` | STRING | Verification status |
| `scanning` | STRING | Scanning status |
| `name` | STRING | Full name of allottee |
| `office_name` | STRING | Government office/department name |
| `cadre_group` | STRING | Cadre/group classification |
| `date_of_joining` | DATE | Government service joining date |
| `post_held` | STRING | Government post/designation |
| `dos` | DATE | Date of seniority |
| `dob` | DATE | Date of birth |
| `office_address` | TEXT | Official office address |
| `mailing_address` | TEXT | Home/mailing address (city extracted from this) |
| `office_tel` | STRING | Office telephone |
| `home_tel` | STRING | Home telephone |
| `cell` | STRING | Mobile number (used for portal login) |
| `category` | STRING | `'B'` (1,496 sq ft) or `'E'` (972 sq ft) |
| `covered_area` | INTEGER | Apartment area in sq ft |
| `due_months` | INTEGER | Number of overdue months (imported from Excel) |
| `maintenance_charges` | DECIMAL(12,2) | Monthly rate × due months |
| `watch_ward_charges` | DECIMAL(12,2) | Rs. 10,000 if possession_date >= 2023-07-23 or NULL |
| `fine` | DECIMAL(12,2) | 10% of (maintenance + W&W) |
| `total_maintenance_charges` | DECIMAL(12,2) | maintenance + W&W + fine |
| `city` | STRING | Extracted from mailing_address (12 cities recognized) |
| `amount_paid` | DECIMAL(12,2) | Payment recorded by admin (default 0) |
| `payment_mode` | STRING(30) | `cash` / `online` / `cheque` |
| `payment_date` | DATE | Date payment was recorded |
| `payment_ref` | STRING(100) | Reference number for payment |
| `created_at` | TIMESTAMP | Auto |
| `updated_at` | TIMESTAMP | Auto |

### Table: `settings` (key-value config store)

| Column | Type | Notes |
|--------|------|-------|
| `id` | INTEGER PK | |
| `key` | STRING UNIQUE | Setting identifier |
| `value` | TEXT | Setting value |
| `label` | STRING | Human-readable label |
| `type` | STRING | `text` or `number` |
| `group` | STRING | `billing`, `defaulter`, `general`, `payment` |

### All Settings Keys (seeded defaults)

| key | Default Value | Label | Group |
|-----|--------------|-------|-------|
| `maintenance_rate_per_sqft` | `3.07` | Maintenance Rate (Rs/Sq Ft) | billing |
| `watch_ward_amount` | `10000` | Watch & Ward Charges (Rs) | billing |
| `delay_charge_percent` | `10` | Delay Charges (%) | billing |
| `watch_ward_cutoff_date` | `2023-07-23` | W&W Applicable After Date | billing |
| `defaulter_months_threshold` | `3` | Defaulter Threshold (Months) | defaulter |
| `defaulter_top_count` | `10` | Top Defaulters Count | defaulter |
| `project_name` | `I-16/3 Apartments` | Project Name | general |
| `dashboard_title` | `PHA Maintenance Dashboard` | Dashboard Title | general |
| `bank_account_no` | `PHA-001-NBP-001` | Bank Account Number (for bill) | payment |
| `bank_name` | `National Bank of Pakistan` | Bank Name | payment |
| `bank_branch` | `Islamabad Main Branch` | Bank Branch Name | payment |

### Table: `users` (Admin accounts)
| Column | Notes |
|--------|-------|
| `id` | PK |
| `name` | Admin name |
| `email` | Login email (`admin@pha.gov.pk`) |
| `password` | Bcrypt hashed (`pha@2026`) |
| `remember_token` | Session token |

### Other Tables (Standard Laravel)
- `sessions` — Database session storage
- `cache` — Database cache storage
- `jobs` / `job_batches` / `failed_jobs` — Queue tables
- `password_reset_tokens` — Password reset (not used in this app)

---

## PART 5 — ALL ROUTES (web.php)

### Public Routes
| Method | URL | Controller | Action |
|--------|-----|-----------|--------|
| GET | `/login` | AuthController | `showLogin` — Admin login form |
| POST | `/login` | AuthController | `login` — Process admin login |
| POST | `/logout` | AuthController | `logout` — Admin logout |
| GET | `/portal` | AllotteePortalController | `showLogin` — Allottee portal login form |
| POST | `/portal/login` | AllotteePortalController | `login` — Process portal login |
| GET | `/portal/dashboard` | AllotteePortalController | `dashboard` — Allottee's personal bill page |
| POST | `/portal/logout` | AllotteePortalController | `logout` — Portal logout |

### Admin-Protected Routes (middleware: `auth`)
| Method | URL | Controller | Action |
|--------|-----|-----------|--------|
| GET | `/` | DashboardController | `index` — Main dashboard |
| GET | `/dashboard` | DashboardController | `index` — Same as above |
| GET | `/allottees` | AllotteeController | `index` — Paginated allottee list |
| GET | `/allottees/{id}` | AllotteeController | `show` — Single allottee detail |
| POST | `/allottees/{id}/payment` | PaymentController | `store` — Record payment |
| GET | `/settings` | SettingController | `index` — Settings form |
| POST | `/settings` | SettingController | `update` — Save settings |
| GET | `/bills/search` | BillController | `search` — Quick search |
| GET | `/bills/bulk-pdf` | BillController | `bulkPdf` — ZIP download |
| GET | `/bills/{id}` | BillController | `show` — Web bill view |
| GET | `/bills/{id}/pdf` | BillController | `pdf` — PDF download |

### Named Routes Reference
```
dashboard          → /
login              → /login
logout             → /logout
allottees.index    → /allottees
allottees.show     → /allottees/{id}
allottees.payment  → /allottees/{id}/payment
settings.index     → /settings
settings.update    → /settings (POST)
bills.search       → /bills/search
bills.bulk-pdf     → /bills/bulk-pdf
bills.show         → /bills/{id}
bills.pdf          → /bills/{id}/pdf
portal.login       → /portal
portal.login.post  → /portal/login
portal.dashboard   → /portal/dashboard
portal.logout      → /portal/logout
```

---

## PART 6 — CONTROLLERS (Business Logic)

### AuthController.php
**Path:** `app/Http/Controllers/AuthController.php`
- `showLogin()` → renders `auth/login.blade.php`
- `login(Request)` → validates email+password, calls `Auth::attempt()`, redirects to dashboard
- `logout(Request)` → calls `Auth::logout()`, invalidates session, redirects to `/login`

---

### DashboardController.php
**Path:** `app/Http/Controllers/DashboardController.php` (190 lines — most complex)

Reads all settings from DB, then computes and passes **30+ variables** to `dashboard/index.blade.php`:

| Variable | What It Is |
|----------|-----------|
| `$totalAllottees` | COUNT of all allottees (1,584) |
| `$totalB` / `$totalE` | Count by category B / E |
| `$areaB` / `$areaE` | Sq ft from settings (1496/972) |
| `$maintenanceRate` | Rs/sq ft from settings (3.07) |
| `$monthlyB` / `$monthlyE` | Per-unit monthly charge by category |
| `$yearlyB` / `$yearlyE` | Per-unit yearly charge |
| `$totalMonthlyBilling` | Sum of all allottees' monthly charges |
| `$totalYearlyBilling` | Monthly × 12 |
| `$wwBeforeCount/After/Null` | W&W eligibility breakdown counts |
| `$wwBeforeAmount/After/Null` | W&W amounts |
| `$totalWWRecoverable` | After + Null W&W amounts |
| `$subtotal` | Monthly billing + W&W |
| `$totalDelayCharges` | 10% of subtotal |
| `$grandTotal` | Subtotal + delay charges |
| `$totalPaid` | SUM of amount_paid |
| `$totalPending` | total_maintenance_charges - amount_paid |
| `$threshold` | Defaulter threshold from settings |
| `$totalDefaulters` | Count where due_months >= threshold |
| `$defaulters` | Top N defaulters by total amount |
| `$trendData` | 6-month billing trend array (Dec-24 to May-25) |
| `$billingByCategory` | `['B' => total, 'E' => total]` for donut chart |
| `$cityData` | City-wise count + billing from DB |
| `$policyBefore/After/Null/Total` | Policy logic breakdown arrays |
| `$sampleAllottees` | Top 15 allottees by total charges |
| `$bpsDistribution` | BPS grade distribution |
| `$monthsDistribution` | Due months distribution |
| `$blockData` | Block-wise analytics (total, handed_over, temp_occ, transferred, billing) |
| `$totalHandedOver` | Count of handed-over flats |
| `$totalTempOcc` | Count of temp occupancy |
| `$totalTransferred` | Count of transferred allottees |

**Trend data logic:** Hard-coded to Dec-2024 → May-2025 (6 months), filters by possession_date <= month-end.

---

### AllotteeController.php
**Path:** `app/Http/Controllers/AllotteeController.php`

- `index(Request)` — Filters: `search` (name/cnic/file_no/membership_no/cell), `category`, `city`, `bps`, `defaulter`. Orders by `total_maintenance_charges DESC`. Paginated 25 per page. Passes `$cities` and `$bpsList` for filter dropdowns.
- `show(Allottee)` — Simple: returns `allottees/show.blade.php` with allottee model.

---

### BillController.php
**Path:** `app/Http/Controllers/BillController.php` (154 lines)

**Private `billData(Allottee)` method** — shared data builder for both web view and PDF:
- Reads settings: rate, W&W amount, cutoff, delay%, bank details
- Reads allottee financial fields
- Builds `$lastPayment` array from payment fields
- Generates QR code as SVG using `simplesoftwareio/simple-qrcode` (format: `PHA|ACC:{bankAccNo}|REF:{file_no}|AMT:PKR {pending}`)
- Encodes logos as base64 data URIs for DomPDF embedding

**Methods:**
- `show(Allottee)` → `bills/show.blade.php` (web bill)
- `pdf(Allottee)` → `Pdf::loadView('bills.pdf')`, A4 portrait, filename `PHA-Bill-{FileNo}.pdf`
- `search(Request)` → searches 5 fields (name/cnic/file_no/membership_no/cell), min 3 chars, max 30 results
- `bulkPdf(Request)` → receives `ids[]`, generates PDFs in loop, creates ZIP via `ZipArchive`, streams download

---

### AllotteePortalController.php
**Path:** `app/Http/Controllers/AllotteePortalController.php`

- **No Laravel Auth** — uses PHP session key `portal_allottee_id`
- `login()` — matches CNIC (with/without dashes), then checks mobile (last 10 digits flexible match)
- `dashboard()` — reads `session('portal_allottee_id')`, loads allottee, renders `portal/dashboard.blade.php`
- `logout()` — `session()->forget('portal_allottee_id')`

---

### PaymentController.php
**Path:** `app/Http/Controllers/PaymentController.php`

- `store(Request, Allottee)` — validates: `amount_paid` (numeric, min 0), `payment_mode` (cash/online/cheque), `payment_date` (date), `payment_ref` (optional). Updates allottee record. Returns back with success flash.

---

### SettingController.php
**Path:** `app/Http/Controllers/SettingController.php`

- `index()` — loads settings grouped by `group`, passes to `settings/index.blade.php`
- `update(Request)` — loops all POST data (except `_token`, `_method`), calls `Setting::setValue(key, value)` for each

---

## PART 7 — MODELS

### Allottee.php
**Path:** `app/Models/Allottee.php`

**Fillable fields (48):** All columns listed in Part 4 schema.

**Casts:**
- Dates: `possession_date`, `booking_transfer_date`, `date_of_joining`, `dos`, `dob`, `payment_date`
- Integers: `due_months`, `covered_area`
- Decimals: `maintenance_charges`, `watch_ward_charges`, `fine`, `total_maintenance_charges`, `amount_paid`

**Methods:**
- `isDefaulter(): bool` — checks if `due_months >= defaulter_months_threshold` setting
- `getAmountPendingAttribute(): float` — computed: `max(0, total - paid)`
- `getPaymentStatusAttribute(): string` — returns `'unpaid'` / `'partial'` / `'paid'`

---

### Setting.php
**Path:** `app/Models/Setting.php`

**Static helper methods:**
- `getValue(string $key, $default = null)` — fetches single setting value
- `setValue(string $key, $value)` — `updateOrCreate` on key

---

## PART 8 — DATA IMPORT (AllotteeSeeder)

**Source Excel:** `C:\Users\tim\Documents\sirnadeemdb\Maintenance_Charges_I-16-3_Calculated.xlsx`
(This path is hard-coded in AllotteeSeeder — the file must exist locally to re-seed)

**How it works:**
1. Truncates `allottees` table
2. Reads all sheets from the Excel file
3. For sheets with `Cat-B` in name → uses `mapCatB()` (40-column mapping)
4. For sheets with `Cat-E` in name → uses `mapCatE()` (28-column mapping)
5. Creates each allottee with `Allottee::create()`
6. Calls `seedSettings()` to insert all default settings

**Cat-B Excel column mapping (40 columns, 0-indexed):**
- Col 1 = file_no + membership_no
- Col 5 = handed_over, Col 6 = temporary_occupancy
- Col 7/34 = possession_date (prefers col 34)
- Col 10 = block_no, Col 11 = floor, Col 12 = flat_no
- Col 13 = bps, Col 14 = cnic
- Col 20 = name, Col 21 = office_name, Col 24 = post_held
- Col 28 = mailing_address (city extracted from this)
- Col 31 = cell, Col 33 = covered_area, Col 35 = due_months
- Col 36 = maintenance_charges, Col 37 = watch_ward_charges
- Col 38 = fine, Col 39 = total_maintenance_charges

**Cat-E Excel column mapping (28 columns, 0-indexed):**
- Col 2 = file_no + membership_no
- Col 4 = handed_over, Col 5 = temporary_occupancy
- Col 6/22 = possession_date (prefers col 22)
- Col 7 = block_no, Col 8 = floor, Col 9 = flat_no
- Col 15 = bps, Col 16 = name, Col 18 = cnic
- Col 17 = mailing_address (city extracted)
- Col 19 = cell, Col 21 = covered_area, Col 23 = due_months
- Col 24–27 = maintenance, W&W, fine, total

**City extraction** from mailing_address (keyword match, lowercase):
`islamabad`, `rawalpindi`, `lahore`, `peshawar`, `karachi`, `quetta`, `multan`, `faisalabad`, `hyderabad`, `abbottabad`, `sialkot`, `gujranwala` → else `'Others'`

**To re-import data:**
```powershell
php artisan db:seed --class=AllotteeSeeder
```

---

## PART 9 — BILLING CALCULATION POLICY (Critical Business Logic)

### 9.1 Maintenance Charges
```
Monthly Rate = Rs. 3.07 × covered_area (sq ft)
  Category B: 1,496 × 3.07 = Rs. 4,592.72 / month / unit
  Category E:   972 × 3.07 = Rs. 2,984.04 / month / unit

Total Maintenance = Monthly Rate × due_months
```

### 9.2 Watch & Ward (W&W) Charges
```
Rs. 10,000 applies IF:
  possession_date >= '2023-07-23'   ← on or after cutoff
  OR possession_date IS NULL        ← not yet recorded

Rs. 0 applies IF:
  possession_date < '2023-07-23'    ← before cutoff
```

### 9.3 Delay/Fine Charges
```
Fine = 10% × (maintenance_charges + watch_ward_charges)
```

### 9.4 Grand Total
```
Grand Total = maintenance_charges + watch_ward_charges + fine
```

### 9.5 Payment Status (computed attribute on Allottee model)
```
amount_paid = 0         → 'unpaid'
amount_paid < total     → 'partial'
amount_paid >= total    → 'paid'
```

### 9.6 Defaulter Definition
Allottee is a defaulter if: `due_months >= defaulter_months_threshold` (default = 3)

### 9.7 Data Facts (from imported Excel)
- **Total allottees:** 1,584
- **Category B** (1,496 sq ft): **672 allottees**
- **Category E** (972 sq ft): **912 allottees**
- Cities: Islamabad, Rawalpindi, Lahore, Karachi, Peshawar, Quetta, Multan, Faisalabad, Hyderabad, Abbottabad, Sialkot, Gujranwala, Others, Unknown

---

## PART 10 — VIEWS / UI PAGES (Blade Templates)

### 10.1 Master Layout: `layouts/app.blade.php`
- Loads: Google Fonts (Inter), Bootstrap 5.3.3 CSS, Bootstrap Icons, ApexCharts JS
- **Sidebar:** Fixed left (260px wide), dark green gradient, PHA + Govt logos, nav links
- **Topbar:** Sticky, "Live Data" badge, current date
- **Content area:** `margin-left: 260px`, animated `fadeIn`
- **CSS color variables:** `--pha-green: #1B6B35`, `--pha-dark: #0f4423`, `--pha-gold: #C9A84C`
- Defines all CSS classes: `.kpi-pill`, `.kpi-card`, `.chart-card`, `.data-table`, `.ww-box`, `.pha-pagination`, etc.

### 10.2 Admin Login: `auth/login.blade.php`
- Standalone page (no layout)
- Green gradient background
- White card with email + password fields
- "Remember me" checkbox
- No logo images (text-only PHA badge)

### 10.3 Main Dashboard: `dashboard/index.blade.php` (31,083 bytes — biggest file)
Sections rendered in order:
1. **KPI Strip** — 7 colored pills: Total Allottees, Cat B, Cat E, Monthly Billing, Yearly Billing, W&W Recoverable, Delay Charges
2. **Standard Charges Table** — Cat B and Cat E rates per unit (monthly + yearly)
3. **W&W Eligibility** — 3 colored boxes (Before/After/NULL) showing count + amount
4. **Monthly Billing Donut Chart** (ApexCharts) — Cat B vs Cat E split
5. **Monthly Billing Trend Line Chart** (ApexCharts) — Dec-24 to May-25, 3 lines (B, E, Total)
6. **Billing Summary Panel** — Grand total + Amount Collected vs Pending
7. **Policy & Calculation Logic** — Text explanation of formulas
8. **City-wise Bar Chart** (ApexCharts) + Table
9. **Block-wise Occupancy Analytics** — 4 KPI cards + grouped bar chart + table per block
10. **Summary by Policy Logic Table** — Before/After/NULL groups with counts and amounts
11. **Top Defaulters Table** — Top N by total charges
12. **Allottee Billing Data Table** (bottom) — Top 15 allottees by total charges

### 10.4 Allottee List: `allottees/index.blade.php`
- Filter bar: Search text, Category dropdown, City dropdown, BPS Grade dropdown, Defaulter toggle
- Table columns: #, Name+CNIC, File No., Category badge, Block/Flat, BPS, Due Months badge, Maintenance, W&W, Fine, Total, City, Actions
- Action buttons per row: **View** (grey outline) | **Bill** (green receipt icon) | **PDF** (red)
- Custom PHA pagination (`{{ $allottees->links() }}`)
- Sorted by `total_maintenance_charges DESC`, 25 per page

### 10.5 Allottee Detail: `allottees/show.blade.php` (11,816 bytes)
- Full allottee info in organized card sections
- Financial breakdown with bill + PDF buttons at top
- Payment Recording Form (Admin): Amount Paid, Mode (cash/online/cheque), Date, Reference No.
- Payment history display

### 10.6 Bill Web View: `bills/show.blade.php` (22,405 bytes — most complex view)
SNGPL/IESCO-style professional bill layout:
- **Header:** Govt of Pakistan emblem + PHA logo + bilingual title
- **Bill strips:** Bill Month, File No., Issue Date, Due Date
- **Allottee info strip:** Name, CNIC, Cell, Block/Floor/Flat, Category, Area, Membership No., Possession Date, BPS
- **Current Charges Table:** Maintenance, W&W, Subtotal, Delay Surcharge, GROSS TOTAL
- **Late Payment Notice** (orange, conditional)
- **Payment Status:** Amount Paid (green box) + Amount Due (red box)
- **AMOUNT DUE big box** (dark green SNGPL-style)
- **Previous Payment History** (if payment recorded)
- **How to Pay:** Cash (bank details) + Online/Raast/1Link
- **QR Code** (rendered client-side via qrcodejs CDN)
- **Mailing Address** section
- **Print Bill** + **Download PDF** buttons

### 10.7 Bill PDF: `bills/pdf.blade.php` (18,852 bytes)
- Same content as web bill but table-based HTML for DomPDF
- Logos embedded as base64 data URIs (passed from BillController)
- QR code as inline SVG (passed from BillController, no JS needed)
- Optimized margins (5mm top/bottom, 7mm left/right)

### 10.8 Bill Search: `bills/search.blade.php` (8,869 bytes)
- Search bar (min 3 chars)
- Results table: Name, CNIC, File No., Category, Block/Flat, Mobile, Total Payable, Amount Paid, Amount Due, Status badge (PAID/PARTIAL/UNPAID), Due Months, View Bill + PDF buttons

### 10.9 Portal Login: `portal/login.blade.php` (7,679 bytes)
- Standalone page (no layout)
- Fixed government header bar (Govt + PHA logos)
- Green gradient background
- CNIC + Mobile fields (bilingual English/Urdu labels)
- Bottom nav icons (News/Events, Projects, Contact — decorative links)

### 10.10 Portal Dashboard: `portal/dashboard.blade.php` (13,308 bytes)
- Standalone page (no layout)
- Top bar with logos
- Allottee header card: name, CNIC, cell, category badge, payment status badge
- Bill Breakdown card: Maintenance, W&W, Fine, Total (dark green box), Amount Paid (green), Amount Pending (red)
- Property & Personal Info grid: File No., Membership, Block, Flat, Floor, BPS, Possession Date, City, Mailing Address
- **Download My Bill (PDF)** button (red gradient)
- **View Bill** button (green)

### 10.11 Settings: `settings/index.blade.php` (1,584 bytes)
- Groups settings by `group` field
- Each setting rendered as text/number input
- Shows setting key as code label
- Save All button + Cancel

---

## PART 11 — UI DESIGN SYSTEM

### Color Palette
| Variable | Hex | Usage |
|----------|-----|-------|
| `--pha-green` | `#1B6B35` | Primary brand, buttons, active nav, table headers |
| `--pha-dark` | `#0f4423` | Sidebar, dark accents, gradient end |
| `--pha-gold` | `#C9A84C` | Active sidebar nav indicator |
| `--pha-light` | `#E8F5EE` | Light green backgrounds |

### KPI Pill Colors
- Blue `#2563eb` — Total Allottees
- Green `#1B6B35` — Category B
- Teal `#0d9488` — Category E
- Orange `#d97706` — Monthly Billing
- Purple `#7c3aed` — Yearly Billing
- Indigo `#4338ca` — W&W Recoverable
- Red `#dc2626` — Delay Charges

### Typography
- Font: **Inter** (Google Fonts, 300–800 weights)
- Body: `#1a2332` on `#eef2f7` background
- Table headers: uppercase, 11px, white on green gradient

### Sidebar
- Fixed 260px width, dark green gradient (`#0a3018` → `#1a6332`)
- Active nav: white background 15% opacity + gold left border (3px)
- Logos: 38×38px

### Pagination (Custom — `.pha-pagination`)
- Green gradient prev/next buttons
- Active page: green gradient number
- Hover: green border + light green bg

### Animations
- Page load: `fadeIn` (0.3s, `translateY(5px) → 0`)
- KPI pills: hover `translateY(-2px)`
- Buttons: hover `translateY(-1px)`, active `scale(0.97)`

---

## PART 12 — KNOWN ISSUES / PENDING ITEMS

| Item | Status | Notes |
|------|--------|-------|
| Payment data | ⏳ Waiting | Teacher to provide Excel with `amount_paid` per allottee. Currently all Rs. 0. |
| Bank Account No. | ⚙️ Configurable | Go to Settings → update `bank_account_no` with real PHA account |
| Bank Name/Branch | ⚙️ Configurable | Update via Settings page |
| AllotteeSeeder Excel path | ⚠️ Hard-coded | `C:\Users\tim\Documents\sirnadeemdb\Maintenance_Charges_I-16-3_Calculated.xlsx` |
| Billing trend | ⚠️ Hard-coded dates | Dec-2024 → May-2025 in DashboardController, update as needed |
| Bulk PDF | ✅ Built | But no UI to select multiple allottees yet — needs `ids[]` query param |
| Admin user | ✅ Seeded | `admin@pha.gov.pk` / `pha@2026` — change in production |
| welcome.blade.php | 🗑️ Unused | Default Laravel file, 82KB, not used anywhere |

---

## PART 13 — QUICK REFERENCE COMMANDS

```powershell
# Start development server
php artisan serve --port=8000

# Run migrations fresh (WARNING: clears all data)
php artisan migrate:fresh

# Re-import allottee data from Excel
php artisan db:seed --class=AllotteeSeeder

# Create admin user manually
php artisan tinker
>>> App\Models\User::create(['name'=>'Admin','email'=>'admin@pha.gov.pk','password'=>bcrypt('pha@2026')]);

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Production deployment
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## PART 14 — PRODUCTION DEPLOYMENT (Ubuntu Server)

**Domain:** `testpha.site`
**Server:** Ubuntu (1 vCPU, 2GB RAM, IP: 172.86.72.15)
**Stack:** LAMP (Apache + PHP 8.2 + SQLite)
**Web root:** `/var/www/pha-dashboard/public`

**Key server steps:**
```bash
# Install PHP + extensions
sudo apt install php8.2 php8.2-cli php8.2-sqlite3 php8.2-mbstring php8.2-xml php8.2-zip php8.2-gd php8.2-curl

# Clone and set up
cd /var/www
git clone https://github.com/taimurkhaneth/pha-dashboard.git
cd pha-dashboard
composer install --no-dev
cp .env.example .env
php artisan key:generate

# Transfer SQLite DB (from local machine)
scp database/database.sqlite user@172.86.72.15:/var/www/pha-dashboard/database/

# Set permissions
sudo chown -R www-data:www-data /var/www/pha-dashboard
sudo chmod -R 775 storage bootstrap/cache

# Apache vhost with SSL (Certbot)
# Domain: testpha.site → /var/www/pha-dashboard/public
```

---

*Report generated: 10 May 2026 — PHA Maintenance Dashboard v2.0*
*For questions contact: Taimur Khan (taimurkhaneth@github)*
