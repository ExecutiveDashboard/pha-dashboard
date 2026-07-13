<?php

use Illuminate\Support\Facades\Route;
\Illuminate\Support\Facades\Artisan::call('route:clear');
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AllotteeController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AllotteePortalController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\MonthlyBillController;
use App\Http\Controllers\CategoryEBillingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProjectController;

Route::get('/audit', function() {
    $results = [];
    $properties = \App\Models\Property::all();
    foreach ($properties as $property) {
        $activeAllottees = \App\Models\Allottee::withoutGlobalScopes()
            ->where('property_id', $property->id)
            ->where('status', 'active')
            ->get();
        
        if ($activeAllottees->count() > 1) {
            $conflictOwners = [];
            foreach ($activeAllottees as $allottee) {
                $conflictOwners[] = [
                    'id' => $allottee->id,
                    'name' => $allottee->name,
                    'cnic' => $allottee->cnic,
                    'ownership_start_date' => $allottee->ownership_start_date ? $allottee->ownership_start_date->format('Y-m-d') : null,
                ];
            }
            
            $results[] = [
                'property_id' => $property->id,
                'block_no' => $property->block_no,
                'flat_no' => $property->flat_no,
                'category' => $property->category,
                'active_count' => $activeAllottees->count(),
                'owners' => $conflictOwners,
            ];
        }
    }
    return response()->json($results);
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── ADMIN PROTECTED ROUTES ──────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/',          [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Allottees
    Route::get('/allottees',            [AllotteeController::class, 'index'])->name('allottees.index');
    Route::get('/allottees/{allottee}', [AllotteeController::class, 'show'])->name('allottees.show');
    Route::get('/allottees/{allottee}/edit', [AllotteeController::class, 'edit'])->name('allottees.edit');
    Route::put('/allottees/{allottee}', [AllotteeController::class, 'update'])->name('allottees.update');
    Route::post('/allottees/{allottee}/transfer', [AllotteeController::class, 'transfer'])->name('allottees.transfer');

    // Payment recording (admin, on allottee detail)
    Route::post('/allottees/{allottee}/payment', [PaymentController::class, 'store'])->name('allottees.payment');

    // Settings
    Route::get('/settings',  [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

    // ── QUICK BILL SEARCH (keep as-is) ────────────────────────────
    Route::get('/bills/search',         [BillController::class, 'search'])->name('bills.search');
    Route::get('/bills/bulk-pdf',       [BillController::class, 'bulkPdf'])->name('bills.bulk-pdf');
    Route::get('/bills/{allottee}',     [BillController::class, 'show'])->name('bills.show');
    Route::get('/bills/{allottee}/pdf', [BillController::class, 'pdf'])->name('bills.pdf');
    Route::get('/bills/{allottee}/challan', [BillController::class, 'challan'])->name('bills.challan');

    // ── MONTHLY BILL MANAGEMENT ────────────────────────────────────
    Route::get('/monthly-bills',                      [MonthlyBillController::class, 'index'])->name('monthly-bills.index');
    Route::post('/monthly-bills/generate',            [MonthlyBillController::class, 'generate'])->name('monthly-bills.generate');
    Route::post('/monthly-bills/{bill}/pay',          [MonthlyBillController::class, 'recordPayment'])->name('monthly-bills.pay');
    Route::post('/monthly-bills/{bill}/settle',       [MonthlyBillController::class, 'settle'])->name('monthly-bills.settle');
    Route::get('/monthly-bills/{bill}/check-psid',   [MonthlyBillController::class, 'checkPsid'])->name('monthly-bills.check-psid');

    // ── MONTHLY BILL MANAGEMENT - CATEGORY E ────────────────────────
    Route::get('/monthly-bills/category-e',                      [CategoryEBillingController::class, 'index'])->name('monthly-bills-e.index');
    Route::post('/monthly-bills/category-e/generate',            [CategoryEBillingController::class, 'generate'])->name('monthly-bills-e.generate');
    Route::post('/monthly-bills/category-e/{bill}/pay',          [CategoryEBillingController::class, 'recordPayment'])->name('monthly-bills-e.pay');
    Route::post('/monthly-bills/category-e/{bill}/settle',       [CategoryEBillingController::class, 'settle'])->name('monthly-bills-e.settle');
    Route::get('/monthly-bills/category-e/{bill}/check-psid',   [CategoryEBillingController::class, 'checkPsid'])->name('monthly-bills-e.check-psid');

    // ── NOTIFICATIONS (WhatsApp / SMS) ─────────────────────────────
    Route::get('/notifications',          [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/send',    [NotificationController::class, 'send'])->name('notifications.send');
    Route::post('/notifications/single',  [NotificationController::class, 'sendSingle'])->name('notifications.single');

    // ── BLOCK VISUAL ───────────────────────────────────────────────
    Route::get('/blocks/visual', [AllotteeController::class, 'blockVisual'])->name('blocks.visual');

    // ── PROJECTS ───────────────────────────────────────────────────
    Route::get('/projects',         [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects/switch', [ProjectController::class, 'switchProject'])->name('projects.switch');
    Route::post('/projects/{project}/bank', [ProjectController::class, 'updateBank'])->name('projects.update-bank');
    
    // ── USER MANAGEMENT ────────────────────────────────────────────
    Route::resource('/users', \App\Http\Controllers\UserController::class)->except(['create', 'show', 'edit']);

    // ── COMPLAINT MANAGEMENT ───────────────────────────────────────
    Route::prefix('admin/complaints')->name('admin.complaints.')->group(function () {
        // Specific named routes MUST come before the /{complaint} wildcard
        Route::get('/dashboard',              [\App\Http\Controllers\Admin\ComplaintReportController::class, 'dashboard'])->name('dashboard');
        Route::get('/reports/export',         [\App\Http\Controllers\Admin\ComplaintReportController::class, 'export'])->name('export');
        Route::get('/reports',                [\App\Http\Controllers\Admin\ComplaintReportController::class, 'reports'])->name('reports');

        Route::resource('categories',         \App\Http\Controllers\Admin\ComplaintCategoryController::class)->except(['create', 'show', 'edit']);
        Route::resource('staff',              \App\Http\Controllers\Admin\MaintenanceStaffController::class)->except(['create', 'show', 'edit']);

        Route::get('/',                       [\App\Http\Controllers\Admin\ComplaintController::class, 'index'])->name('index');

        // Wildcard routes last so they don't swallow named segments above
        Route::post('/{complaint}/assign',    [\App\Http\Controllers\Admin\ComplaintController::class, 'assign'])->name('assign');
        Route::post('/{complaint}/priority',  [\App\Http\Controllers\Admin\ComplaintController::class, 'updatePriority'])->name('priority');
        Route::post('/{complaint}/status',    [\App\Http\Controllers\Admin\ComplaintController::class, 'updateStatus'])->name('status');
        Route::post('/{complaint}/resolve',   [\App\Http\Controllers\Admin\ComplaintController::class, 'resolve'])->name('resolve');
        Route::post('/{complaint}/close',     [\App\Http\Controllers\Admin\ComplaintController::class, 'close'])->name('close');
        Route::post('/{complaint}/remark',    [\App\Http\Controllers\Admin\ComplaintController::class, 'addRemark'])->name('remark');
        Route::get('/{complaint}',            [\App\Http\Controllers\Admin\ComplaintController::class, 'show'])->name('show');
    });

    // ── STAFF HR MANAGEMENT ────────────────────────────────────────────────
    Route::prefix('admin/staff')->name('admin.staff.')->group(function () {

        // Attendance
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/',     [\App\Http\Controllers\Admin\StaffAttendanceController::class, 'index'])->name('index');
            Route::post('/save',[\App\Http\Controllers\Admin\StaffAttendanceController::class, 'save'])->name('save');
        });

        // Payroll
        Route::prefix('payroll')->name('payroll.')->group(function () {
            Route::get('/',                [\App\Http\Controllers\Admin\StaffPayrollController::class, 'index'])->name('index');
            Route::post('/generate',       [\App\Http\Controllers\Admin\StaffPayrollController::class, 'generate'])->name('generate');
            Route::get('/{payroll}',       [\App\Http\Controllers\Admin\StaffPayrollController::class, 'show'])->name('show');
            Route::post('/{payroll}/pay',  [\App\Http\Controllers\Admin\StaffPayrollController::class, 'markPaid'])->name('pay');
        });

        // Performance
        Route::prefix('performance')->name('performance.')->group(function () {
            Route::get('/',        [\App\Http\Controllers\Admin\StaffPerformanceController::class, 'index'])->name('index');
            Route::get('/{staff}', [\App\Http\Controllers\Admin\StaffPerformanceController::class, 'show'])->name('show');
        });
    });

});


// ── ALLOTTEE PORTAL (no admin auth required) ────────────────────────
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/',          [AllotteePortalController::class, 'showLogin'])->name('login');
    Route::post('/login',    [AllotteePortalController::class, 'login'])->name('login.post');
    Route::get('/dashboard', [AllotteePortalController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout',   [AllotteePortalController::class, 'logout'])->name('logout');
    Route::get('/bill/{month}', [AllotteePortalController::class, 'viewMonthlyBill'])->name('bill.monthly');
});

// ── PORTAL COMPLAINTS ───────────────────────────────────────────────
Route::prefix('portal/complaints')->name('portal.complaints.')->group(function () {
    Route::get('/',                     [\App\Http\Controllers\Portal\PortalComplaintController::class, 'index'])->name('index');
    Route::post('/',                    [\App\Http\Controllers\Portal\PortalComplaintController::class, 'store'])->name('store');
    Route::get('/{complaint}',          [\App\Http\Controllers\Portal\PortalComplaintController::class, 'show'])->name('show');
    Route::post('/{complaint}/feedback', [\App\Http\Controllers\Portal\PortalComplaintController::class, 'feedback'])->name('feedback');
    Route::post('/{complaint}/reopen',   [\App\Http\Controllers\Portal\PortalComplaintController::class, 'reopen'])->name('reopen');
    Route::post('/{complaint}/remark',   [\App\Http\Controllers\Portal\PortalComplaintController::class, 'addRemark'])->name('remark');
});

// Temporary Route to Copy Reports, Commit and Push to GitHub
Route::get('/git-push', function() {
    // 1. Copy reports from App Data folder into workspace under docs/release/
    $srcDir = 'C:/Users/nadee/.gemini/antigravity-ide/brain/bb0e8d25-0b7c-4319-964a-99cf252749f3';
    $destDir = base_path('docs/release');
    if (!file_exists($destDir)) {
        @mkdir($destDir, 0777, true);
    }
    
    $files = [
        'release_evidence_pack.md',
        'production_hygiene_report.md',
        'tenant_occupancy_compliance_report.md',
        'walkthrough.md',
        'task.md'
    ];
    
    $copied = [];
    foreach ($files as $file) {
        $srcPath = "{$srcDir}/{$file}";
        $destPath = "{$destDir}/{$file}";
        if (file_exists($srcPath)) {
            $copied[$file] = @copy($srcPath, $destPath) ? 'SUCCESS' : 'FAILED';
        } else {
            $copied[$file] = 'SRC_NOT_FOUND';
        }
    }

    // 2. Run Git Commands
    chdir(base_path());
    $log = [];
    
    // Set Git User configuration
    $log['git_config_email'] = shell_exec('git config --global user.email "nadeemseventy3@gmail.com" 2>&1');
    $log['git_config_name']  = shell_exec('git config --global user.name "Nadeem" 2>&1');
    
    // Git add
    $log['git_add'] = shell_exec('git add . 2>&1');
    
    // Git commit
    $commitMsg = "Version 1.0.1 - Completed Tenant Occupancy compliance audit, UAT and production hygiene validation";
    $log['git_commit'] = shell_exec('git commit -m "' . addslashes($commitMsg) . '" 2>&1');
    
    // Git push
    $log['git_push'] = shell_exec('git push 2>&1');

    return response()->json([
        'files_copied' => $copied,
        'git_log' => $log,
    ]);
});



