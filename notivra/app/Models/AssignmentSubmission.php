<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'assignment_id',
    'assignment_group_id',
    'uploaded_by_member_id',
    'file_path',
    'original_filename',
    'mime_type',
    'file_size',
    'sha256',
    'content_fingerprint',
    'structure_fingerprint',
    'metadata_fingerprint',
    'duplicate_status',
    'metadata',
    'duplicate_findings',
    'submitted_at',
])]
class AssignmentSubmission extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'metadata' => 'array',
            'duplicate_findings' => 'array',
            'submitted_at' => 'datetime',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(
            AssignmentGroup::class,
            'assignment_group_id'
        );
    }

    public function uploadedByMember(): BelongsTo
    {
        return $this->belongsTo(
            AssignmentGroupMember::class,
            'uploaded_by_member_id'
        );
    }
}
