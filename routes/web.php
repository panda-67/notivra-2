<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/app', function () {
    return Inertia::render('Dashboard');
});

Route::get('/blog', fn() => 'blog index');
Route::get('/blog/{slug}', fn($slug) => "post: $slug");
