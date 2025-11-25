<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\Admin\NurseController;
use App\Http\Controllers\Admin\SpecialtyController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\ClinicRoomController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\DrugController;
use App\Http\Controllers\Admin\InventoryProductController;
use App\Http\Controllers\Admin\InventoryDrugController;

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

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('doctors', DoctorController::class);
        Route::resource('nurses', NurseController::class);
        Route::resource('specialties', SpecialtyController::class);
        Route::resource('patients', PatientController::class);
        Route::resource('clinic-rooms', ClinicRoomController::class);
        Route::resource('products', ProductController::class);
        Route::resource('drugs', DrugController::class);
        Route::resource('inventory-products', InventoryProductController::class);
        Route::resource('inventory-drugs', InventoryDrugController::class);
        Route::post('/doctors/{id}/send-reset-email', [DoctorController::class, 'sendResetEmail'])->name('doctors.sendResetEmail');
        Route::post('/nurses/{id}/send-reset-email', [NurseController::class, 'sendResetEmail'])->name('nurses.sendResetEmail');
        Route::get('roles-permissions', [RoleController::class, 'index'])->name('roles-permissions.index');
        Route::post('roles-permissions/toggle', [RoleController::class, 'togglePermission'])->name('roles-permissions.toggle');
        Route::resource('appointments', AppointmentController::class);
    });
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
