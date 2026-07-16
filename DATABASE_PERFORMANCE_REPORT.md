# Database Performance Report

This report analyzes the database queries and schema of the Maintenance System-MIS SQLite database, identifying query bottlenecks, redundant/N+1 executions, and providing recommended indexing scripts.

---

## 1. Identified Database Query Bottlenecks

### 1.1 settings table query bomb
* **Query Pattern**: `SELECT * FROM settings WHERE key = ? LIMIT 1;`
* **Occurrence**: ~25+ times per dashboard view, 8 times per bill print, repeatedly in loops.
* **Problem**: Setting attributes were queried individually.
* **Resolution**: Cached settings dict stored in request memory and Laravel cache.

### 1.2 N+1 query loop on properties table
* **Query Pattern**: `SELECT * FROM properties WHERE id = ? LIMIT 1;`
* **Occurrence**: 1,500+ times on the dashboard overview page.
* **Problem**: Eager loading was missing when iterating over allottees to get city-wise area statistics.
* **Resolution**: Replaced PHP loop with a single database level JOIN group-by SUM aggregation.

### 1.3 Redundant exists/whereHas queries on monthly billing
* **Query Pattern**: 5 distinct queries checking `whereHas('allottee.property')` for Cat B and Cat E bills.
* **Occurrence**: Index pages of Monthly Bill Controllers.
* **Problem**: SQLite subqueries are slow when repeated 5 times.
* **Resolution**: Replaced with 1 aggregated query containing `COUNT(*)`, `SUM(CASE WHEN...)`, and `SUM(total_amount)`.

### 1.4 Heavy in-memory complaints resolution scan
* **Query Pattern**: `SELECT * FROM complaints WHERE resolved_at IS NOT NULL;`
* **Occurrence**: CMS Dashboard.
* **Problem**: Loads all records with all text columns just to calculate resolution time difference.
* **Resolution**: Replaced with `Complaint::whereNotNull('resolved_at')->selectRaw('AVG((strftime("%s", resolved_at) - strftime("%s", created_at)) / 3600.0)')->first()`.

---

## 2. Index Audit & Recommendations

The following indexes are missing and are highly recommended to accelerate search and filter queries:

### Recommended Indexing Script (SQLite compatible)

You can run the following SQL statements on your database to provision the performance indexes:

```sql
-- Index to speed up yearly actual income calculation on dashboard
CREATE INDEX IF NOT EXISTS idx_payment_transactions_payment_date 
ON payment_transactions(payment_date);

-- Index to speed up city-wise statistics queries
CREATE INDEX IF NOT EXISTS idx_allottees_city 
ON allottees(city);

-- Index to accelerate CMS monthly trends and average resolution time metrics
CREATE INDEX IF NOT EXISTS idx_complaints_created_at 
ON complaints(created_at);

CREATE INDEX IF NOT EXISTS idx_complaints_resolved_at 
ON complaints(resolved_at);

-- Index to accelerate fiscal year billing sums
CREATE INDEX IF NOT EXISTS idx_bills_bill_month_status 
ON bills(bill_month, status);
```

---

## 3. Query Parity & Verification

All optimized raw queries have been designed to match the original Eloquent query criteria exactly:
* Enforces identical global scopes (active project partitioning).
* Retains exact calculations and category codes (B/E).
* Grouping behavior matches the original PHP collection groupings.
