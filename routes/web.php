<?php

use App\Domains\Hire\Controller\HireDraftController;
use App\Domains\Hire\Controller\ProjectController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/about', function () {
    return inertia('About');
})->name('about');

Route::get('/', [BlogController::class, 'index'])->name('blog.index');
Route::get('/{slug}/insight', [BlogController::class, 'show'])->name('blog.show');

Route::post('/newsletter/subscribe', [NewsletterController::class, 'store'])
    ->name('newsletter.subscribe');

Route::prefix('services')->controller(ServiceController::class)->group(function () {
    Route::get('/', 'index')->name('services.index');
    Route::get('/{service}', 'show')->name('services.show');
});

Route::prefix('dashboard')->controller(ProjectController::class)->group(function () {
    Route::get('/', 'dashboard')->name('dashboard');
    Route::get('/{project}/edit', 'edit')->name('projects.edit');
    Route::put('/{project}/update', 'update')->name('projects.update');
});

Route::get('/user/profile', function () {
    return 'this is profile';
})->name('profile.edit');

Route::prefix('hire')->controller(HireDraftController::class)->group(function () {
    Route::get('/apply', 'create')->name('hire.apply');
    Route::post('/draft', 'store')->name('hire.draft.store');
    Route::put('/draft/{draft}',  'update')->name('hire.draft.update');
    Route::post('/draft/{draft}/submit',  'submit')->name('hire.draft.submit');
});


Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'store']);

    Route::get('register', [RegisterController::class, 'show'])->name('register');
    Route::post('register', [RegisterController::class, 'store']);
});

Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

Route::get('auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('auth/google/callback', [GoogleAuthController::class, 'callback']);
