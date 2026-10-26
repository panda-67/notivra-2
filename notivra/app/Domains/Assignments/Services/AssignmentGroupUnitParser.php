<?php

namespace App\Domains\Assignments\Services;

class AssignmentGroupUnitParser
{
    /**
     * Mengambil nomor unit dari nama kelompok.
     *
     * Contoh:
     * "Kelompok 1 Unit 1"  => 1
     * "kelompok 02 unit 02" => 2
     * "Kelompok 3 Unit 3"  => 3
     *
     * Mengembalikan null jika nama tidak cocok
     * atau unit berada di luar rentang 1-3.
     */
    public function parse(?string $groupName): ?int
    {
        if (! is_string($groupName) || trim($groupName) === '') {
            return null;
        }

        $matched = preg_match(
            '/\bkelompok\s*\d+\s+unit\s*0*([1-3])\b/i',
            trim($groupName),
            $matches
        );

        if ($matched !== 1) {
            return null;
        }

        return (int) $matches[1];
    }

    public function label(?string $groupName): string
    {
        $unit = $this->parse($groupName);

        return $unit === null
            ? 'Tidak terpetakan'
            : "Unit {$unit}";
    }
}
