<?php

namespace App\Content;

use League\CommonMark\CommonMarkConverter;
use Symfony\Component\Yaml\Yaml;

class MarkdownParser
{
    public function parse(string $path): array
    {
        if (!file_exists($path)) {
            return ['meta' => [], 'html' => ''];
        }

        $raw = file_get_contents($path);

        /**
         * Penjelasan Regex:
         * /^---/m       : Mencari --- di awal baris (flag m = multiline)
         * (.*?)         : Menangkap konten meta secara non-greedy
         * /---/s        : Mencari --- penutup, dot mencakup newline (flag s = dotall)
         * (.*)$/        : Menangkap sisa konten sebagai body
         */
        $pattern = '/^---[\r\n]+(.*?)[\r\n]+---[\r\n]+(.*)/s';

        // Jika file tidak memiliki Front Matter (---), anggap semua adalah konten
        if (!preg_match($pattern, $raw, $matches)) {
            return [
                'meta' => [],
                'html' => app(CommonMarkConverter::class)->convert($raw)->getContent()
            ];
        }

        $meta = Yaml::parse(trim($matches[1]));
        $content = trim($matches[2]);

        $html = app(CommonMarkConverter::class)->convert($content)->getContent();

        return compact('meta', 'html');
    }
}
