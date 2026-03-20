<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LearnerController;
use App\Http\Controllers\Admin\MentorManagementController;
use App\Http\Controllers\Admin\ProgramController as AdminProgramController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\SessionController as AdminSessionController;
use App\Http\Controllers\Admin\GraduationController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\Learner\CertificationsController;
use App\Http\Controllers\Learner\MyLearningController;
use App\Http\Controllers\Learner\ProgramController as LearnerProgramController;
use App\Http\Controllers\Learner\ProfileController;
use App\Http\Controllers\Learner\LearningController;
use App\Http\Controllers\Learner\CurriculumController;
use App\Http\Controllers\Learner\AssessmentAttemptController;
use App\Http\Controllers\Learner\GraduationController as LearnerGraduationController;
use App\Http\Controllers\Mentor\DashboardController as MentorDashboardController;
use App\Http\Controllers\Mentor\SessionController as MentorSessionController;
use App\Http\Controllers\Mentor\StudentController;
use App\Http\Controllers\Mentor\ContentController as MentorContentController;
use App\Http\Controllers\Mentor\ProgramController as MentorProgramController;
use App\Http\Controllers\Mentor\CurriculumController as MentorCurriculumController;
use App\Http\Controllers\Mentor\AssessmentController as MentorAssessmentController;
use App\Http\Controllers\Mentor\StudentController as MentorStudentController; 
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ══════════════════════════════════════════════════════════════════════════════
// PUBLIC ROUTES
// ══════════════════════════════════════════════════════════════════════════════

// Home — passes active programs to the landing page for the courses section
Route::get('/', function () {
    $programs = \App\Models\Program::where('status', 'active')
        ->latest()
        ->take(6)
        ->get();

    return view('welcome', compact('programs'));
})->name('home');
Route::get('/explore', [ExploreController::class, 'index'])->name('explore');

// Certificate Verification (Public — no auth required)
Route::get('/certificate/verify/{key}', function ($key) {
    $enrollment = \App\Models\Enrollment::where('certificate_key', $key)->first();

    if (! $enrollment || $enrollment->graduation_status !== 'graduated') {
        abort(404, 'Certificate not found');
    }

    return view('public.certificate-verify', compact('enrollment'));
})->name('certificate.verify');


// ══════════════════════════════════════════════════════════════════════════════
// GUEST ROUTES (only accessible when NOT logged in)
// ══════════════════════════════════════════════════════════════════════════════

Route::middleware('guest')->group(function () {
    // These views are now fallback pages — the primary flow uses modals on the
    // landing page. They remain useful for direct URL access and email redirects.
    Route::get('/login', function () {
            return redirect()->route('home');
        })->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/register', function () {
            return redirect()->route('home');
        })->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // Password Reset
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});


// ══════════════════════════════════════════════════════════════════════════════
// AUTHENTICATED ROUTES
// ══════════════════════════════════════════════════════════════════════════════

Route::middleware(['auth', 'check.user.status', 'no.cache'])->group(function () {

    // ── Logout ─────────────────────────────────────────────────────────────
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // ── Email Verification ─────────────────────────────────────────────────
    // (Accessible to authenticated users regardless of verification status
    //  so unverified users can reach the notice and resend pages.)

    // 1. Notice — shown after registration: "Check your inbox"
    Route::get('/email/verify', function () {
        // If already verified, send them to their dashboard immediately
        if (auth()->user()->hasVerifiedEmail()) {
            return redirect()->route(
                match(auth()->user()->role) {
                    'superadmin', 'admin' => 'admin.dashboard',
                    'mentor'              => 'mentor.dashboard',
                    default               => 'learner.dashboard',
                }
            );
        }

        return view('auth.verify-email');
    })->name('verification.notice');

    // 2. Verification link handler — signed URL from the email
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill(); // Sets email_verified_at + fires Verified event

        $user = $request->user();

        $redirectRoute = match($user->role) {
            'superadmin', 'admin' => 'admin.dashboard',
            'mentor'              => 'mentor.dashboard',
            default               => 'learner.dashboard',
        };

        return redirect()->route($redirectRoute)->with([
            'message'    => 'Email verified! Welcome to G-Luper, ' . $user->first_name . '.',
            'alert-type' => 'success',
        ]);
    })->middleware('signed')->name('verification.verify');

    // 3. Resend verification email (rate-limited: 6 attempts per minute)
    Route::post('/email/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route(
                match($request->user()->role) {
                    'superadmin', 'admin' => 'admin.dashboard',
                    'mentor'              => 'mentor.dashboard',
                    default               => 'learner.dashboard',
                }
            );
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with([
            'status'     => 'verification-link-sent',
            'message'    => 'Verification link sent! Check your inbox.',
            'alert-type' => 'success',
        ]);
    })->middleware('throttle:6,1')->name('verification.send');


    // ── Payments ───────────────────────────────────────────────────────────
    // Payment routes do NOT require verified email — a user should be able
    // to complete payment even before verifying (edge case guard).
    Route::post('/payment/initiate', [PaymentController::class, 'initiatePayment'])->name('payment.initiate');
    Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');
    Route::post('/payment/installment', [PaymentController::class, 'payInstallment'])->name('payment.installment');


    /*
|=============================================================================
| ADMIN ROUTES — replace the entire admin middleware group in web.php
|=============================================================================
*/
 
    Route::middleware(['check.role:admin,superadmin'])->prefix('admin')->name('admin.')->group(function () {
    
        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
        // Activity log
        Route::get('/activity-log',      [ActivityLogController::class, 'index'])->name('activity-log');
        Route::get('/activity-log/{id}', [ActivityLogController::class, 'show'])->name('activity-log.show');
    
        // ── Programs (review-only — mentors own content creation) ────────────────
        Route::get('/programs',                         [AdminProgramController::class, 'index'])->name('programs.index');
        Route::get('/programs/{program}',               [AdminProgramController::class, 'show'])->name('programs.show');
        Route::post('/programs/{program}/publish',      [AdminProgramController::class, 'publish'])->name('programs.publish');
        Route::post('/programs/{program}/reject',       [AdminProgramController::class, 'reject'])->name('programs.reject');
        Route::post('/programs/{program}/take-offline', [AdminProgramController::class, 'takeOffline'])->name('programs.take-offline');
        Route::post('/programs/{program}/restore',      [AdminProgramController::class, 'restore'])->name('programs.restore');
        Route::delete('/programs/{program}',            [AdminProgramController::class, 'destroy'])->name('programs.destroy');
    
        // ── Mentor management ─────────────────────────────────────────────────────
        Route::get('/mentors',               [MentorManagementController::class, 'index'])->name('mentors.index');
        Route::get('/mentors/create',        [MentorManagementController::class, 'create'])->name('mentors.create');
        Route::post('/mentors',              [MentorManagementController::class, 'store'])->name('mentors.store');
        Route::get('/mentors/{id}',          [MentorManagementController::class, 'show'])->name('mentors.show');
        Route::get('/mentors/{id}/edit',     [MentorManagementController::class, 'edit'])->name('mentors.edit');
        Route::put('/mentors/{id}',          [MentorManagementController::class, 'update'])->name('mentors.update');
        Route::delete('/mentors/{id}',       [MentorManagementController::class, 'destroy'])->name('mentors.destroy');
        Route::post('/mentors/{id}/status',  [MentorManagementController::class, 'updateStatus'])->name('mentors.update-status');
    
        // ── Learner management ────────────────────────────────────────────────────
        Route::get('/learners',                                                     [LearnerController::class, 'index'])->name('learners.index');
        Route::get('/learners/{id}',                                                [LearnerController::class, 'show'])->name('learners.show');
        Route::post('/learners/{id}/status',                                        [LearnerController::class, 'updateStatus'])->name('learners.update-status');
        Route::get('/learners/{learner}/assessments/{attempt}',                     [LearnerController::class, 'showAssessmentAttempt'])->name('learners.assessment-attempt');
    
        // ── Sessions (unified calendar — admin creates, views all mentor sessions) ─
        Route::get('/sessions',          [AdminSessionController::class, 'index'])->name('sessions.index');
        Route::get('/sessions/events',   [AdminSessionController::class, 'events'])->name('sessions.events');
        Route::post('/sessions',         [AdminSessionController::class, 'store'])->name('sessions.store');
        Route::put('/sessions/{session}',    [AdminSessionController::class, 'update'])->name('sessions.update');
        Route::delete('/sessions/{session}', [AdminSessionController::class, 'destroy'])->name('sessions.destroy');
    
        // ── Payments ──────────────────────────────────────────────────────────────
        Route::get('/payments',            [AdminPaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/export/csv', [AdminPaymentController::class, 'export'])->name('payments.export');
        Route::get('/payments/{id}',       [AdminPaymentController::class, 'show'])->name('payments.show');
    
        // ── Graduation management ─────────────────────────────────────────────────
        Route::get('/graduations',                      [GraduationController::class, 'index'])->name('graduations.index');
        Route::get('/graduations/graduated',            [GraduationController::class, 'graduated'])->name('graduations.graduated');
        Route::get('/graduations/{enrollment}/review',  [GraduationController::class, 'review'])->name('graduations.review');
        Route::post('/graduations/{enrollment}/approve',[GraduationController::class, 'approve'])->name('graduations.approve');
        Route::post('/graduations/{enrollment}/reject', [GraduationController::class, 'reject'])->name('graduations.reject');
        Route::post('/graduations/bulk-approve',        [GraduationController::class, 'bulkApprove'])->name('graduations.bulk-approve');
    
        // ── User management (SuperAdmin only) ─────────────────────────────────────
        Route::middleware(['check.role:superadmin'])->group(function () {
            Route::get('/users/data',        [UserController::class, 'getUsersData'])->name('users.data');
            Route::post('/users/{id}/status',[UserController::class, 'updateStatus'])->name('users.update-status');
            Route::resource('users', UserController::class);
        });
    });
        

        // ══════════════════════════════════════════════════════════════════════════════
            // MENTOR ROUTES  (replace existing mentor group entirely)
            // ══════════════════════════════════════════════════════════════════════════════
            Route::middleware(['check.role:mentor'])
                ->prefix('mentor')
                ->name('mentor.')
                ->group(function () {
            
                // Dashboard
                Route::get('/dashboard', [MentorDashboardController::class, 'index'])->name('dashboard');
            
                // ── Programs ─────────────────────────────────────────────────────────────
                Route::get('/programs',                    [MentorProgramController::class, 'index'])->name('programs.index');
                Route::get('/programs/create',             [MentorProgramController::class, 'create'])->name('programs.create');
                Route::post('/programs',                   [MentorProgramController::class, 'store'])->name('programs.store');
                Route::get('/programs/{program}',          [MentorProgramController::class, 'show'])->name('programs.show');
                Route::get('/programs/{program}/edit',     [MentorProgramController::class, 'edit'])->name('programs.edit');
                Route::put('/programs/{program}',          [MentorProgramController::class, 'update'])->name('programs.update');
                Route::delete('/programs/{program}',       [MentorProgramController::class, 'destroy'])->name('programs.destroy');
                Route::post('/programs/{program}/submit',  [MentorProgramController::class, 'submitForReview'])->name('programs.submit');
            
                // ── Curriculum — Modules ──────────────────────────────────────────────────
                Route::post('/programs/{program}/modules',                         [MentorCurriculumController::class, 'storeModule'])->name('curriculum.modules.store');
                Route::put('/programs/{program}/modules/{module}',                 [MentorCurriculumController::class, 'updateModule'])->name('curriculum.modules.update');
                Route::delete('/programs/{program}/modules/{module}',              [MentorCurriculumController::class, 'destroyModule'])->name('curriculum.modules.destroy');
                Route::post('/programs/{program}/modules/reorder',                 [MentorCurriculumController::class, 'reorderModules'])->name('curriculum.modules.reorder');
            
                // ── Curriculum — Weeks ────────────────────────────────────────────────────
                Route::post('/programs/{program}/modules/{module}/weeks',          [MentorCurriculumController::class, 'storeWeek'])->name('curriculum.weeks.store');
                Route::put('/programs/{program}/weeks/{week}',                     [MentorCurriculumController::class, 'updateWeek'])->name('curriculum.weeks.update');
                Route::delete('/programs/{program}/weeks/{week}',                  [MentorCurriculumController::class, 'destroyWeek'])->name('curriculum.weeks.destroy');
            
                // ── Curriculum — Contents ─────────────────────────────────────────────────
                Route::post('/programs/{program}/weeks/{week}/contents',           [MentorCurriculumController::class, 'storeContent'])->name('curriculum.contents.store');
                Route::put('/programs/{program}/contents/{content}',               [MentorCurriculumController::class, 'updateContent'])->name('curriculum.contents.update');
                Route::delete('/programs/{program}/contents/{content}',            [MentorCurriculumController::class, 'destroyContent'])->name('curriculum.contents.destroy');
                Route::post('/programs/{program}/weeks/{week}/contents/reorder',   [MentorCurriculumController::class, 'reorderContents'])->name('curriculum.contents.reorder');
            
                // ── Assessments ───────────────────────────────────────────────────────────
                Route::post('/programs/{program}/weeks/{week}/assessment',         [MentorAssessmentController::class, 'store'])->name('assessments.store');
                Route::put('/programs/{program}/assessments/{assessment}',         [MentorAssessmentController::class, 'update'])->name('assessments.update');
                Route::delete('/programs/{program}/assessments/{assessment}',      [MentorAssessmentController::class, 'destroy'])->name('assessments.destroy');
            
                // Questions
                Route::get('/programs/{program}/assessments/{assessment}/questions',           [MentorAssessmentController::class, 'questions'])->name('assessments.questions');
                Route::post('/programs/{program}/assessments/{assessment}/questions',          [MentorAssessmentController::class, 'storeQuestion'])->name('assessments.questions.store');
                Route::put('/programs/{program}/assessments/{assessment}/questions/{question}',[MentorAssessmentController::class, 'updateQuestion'])->name('assessments.questions.update');
                Route::delete('/programs/{program}/questions/{question}',                      [MentorAssessmentController::class, 'destroyQuestion'])->name('assessments.questions.destroy');
            
                // CSV Import
                Route::get('/assessments/questions/template',                                  [MentorAssessmentController::class, 'downloadTemplate'])->name('assessments.questions.template');
                Route::post('/programs/{program}/assessments/{assessment}/import',             [MentorAssessmentController::class, 'importQuestions'])->name('assessments.questions.import');
            
                // ── Sessions ──────────────────────────────────────────────────────────────
                Route::get('/sessions',                    [MentorSessionController::class, 'index'])->name('sessions.index');
                Route::get('/sessions/events',             [MentorSessionController::class, 'events'])->name('sessions.events');
                Route::post('/sessions',                   [MentorSessionController::class, 'store'])->name('sessions.store');
                Route::put('/sessions/{session}',          [MentorSessionController::class, 'update'])->name('sessions.update');
                Route::delete('/sessions/{session}',       [MentorSessionController::class, 'destroy'])->name('sessions.destroy');
            
                // ── Students ──────────────────────────────────────────────────────────────
                Route::get('/students',                    [MentorStudentController::class, 'index'])->name('students.index');
                Route::get('/students/{enrollment}',       [MentorStudentController::class, 'show'])->name('students.show');
            });
            


 


    // ── LEARNER AUTH GROUP ────────────────────────────────────────────────────────
        // Replace your existing learner group with this block,
        // or just add the new routes into your existing group.
        Route::middleware(['verified'])->prefix('learner')->name('learner.')->group(function () {

            // ── My Learning (replaces DashboardController + CalendarController) ──
            // Both URLs point to the same controller — /dashboard kept for backward
            // compatibility with any existing hard-coded links or email links.
            Route::get('/dashboard',   [MyLearningController::class, 'index'])->name('dashboard');
            Route::get('/my-learning', [MyLearningController::class, 'index'])->name('my-learning');

            // AJAX — calendar events for the schedule panel
            Route::get('/my-learning/events', [MyLearningController::class, 'events'])->name('my-learning.events');

            // ── Certifications ────────────────────────────────────────────────────
            Route::get('/certifications', [CertificationsController::class, 'index'])->name('certifications');

            // ── Programs — ENROLL ENDPOINT ONLY (index moved to public /explore) ──
            // Keep this for the AJAX enroll call from the Explore page.
            Route::post('/programs/{program}/enroll', [\App\Http\Controllers\Learner\ProgramController::class, 'enroll'])
                ->name('programs.enroll');

            // ── Learning — NOW REQUIRES enrollmentId (Batch 2 will also add showWeek/content) ──
            // The old route was: Route::get('/learning', [...]) with no param.
            // Changed to accept an enrollment ID so My Learning can link to the
            // correct course when a learner has multiple enrollments.
            Route::get('/learning/{enrollmentId}',         [\App\Http\Controllers\Learner\LearningController::class, 'index'])
                ->name('learning.index');
            Route::get('/learning/{enrollmentId}/week/{weekId}', [\App\Http\Controllers\Learner\LearningController::class, 'showWeek'])
                ->name('learning.week');
            Route::get('/learning/content/{contentId}',    [\App\Http\Controllers\Learner\LearningController::class, 'showContent'])
                ->name('learning.content');

            Route::get('/learning/{enrollmentId}/week/{weekId}/contents', [LearningController::class, 'getWeekContents'])->name('learning.week-contents');
            Route::get('/learning/{enrollmentId}/assessment/{assessmentId}', [LearningController::class, 'getAssessmentData'])->name('learning.assessment-data');

            // Progress update AJAX endpoints (unchanged)
            Route::post('/learning/content/{contentId}/complete',  [\App\Http\Controllers\Learner\LearningController::class, 'markContentComplete'])
                ->name('learning.complete');
            Route::post('/learning/content/{contentId}/progress',  [\App\Http\Controllers\Learner\LearningController::class, 'updateContentProgress'])
                ->name('learning.progress');

            Route::post('/assessments/{assessment}/attempt', [AssessmentAttemptController::class, 'createAttempt'])
                ->name('assessments.attempt');
            
            // Submit answers (called when learner clicks "Submit Assessment")
            Route::post('/attempts/{attempt}/submit', [AssessmentAttemptController::class, 'submit'])
                ->name('attempts.submit');
            
            // Optional: keep the traditional full-page routes too
            Route::get('/attempts/{attempt}',         [AssessmentAttemptController::class, 'show'])
                ->name('attempts.show');
            Route::get('/attempts/{attempt}/results', [AssessmentAttemptController::class, 'results'])
                ->name('attempts.results');
            // Explicit graduation request (safety net — auto-trigger usually fires first)
            Route::post('/graduation/{enrollment}/request',
                [LearnerGraduationController::class, 'request'])
                ->name('graduation.request');           
            
            // Graduation status page (optional — shows eligibility checklist)
            Route::get('/graduation/{enrollment}',
                [LearnerGraduationController::class, 'status'])
                ->name('graduation.status');            

            // Profile
            Route::get('/profile/edit', [\App\Http\Controllers\Learner\ProfileController::class, 'edit'])
                ->name('profile.edit');

            // Certificate download (stub — implement in Batch 2)
            Route::get('/certificate/{key}', [\App\Http\Controllers\Learner\CertificateController::class, 'download'])
                ->name('certificate.download');

        });

}); 