<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'assignment_group_member_id',
    'status',
    'answers',
    'resume_token_hash',
    'token_expires_at',
    'token_revoked_at',
    'start_token_hash',
    'start_token_encrypted',
    'start_token_used_at',
    'attempt_count',
    'last_ip_address',
    'last_user_agent',
    'device_token_hash',
    'started_at',
    'quiz_expires_at',
    'last_seen_at',
    'completed_at',
])]
class AssignmentVerification extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'attempt_count' => 'integer',
            'token_expires_at' => 'datetime',
            'token_revoked_at' => 'datetime',
            'start_token_encrypted' => 'encrypted',
            'start_token_used_at' => 'datetime',
            'started_at' => 'datetime',
            'quiz_expires_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(
            AssignmentGroupMember::class,
            'assignment_group_member_id'
        );
    }
}
