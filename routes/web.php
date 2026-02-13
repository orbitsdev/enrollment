<?php

use App\Http\Controllers\Api\DuplicateCheckController;
use App\Http\Controllers\Api\EnrollmentApiController;
use App\Http\Controllers\Api\StudentSearchController;
use App\Http\Controllers\CurriculumController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SchoolYearController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentPortalController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

// Admin only
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::put('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    Route::resource('users', UserController::class);

    // School settings
    Route::get('school-settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('school-settings', [SettingController::class, 'update'])->name('settings.update');

    // School years
    Route::get('school-years', [SchoolYearController::class, 'index'])->name('school-years.index');
    Route::post('school-years', [SchoolYearController::class, 'store'])->name('school-years.store');
    Route::put('school-years/{schoolYear}', [SchoolYearController::class, 'update'])->name('school-years.update');
    Route::post('school-years/{schoolYear}/activate', [SchoolYearController::class, 'activate'])->name('school-years.activate');
    Route::post('semesters/{semester}/toggle-enrollment', [SchoolYearController::class, 'toggleEnrollment'])->name('semesters.toggle-enrollment');
    Route::post('semesters/{semester}/activate', [SchoolYearController::class, 'activateSemester'])->name('semesters.activate');

    // Unlock grades (admin only)
    Route::put('grades/{section}/{subject}/unlock', [GradeController::class, 'unlock'])->name('grades.unlock');
});

// Admin & Registrar
Route::middleware(['auth', 'role:admin|registrar'])->group(function () {
    // Teachers
    Route::get('teachers', [TeacherController::class, 'index'])->name('teachers.index');
    Route::get('teachers/{teacher}', [TeacherController::class, 'show'])->name('teachers.show');
    Route::get('teachers/{teacher}/edit', [TeacherController::class, 'edit'])->name('teachers.edit');
    Route::put('teachers/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');
    Route::post('teachers/{teacher}/trainings', [TeacherController::class, 'storeTraining'])->name('teachers.trainings.store');
    Route::delete('teachers/{teacher}/trainings/{training}', [TeacherController::class, 'destroyTraining'])->name('teachers.trainings.destroy');

    // Curriculum
    Route::get('curriculum', [CurriculumController::class, 'index'])->name('curriculum.index');
    Route::post('curriculum/tracks', [CurriculumController::class, 'storeTrack'])->name('curriculum.tracks.store');
    Route::put('curriculum/tracks/{track}', [CurriculumController::class, 'updateTrack'])->name('curriculum.tracks.update');
    Route::post('curriculum/strands', [CurriculumController::class, 'storeStrand'])->name('curriculum.strands.store');
    Route::put('curriculum/strands/{strand}', [CurriculumController::class, 'updateStrand'])->name('curriculum.strands.update');
    Route::put('curriculum/tracks/{track}/toggle-active', [CurriculumController::class, 'toggleTrackActive'])->name('curriculum.tracks.toggle-active');
    Route::put('curriculum/strands/{strand}/toggle-active', [CurriculumController::class, 'toggleStrandActive'])->name('curriculum.strands.toggle-active');
    Route::resource('subjects', SubjectController::class);

    // Students
    Route::resource('students', StudentController::class);

    // Sections
    Route::resource('sections', SectionController::class);

    // Enrollment
    Route::get('enrollment', [EnrollmentController::class, 'index'])->name('enrollment.index');
    Route::get('enrollment/create', [EnrollmentController::class, 'create'])->name('enrollment.create');
    Route::post('enrollment', [EnrollmentController::class, 'store'])->name('enrollment.store');
    Route::get('enrollment/{enrollment}', [EnrollmentController::class, 'show'])->name('enrollment.show');
    Route::put('enrollment/{enrollment}/status', [EnrollmentController::class, 'updateStatus'])->name('enrollment.update-status');
    Route::get('enrollment/{enrollment}/print', [EnrollmentController::class, 'printSlip'])->name('enrollment.print');

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/enrollment-summary', [ReportController::class, 'enrollmentSummary'])->name('reports.enrollment-summary');
    Route::get('reports/class-list', [ReportController::class, 'classList'])->name('reports.class-list');
    Route::get('reports/masterlist', [ReportController::class, 'masterlist'])->name('reports.masterlist');
    Route::get('reports/grade-summary', [ReportController::class, 'gradeSummary'])->name('reports.grade-summary');
    Route::get('reports/school-forms', [ReportController::class, 'schoolForms'])->name('reports.school-forms');
    Route::get('reports/export/enrollment-summary', [ReportController::class, 'exportEnrollmentSummary'])->name('reports.export.enrollment-summary');
    Route::get('reports/export/class-list/{section}', [ReportController::class, 'exportClassList'])->name('reports.export.class-list');
    Route::get('reports/export/masterlist', [ReportController::class, 'exportMasterlist'])->name('reports.export.masterlist');
    Route::get('reports/generate/sf1/{section}', [ReportController::class, 'generateSF1'])->name('reports.generate.sf1');
    Route::get('reports/generate/sf5/{section}', [ReportController::class, 'generateSF5'])->name('reports.generate.sf5');
    Route::get('reports/generate/sf9/{enrollment}', [ReportController::class, 'generateSF9'])->name('reports.generate.sf9');
    Route::get('reports/generate/sf10/{student}', [ReportController::class, 'generateSF10'])->name('reports.generate.sf10');

    // Import
    Route::get('import', [ImportController::class, 'index'])->name('import.index');
    Route::post('import/students/upload', [ImportController::class, 'uploadStudents'])->name('import.students.upload');
    Route::post('import/students/confirm', [ImportController::class, 'confirmStudents'])->name('import.students.confirm');
    Route::post('import/grades/upload', [ImportController::class, 'uploadGrades'])->name('import.grades.upload');
    Route::post('import/grades/confirm', [ImportController::class, 'confirmGrades'])->name('import.grades.confirm');
    Route::get('import/template/{type}', [ImportController::class, 'downloadTemplate'])->name('import.template');
});

// Admin, Registrar, Teacher - Grades
Route::middleware(['auth', 'role:admin|registrar|teacher'])->group(function () {
    Route::get('grades', [GradeController::class, 'index'])->name('grades.index');
    Route::get('grades/{section}/{subject}', [GradeController::class, 'entry'])->name('grades.entry');
    Route::put('grades/{section}/{subject}', [GradeController::class, 'store'])->name('grades.store');
    Route::put('grades/{section}/{subject}/lock', [GradeController::class, 'lock'])->name('grades.lock');
});

// API routes (auth required)
Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('students/search', StudentSearchController::class)->name('api.students.search');
    Route::post('students/duplicate-check', DuplicateCheckController::class)->name('api.students.duplicate-check');
    Route::get('enrollment/subjects', [EnrollmentApiController::class, 'getSubjectLoad'])->name('api.enrollment.subjects');
    Route::post('enrollment/prerequisites', [EnrollmentApiController::class, 'checkPrerequisites'])->name('api.enrollment.prerequisites');
    Route::get('enrollment/sections', [EnrollmentApiController::class, 'getAvailableSections'])->name('api.enrollment.sections');
});

// Student Portal
Route::middleware(['auth', 'role:student'])->prefix('my')->group(function () {
    Route::get('profile', [StudentPortalController::class, 'profile'])->name('my.profile');
    Route::get('subjects', [StudentPortalController::class, 'subjects'])->name('my.subjects');
    Route::get('grades', [StudentPortalController::class, 'grades'])->name('my.grades');
});

require __DIR__.'/settings.php';
