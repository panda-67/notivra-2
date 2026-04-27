<?php

namespace App\Providers;

use App\Content\Repository\BlogRepository;
use App\Models\Service;
use Illuminate\Support\Facades\App;
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

        View::composer('partials.footer', function ($view) {
            $locale = App::getLocale();
            $services = Service::all()->map(function ($service) use ($locale) {
                return [
                    'id' => $service->id,
                    'title' => $service->title[$locale] ?? $service->title['id'] ?? 'No Title'
                ];
            });
            $view->with([
                'services' => $services,
                'locale' => $locale
            ]);
        });
    }
}
