<?php

use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\AppointmentReminderController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ClinicRoomController;
use App\Http\Controllers\Admin\ConsentTypeController;
use App\Http\Controllers\Admin\ConsentVersionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\Admin\DrugController;
use App\Http\Controllers\Admin\InventoryDrugController;
use App\Http\Controllers\Admin\InventoryProductController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\MedicalEntryController;
use App\Http\Controllers\Admin\NurseController;
use App\Http\Controllers\Admin\PatientConsentController;
use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\Admin\PatientHealthHistoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReminderTypeController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SecretaryController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SpecialtyController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Route::get('/dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/test-tenant-connection', function () {
    $clinic = \App\Models\Clinic::findOrFail(1);

    app(\App\Services\TenantDatabaseService::class)->connect($clinic);

    dd(
        DB::connection('tenant')->getDatabaseName()
    );
});

Route::middleware(['auth'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('doctors', DoctorController::class);
        Route::resource('nurses', NurseController::class);
        Route::resource('specialties', SpecialtyController::class);
        Route::resource('services', ServiceController::class);
        Route::get('/patients/{patient}/consents/{consent}/document', [PatientConsentController::class, 'document'])->name('patient.consents.document');
        Route::get('/patients/search', [PatientController::class, 'search'])->name('patients.search');
        Route::resource('patients', PatientController::class);
        Route::resource('clinic-rooms', ClinicRoomController::class);
        Route::resource('products', ProductController::class);
        Route::resource('drugs', DrugController::class);
        Route::resource('inventory-products', InventoryProductController::class);
        Route::get('medical-records/{id}/entries', [MedicalEntryController::class, 'index'])->name('medical-entries.index');
        Route::post('medical-entries', [MedicalEntryController::class, 'store'])->name('medical-entries.store');
        Route::put('medical-entries/{medical_entry}', [MedicalEntryController::class, 'update'])->name('medical-entries.update');
        Route::get('/medical-entries/{version}/pdf', [MedicalEntryController::class, 'versionPdf'])->name('medical-entries.pdf');
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::put('inventory-products/{inventoryProduct}/update-quantity', [InventoryProductController::class, 'updateQuantity'])->name('inventory-products.update-quantity');
        Route::put('inventory-products/{inventoryProduct}/update-expiration', [InventoryProductController::class, 'updateExpiryDate'])->name('inventory-products.update-expiration');
        Route::put('inventory-drugs/{inventoryDrug}/update-quantity', [InventoryDrugController::class, 'updateQuantity'])->name('inventory-drugs.update-quantity');
        Route::put('inventory-drugs/{inventoryDrug}/update-expiration', [InventoryDrugController::class, 'updateExpiryDate'])->name('inventory-drugs.update-expiration');
        Route::resource('inventory-drugs', InventoryDrugController::class);
        Route::post('/doctors/{id}/send-reset-email', [DoctorController::class, 'sendResetEmail'])->name('doctors.sendResetEmail');
        Route::post('/nurses/{id}/send-reset-email', [NurseController::class, 'sendResetEmail'])->name('nurses.sendResetEmail');
        Route::post('/secretary/{id}/send-reset-email', [SecretaryController::class, 'sendResetEmail'])->name('secretaries.sendResetEmail');
        Route::get('roles-permissions', [RoleController::class, 'index'])->name('roles-permissions.index');
        Route::post('roles-permissions/toggle', [RoleController::class, 'togglePermission'])->name('roles-permissions.toggle');
        Route::resource('appointments', AppointmentController::class);
        Route::put('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus']);
        Route::resource('secretaries', SecretaryController::class);
        Route::post('/patient-health-history', [PatientHealthHistoryController::class, 'store'])->name('patient-health-history.store');
        Route::resource('/reminder-types', ReminderTypeController::class);
        Route::get('/reminders', [AppointmentReminderController::class, 'index'])->name('reminders.index');
        Route::get('/reminders/{reminder}', [AppointmentReminderController::class, 'show'])->name('reminders.show');
        Route::put('/invoices/{invoice}/change-status', [InvoiceController::class, 'changeStatus'])->name('invoices.change-status');
        Route::get('/invoices/create/{appointment}', [InvoiceController::class, 'create'])->name('appointments.invoice.create');
        Route::resource('invoices', InvoiceController::class);
        Route::resource('/consent-types', ConsentTypeController::class);
        Route::resource('/consent-types.consent-versions', ConsentVersionController::class);
        Route::get('/consent-types/{consent_type}/consent-versions/{consent_version}/generate-pdf', [ConsentVersionController::class, 'generatePdf'])->name('consent-types.consent-versions.generate-pdf');

        Route::resource('patient.consents', PatientConsentController::class);
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
