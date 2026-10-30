<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'assignment_participant_id',
    'status',
    'answers',
    'question_ids',
    'resume_token_hash',
    'token_expires_at',
    'token_revoked_at',
    'device_token_hash',
    'last_ip_address',
    'last_user_agent',
    'started_at',
    'expires_at',
    'last_seen_at',
    'completed_at',
    'expired_at',
])]
class AssignmentQuizAttempt extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'question_ids' => 'array',
            'token_expires_at' => 'datetime',
            'token_revoked_at' => 'datetime',
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'completed_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(AssignmentParticipant::class, 'assignment_participant_id');
    }
}
