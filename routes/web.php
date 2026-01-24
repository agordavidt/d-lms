<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\LearnerController;
use App\Http\Controllers\Admin\MentorManagementController;
use App\Http\Controllers\Admin\ProgramController as AdminProgramController;
use App\Http\Controllers\Admin\CohortController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\SessionController as AdminSessionController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\WeekController;
use App\Http\Controllers\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Learner\DashboardController as LearnerDashboardController;
use App\Http\Controllers\Learner\CalendarController;
use App\Http\Controllers\Learner\ProgramController as LearnerProgramController;
use App\Http\Controllers\Learner\ProfileController;
use App\Http\Controllers\Learner\LearningController;
use App\Http\Controllers\Mentor\DashboardController as MentorDashboardController;
use App\Http\Controllers\Mentor\SessionController as MentorSessionController;
use App\Http\Controllers\Mentor\StudentController;
use App\Http\Controllers\Mentor\ContentController as MentorContentController;
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

    // Register (Learners Only)
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

        // Activity Log
        Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log');
        Route::get('/activity-log/{id}', [ActivityLogController::class, 'show'])->name('activity-log.show');

        // Learner Management
        Route::get('/learners', [LearnerController::class, 'index'])->name('learners.index');
        Route::get('/learners/data', [LearnerController::class, 'getData'])->name('learners.data');
        Route::get('/learners/{id}', [LearnerController::class, 'show'])->name('learners.show');
        Route::post('/learners/{id}/status', [LearnerController::class, 'updateStatus'])->name('learners.update-status');

        // Mentor Management
        Route::get('/mentors', [MentorManagementController::class, 'index'])->name('mentors.index');
        Route::get('/mentors/data', [MentorManagementController::class, 'getData'])->name('mentors.data');
        Route::get('/mentors/create', [MentorManagementController::class, 'create'])->name('mentors.create');
        Route::post('/mentors', [MentorManagementController::class, 'store'])->name('mentors.store');
        Route::get('/mentors/{id}/edit', [MentorManagementController::class, 'edit'])->name('mentors.edit');
        Route::put('/mentors/{id}', [MentorManagementController::class, 'update'])->name('mentors.update');
        Route::delete('/mentors/{id}', [MentorManagementController::class, 'destroy'])->name('mentors.destroy');
        Route::post('/mentors/{id}/status', [MentorManagementController::class, 'updateStatus'])->name('mentors.update-status');

        // User Management (SuperAdmin only - for creating admins)
        Route::middleware(['check.role:superadmin'])->group(function () {
            Route::get('/users/data', [UserController::class, 'getUsersData'])->name('users.data');
            Route::post('/users/{id}/status', [UserController::class, 'updateStatus'])->name('users.update-status');
            Route::resource('users', UserController::class);
        });

        // Program Management
        Route::resource('programs', AdminProgramController::class);
        Route::resource('cohorts', CohortController::class);

        // Curriculum Management 
        Route::resource('modules', ModuleController::class);
        Route::post('/modules/reorder', [ModuleController::class, 'reorder'])->name('modules.reorder');
        
        Route::resource('weeks', WeekController::class);
        Route::get('/weeks/modules-by-program', [WeekController::class, 'getModulesByProgram'])
            ->name('weeks.modules-by-program');

        Route::resource('contents', AdminContentController::class);
        Route::post('/contents/reorder', [AdminContentController::class, 'reorder'])->name('contents.reorder');
        Route::get('/contents/weeks-by-module', [AdminContentController::class, 'getWeeksByModule'])
            ->name('contents.weeks-by-module');

        // Sessions/Calendar
        Route::get('/sessions/calendar', [AdminSessionController::class, 'calendar'])->name('sessions.calendar');
        Route::get('/sessions/events', [AdminSessionController::class, 'getEvents'])->name('sessions.events');
        Route::resource('sessions', AdminSessionController::class);
    });

    // Mentor Routes
    Route::middleware(['check.role:mentor'])->prefix('mentor')->name('mentor.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [MentorDashboardController::class, 'index'])->name('dashboard');

        // Sessions Management
        Route::get('/sessions/calendar', [MentorSessionController::class, 'calendar'])->name('sessions.calendar');
        Route::get('/sessions/events', [MentorSessionController::class, 'getEvents'])->name('sessions.events');
        Route::post('/sessions/{session}/attendance', [MentorSessionController::class, 'markAttendance'])->name('sessions.attendance');
        Route::resource('sessions', MentorSessionController::class);

        // Student Management
        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
        Route::get('/students/{id}', [StudentController::class, 'show'])->name('students.show');

        // Content Management
        Route::resource('contents', MentorContentController::class);
    });

    // Learner Routes
    Route::middleware(['check.role:learner'])->prefix('learner')->name('learner.')->group(function () {
        // Dashboard (redirects to appropriate view)
        Route::get('/dashboard', [LearnerDashboardController::class, 'index'])->name('dashboard');

        // Programs (Browse and Enroll)
        Route::get('/programs', [LearnerProgramController::class, 'index'])->name('programs.index');
        Route::get('/programs/{slug}', [LearnerProgramController::class, 'show'])->name('programs.show');
        Route::post('/programs/{program}/enroll', [LearnerProgramController::class, 'enroll'])->name('programs.enroll');

        // Learning Dashboard 
        Route::get('/learning', [LearningController::class, 'index'])->name('learning.index');
        Route::get('/learning/curriculum', [LearningController::class, 'curriculum'])->name('learning.curriculum');
        Route::get('/learning/week/{week}', [LearningController::class, 'showWeek'])->name('learning.week');
        Route::get('/learning/content/{content}', [LearningController::class, 'showContent'])->name('learning.content');
        
        // Content Progress 
        Route::post('/learning/content/{content}/complete', [LearningController::class, 'markContentComplete'])
            ->name('learning.content.complete');
        Route::post('/learning/content/{content}/progress', [LearningController::class, 'updateContentProgress'])
            ->name('learning.content.progress');

        // Calendar (for viewing sessions)
        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
        Route::get('/sessions/events', [CalendarController::class, 'getEvents'])->name('sessions.events');

        // Profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    });
});