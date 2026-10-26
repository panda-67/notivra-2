<?php

namespace App\Domains\Admin\Controllers\Assignments;

use App\Domains\Assignments\Services\AssignmentQuizResultService;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Domains\Admin\Exports\AssignmentQuizResultsExport;

class AssignmentQuizResultController
{
    public function index(
        Request $request,
        AssignmentQuizResultService $resultService
    ) {
        $filters = $request->validate([
            'assignment_id' => [
                'nullable',
                'integer',
                Rule::exists('assignments', 'id'),
            ],
            'unit' => ['nullable', 'integer', Rule::in([1, 2, 3])],
            'status' => [
                'nullable',
                Rule::in([
                    'pending',
                    'in_progress',
                    'completed',
                    'expired',
                    'invalid',
                ]),
            ],
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $rows = $resultService->rows($filters);

        $perPage = 25;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $paginatedRows = new LengthAwarePaginator(
            $rows->forPage($currentPage, $perPage)->values(),
            $rows->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return Inertia::render(
            'Admin/Assignments/QuizResults/Index',
            [
                'results' => $paginatedRows,
                'assignments' => Assignment::query()
                    ->orderBy('title')
                    ->get(['id', 'title']),
                'filters' => [
                    'assignment_id' => $filters['assignment_id'] ?? '',
                    'unit' => $filters['unit'] ?? '',
                    'status' => $filters['status'] ?? '',
                    'search' => $filters['search'] ?? '',
                ],
                'unmappedGroupNames' =>
                $resultService->unmappedGroupNames(
                    isset($filters['assignment_id'])
                        ? (int) $filters['assignment_id']
                        : null
                ),
            ]
        );
    }

    public function export(
        Request $request,
        AssignmentQuizResultService $resultService
    ) {
        $filters = $request->validate([
            'assignment_id' => [
                'nullable',
                'integer',
                Rule::exists('assignments', 'id'),
            ],
        ]);

        // Ekspor selalu memuat seluruh status kuis.
        // Filter unit/status/pencarian tidak diterapkan agar
        // ketiga sheet berisi seluruh data untuk tugas terpilih.
        $rows = $resultService->rows([
            'assignment_id' => $filters['assignment_id'] ?? null,
        ]);

        $filename = 'hasil-kuis-'
            . now()->format('Ymd-His')
            . '.xlsx';

        return Excel::download(
            new AssignmentQuizResultsExport($rows),
            $filename
        );
    }
}
