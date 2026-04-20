<?php

use App\Domains\Hire\Controller\HireDraftController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/app', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

Route::get('/blog', fn() => 'blog index');
Route::get('/blog/{slug}', fn($slug) => "post: $slug");

Route::prefix('app/hire')->controller(HireDraftController::class)->group(function () {
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
