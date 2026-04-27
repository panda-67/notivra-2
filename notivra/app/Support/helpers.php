<?php

use Illuminate\Support\Str;

if (!function_exists('content_path')) {
    function content_path(string $path = ''): string
    {
        return base_path('content') . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}

if (!function_exists('blog_path')) {
    function blog_path(string $path = ''): string
    {
        return content_path('blog') . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}

if (!function_exists('blog_url')) {
    function blog_url(string $slug = ''): string
    {
        return url(($slug ? '/' . ltrim($slug, '/') : '') . '/insight');
    }
}

if (!function_exists('read_markdown_file')) {
    function read_markdown_file(string $path): ?string
    {
        if (!file_exists($path)) {
            return null;
        }

        return file_get_contents($path);
    }
}

if (!function_exists('extract_frontmatter')) {
    function extract_frontmatter(string $content): array
    {
        preg_match('/^-{3}(.*?)^-{3}(.*)$/s', $content, $matches);

        return [
            'frontmatter' => trim($matches[1] ?? ''),
            'body' => trim($matches[2] ?? $content),
        ];
    }
}

if (!function_exists('slug_from_filename')) {
    function slug_from_filename(string $filename): string
    {
        return Str::of(pathinfo($filename, PATHINFO_FILENAME))->slug('-');
    }
}

if (!function_exists('is_markdown_file')) {
    function is_markdown_file(string $file): bool
    {
        return str_ends_with($file, '.md');
    }
}

if (!function_exists('blog_files')) {
    function blog_files(): array
    {
        $path = blog_path();

        if (!is_dir($path)) {
            return [];
        }

        return array_values(array_filter(
            glob($path . DIRECTORY_SEPARATOR . '*.md'),
            fn($file) => is_markdown_file($file)
        ));
    }
}
