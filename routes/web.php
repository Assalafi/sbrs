<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Applicant\AuthController as ApplicantAuthController;
use App\Http\Controllers\Student\AuthController as StudentAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\AcademicSessionController;
use App\Http\Controllers\Admin\ProgrammeController;
use App\Http\Controllers\Admin\FeeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ApplicationController;
use App\Http\Controllers\Admin\ScreeningController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\ResultController as AdminResultController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\PasswordResetController;
use App\Http\Controllers\Admin\RegisteredUserController;
use App\Http\Controllers\Applicant\DashboardController as ApplicantDashboardController;
use App\Http\Controllers\Applicant\PaymentController as ApplicantPaymentController;
use App\Http\Controllers\Applicant\ApplicationFormController;
use App\Http\Controllers\Applicant\AdmissionController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\RegistrationController;
use App\Http\Controllers\Student\BiodataController;
use App\Http\Controllers\Student\CourseRegistrationController;
use App\Http\Controllers\Student\ExamController;
use App\Http\Controllers\Student\ResultController as StudentResultController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Admin Auth Routes
|--------------------------------------------------------------------------
*/
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Applicant Auth Routes
|--------------------------------------------------------------------------
*/
Route::prefix('applicant')->group(function () {
    Route::get('/login', [ApplicantAuthController::class, 'showLoginForm'])->name('applicant.login');
    Route::post('/login', [ApplicantAuthController::class, 'login']);
    Route::get('/register', [ApplicantAuthController::class, 'showRegisterForm'])->name('applicant.register');
    Route::post('/register', [ApplicantAuthController::class, 'register']);
    Route::post('/logout', [ApplicantAuthController::class, 'logout'])->name('applicant.logout');
});

/*
|--------------------------------------------------------------------------
| Student Auth Routes
|--------------------------------------------------------------------------
*/
Route::prefix('student')->group(function () {
    Route::get('/login', [StudentAuthController::class, 'showLoginForm'])->name('student.login');
    Route::post('/login', [StudentAuthController::class, 'login']);
    Route::post('/logout', [StudentAuthController::class, 'logout'])->name('student.logout');
});

/*
|--------------------------------------------------------------------------
| Admin Routes (auth:web middleware)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->middleware('permission:dashboard.view')->name('admin.dashboard');
    Route::get('/password', [AdminDashboardController::class, 'showPasswordForm'])->name('admin.password');
    Route::post('/password', [AdminDashboardController::class, 'updatePassword'])->name('admin.password.update');

    // Settings
    Route::middleware('permission:settings.view')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('admin.settings.index');
        Route::get('/settings/{group}/edit', [SettingsController::class, 'edit'])->name('admin.settings.edit');
        Route::put('/settings/{group}', [SettingsController::class, 'update'])->middleware('permission:settings.edit')->name('admin.settings.update');
        Route::post('/settings/clear-cache', [SettingsController::class, 'clearCache'])->name('admin.settings.clear-cache');
    });

    // Academic Sessions
    Route::middleware('permission:academic-sessions.view')->group(function () {
        Route::resource('academic-sessions', AcademicSessionController::class)->except(['show'])->names('admin.academic-sessions');
        Route::post('academic-sessions/{academicSession}/set-current', [AcademicSessionController::class, 'setCurrent'])->name('admin.academic-sessions.set-current');
    });

    // Programmes
    Route::middleware('permission:programmes.view')->group(function () {
        Route::resource('programmes', ProgrammeController::class)->names('admin.programmes');
        Route::post('programmes/{programme}/add-combination', [ProgrammeController::class, 'addCombination'])->name('admin.programmes.add-combination');
        Route::delete('programmes/combinations/{combination}', [ProgrammeController::class, 'removeCombination'])->name('admin.programmes.remove-combination');
        Route::get('programmes/{programme}/combinations', [ProgrammeController::class, 'getCombinations'])->name('admin.programmes.combinations');
    });

    // Fees
    Route::middleware('permission:fees.view')->group(function () {
        Route::resource('fees', FeeController::class)->except(['show'])->names('admin.fees');
    });

    // Users
    Route::middleware('permission:users.view')->group(function () {
        Route::resource('users', UserController::class)->except(['show'])->names('admin.users');
    });

    // Roles
    Route::middleware('permission:roles.view')->group(function () {
        Route::resource('roles', RoleController::class)->except(['show'])->names('admin.roles');
    });

    // Applications
    Route::middleware('permission:applications.view')->group(function () {
        Route::get('applications', [ApplicationController::class, 'index'])->name('admin.applications.index');
        Route::get('applications/{application}', [ApplicationController::class, 'show'])->name('admin.applications.show');
        Route::post('applications/{application}/approve', [ApplicationController::class, 'approve'])->middleware('permission:applications.approve')->name('admin.applications.approve');
        Route::post('applications/{application}/reject', [ApplicationController::class, 'reject'])->middleware('permission:applications.reject')->name('admin.applications.reject');
        Route::post('applications/bulk-approve', [ApplicationController::class, 'bulkApprove'])->middleware('permission:applications.approve')->name('admin.applications.bulk-approve');
    });

    // Screening
    Route::middleware('permission:screening.view')->group(function () {
        Route::get('screening', [ScreeningController::class, 'index'])->name('admin.screening.index');
        Route::get('screening/{student}', [ScreeningController::class, 'show'])->name('admin.screening.show');
        Route::post('screening/{student}/approve', [ScreeningController::class, 'approve'])->middleware('permission:screening.approve')->name('admin.screening.approve');
        Route::post('screening/{student}/reject', [ScreeningController::class, 'reject'])->middleware('permission:screening.reject')->name('admin.screening.reject');
    });

    // Students
    Route::middleware('permission:students.view')->group(function () {
        Route::get('students', [AdminStudentController::class, 'index'])->name('admin.students.index');
        Route::get('students/export', [AdminStudentController::class, 'export'])->middleware('permission:students.export')->name('admin.students.export');
        Route::get('students/{student}', [AdminStudentController::class, 'show'])->name('admin.students.show');
    });

    // Payments
    Route::middleware('permission:payments.view')->group(function () {
        Route::get('payments', [AdminPaymentController::class, 'index'])->name('admin.payments.index');
        Route::get('payments/export', [AdminPaymentController::class, 'export'])->middleware('permission:payments.export')->name('admin.payments.export');
        Route::get('payments/{payment}', [AdminPaymentController::class, 'show'])->name('admin.payments.show');
        Route::post('payments/{payment}/verify', [AdminPaymentController::class, 'verify'])->middleware('permission:payments.verify')->name('admin.payments.verify');
    });

    // Courses
    Route::middleware('permission:courses.view')->group(function () {
        Route::resource('courses', CourseController::class)->except(['show'])->names('admin.courses');
    });

    // Results
    Route::middleware('permission:results.view')->group(function () {
        Route::get('results', [AdminResultController::class, 'index'])->name('admin.results.index');
        Route::get('results/create', [AdminResultController::class, 'create'])->middleware('permission:results.upload')->name('admin.results.create');
        Route::post('results/upload', [AdminResultController::class, 'upload'])->middleware('permission:results.upload')->name('admin.results.upload');
        Route::get('results/courses', [AdminResultController::class, 'getCoursesForProgramme'])->name('admin.results.courses');
        Route::get('results/students', [AdminResultController::class, 'getStudentsForCourse'])->name('admin.results.students');
    });

    // Audit Logs
    Route::middleware('permission:audit-logs.view')->group(function () {
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('admin.audit-logs.index');
        Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('admin.audit-logs.show');
    });

    // Password Reset (for all user types)
    Route::middleware('permission:users.view')->group(function () {
        Route::get('password-reset', [PasswordResetController::class, 'index'])->name('admin.password-reset.index');
        Route::post('password-reset/search', [PasswordResetController::class, 'search'])->name('admin.password-reset.search');
        Route::post('password-reset/reset', [PasswordResetController::class, 'reset'])->name('admin.password-reset.reset');
    });

    // Registered Users (all applicants including those without submissions)
    Route::middleware('permission:applications.view')->group(function () {
        Route::get('registered-users', [RegisteredUserController::class, 'index'])->name('admin.registered-users.index');
        Route::get('registered-users/export', [RegisteredUserController::class, 'export'])->name('admin.registered-users.export');
    });
});

/*
|--------------------------------------------------------------------------
| Applicant Routes (applicant.auth middleware)
|--------------------------------------------------------------------------
*/
Route::prefix('applicant')->middleware(['applicant.auth'])->group(function () {
    Route::get('/dashboard', [ApplicantDashboardController::class, 'index'])->name('applicant.dashboard');
    Route::get('/password', [ApplicantDashboardController::class, 'showPasswordForm'])->name('applicant.password');
    Route::post('/password', [ApplicantDashboardController::class, 'updatePassword'])->name('applicant.password.update');

    // Payments
    Route::get('/payment/application-fee', [ApplicantPaymentController::class, 'applicationFee'])->name('applicant.payment.application-fee');
    Route::post('/payment/application-fee/initiate', [ApplicantPaymentController::class, 'initiateApplicationFee'])->name('applicant.payment.application-fee.initiate');
    Route::get('/payment/application-fee/verify', [ApplicantPaymentController::class, 'verifyApplicationFee'])->name('applicant.payment.application-fee.verify');
    Route::get('/payment/admission-fee', [ApplicantPaymentController::class, 'admissionFee'])->name('applicant.payment.admission-fee');
    Route::post('/payment/admission-fee/initiate', [ApplicantPaymentController::class, 'initiateAdmissionFee'])->name('applicant.payment.admission-fee.initiate');
    Route::get('/payment/admission-fee/verify', [ApplicantPaymentController::class, 'verifyAdmissionFee'])->name('applicant.payment.admission-fee.verify');
    Route::get('/payment/callback', [ApplicantPaymentController::class, 'callback'])->name('applicant.payment.callback');

    // Application Form
    Route::get('/application', [ApplicationFormController::class, 'edit'])->name('applicant.application.edit');
    Route::post('/application/personal', [ApplicationFormController::class, 'updatePersonalInfo'])->name('applicant.application.personal');
    Route::post('/application/schools', [ApplicationFormController::class, 'updateSchools'])->name('applicant.application.schools');
    Route::post('/application/results', [ApplicationFormController::class, 'updateResults'])->name('applicant.application.results');
    Route::post('/application/sponsorship', [ApplicationFormController::class, 'updateSponsorship'])->name('applicant.application.sponsorship');
    Route::post('/application/referees', [ApplicationFormController::class, 'updateReferees'])->name('applicant.application.referees');
    Route::post('/application/submit', [ApplicationFormController::class, 'submit'])->name('applicant.application.submit');
    Route::get('/application/combinations/{programme}', [ApplicationFormController::class, 'getCombinations'])->name('applicant.application.combinations');
    Route::get('/application/lgas/{state}', [ApplicationFormController::class, 'getLgas'])->name('applicant.application.lgas');

    // Admission
    Route::get('/admission/letter', [AdmissionController::class, 'letter'])->name('applicant.admission.letter');
    Route::get('/admission/download', [AdmissionController::class, 'downloadLetter'])->name('applicant.admission.download');
});

/*
|--------------------------------------------------------------------------
| Student Routes (student.auth middleware)
|--------------------------------------------------------------------------
*/
Route::prefix('student')->middleware(['student.auth'])->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
    Route::get('/password', [StudentDashboardController::class, 'showPasswordForm'])->name('student.password');
    Route::post('/password', [StudentDashboardController::class, 'updatePassword'])->name('student.password.update');

    // Registration
    Route::get('/registration', [RegistrationController::class, 'index'])->name('student.registration.index');
    Route::post('/registration/initiate', [RegistrationController::class, 'initiatePayment'])->name('student.registration.initiate');
    Route::get('/registration/verify', [RegistrationController::class, 'verifyPayment'])->name('student.registration.verify');

    // Bio-data
    Route::get('/biodata', [BiodataController::class, 'index'])->name('student.biodata.index');
    Route::put('/biodata', [BiodataController::class, 'update'])->name('student.biodata.update');
    Route::get('/lgas/{state}', [BiodataController::class, 'getLgas'])->name('student.lgas');

    // Course Registration
    Route::get('/courses', [CourseRegistrationController::class, 'index'])->name('student.courses.index');
    Route::post('/courses/register', [CourseRegistrationController::class, 'store'])->name('student.courses.register');
    Route::get('/courses/print', [CourseRegistrationController::class, 'printForm'])->name('student.courses.print');

    // Exam Fee
    Route::get('/exam', [ExamController::class, 'index'])->name('student.exam.index');
    Route::post('/exam/initiate', [ExamController::class, 'initiatePayment'])->name('student.exam.initiate');
    Route::get('/exam/verify', [ExamController::class, 'verifyPayment'])->name('student.exam.verify');

    // Results
    Route::get('/results', [StudentResultController::class, 'index'])->name('student.results.index');
});
