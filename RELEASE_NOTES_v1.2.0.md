# Release Notes: v1.2.0 - Maintenance Rate Source-of-Truth

Release Date: July 15, 2026

---

## New Features
*   **Dynamic Setting Propagation:** Established `settings.maintenance_rate_per_sqft` as the absolute single source of truth for the system-wide billing calculations. Changes to this value in the settings panel take effect immediately across all dashboard statistics and portal layouts.

---

## Bug Fixes
*   **Maintenance Rate propagation bug:** Fixed the override bug where calculations previously referenced the active project table (`projects.maintenance_rate`), preventing setting changes from propagating to the dashboard monthly estimate, billing cards, generated bills, and portals.

---

## Improvements
*   **Audit Commenting:** Added documentation header blocks to all rate resolution paths explaining the Settings-based source of truth.
*   **Allottee Model Optimization:** Removed nested redundant project database query checks from overdue calculations.

---

## Database Changes
*   None. (The system continues to use the existing `settings` schema).

---

## Breaking Changes
*   None. All pre-existing billing records remain unaffected as they preserve their historical snapshot data.

---

## Migration Impact
*   No schema changes or database migrations are required for this release.

---

## Testing Summary
*   **Unit & Feature Tests:** Run and verified via PHPUnit (24 tests, 154 assertions passing).
*   **UAT Audited Scenarios:**
    *   Admin dashboard Monthly estimate resolves to **Rs. 14,825,041.92** at the `8.07` rate.
    *   Category B dynamically resolves to **Rs. 12,072.72/month**.
    *   Category E dynamically resolves to **Rs. 7,359.84/month**.

---

## Deployment Instructions
1.  Verify the Git branch is on `main` and updated to the latest revision.
2.  Log in to the Admin Dashboard settings menu and set **Maintenance Rate** to **8.07** to complete UAT verification.
3.  Confirm dashboard figures update dynamically.
