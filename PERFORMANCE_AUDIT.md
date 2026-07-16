# Performance Audit Report

This report outlines the initial performance audit of the Laravel Maintenance System-MIS running on the local environment, identifying key framework, database, and application-level bottlenecks.

---

## 1. System Environment & Baseline Configurations

The system is currently configured with the following parameters:

* **Laravel Version**: 12.0.0
* **PHP Version**: 8.2.12
* **Database Driver**: SQLite 3
* **APP_ENV**: `local`
* **APP_DEBUG**: `true`
* **Cache Driver**: `database` (config: `CACHE_STORE=database`)
* **Session Driver**: `database` (config: `SESSION_DRIVER=database`)
* **Queue Connection**: `database` (config: `QUEUE_CONNECTION=database`)
* **Log Channel**: `stack` (configured for single file debugging)
* **Route Count**: ~60 routes
* **Active Project Code**: Enforces active project scope on allottees

---

## 2. Front-End Asset Sizes

Vite compiles front-end assets with the following production footprint:

* **CSS Bundle Size**: `resources/css/app.css` (~390 Bytes)
* **JS Bundle Size**: `resources/js/app.js` (~22 Bytes) + `bootstrap.js` (127 Bytes)
* **Key Visual Images**: 
  * `public/images/bg/login-bg.png` (**980.36 KB**) - Single largest asset loaded on login.
  * `public/images/logos/govt-pk.svg` (179.09 KB)
  * `public/images/logos/pha-logo.svg` (21.99 KB)
  * `public/images/logos/1link-logo.png` (3.43 KB)

---

## 3. Major Architectural & Query Bottlenecks

### Bottleneck A: Automatic Route Cache Purge
* **Location**: [web.php](file:///c:/AI%20Agentic/Maintenance%20System-MIS/routes/web.php#L4)
* **Mechanism**: `\Illuminate\Support\Facades\Artisan::call('route:clear');` is called directly in the route file.
* **Impact**: On *every single request*, the route cache is explicitly cleared. This wipes out compiled routes, forcing Laravel to re-parse all 60 routes on every request, completely rendering route caching useless.

### Bottleneck B: Setting Retrieval Database Bombing
* **Location**: [Setting.php](file:///c:/AI%20Agentic/Maintenance%20System-MIS/app/Models/Setting.php#L11-L15)
* **Mechanism**: `Setting::getValue($key)` performs an individual database query `static::where('key', $key)->first()` on every call.
* **Impact**: The application queries settings repeatedly:
  * Over 15 settings lookups on the Dashboard index page.
  * 8 lookups inside `BillController::billData()`.
  * Repeated lookups inside loop iterators when calculating overdue months.
  * Generates redundant SQLite query overhead.

### Bottleneck C: Active Project Scope Checks
* **Location**: [Allottee.php](file:///c:/AI%20Agentic/Maintenance%20System-MIS/app/Models/Allottee.php#L13-L18) and [Project.php](file:///c:/AI%20Agentic/Maintenance%20System-MIS/app/Models/Project.php#L25-L28)
* **Mechanism**: The global scope joins/filters allottees by active project ID, querying `Project::active()` which runs a database call.
* **Impact**: For every allottee query built in the request, a fresh query to fetch the active project is executed.

### Bottleneck D: N+1 Lazy Loading & Heavy PHP Grouping on Dashboard
* **Location**: [DashboardController.php](file:///c:/AI%20Agentic/Maintenance%20System-MIS/app/Http/Controllers/DashboardController.php#L200-L220)
* **Mechanism**: The city-wise analytics fetches all active allottees via `Allottee::active()->get()`, then groups by city and iterates over each allottee to sum covered areas.
* **Impact**: Accessing `$allottee->category` and `$allottee->covered_area` internally invokes relations. Because properties are not eager loaded, it triggers an N+1 property query for **every single allottee**! For 1,584 active owners, this inflicts 1,500+ database hits.

### Bottleneck E: Multi-Query Loops for Trend Graphs
* **Location**: [DashboardController.php](file:///c:/AI%20Agentic/Maintenance%20System-MIS/app/Http/Controllers/DashboardController.php#L172-L197)
* **Mechanism**: Iterates 6 months and queries count of active allottees for each category.
* **Impact**: Runs 24 distinct queries in a loop.

### Bottleneck F: Redundant Aggregation Queries in Billing
* **Location**: [MonthlyBillController.php](file:///c:/AI%20Agentic/Maintenance%20System-MIS/app/Http/Controllers/MonthlyBillController.php#L30-L34) and [CategoryEBillingController.php](file:///c:/AI%20Agentic/Maintenance%20System-MIS/app/Http/Controllers/CategoryEBillingController.php#L30-L49)
* **Mechanism**: Separately queries `count()`, `whereIn('status')->count()`, `sum('total_amount')`, and `sum('paid_amount')` using 5 distinct queries.
* **Impact**: 5 slow EXISTS queries on SQLite instead of 1 aggregated statement.

### Bottleneck G: In-Memory resolved complaints calculation
* **Location**: [ComplaintReportController.php](file:///c:/AI%20Agentic/Maintenance%20System-MIS/app/Http/Controllers/Admin/ComplaintReportController.php#L35-L39)
* **Mechanism**: `Complaint::whereNotNull('resolved_at')->get()` instantiates all completed complaints as Eloquent models to calculate duration in PHP.
* **Impact**: Heavy RAM allocation and garbage collection overhead.
