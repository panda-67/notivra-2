<?php

use App\Domains\Hire\Controller\HireDraftController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\NewsletterController;
use Illuminate\Support\Facades\Route;


Route::get('/', [BlogController::class, 'index'])->name('blog.index');
Route::get('/{slug}/insight', [BlogController::class, 'show'])->name('blog.show');

Route::get('/dashboard', function () {
    return inertia('Dashboard');
})->name('dashboard');

Route::post('/newsletter/subscribe', [NewsletterController::class, 'store'])
    ->name('newsletter.subscribe');

Route::prefix('hire')->controller(HireDraftController::class)->group(function () {
    Route::get('/apply', 'create')->name('hire.apply');
    Route::post('/draft', 'store')->name('hire.draft.store');
    Route::put('/draft/{draft}',  'update')->middleware(['auth'])->name('hire.draft.update');
    Route::post('/draft/{draft}/submit',  'submit')->middleware(['auth'])->name('hire.draft.submit');
});


Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'store']);

    Route::get('register', [RegisterController::class, 'show'])->name('register');
    Route::post('register', [RegisterController::class, 'store']);
});

Route::post('logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

Route::get('auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('auth/google/callback', [GoogleAuthController::class, 'callback']);
