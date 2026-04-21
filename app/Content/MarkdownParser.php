<?php

namespace App\Content;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;
use Symfony\Component\Yaml\Yaml;

class MarkdownParser
{
    public function parse(string $path): array
    {
        if (!file_exists($path)) {
            return ['meta' => [], 'html' => ''];
        }

        $raw = file_get_contents($path);

        $config = [];
        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());

        // 2. Tambahkan TableExtension di sini
        $environment->addExtension(new TableExtension());

        $converter = new MarkdownConverter($environment);

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
                'html' => $converter->convert($raw)->getContent()
            ];
        }

        $meta = Yaml::parse(trim($matches[1]));
        $content = trim($matches[2]);

        $html = $converter->convert($content)->getContent();

        return compact('meta', 'html');
    }
}
