<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AllotteeController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AllotteePortalController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\MonthlyBillController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProjectController;

// ── ADMIN AUTH ──────────────────────────────────────────────────────
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
});

// ── ALLOTTEE PORTAL (no admin auth required) ────────────────────────
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/',          [AllotteePortalController::class, 'showLogin'])->name('login');
    Route::post('/login',    [AllotteePortalController::class, 'login'])->name('login.post');
    Route::get('/dashboard', [AllotteePortalController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout',   [AllotteePortalController::class, 'logout'])->name('logout');
    Route::get('/bill/{month}', [AllotteePortalController::class, 'viewMonthlyBill'])->name('bill.monthly');
});

