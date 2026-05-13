<?php

use App\Content\Controllers\BlogController;
use App\Content\Controllers\NewsletterController;
use App\Domains\Hire\Controller\FileManagerController;
use App\Domains\Hire\Controller\HireDraftController;
use App\Domains\Hire\Controller\ProjectController;
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
