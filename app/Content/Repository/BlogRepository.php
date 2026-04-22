<?php

namespace App\Content\Repository;

use App\Content\MarkdownParser;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;

class BlogRepository
{
    public function find($slug): array
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

    public function paginate($perPage = 5, $search = null): LengthAwarePaginator
    {
        $items = collect($this->all());

        if ($search) {
            $items = $items->filter(function ($item) use ($search) {
                $title = strtolower($item['meta']['title'] ?? '');
                $excerpt = strtolower($item['meta']['description'] ?? '');
                $tags = implode(' ', (array)($item['meta']['tags'] ?? []));
                $searchTerm = strtolower($search);

                return str_contains($title, $searchTerm) ||
                    str_contains($excerpt, $searchTerm) ||
                    str_contains(strtolower($tags), $searchTerm);
            });
        }

        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        // Potong koleksi sesuai halaman saat ini
        $currentItems = $items->slice(($currentPage - 1) * $perPage, $perPage)->all();

        // Buat objek paginator manual
        return new LengthAwarePaginator(
            $currentItems,
            $items->count(),
            $perPage,
            $currentPage,
            ['path' => Request::url(), 'query' => Request::query()]
        );
    }

    public function all(): array
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
                        'tags' => (array) $data['meta']['tags'] ?? [],
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
