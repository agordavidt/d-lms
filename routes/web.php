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
use App\Http\Controllers\Admin\GraduationController;
use App\Http\Controllers\Learner\DashboardController as LearnerDashboardController;
use App\Http\Controllers\Learner\CalendarController;
use App\Http\Controllers\Learner\ProgramController as LearnerProgramController;
use App\Http\Controllers\Learner\ProfileController;
use App\Http\Controllers\Learner\LearningController;
use App\Http\Controllers\Learner\CurriculumController;
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

// Certificate Verification (Public)
Route::get('/certificate/verify/{key}', function($key) {
    $enrollment = \App\Models\Enrollment::where('certificate_key', $key)->first();
    
    if (!$enrollment || $enrollment->graduation_status !== 'graduated') {
        abort(404, 'Certificate not found');
    }
    
    return view('public.certificate-verify', compact('enrollment'));
})->name('certificate.verify');

// Guest Routes (only accessible when not logged in)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Authenticated Routes
Route::middleware(['auth', 'check.user.status', 'no.cache'])->group(function () {
    
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Payment Routes
    Route::post('/payment/initiate', [PaymentController::class, 'initiatePayment'])->name('payment.initiate');
    Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');
    Route::post('/payment/installment', [PaymentController::class, 'payInstallment'])->name('payment.installment');

    // Admin Routes
    Route::middleware(['check.role:admin,superadmin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
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

        // User Management (SuperAdmin only)
        Route::middleware(['check.role:superadmin'])->group(function () {
            Route::get('/users/data', [UserController::class, 'getUsersData'])->name('users.data');
            Route::post('/users/{id}/status', [UserController::class, 'updateStatus'])->name('users.update-status');
            Route::resource('users', UserController::class);
        });

        // Program Management
        Route::resource('programs', AdminProgramController::class);
        Route::resource('cohorts', CohortController::class);
        Route::resource('modules', ModuleController::class);
        Route::post('/modules/reorder', [ModuleController::class, 'reorder'])->name('modules.reorder');
        Route::resource('weeks', WeekController::class);
        Route::get('/weeks/modules-by-program', [WeekController::class, 'getModulesByProgram'])->name('weeks.modules-by-program');
        
        // Content Management
        Route::resource('contents', AdminContentController::class);
        Route::post('contents/upload-image', [AdminContentController::class, 'uploadImage'])->name('contents.upload-image');
        Route::get('contents/modules-by-program', [AdminContentController::class, 'getModulesByProgram'])->name('contents.modules-by-program');
        Route::get('contents/weeks-by-module', [AdminContentController::class, 'getWeeksByModule'])->name('contents.weeks-by-module');
        Route::post('contents/reorder', [AdminContentController::class, 'reorder'])->name('contents.reorder');

        // Sessions/Calendar
        Route::get('/sessions/calendar', [AdminSessionController::class, 'calendar'])->name('sessions.calendar');
        Route::get('/sessions/events', [AdminSessionController::class, 'getEvents'])->name('sessions.events');
        Route::resource('sessions', AdminSessionController::class);

        // Payment Management
        Route::get('/payments', [App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/export/csv', [App\Http\Controllers\Admin\PaymentController::class, 'export'])->name('payments.export');
        Route::get('/payments/{id}', [App\Http\Controllers\Admin\PaymentController::class, 'show'])->name('payments.show');

        // Assessment Management
        Route::get('/assessments/create', [App\Http\Controllers\Admin\AssessmentController::class, 'create'])->name('assessments.create');
        Route::post('/assessments', [App\Http\Controllers\Admin\AssessmentController::class, 'store'])->name('assessments.store');
        Route::get('/assessments/{assessment}', [App\Http\Controllers\Admin\AssessmentController::class, 'show'])->name('assessments.show');
        Route::get('/assessments/{assessment}/edit', [App\Http\Controllers\Admin\AssessmentController::class, 'edit'])->name('assessments.edit');
        Route::put('/assessments/{assessment}', [App\Http\Controllers\Admin\AssessmentController::class, 'update'])->name('assessments.update');
        Route::delete('/assessments/{assessment}', [App\Http\Controllers\Admin\AssessmentController::class, 'destroy'])->name('assessments.destroy');
        Route::post('/assessments/{assessment}/toggle-active', [App\Http\Controllers\Admin\AssessmentController::class, 'toggleActive'])->name('assessments.toggle-active');

        // Assessment Question Management
        Route::get('/assessments/{assessment}/questions', [App\Http\Controllers\Admin\AssessmentQuestionController::class, 'index'])->name('assessments.questions.index');
        Route::post('/assessments/{assessment}/questions', [App\Http\Controllers\Admin\AssessmentQuestionController::class, 'store'])->name('assessments.questions.store');
        Route::put('/assessments/{assessment}/questions/{question}', [App\Http\Controllers\Admin\AssessmentQuestionController::class, 'update'])->name('assessments.questions.update');
        Route::delete('/assessments/{assessment}/questions/{question}', [App\Http\Controllers\Admin\AssessmentQuestionController::class, 'destroy'])->name('assessments.questions.destroy');
        Route::post('/assessments/{assessment}/questions/reorder', [App\Http\Controllers\Admin\AssessmentQuestionController::class, 'reorder'])->name('assessments.questions.reorder');
        Route::get('/assessments/{assessment}/questions/import', [App\Http\Controllers\Admin\AssessmentQuestionController::class, 'showImport'])->name('assessments.questions.import-form');
        Route::post('/assessments/{assessment}/questions/import', [App\Http\Controllers\Admin\AssessmentQuestionController::class, 'import'])->name('assessments.questions.import');
        Route::get('/assessments/questions/template', [App\Http\Controllers\Admin\AssessmentQuestionController::class, 'downloadTemplate'])->name('assessments.questions.template');
       
        // Graduation Management
        Route::get('/graduations', [GraduationController::class, 'index'])->name('graduations.index');
        Route::get('/graduations/{enrollment}/review', [GraduationController::class, 'review'])->name('graduations.review');
        Route::post('/graduations/{enrollment}/approve', [GraduationController::class, 'approve'])->name('graduations.approve');
        Route::post('/graduations/{enrollment}/reject', [GraduationController::class, 'reject'])->name('graduations.reject');
        Route::post('/graduations/bulk-approve', [GraduationController::class, 'bulkApprove'])->name('graduations.bulk-approve');
        Route::get('/graduations/graduated', [GraduationController::class, 'graduated'])->name('graduations.graduated');
    });

    // Mentor Routes
    Route::middleware(['check.role:mentor'])->prefix('mentor')->name('mentor.')->group(function () {
        Route::get('/dashboard', [MentorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/sessions/calendar', [MentorSessionController::class, 'calendar'])->name('sessions.calendar');
        Route::get('/sessions/events', [MentorSessionController::class, 'getEvents'])->name('sessions.events');
        Route::post('/sessions/{session}/attendance', [MentorSessionController::class, 'markAttendance'])->name('sessions.attendance');
        Route::resource('sessions', MentorSessionController::class);
        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
        Route::get('/students/{id}', [StudentController::class, 'show'])->name('students.show');
        Route::resource('contents', MentorContentController::class);
    });

    // Learner Routes
    Route::middleware(['check.role:learner'])->prefix('learner')->name('learner.')->group(function () {
        Route::get('/dashboard', [LearnerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/programs', [LearnerProgramController::class, 'index'])->name('programs.index');
        Route::post('/programs/{program}/enroll', [LearnerProgramController::class, 'enroll'])->name('programs.enroll');
        Route::get('/learning', [LearningController::class, 'index'])->name('learning.index');
        Route::get('/learning/week/{week}', [LearningController::class, 'showWeek'])->name('learning.week');
        Route::get('/learning/content/{content}', [LearningController::class, 'showContent'])->name('learning.content');
        Route::post('/learning/content/{content}/complete', [LearningController::class, 'markContentComplete'])->name('learning.content.complete');
        Route::post('/learning/content/{content}/progress', [LearningController::class, 'updateContentProgress'])->name('learning.content.progress');

        // Learner Assessment Routes
        Route::get('/assessments/{assessment}/start', [App\Http\Controllers\Learner\AssessmentAttemptController::class, 'start'])->name('assessments.start');
        Route::post('/assessments/{assessment}/attempt', [App\Http\Controllers\Learner\AssessmentAttemptController::class, 'createAttempt'])->name('assessments.attempt');
        Route::get('/attempts/{attempt}', [App\Http\Controllers\Learner\AssessmentAttemptController::class, 'show'])->name('attempts.show');
        Route::post('/attempts/{attempt}/submit', [App\Http\Controllers\Learner\AssessmentAttemptController::class, 'submit'])->name('attempts.submit');
        Route::get('/attempts/{attempt}/results', [App\Http\Controllers\Learner\AssessmentAttemptController::class, 'results'])->name('attempts.results');

        // Graduation
        Route::post('/graduation/request', [App\Http\Controllers\Learner\GraduationController::class, 'request'])->name('graduation.request');

        Route::get('/curriculum', [CurriculumController::class, 'index'])->name('curriculum');
        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
        Route::get('/sessions/events', [CalendarController::class, 'getEvents'])->name('sessions.events');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    });
});