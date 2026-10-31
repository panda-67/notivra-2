<?php

use App\Content\Controllers\BlogController;
use App\Content\Controllers\NewsletterController;
use App\Domains\Admin\Controllers\Assignments\AssignmentFileController;
use App\Domains\Admin\Controllers\Assignments\AssignmentGroupController as AdminAssignmentGroupController;
use App\Domains\Admin\Controllers\Assignments\AssignmentIndividualQuizResultController;
use App\Domains\Admin\Controllers\Assignments\AssignmentQuizResultController;
use App\Domains\Admin\Controllers\Assignments\IndividualQuizParticipantController;
use App\Domains\Admin\Controllers\DashboardController;
use App\Domains\Admin\Controllers\ProjectRequestController;
use App\Domains\Assignments\Controllers\AssignmentGroupController;
use App\Domains\Assignments\Controllers\AssignmentIndividualQuizController;
use App\Domains\Assignments\Controllers\AssignmentQuizController;
use App\Domains\Assignments\Controllers\AssignmentRegistrationController;
use App\Domains\Assignments\Controllers\AssignmentSubmissionController;
use App\Domains\Hire\Controllers\FileManagerController;
use App\Domains\Hire\Controllers\HireDraftController;
use App\Domains\Hire\Controllers\ProjectController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;


Route::get('/about', AboutController::class)->name('about');
Route::post('/newsletter/subscribe', NewsletterController::class)->name('newsletter.subscribe');

Route::controller(BlogController::class)->group(function () {
    Route::get('/', 'index')->name('blog.index');
    Route::get('/{slug}/insight', 'show')->name('blog.show');
});

Route::prefix('gallery')->controller(GalleryController::class)->group(function () {
    Route::get('/', 'index')->name('gallery.index');
    Route::get('/{slug}', 'show')->name('gallery.show');
});

Route::prefix('services')->controller(ServiceController::class)->group(function () {
    Route::get('/', 'index')->name('services.index');
    Route::get('/{service}', 'show')->name('services.show');
});

Route::prefix('dashboard')->group(function () {
    Route::controller(ProjectController::class)->group(function () {
        Route::get('/', 'dashboard')->name('dashboard');
        Route::get('/{project}/edit', 'edit')->name('projects.edit');
        Route::put('/{project}/update', 'update')->name('projects.update');
    });

    Route::controller(FileManagerController::class)->group(function () {
        Route::get('/{project}/file-manager', 'show')->name('file-manager');
        Route::post('/{project}/file-manager', 'store')->name('file-manager.store');
        Route::delete('/{file}/file-manager', 'destroy')->name('file-manager.destroy');
    });
});

Route::prefix('user')->controller(ProfileController::class)->group(function () {
    Route::get('/profile', 'edit')->name('profile.edit');
    Route::patch('/profile', 'update')->name('profile.update');
});

Route::prefix('hire')->controller(HireDraftController::class)->group(function () {
    Route::get('/apply', 'create')->name('hire.apply');
    Route::post('/draft', 'store')->name('hire.draft.store');
    Route::put('/draft/{draft}',  'update')->name('hire.draft.update');
    Route::post('/draft/{draft}/submit',  'submit')->name('hire.draft.submit');
});

Route::prefix('assignments')->as('assignments.')->group(function () {
    Route::controller(AssignmentGroupController::class)->group(function () {
        Route::get('/{slug}/groups', 'index')
            ->name('groups.index');

        Route::get('/{slug}/groups/{reference_code}', 'show')
            ->name('groups.show');
    });

    Route::controller(AssignmentRegistrationController::class)->group(function () {
        Route::get('/{slug}/register', 'create')
            ->name('register');

        Route::post('/{slug}/register', 'store')
            ->middleware('throttle:10,1')
            ->name('register.store');
    });

    Route::prefix('quiz')->as('quiz.')->controller(AssignmentQuizController::class)->group(function () {
        Route::get('/start/{token}', 'showQuiz')->name('page');
        Route::post('/start', 'start')->middleware('throttle:10,1')->name('start');
        Route::post('/resume', 'resume')->middleware('throttle:20,1')->name('resume');

        Route::get('/recover/{verification}', 'recoverPage')->whereNumber('verification')->name('recover.page');
        Route::post('/recover', 'recover')->middleware('throttle:10,1')->name('recover');

        Route::get('/session', 'show')->middleware('throttle:60,1')->name('session');

        Route::put('/answers/{position}', 'answer')->whereNumber('position')
            ->middleware('throttle:60,1')->name('answers.store');

        Route::post('/complete', 'complete')->middleware('throttle:10,1')->name('complete');
    });

    Route::post('/{slug}/groups/{reference_code}/submission', [AssignmentSubmissionController::class, 'store'],)
        ->middleware('throttle:10,1')
        ->name('submission.store');

    Route::prefix('{slug}/individual-quiz')->as('individual-quiz.')
        ->controller(AssignmentIndividualQuizController::class)
        ->group(function () {
            Route::get('/', 'show')
                ->name('show');

            Route::post('/start', 'start')
                ->middleware('throttle:10,1')
                ->name('start');

            Route::get('/session', 'session')
                ->middleware('throttle:60,1')
                ->name('session');

            Route::put('/answers/{position}', 'answer')
                ->whereNumber('position')
                ->middleware('throttle:60,1')
                ->name('answers.store');

            Route::post('/complete', 'complete')
                ->middleware('throttle:10,1')
                ->name('complete');
        });
});

Route::controller(RegisterController::class)->group(function () {
    Route::get('register', 'show')->name('register');
    Route::post('register', 'store');
});

Route::controller(LoginController::class)->group(function () {
    Route::get('login', 'show')->name('login');
    Route::post('login', 'store');
    Route::post('logout', 'destroy')->name('logout');
});

Route::controller(GoogleAuthController::class)->group(function () {
    Route::get('auth/google', 'redirect')->name('auth.google');
    Route::get('auth/google/callback', 'callback');
});


Route::prefix('admin')->as('admin.')->middleware('role:super_admin,admin_staff')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');
        Route::get('/projects', [ProjectRequestController::class, 'index'])
            ->name('projects.index');
        Route::patch('/projects/{id}/update-status', [ProjectRequestController::class, 'updateStatus'])
            ->name('projects.updateStatus');

        Route::prefix('/assignments')->name('assignments.')->controller(AssignmentQuizResultController::class)
            ->group(function () {
                Route::get('/quiz-results', 'index')->name('quiz-results.index');
                Route::get('/quiz-results/export', 'export')->name('quiz-results.export');
            });

        Route::prefix('/assignments')->name('assignments.')->controller(AdminAssignmentGroupController::class)
            ->group(function () {
                Route::get('/{slug}/groups', 'index')->name('groups.index');
                Route::get('/{slug}/groups/{reference_code}', 'show')->name('groups.show');

                Route::post('/{slug}/groups/{reference_code}/bypass-upload', 'bypassUpload')
                    ->name('groups.bypass-upload');

                Route::post('/{slug}/groups/{reference_code}/members/{member}/reset-session', 'resetMemberSession')
                    ->whereNumber('member')->name('groups.members.reset-session');

                Route::delete('/{slug}/groups/{reference_code}/members/{member}/device', 'removeMemberDevice')
                    ->whereNumber('member')->name('groups.members.remove-device');
            });

        Route::prefix('/assignments')->name('assignments.')->controller(AssignmentFileController::class)
            ->group(function () {
                Route::get('/files', 'index')->name('files.index');
                Route::get('/files/{submission}/download', 'download')->whereNumber('submission')
                    ->name('files.download');
            });

        Route::prefix('/assignments/{slug}/individual-quiz/participants')->as('assignments.individual-quiz.participants.')
            ->controller(IndividualQuizParticipantController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');

                Route::post('/', 'store')
                    ->middleware('throttle:30,1')
                    ->name('store');

                Route::patch('/schedule', 'updateSchedule')->name('schedule.update');

                Route::patch('/{participant}/status', 'updateStatus')
                    ->whereNumber('participant')
                    ->name('status.update');

                Route::post('/{participant}/reset-session', 'resetSession')
                    ->whereNumber('participant')
                    ->name('session.reset');

                Route::delete('/{participant}/device', 'removeDevice')
                    ->whereNumber('participant')
                    ->name('device.remove');

                Route::get('/template', 'downloadTemplate')
                    ->name('template');

                Route::post('/import', 'import')
                    ->middleware('throttle:10,1')
                    ->name('import');
            });

        Route::prefix('/assignments')->name('assignments.')->controller(AssignmentIndividualQuizResultController::class)
            ->group(function () {
                Route::get('/individual-quiz-results', 'index')->name('individual-quiz-results.index');
                Route::get('/individual-quiz-results/export', 'export')->name('individual-quiz-results.export');
            });
    });
