<?php

namespace App\Domains\Admin\Controllers\Assignments;

use App\Domains\Admin\Exports\AssignmentIndividualQuizResultsExport;
use App\Domains\Assignments\Services\AssignmentIndividualQuizResultService;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class AssignmentIndividualQuizResultController
{
    public function index(
        Request $request,
        AssignmentIndividualQuizResultService $resultService
    ) {
        $filters = $request->validate([
            'assignment_id' => [
                'nullable',
                'integer',
                Rule::exists('assignments', 'id'),
            ],
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
            'Admin/Assignments/IndividualQuizResults/Index',
            [
                'results' => $paginatedRows,
                'assignments' => Assignment::query()
                    ->whereHas('individualQuizSettings')
                    ->orderBy('title')
                    ->get(['id', 'title']),
                'filters' => [
                    'assignment_id' => $filters['assignment_id'] ?? '',
                    'status' => $filters['status'] ?? '',
                    'search' => $filters['search'] ?? '',
                ],
            ]
        );
    }

    public function export(
        Request $request,
        AssignmentIndividualQuizResultService $resultService
    ) {
        $filters = $request->validate([
            'assignment_id' => [
                'nullable',
                'integer',
                Rule::exists('assignments', 'id'),
            ],
        ]);

        $rows = $resultService->rows([
            'assignment_id' => $filters['assignment_id'] ?? null,
        ]);

        return Excel::download(
            new AssignmentIndividualQuizResultsExport($rows),
            'hasil-kuis-individual-' . now()->format('Ymd-His') . '.xlsx'
        );
    }
}
