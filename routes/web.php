<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\CohortController;
use App\Http\Controllers\Admin\SessionController; 
use App\Http\Controllers\Learner\CalendarController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Guest Routes (only accessible when not logged in)
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Register
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // Password Reset
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
        ->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
        ->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
        ->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])
        ->name('password.update');
});

// Authenticated Routes
Route::middleware(['auth', 'check.user.status', 'no.cache'])->group(function () {
    
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Payment Routes (inside auth middleware)
    Route::post('/payment/initiate', [PaymentController::class, 'initiatePayment'])->name('payment.initiate');
    Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');
    Route::post('/payment/installment', [PaymentController::class, 'payInstallment'])->name('payment.installment');

    // Admin Routes
    Route::middleware(['check.role:admin,superadmin'])->prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // User Management
        Route::get('/users/data', [UserController::class, 'getUsersData'])->name('users.data');
        Route::post('/users/{id}/status', [UserController::class, 'updateStatus'])->name('users.update-status');
        Route::resource('users', UserController::class);

        // Admin Routes (inside admin middleware group)
        Route::resource('programs', ProgramController::class);
        Route::resource('cohorts', CohortController::class);

        // Sessions/Calendar
        Route::get('/sessions/calendar', [SessionController::class, 'calendar'])->name('sessions.calendar');
        Route::get('/sessions/events', [SessionController::class, 'getEvents'])->name('sessions.events');
        Route::resource('sessions', SessionController::class);



    });

    // Mentor Routes
    Route::middleware(['check.role:mentor'])->prefix('mentor')->name('mentor.')->group(function () {
        Route::get('/dashboard', function () {
            return view('mentor.dashboard');
        })->name('dashboard');

        Route::get('/sessions/calendar', [SessionController::class, 'calendar'])->name('sessions.calendar');
        Route::get('/sessions/events', [SessionController::class, 'getEvents'])->name('sessions.events');
        Route::resource('sessions', SessionController::class);
    });

    // Learner Routes
    Route::middleware(['check.role:learner'])->prefix('learner')->name('learner.')->group(function () {
        Route::get('/dashboard', function () {
            return view('learner.dashboard');
        })->name('dashboard');

        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
        Route::get('/sessions/events', [CalendarController::class, 'getEvents'])->name('sessions.events');
    });
});