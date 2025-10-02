<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RoleLoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Tutor\MascotaController;
use App\Http\Controllers\Tutor\CitaController as TutorCitaController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminController; // Registro/Perfil de Admin
use App\Http\Middleware\EnsureRole;
use App\Http\Controllers\Admin\Auth\AdminPasswordResetLinkController;
use App\Http\Controllers\Admin\Auth\AdminNewPasswordController;

// ---------------- Public / Auth ----------------
Route::get('/', fn () => view('index'));
Route::get('/login', fn () => redirect()->route('login.tutor'))->name('login');

Route::get('/login/tutor', fn () => view('auth.tutor-login'))->name('login.tutor');
Route::get('/login/veterinario', fn () => view('auth.vet-login'))->name('login.veterinario');
Route::get('/login/admin', fn () => view('auth.admin-login'))->name('login.admin');

Route::post('/login', [RoleLoginController::class, 'login'])->name('login.perform');
Route::post('/logout', [RoleLoginController::class, 'logout'])->name('logout');

// ---------------- Password Reset (genérico) ----------------
Route::get('/password/forgot', [PasswordController::class, 'showForgotForm'])->name('password.request');
Route::post('/password/forgot', [PasswordController::class, 'sendResetLink'])->name('password.email');

// ---------------- Registro Tutor ----------------
Route::get('/register/tutor', [RegisterController::class, 'showTutorForm'])->name('register.tutor');
Route::post('/register/tutor', [RegisterController::class, 'registerTutor'])->name('register.tutor.perform');

/**
 * ============================================================
 * Opción B: Registro público de Administrador desde el login
 * ============================================================
 * - GET  /admin/register -> Form de registro (guest)
 * - POST /admin/register -> Crear admin (guest)
 * (NOMBRES RENOMBRADOS para no chocar con admin.admins.store del dashboard)
 */
Route::middleware('guest')->group(function () {
    Route::get('/admin/register', [AdminController::class, 'create'])->name('admin.register.create');
    Route::post('/admin/register', [AdminController::class, 'store'])->name('admin.register.store');
});
Route::prefix('admin')->name('admin.')->middleware('guest')->group(function () {
    // Mostrar formulario "Olvidaste tu contraseña"
    Route::get('forgot-password', [AdminPasswordResetLinkController::class, 'create'])
        ->name('password.request');

    // Enviar email con enlace de reseteo (solo para usuarios con role=admin)
    Route::post('forgot-password', [AdminPasswordResetLinkController::class, 'store'])
        ->name('password.email');

    // Formulario para definir la nueva contraseña
    Route::get('reset-password/{token}', [AdminNewPasswordController::class, 'create'])
        ->name('password.reset');

    // Guardar nueva contraseña
    Route::post('reset-password', [AdminNewPasswordController::class, 'store'])
        ->name('password.update');
});
// ============================ ZONA AUTENTICADA ============================
Route::middleware(['auth'])->group(function () {

    // Reporte PDF del sistema (solo admin)
    Route::get('/admin/report/pdf', [AdminDashboardController::class, 'exportSystemReportPdf'])
        ->middleware([EnsureRole::class . ':admin'])
        ->name('admin.report.pdf');

    // ---------------- Panel de Administración ----------------
    Route::middleware([EnsureRole::class . ':admin'])->prefix('admin')->name('admin.')->group(function () {

        // Dashboard & vistas
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/summary', [AdminDashboardController::class, 'summary'])->name('summary');
        Route::get('/statistics', [AdminDashboardController::class, 'statistics'])->name('statistics');
        Route::get('/activities', [AdminDashboardController::class, 'activities'])->name('activities');

        // Usuarios
        Route::get('/users', [AdminDashboardController::class, 'users'])->name('users');
        Route::get('/users/export', [AdminDashboardController::class, 'exportUsers'])->name('users.export');
        Route::get('/users/report/pdf', [AdminDashboardController::class, 'exportUsersPdf'])->name('users.report.pdf');
        Route::get('/users/{user}/details', [AdminDashboardController::class, 'userDetails'])->name('users.details');
        Route::patch('/users/{user}', [AdminDashboardController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [AdminDashboardController::class, 'destroyUser'])->name('users.destroy');

        // Citas
        Route::get('/appointments', [AdminDashboardController::class, 'appointments'])->name('appointments');
        Route::patch('/appointments/{cita}/estado', [AdminDashboardController::class, 'updateAppointmentStatus'])->name('appointments.update-status');
        Route::patch('/appointments/{cita}/assign-vet', [AdminDashboardController::class, 'assignDoctor'])->name('appointments.assign-vet');
        Route::get('/appointments/export', [AdminDashboardController::class, 'exportAppointments'])->name('appointments.export');
        Route::get('/appointments/report/pdf', [AdminDashboardController::class, 'exportAppointmentsPdf'])->name('appointments.report');

        // Data / Reportes
        Route::get('/datos/export', [AdminDashboardController::class, 'exportData'])->name('data.export');
        Route::get('/reporte', [AdminDashboardController::class, 'generateReport'])->name('report.generate');

        // Altas desde dashboard
        Route::post('/veterinarios', [AdminDashboardController::class, 'storeVeterinarian'])->name('veterinarios.store');
        Route::post('/admins', [AdminDashboardController::class, 'storeAdmin'])->name('admins.store'); // <- se mantiene

        // Perfil del admin (editar sus propios datos)
        Route::get('/profile/edit', [AdminController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [AdminController::class, 'update'])->name('profile.update');
    });

    // ---------------- Panel Veterinario ----------------
    Route::view('/vet', 'dashboards.vet')->name('vet.dashboard');

    Route::middleware([EnsureRole::class . ':vet'])->prefix('vet')->name('vet.')->group(function () {
        Route::get('/pacientes', [\App\Http\Controllers\Vet\PacienteController::class, 'index'])->name('pacientes.index');
        Route::get('/pacientes/{mascota}', [\App\Http\Controllers\Vet\PacienteController::class, 'show'])->name('pacientes.show');
        Route::post('/pacientes', [\App\Http\Controllers\Vet\PacienteController::class, 'store'])->name('pacientes.store');

        // Propietarios
        Route::get('/tutores', [\App\Http\Controllers\Vet\TutorController::class, 'index'])->name('tutores.index');
        Route::get('/tutores/search', [\App\Http\Controllers\Vet\TutorController::class, 'index'])->name('tutores.search');
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

    // ---------------- Panel Tutor ----------------
    Route::get('/tutor', [\App\Http\Controllers\Tutor\DashboardController::class, 'index'])->name('tutor.dashboard');

    Route::prefix('tutor')->name('tutor.')->group(function () {
        Route::resource('mascotas', MascotaController::class)->parameters(['mascotas' => 'mascota']);
        Route::get('citas/available-vets', [TutorCitaController::class, 'availableVets'])->name('citas.available-vets');
        Route::resource('citas', TutorCitaController::class)->parameters(['citas' => 'cita']);
        Route::patch('citas/{cita}/estado', [TutorCitaController::class, 'updateStatus'])->name('citas.update-status');
        Route::post('mascotas/quick', [\App\Http\Controllers\Tutor\PetQuickController::class, 'store'])->name('mascotas.quick');
    });

    // Mockup de recuperación de contraseña
Route::get('/password/mockup', function () {
    return view('auth.password-mockup');
})->name('password.mockup');

});
