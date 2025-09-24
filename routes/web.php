<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RoleLoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Tutor\MascotaController;
use App\Http\Controllers\Tutor\CitaController as TutorCitaController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Middleware\EnsureRole;

Route::get('/', fn () => view('index'));
Route::get('/login', fn () => redirect()->route('login.tutor'))->name('login');

Route::get('/login/tutor', fn () => view('auth.tutor-login'))->name('login.tutor');
Route::get('/login/veterinario', fn () => view('auth.vet-login'))->name('login.veterinario');
Route::get('/login/admin', fn () => view('auth.admin-login'))->name('login.admin');

Route::post('/login', [RoleLoginController::class, 'login'])->name('login.perform');
Route::post('/logout', [RoleLoginController::class, 'logout'])->name('logout');

// Recuperar contraseña (vista + acción genérica)
Route::get('/password/forgot', [PasswordController::class, 'showForgotForm'])->name('password.request');
Route::post('/password/forgot', [PasswordController::class, 'sendResetLink'])->name('password.email');

// Registro de tutor
Route::get('/register/tutor', [RegisterController::class, 'showTutorForm'])->name('register.tutor');
Route::post('/register/tutor', [RegisterController::class, 'registerTutor'])->name('register.tutor.perform');

Route::middleware(['auth'])->group(function () {
    Route::middleware([EnsureRole::class . ':admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/summary', [AdminDashboardController::class, 'summary'])->name('summary');
        Route::get('/statistics', [AdminDashboardController::class, 'statistics'])->name('statistics');
        Route::get('/activities', [AdminDashboardController::class, 'activities'])->name('activities');
        Route::get('/users', [AdminDashboardController::class, 'users'])->name('users');
        Route::get('/appointments', [AdminDashboardController::class, 'appointments'])->name('appointments');
        Route::patch('/appointments/{cita}/estado', [AdminDashboardController::class, 'updateAppointmentStatus'])->name('appointments.update-status');
        Route::get('/appointments/export', [AdminDashboardController::class, 'exportAppointments'])->name('appointments.export');
        Route::get('/users/export', [AdminDashboardController::class, 'exportUsers'])->name('users.export');
        Route::get('/datos/export', [AdminDashboardController::class, 'exportData'])->name('data.export');
        Route::get('/reporte', [AdminDashboardController::class, 'generateReport'])->name('report.generate');
        Route::post('/veterinarios', [AdminDashboardController::class, 'storeVeterinarian'])->name('veterinarios.store');
    });

    Route::view('/vet', 'dashboards.vet')->name('vet.dashboard');
    Route::view('/tutor', 'dashboards.tutor')->name('tutor.dashboard');

    Route::prefix('tutor')->name('tutor.')->group(function () {
        Route::resource('mascotas', MascotaController::class)->parameters(['mascotas' => 'mascota']);
        Route::resource('citas', TutorCitaController::class)->parameters(['citas' => 'cita']);
    });
});