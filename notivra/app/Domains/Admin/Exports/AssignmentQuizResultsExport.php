<?php

namespace App\Domains\Admin\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AssignmentQuizResultsExport implements Export, WithMultipleSheets
{
    public function __construct(
        private readonly Collection $rows,
    ) {}

    public function sheets(): array
    {
        return [
            new UnitQuizResultsSheet($this->rows, 1),
            new UnitQuizResultsSheet($this->rows, 2),
            new UnitQuizResultsSheet($this->rows, 3),
        ];
    }
}
