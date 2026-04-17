<?php

use App\Domains\Hire\Controller\HireDraftController;
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
    Route::put('/draft/{draft}',  'update')->name('hire.draft.update');
    Route::post('/draft/{draft}/submit',  'submit')->name('hire.draft.submit');
});
