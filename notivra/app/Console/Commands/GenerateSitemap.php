<?php

namespace App\Console\Commands;

use App\Content\Repository\BlogRepository;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Service;

#[Signature('app:generate-sitemap')]
#[Description('Generate the sitemap.')]
class GenerateSitemap extends Command
{
    public function __construct(
        protected BlogRepository $blogRepository
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sitemap = Sitemap::create();

        // Halaman Statis
        $sitemap->add(Url::create('/')->setPriority(1.0))
            ->add(Url::create('/about')->setPriority(0.8))
            ->add(Url::create('/gallery')->setPriority(0.7))
            ->add(Url::create('/services')->setPriority(0.7))

            ->add(Url::create(route('login'))->setPriority(0.7))
            ->add(Url::create(route('register'))->setPriority(0.7))
            ->add(Url::create(route('dashboard'))->setPriority(0.7))
            ->add(Url::create(route('hire.apply'))->setPriority(0.7));

        // Halaman Blog (Blade - Dynamic)
        $blogs = collect($this->blogRepository->all());

        $blogs->each(function ($blog) use ($sitemap) {
            $sitemap->add(Url::create(route('blog.show', $blog['slug'])));
        });

        // Halaman Gallery
        $path = resource_path('js/Data/outputs.json');
        $outputs = collect(json_decode(file_get_contents($path), true));

        $outputs->each(function ($output) use ($sitemap) {
            $sitemap->add(Url::create(route('gallery.show', $output['slug'])));
        });

        // Halaman Services (Inertia - Dynamic)
        Service::all()->each(function (Service $service) use ($sitemap) {
            $sitemap->add(Url::create(route('services.show', $service->slug)));
        });

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap berhasil dibuat!');
    }
}
