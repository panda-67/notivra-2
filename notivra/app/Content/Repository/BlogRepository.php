<?php

namespace App\Content\Repository;

use App\Content\MarkdownParser;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request;
use SplFileInfo;

class BlogRepository
{
    public function find($slug): array
    {
        $blog = collect($this->all())->first(function ($item) use ($slug) {
            return $item['slug'] === $slug;
        });

        abort_unless($blog, 404);

        return Cache::remember("blog:content:$slug", 3600, function () use ($blog) {

            $relativePath = str_replace('_', DIRECTORY_SEPARATOR, $blog['slug']) . '.md';
            $path = base_path('content' . DIRECTORY_SEPARATOR . $relativePath);

            abort_unless(file_exists($path), 404);

            $data = app(MarkdownParser::class)->parse($path);

            return ['html' => $data['html'], 'meta' => $blog['meta']];
        });
    }

    public function getRelated($currentSlug, array $tags, $limit = 3): array
    {
        return collect($this->all())
            ->filter(fn($blog) => $blog['slug'] !== $currentSlug)
            ->map(function ($blog) use ($tags) {
                $commonTags = array_intersect($blog['meta']['tags'], $tags);
                $blog['relevance_count'] = count($commonTags);
                return $blog;
            })
            ->sortByDesc([
                ['relevance_count', 'desc'],
                ['meta.raw_date', 'desc']
            ])
            ->take($limit)
            ->values()
            ->all();
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

        $currentItems = $items->slice(($currentPage - 1) * $perPage, $perPage)->all();

        return new LengthAwarePaginator(
            $currentItems,
            $items->count(),
            $perPage,
            $currentPage,
            ['path' => Request::url(), 'query' => Request::query()]
        );
    }

    public function getCategories(): array
    {
        $blogs = collect($this->all());
        $categories = [];

        foreach ($blogs as $blog) {
            $tags = (array) ($blog['meta']['tags'] ?? []);
            foreach ($tags as $tag) {
                $tag = trim($tag);
                if (!isset($categories[$tag])) {
                    $categories[$tag] = 0;
                }
                $categories[$tag]++;
            }
        }

        arsort($categories);

        return array_slice($categories, 0, 8, true);
    }

    public function all(): array
    {
        return Cache::remember('blog:all', 3600, function () {
            $files = File::allFiles(base_path('content'));

            return collect($files)->filter(fn(SplFileInfo $file) => $file->getExtension() === 'md')
                ->map(function (SplFileInfo $file) {

                    $path = $file->getPathname();
                    $data = app(MarkdownParser::class)->parse($path);

                    $filenameSlug = str_replace(['/', '.md'], ['_', ''], $file->getRelativePathname());
                    $pathParts = collect(explode(DIRECTORY_SEPARATOR, $file->getRelativePath()))->filter();
                    $date = Carbon::parse($data['meta']['pubDate'] ?? now());

                    return [
                        'slug' => $filenameSlug,
                        'meta' => array_merge($data['meta'], [
                            'tags' => (array) $data['meta']['tags'] ?? [],
                            'date' => $date->translatedFormat('d F Y'),
                            'raw_date' => $date->toDateString(),
                            'slug' => $data['meta']['slug'] ?? $filenameSlug,
                            'source' => $pathParts->implode(' :: ') ?: 'blog',
                        ])
                    ];
                })
                ->sortByDesc('meta.raw_date')
                ->values()
                ->all();
        });
    }
}
