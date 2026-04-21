<?php

namespace App\Content\Repository;

use App\Content\MarkdownParser;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class BlogRepository
{
    public function find($slug)
    {
        return Cache::remember("blog:$slug", 3600, function () use ($slug) {
            $path = blog_path("$slug.md");

            abort_unless(file_exists($path), 404);

            $data = app(MarkdownParser::class)->parse($path);
            $date = Carbon::parse($data['meta']['pubDate'] ?? now());

            return [
                'html' => $data['html'],
                'meta' => array_merge($data['meta'], [
                    'date' => $date->translatedFormat('d F Y'),
                ]),
            ];
        });
    }

    public function all()
    {
        return Cache::remember('blog:all', 3600, function () {
            $files = glob(blog_path('*.md'));

            return collect($files)->map(function ($path) {
                $data = app(MarkdownParser::class)->parse($path);
                $filenameSlug = basename($path, '.md');

                $date = Carbon::parse($data['meta']['pubDate'] ?? now());

                return [
                    'slug' => $filenameSlug,
                    'meta' => array_merge($data['meta'], [
                        'date' => $date->translatedFormat('d F Y'),
                        'raw_date' => $date->toDateString(),
                        'slug' => $data['meta']['slug'] ?? $filenameSlug
                    ])
                ];
            })
                ->sortByDesc('meta.raw_date')
                ->values()
                ->all();
        });
    }
}
