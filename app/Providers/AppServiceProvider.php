<?php

namespace App\Providers;

use App\Content\Repository\BlogRepository;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('partials.sidebar', function ($view) {
            $repo = new BlogRepository();
            $view->with('categories', $repo->getCategories());
        });
    }
}
