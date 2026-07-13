<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SystemRepairService
{
    /**
     * Repair duplicate bills: deletes duplicate monthly bills, keeping the latest.
     *
     * @return int Count of removed bills
     */
    public function repairDuplicateBills()
    {
        $removed = 0;
        
        // Find property/month combinations with duplicate bills
        $duplicates = DB::select("
            SELECT a.property_id, b.bill_month, COUNT(*) as bill_count
            FROM bills b
            JOIN allottees a ON b.allottee_id = a.id
            WHERE a.status = 'active' AND a.property_id IS NOT NULL
            GROUP BY a.property_id, b.bill_month
            HAVING bill_count > 1
        ");

        foreach ($duplicates as $dup) {
            // Get all bills for this property and month
            $bills = DB::table('bills')
                ->join('allottees', 'bills.allottee_id', '=', 'allottees.id')
                ->where('allottees.property_id', $dup->property_id)
                ->where('bills.bill_month', $dup->bill_month)
                ->orderByDesc('bills.id') // keep the latest one (highest ID)
                ->select('bills.id')
                ->get();

            // Delete duplicates (skip the first one which is the latest)
            if ($bills->count() > 1) {
                $idsToDelete = $bills->slice(1)->pluck('id')->toArray();
                DB::table('bills')->whereIn('id', $idsToDelete)->delete();
                $removed += count($idsToDelete);
            }
        }

        return $removed;
    }

    /**
     * Repair orphaned bills: deletes bills pointing to invalid properties/allottees.
     *
     * @return int Count of deleted bills
     */
    public function repairOrphanBills()
    {
        $orphanIds = DB::table('bills as b')
            ->leftJoin('allottees as a', 'b.allottee_id', '=', 'a.id')
            ->whereNull('a.id')
            ->pluck('b.id')
            ->toArray();
            
        // We also want to delete bills where property is missing (for non-legacy projects)
        $allProjects = DB::table('projects')->get();
        $legacyProjectIds = [];
        foreach ($allProjects as $project) {
            $propCount = DB::table('properties')->where('project_id', $project->id)->count();
            if ($propCount <= 1) {
                $legacyProjectIds[] = $project->id;
            }
        }
        $legacyPlaceholder = empty($legacyProjectIds) ? '0' : implode(',', $legacyProjectIds);

        $orphanPropBills = DB::select("
            SELECT b.id FROM bills b
            JOIN allottees a ON b.allottee_id = a.id
            WHERE a.project_id NOT IN ({$legacyPlaceholder})
              AND (a.property_id IS NULL OR a.property_id NOT IN (SELECT id FROM properties))
        ");
        foreach ($orphanPropBills as $ob) {
            $orphanIds[] = $ob->id;
        }

        $uniqueIds = array_unique($orphanIds);
        if (!empty($uniqueIds)) {
            DB::table('bills')->whereIn('id', $uniqueIds)->delete();
        }

        return count($uniqueIds);
    }

    /**
     * Repair orphaned payments: breaks relationship with non-existent bills.
     *
     * @return int Count of modified payments
     */
    public function repairOrphanPayments()
    {
        $orphanPayments = DB::table('payment_transactions')
            ->whereNotNull('bill_id')
            ->whereNotIn('bill_id', DB::table('bills')->pluck('id'))
            ->pluck('id')
            ->toArray();

        if (!empty($orphanPayments)) {
            DB::table('payment_transactions')
                ->whereIn('id', $orphanPayments)
                ->update(['bill_id' => null]);
        }

        return count($orphanPayments);
    }

    /**
     * Repair unowned allotted properties: sets status to Available.
     *
     * @return int Count of updated properties
     */
    public function repairUnownedProperties()
    {
        $allProjects = DB::table('projects')->get();
        $legacyProjectIds = [];
        foreach ($allProjects as $project) {
            $propCount = DB::table('properties')->where('project_id', $project->id)->count();
            if ($propCount <= 1) {
                $legacyProjectIds[] = $project->id;
            }
        }
        $legacyPlaceholder = empty($legacyProjectIds) ? '0' : implode(',', $legacyProjectIds);

        $unowned = DB::select("
            SELECT id FROM properties
            WHERE status = 'Allotted' AND project_id NOT IN ({$legacyPlaceholder})
              AND id NOT IN (SELECT DISTINCT property_id FROM allottees WHERE status = 'active' AND property_id IS NOT NULL)
        ");

        $ids = array_column($unowned, 'id');
        if (!empty($ids)) {
            DB::table('properties')
                ->whereIn('id', $ids)
                ->update(['status' => 'Available']);
        }

        return count($ids);
    }
}
