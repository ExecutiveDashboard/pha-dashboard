<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AllotteeController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AllotteePortalController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\BillController;

// ── ADMIN AUTH ──────────────────────────────────────────────────────
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── ADMIN PROTECTED ROUTES ──────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/',          [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/allottees',            [AllotteeController::class, 'index'])->name('allottees.index');
    Route::get('/allottees/{allottee}', [AllotteeController::class, 'show'])->name('allottees.show');

    // Payment recording (admin)
    Route::post('/allottees/{allottee}/payment', [PaymentController::class, 'store'])->name('allottees.payment');

    Route::get('/settings',  [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

    // ── BILLS ──────────────────────────────────────────────────────
    Route::get('/bills/search',         [BillController::class, 'search'])->name('bills.search');
    Route::get('/bills/bulk-pdf',       [BillController::class, 'bulkPdf'])->name('bills.bulk-pdf');
    Route::get('/bills/{allottee}',     [BillController::class, 'show'])->name('bills.show');
    Route::get('/bills/{allottee}/pdf', [BillController::class, 'pdf'])->name('bills.pdf');
});

// ── ALLOTTEE PORTAL (no admin auth required) ────────────────────────
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/',          [AllotteePortalController::class, 'showLogin'])->name('login');
    Route::post('/login',    [AllotteePortalController::class, 'login'])->name('login.post');
    Route::get('/dashboard', [AllotteePortalController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout',   [AllotteePortalController::class, 'logout'])->name('logout');
});
