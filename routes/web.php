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

// Registro de veterinario (solo admin)
Route::middleware(['auth'])->group(function () {
    
    // Ruta para generar y descargar el reporte PDF del sistema
    Route::get('/admin/report/pdf', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'exportSystemReportPdf'])
    ->middleware(['auth', \App\Http\Middleware\EnsureRole::class . ':admin'])
    ->name('admin.report.pdf');

    
    // Rutas del panel de administración 
    Route::middleware([EnsureRole::class . ':admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/summary', [AdminDashboardController::class, 'summary'])->name('summary');
        Route::get('/statistics', [AdminDashboardController::class, 'statistics'])->name('statistics');
        Route::get('/activities', [AdminDashboardController::class, 'activities'])->name('activities');
        Route::get('/users', [AdminDashboardController::class, 'users'])->name('users');
        Route::get('/appointments', [AdminDashboardController::class, 'appointments'])->name('appointments');
        Route::patch('/appointments/{cita}/estado', [AdminDashboardController::class, 'updateAppointmentStatus'])->name('appointments.update-status');
        Route::get('/appointments/export', [AdminDashboardController::class, 'exportAppointments'])->name('appointments.export');
        Route::get('/appointments/report/pdf', [AdminDashboardController::class, 'exportAppointmentsPdf'])->name('appointments.report');
        Route::get('/users/export', [AdminDashboardController::class, 'exportUsers'])->name('users.export');
        Route::patch('/users/{user}', [AdminDashboardController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [AdminDashboardController::class, 'destroyUser'])->name('users.destroy');
        Route::get('/datos/export', [AdminDashboardController::class, 'exportData'])->name('data.export');
        Route::get('/reporte', [AdminDashboardController::class, 'generateReport'])->name('report.generate');
        Route::post('/veterinarios', [AdminDashboardController::class, 'storeVeterinarian'])->name('veterinarios.store');
        Route::get('/appointments', [AdminDashboardController::class, 'appointments'])->name('appointments');
        Route::patch('/appointments/{cita}/estado', [AdminDashboardController::class, 'updateAppointmentStatus'])->name('appointments.update-status');
        Route::get('/appointments/export', [AdminDashboardController::class, 'exportAppointments'])->name('appointments.export');
    });

    Route::view('/vet', 'dashboards.vet')->name('vet.dashboard');

    // Rutas del panel de veterinario
    Route::middleware([EnsureRole::class . ':vet'])->prefix('vet')->name('vet.')->group(function () {
        Route::get('/pacientes', [\App\Http\Controllers\Vet\PacienteController::class, 'index'])->name('pacientes.index');
        Route::get('/pacientes/{mascota}', [\App\Http\Controllers\Vet\PacienteController::class, 'show'])->name('pacientes.show');
        Route::post('/pacientes', [\App\Http\Controllers\Vet\PacienteController::class, 'store'])->name('pacientes.store');
        Route::get('/tutores/search', [\App\Http\Controllers\Vet\PacienteController::class, 'searchTutor'])->name('tutores.search');
        // Propietarios
        Route::get('/tutores', [\App\Http\Controllers\Vet\TutorController::class, 'index'])->name('tutores.index');
        Route::post('/tutores', [\App\Http\Controllers\Vet\TutorController::class, 'store'])->name('tutores.store');

        // Agenda del veterinario
        Route::get('/agenda', [\App\Http\Controllers\Vet\AgendaController::class, 'page'])->name('agenda');
        Route::get('/appointments', [\App\Http\Controllers\Vet\AgendaController::class, 'appointments'])->name('appointments');
        Route::post('/appointments', [\App\Http\Controllers\Vet\AgendaController::class, 'storeAppointment'])->name('appointments.store');
        Route::patch('/appointments/{cita}/estado', [\App\Http\Controllers\Vet\AgendaController::class, 'updateStatus'])->name('appointments.update-status');
        Route::get('/notifications', [\App\Http\Controllers\Vet\AgendaController::class, 'notifications'])->name('notifications');
        Route::get('/agenda/report/pdf', [\App\Http\Controllers\Vet\AgendaController::class, 'exportPdf'])->name('agenda.report');
        Route::get('/tutores/{tutor}/mascotas', [\App\Http\Controllers\Vet\AgendaController::class, 'getTutorPets'])->name('tutores.pets');
    });
    Route::get('/tutor', [\App\Http\Controllers\Tutor\DashboardController::class, 'index'])->name('tutor.dashboard');

    Route::prefix('tutor')->name('tutor.')->group(function () {
        Route::resource('mascotas', MascotaController::class)->parameters(['mascotas' => 'mascota']);
        Route::get('citas/available-vets', [TutorCitaController::class, 'availableVets'])->name('citas.available-vets');
        Route::resource('citas', TutorCitaController::class)->parameters(['citas' => 'cita']);
        Route::post('mascotas/quick', [\App\Http\Controllers\Tutor\PetQuickController::class, 'store'])->name('mascotas.quick');
    });



});
