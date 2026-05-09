# PHA Maintenance Dashboard — Project Report
**Project:** I-16/3 Apartments — Allottee Financial & Billing Dashboard
**Technology:** Laravel 11, SQLite, Bootstrap 5, ApexCharts, DomPDF
**Report Date:** 08 May 2026
**Prepared by:** Taimur Khan

---

## 1. PROJECT OVERVIEW

This is a **Government of Pakistan — Ministry of Housing & Works** web application built for **Punjab Housing Authority (PHA) Foundation** to manage maintenance billing for **1,584 allottees** of I-16/3 Islamabad apartments.

The system has two types of users:
- **Admin** — PHA office staff who manage data, generate bills, and record payments
- **Allottee** — Apartment owners who can login, check bill status, and download their bill PDF

---

## 2. TECHNOLOGY STACK

| Layer | Technology |
|-------|-----------|
| Backend Framework | Laravel 11 (PHP) |
| Database | SQLite (local file-based) |
| Frontend CSS | Bootstrap 5 |
| Charts | ApexCharts JS |
| Fonts | Google Fonts — Inter |
| Icons | Bootstrap Icons |
| Excel Import | PhpSpreadsheet / Maatwebsite Excel |
| PDF Generation | barryvdh/laravel-dompdf |
| QR Code (browser) | qrcodejs (CDN) |

---

## 3. DATA IMPORTED

- **Total Allottees:** 1,584
- **Category B** (1,496 Sq Ft): **672 allottees**
- **Category E** (972 Sq Ft): **912 allottees**
- **Source:** Excel file provided by teacher
- **Cities covered:** Islamabad, Others, Karachi, Lahore, Rawalpindi, Peshawar, Quetta, Multan, Abbottabad, Hyderabad, Faisalabad, Unknown, Gujranwala, Sialkot

**Data fields per allottee (35+ fields):**
File No., Membership No., CNIC, Name, Category, Area, Possession Date, Block/Floor/Flat, BPS Grade, Office Name, Post Held, Cell Number, City, Mailing Address, Maintenance Charges, Watch & Ward Charges, Fine (10%), Total Payable, Due Months, Handed Over, Temporary Occupancy, Transfer status, and more.

---

## 4. BILLING CALCULATION LOGIC (POLICY)

### 4.1 Maintenance Charges
```
Monthly Charge = Rs. 3.07 × Area (Sq Ft)
  Category B: 1,496 × 3.07 = Rs. 4,592.72 / month / unit
  Category E:   972 × 3.07 = Rs. 2,984.04 / month / unit

Total Charges = Monthly Rate × Number of Due Months
```

### 4.2 Watch & Ward (W&W) Charges
- **Rs. 10,000/-** applicable if:
  - Possession Date is **on or after 23 July 2023**, OR
  - Possession Date is **NULL** (not recorded)
- **No W&W** if Possession Date is before 23 July 2023

### 4.3 Delay Charges (Fine)
```
Fine = 10% of (Maintenance Charges + W&W Charges)
```

### 4.4 Grand Total Formula
```
Grand Total = Maintenance + W&W + Fine (10%)
```

---

## 5. WHAT HAS BEEN BUILT

### 5.1 Admin Login Page
- Green-themed login page with PHA branding
- Email + Password authentication
- **Admin credentials:** `admin@pha.gov.pk` / `pha@2026`

---

### 5.2 Main Dashboard (Overview Page)
URL: `http://127.0.0.1:8000`

#### KPI Strip (Top Row — 7 colored badges)
| Badge | Shows |
|-------|-------|
| Total Allottees | 1,584 |
| Category B | 672 (1,496 Sq Ft) |
| Category E | 912 (972 Sq Ft) |
| Monthly Billing (Est.) | Sum of all allottees monthly |
| Yearly Billing (Est.) | Monthly × 12 |
| W&W Recoverable | Total Watch & Ward due |
| Delay Charges (10%) | Fine on subtotal |

#### Section 1 — Standard Charges Table
- Shows Cat B and Cat E rates per unit
- Monthly per unit, Yearly per unit
- Rate and area pulled from **Settings** (configurable)

#### Section 2 — Watch & Ward Eligibility (3 Boxes)
- Before / On-After / NULL possession date boxes

#### Section 3 — Monthly Billing by Category (Donut Chart)

#### Section 4 — Monthly Billing Trend (Line Chart)
- Dec-24 to May-25 (6 months, 3 lines)

#### Section 5 — Billing Summary Panel
- Grand Total + Amount Collected vs Pending

#### Section 6 — Policy & Calculation Logic Panel

#### Section 7 — City-wise Allottee Distribution Chart & Table

#### Section 8 — **NEW: Block-wise Occupancy Analytics**
- **4 KPI cards:** Total Blocks, Handed Over, Temporary Occupancy, Transferred
- **Grouped bar chart** per block: Total vs Handed Over vs Temp Occ vs Transferred
- **Block-wise table** with all 4 metrics per block

#### Section 9 — Summary by Policy Logic Table

#### Section 10 — Top Defaulters Table

#### Section 11 — Allottee Billing Data Table (Bottom)

---

### 5.3 Allottee List Page
URL: `http://127.0.0.1:8000/allottees`
- Full list of all 1,584 allottees
- Search by name, CNIC, file number, city
- Filter by category (B / E)
- **NEW:** View Bill (green receipt icon) + Download PDF (red) buttons per row

---

### 5.4 Allottee Detail Page
URL: `http://127.0.0.1:8000/allottees/{id}`
- Full financial breakdown for one allottee
- **NEW:** View Bill + Download PDF buttons at top
- Payment Recording Form (Admin): Amount Paid, Mode, Date, Reference

---

### 5.5 Settings Page
URL: `http://127.0.0.1:8000/settings`
- **Configurable parameters (all groups):**
  - Billing: Maintenance rate/sq ft, W&W amount, cutoff date, delay %
  - Allottees: Category areas, defaulter threshold
  - **NEW — Payment group:** Bank Account No., Bank Name, Bank Branch, Project Name

---

### 5.6 NEW — Professional Bill Generation (SNGPL/IESCO Style)
URL: `http://127.0.0.1:8000/bills/{id}`

**Bill Layout:**
- **Government header** — Govt of Pakistan + PHA Foundation logos + bilingual name
- **Bill strips** — Bill month, File No., Issue Date, Due Date
- **Allottee info strip** — Name, CNIC, Cell, Block/Floor/Flat, Category, Area, Membership No., Possession Date, BPS
- **Current Charges Table:**
  - Maintenance Charges (with formula shown)
  - Watch & Ward Charges (if applicable)
  - Sub-Total
  - Delay Surcharge (10% fine if applicable)
  - **GROSS TOTAL PAYABLE** (dark green row)
- **Late Payment Notice** (orange warning if fine applied)
- **Payment Status boxes** — Amount Paid (green) + Amount Due (red)
- **AMOUNT DUE big box** (SNGPL-style dark green box with amount)
- **Previous Payment History** — date, amount, mode, reference (like IESCO bill)
- **How to Pay section:**
  - Cash Payment (bank account details)
  - Online / Raast / 1Link (account + reference)
- **QR Code** (dynamically generated in browser using qrcodejs)
- **Mailing Address** — allottee's registered address + property address
- **Professional footer**

**Buttons:** Print Bill | Download PDF

---

### 5.7 NEW — PDF Bill Export
URL: `http://127.0.0.1:8000/bills/{id}/pdf`

- Downloads a professional PDF (A4 portrait)
- Uses DomPDF (barryvdh/laravel-dompdf)
- PDF filename: `PHA-Bill-{FileNo}.pdf`
- Same content as web bill — optimized for PDF rendering (table-based layout)
- QR section shows placeholder in PDF (browser can't run JS)
- **From allottee list:** Red PDF icon button per row
- **From allottee detail:** Download PDF button
- **From allottee portal:** "Download My Bill (PDF)" red button

---

### 5.8 NEW — Quick Bill Search Panel
URL: `http://127.0.0.1:8000/bills/search`

**Search by any of:**
- CNIC number
- Mobile / Cell number
- Full name (partial match)
- File Number
- Membership Number

**Results show:**
- Name, CNIC, File No., Category, Block/Flat, Mobile
- Total Payable, Amount Paid, Amount Due
- Payment Status badge (PAID / PARTIAL / UNPAID)
- Due Months
- **View Bill** + **Download PDF** action buttons

---

### 5.9 Allottee Self-Service Portal (Updated)
URL: `http://127.0.0.1:8000/portal`

- Login: CNIC + Mobile
- Portal dashboard shows all bill info
- **NEW:** "Download My Bill (PDF)" red button
- **NEW:** "View Bill" green button — both open the full SNGPL-style bill

---

### 5.10 Payment Tracking System
- Admin records payment on Allottee Detail page
- Payment data shows on bill (previous payment history section)
- Dashboard shows total collected vs total pending

---

### 5.11 Government Branding (Logos)
Both logos in sidebar, portal header, bill header:
- Government of Pakistan (Emblem) — `public/images/logos/govt-pk.svg`
- PHA Foundation (Diamond logo) — `public/images/logos/pha-logo.svg`

---

## 6. URL ROUTES REFERENCE

| URL | Who Can Access | What It Does |
|-----|---------------|-------------|
| `/login` | Public | Admin login page |
| `/` or `/dashboard` | Admin only | Main financial dashboard |
| `/allottees` | Admin only | List all 1,584 allottees |
| `/allottees/{id}` | Admin only | Single allottee detail + payment form |
| `/settings` | Admin only | Configure billing rates & thresholds |
| `/bills/search` | Admin only | **NEW** Quick search by CNIC/Mobile/Name/File No |
| `/bills/{id}` | Admin only | **NEW** Professional SNGPL-style bill view |
| `/bills/{id}/pdf` | Admin only | **NEW** Download bill as PDF |
| `/bills/bulk-pdf` | Admin only | **NEW** Download ZIP of multiple PDFs |
| `/portal` | Public | Allottee portal login (CNIC + Mobile) |
| `/portal/dashboard` | Allottee (after login) | Personal bill status + download PDF |

---

## 7. WHAT IS PENDING (Teacher's Input Required)

| Item | Status | What's Needed |
|------|--------|--------------|
| **Payment Data** | ⏳ Waiting | Teacher to provide Excel with `amount_paid` per allottee. Currently all show Rs. 0 collected. |
| **Bank Account No.** | ⚙️ Configurable | Go to Settings → Update "Bank Account Number" with real PHA account |
| **Bank Name/Branch** | ⚙️ Configurable | Set real bank details in Settings page |

---

## 8. HOW TO RUN THE PROJECT

```powershell
# Go to project folder
cd C:\Users\tim\Documents\sirnadeemdb\pha-dashboard

# Start the server
php artisan serve --port=8000

# Open in browser
# Admin:   http://127.0.0.1:8000
# Portal:  http://127.0.0.1:8000/portal
# Bill Search: http://127.0.0.1:8000/bills/search

# Admin Login
Email:    admin@pha.gov.pk
Password: pha@2026

# Allottee Login
CNIC:   (from database)
Mobile: (from database)
```

---

## 9. SUMMARY

| Feature | Status |
|---------|--------|
| Data Import (1,584 allottees) | ✅ Done |
| Admin Login | ✅ Done |
| Executive Dashboard (10+ sections) | ✅ Done |
| KPI Strip (7 badges) | ✅ Done |
| Standard Charges Table | ✅ Done |
| W&W Eligibility Breakdown | ✅ Done |
| Monthly Billing Donut Chart | ✅ Done |
| Monthly Billing Trend Chart | ✅ Done |
| Billing Summary + Grand Total | ✅ Done |
| Policy & Calculation Logic | ✅ Done |
| City-wise Bar Chart | ✅ Done |
| City-wise Billing Table | ✅ Done |
| **Block-wise Occupancy Analytics** | ✅ **NEW — Done** |
| **Block KPI cards (Handed Over, Temp Occ, Transfer)** | ✅ **NEW — Done** |
| **Block-wise grouped bar chart** | ✅ **NEW — Done** |
| Summary by Policy Logic Table | ✅ Done |
| Top Defaulters Table | ✅ Done |
| Allottee Billing Data Table | ✅ Done |
| Allottee List + Search | ✅ Done |
| Allottee Detail Page | ✅ Done |
| Settings (configurable rates + bank details) | ✅ Done |
| Payment Recording (Admin) | ✅ Done |
| Allottee Portal Login (bilingual) | ✅ Done |
| Allottee Self-Service Dashboard | ✅ Done |
| Government Logos (Govt PK + PHA) | ✅ Done |
| **SNGPL/IESCO Style Bill Generation** | ✅ **NEW — Done** |
| **Previous Payment History on Bill** | ✅ **NEW — Done** |
| **Delay Surcharge Notice on Bill** | ✅ **NEW — Done** |
| **PDF Bill Export (DomPDF)** | ✅ **NEW — Done** |
| **QR Code on Bill (Raast/1Link)** | ✅ **NEW — Done** |
| **Quick Bill Search (CNIC/Mobile/Name/File)** | ✅ **NEW — Done** |
| **Allottee Can Download Own Bill PDF** | ✅ **NEW — Done** |
| **Bill buttons in Allottee List & Detail pages** | ✅ **NEW — Done** |
| **Bank/Payment Settings (configurable)** | ✅ **NEW — Done** |
| Payment Data (Collected vs Pending) | ⏳ Waiting for teacher's Excel |

---

*Report updated: 08 May 2026 — PHA Maintenance Dashboard v2.0*
