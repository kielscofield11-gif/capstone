<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BlotterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});



Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('residents', ResidentController::class);
    Route::resource('households', HouseholdController::class);
    Route::resource('blotters', BlotterController::class);

    Route::resource('documents', DocumentController::class);

    Route::middleware('role:admin,secretary,kagawad')->group(function () {
        Route::patch('/documents/{document}/approve', [DocumentController::class, 'approve'])->name('documents.approve');
        Route::patch('/documents/{document}/release', [DocumentController::class, 'release'])->name('documents.release');
        Route::patch('/documents/{document}/cancel', [DocumentController::class, 'cancel'])->name('documents.cancel');
    });

    Route::resource('document-types', DocumentTypeController::class)->except(['show']);

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/residents', [ReportController::class, 'residents'])->name('residents');
        Route::get('/blotters', [ReportController::class, 'blotters'])->name('blotters');
        Route::get('/documents', [ReportController::class, 'documents'])->name('documents');
    });

    Route::prefix('exports')->name('exports.')->group(function () {
        Route::get('/residents/pdf', [ExportController::class, 'residentsPdf'])->name('residents.pdf');
        Route::get('/blotters/pdf', [ExportController::class, 'blottersPdf'])->name('blotters.pdf');
        Route::get('/documents/pdf', [ExportController::class, 'documentsPdf'])->name('documents.pdf');
        Route::get('/residents/{resident}/clearance', [ExportController::class, 'clearancePdf'])->name('clearance.pdf');
    });

    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});
