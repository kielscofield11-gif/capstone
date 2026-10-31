<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\BlotterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DocumentTemplateController;
use App\Http\Controllers\GeneratedDocumentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PayMongoWebhookController;
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

// Public payment return/cancel pages are protected by a per-payment token.
Route::get('/payments/{payment}/return/{token}', [PaymentController::class, 'returnPage'])->name('payments.return');
Route::get('/payments/{payment}/cancel/{token}', [PaymentController::class, 'cancelPage'])->name('payments.cancel');
Route::get('/payments/{payment}/demo/{token}', [PaymentController::class, 'demoCheckout'])->name('payments.demo');
Route::post('/payments/{payment}/demo/{token}', [PaymentController::class, 'demoComplete'])->name('payments.demo.complete');

// PayMongo signs the raw request body; CSRF is excluded in bootstrap/app.php.
Route::post('/webhooks/paymongo', PayMongoWebhookController::class)->name('webhooks.paymongo');



Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');

    Route::resource('residents', ResidentController::class);
    Route::resource('households', HouseholdController::class);
    Route::resource('blotters', BlotterController::class);

    Route::resource('announcements', AnnouncementController::class);
    Route::patch('/announcements/{announcement}/publish', [AnnouncementController::class, 'publish'])->name('announcements.publish');
    Route::patch('/announcements/{announcement}/unpublish', [AnnouncementController::class, 'unpublish'])->name('announcements.unpublish');

    Route::resource('documents', DocumentController::class);

    Route::patch('/documents/{document}/approve', [DocumentController::class, 'approve'])->name('documents.approve');
    Route::patch('/documents/{document}/release', [DocumentController::class, 'release'])->name('documents.release');
    Route::patch('/documents/{document}/cancel', [DocumentController::class, 'cancel'])->name('documents.cancel');
    Route::get('/documents/{document}/preview', [GeneratedDocumentController::class, 'preview'])->name('documents.preview');
    Route::get('/documents/{document}/pdf', [GeneratedDocumentController::class, 'pdf'])->name('documents.generated-pdf');

    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('/documents/{document}/payments', [PaymentController::class, 'store'])->name('documents.payments.store');
    Route::post('/documents/{document}/payments/cash', [PaymentController::class, 'recordCash'])->name('documents.payments.cash');

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
        Route::get('/residents/excel', [ExportController::class, 'residentsExcel'])->name('residents.excel');
        Route::get('/households/excel', [ExportController::class, 'householdsExcel'])->name('households.excel');
        Route::get('/blotters/excel', [ExportController::class, 'blottersExcel'])->name('blotters.excel');
        Route::get('/documents/excel', [ExportController::class, 'documentsExcel'])->name('documents.excel');
    });

    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::resource('document-templates', DocumentTemplateController::class)->except(['show', 'destroy']);
    });
});
