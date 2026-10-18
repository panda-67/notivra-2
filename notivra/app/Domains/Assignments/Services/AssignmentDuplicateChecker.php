<?php

namespace App\Domains\Assignments\Services;

use App\Models\AssignmentSubmission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AssignmentDuplicateChecker
{
    public function check(AssignmentSubmission $submission): AssignmentSubmission
    {
        return DB::transaction(function () use ($submission) {
            $current = AssignmentSubmission::query()
                ->lockForUpdate()
                ->findOrFail($submission->id);

            if (in_array($current->duplicate_status, [
                'under_review',
                'confirmed_duplicate',
            ], true)) {
                return $current;
            }

            $matches = AssignmentSubmission::query()
                ->where('assignment_id', $current->assignment_id)
                ->where('sha256', $current->sha256)
                ->where('id', '!=', $current->id)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($matches->isEmpty()) {
                $current->forceFill([
                    'duplicate_status' => 'clear',
                    'duplicate_findings' => $this->buildNoMatchFindings($current),
                ])->save();

                return $current->refresh();
            }

            $current->forceFill([
                'duplicate_status' => 'flagged',
                'duplicate_findings' => $this->buildMatchFindings(
                    $current,
                    $matches,
                ),
            ])->save();

            foreach ($matches as $match) {
                // Sinkronkan seluruh pasangan duplikat untuk submission ini.
                $peerMatches = AssignmentSubmission::query()
                    ->where('assignment_id', $match->assignment_id)
                    ->where('sha256', $match->sha256)
                    ->where('id', '!=', $match->id)
                    ->orderBy('id')
                    ->get();

                $match->forceFill([
                    'duplicate_status' => 'flagged',
                    'duplicate_findings' => $this->buildMatchFindings(
                        $match,
                        $peerMatches,
                    ),
                ])->save();
            }

            return $current->refresh();
        });
    }

    private function buildNoMatchFindings(
        AssignmentSubmission $submission,
    ): array {
        $existing = $this->findExistingFinding(
            $submission,
            'no_match',
            null,
        );

        $unrelatedFindings = $this->withoutExactSha256(
            $submission->duplicate_findings,
        );

        $unrelatedFindings[] = [
            'type' => 'exact_sha256',
            'result' => 'no_match',
            'checked_at' => $existing['checked_at']
                ?? now()->toIso8601String(),
        ];

        return $unrelatedFindings;
    }

    private function buildMatchFindings(
        AssignmentSubmission $submission,
        Collection $matches,
    ): array {
        $findings = $this->withoutExactSha256(
            $submission->duplicate_findings,
        );

        foreach ($matches as $match) {
            $existing = $this->findExistingFinding(
                $submission,
                'match',
                $match->id,
            );

            $findings[] = [
                'type' => 'exact_sha256',
                'result' => 'match',
                'matched_submission_id' => $match->id,
                'matched_group_id' => $match->assignment_group_id,
                'matched_filename' => $match->original_filename,
                'matched_sha256' => $match->sha256,
                'checked_at' => $existing['checked_at']
                    ?? now()->toIso8601String(),
            ];
        }

        return $findings;
    }

    private function findExistingFinding(
        AssignmentSubmission $submission,
        string $result,
        int|string|null $matchedSubmissionId,
    ): ?array {
        $findings = is_array($submission->duplicate_findings)
            ? $submission->duplicate_findings
            : [];

        foreach ($findings as $finding) {
            if (
                ($finding['type'] ?? null) !== 'exact_sha256'
                || ($finding['result'] ?? null) !== $result
            ) {
                continue;
            }

            if ($result === 'no_match') {
                return $finding;
            }

            if (
                (string) ($finding['matched_submission_id'] ?? '')
                === (string) $matchedSubmissionId
            ) {
                return $finding;
            }
        }

        return null;
    }

    private function withoutExactSha256(mixed $findings): array
    {
        if (! is_array($findings)) {
            return [];
        }

        return array_values(array_filter(
            $findings,
            fn($finding) => ! (
                is_array($finding)
                && ($finding['type'] ?? null) === 'exact_sha256'
            ),
        ));
    }
}
