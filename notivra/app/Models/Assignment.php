<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'title',
    'slug',
    'description',
    'instructions',
    'opens_at',
    'due_at',
    'published_at',
    'status',
    'created_by',
])]
class Assignment extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'opens_at' => 'datetime',
            'due_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function groups(): HasMany
    {
        return $this->hasMany(AssignmentGroup::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function quizQuestions(): HasMany
    {
        return $this->hasMany(AssignmentQuizQuestion::class)
            ->orderBy('position');
    }

    public function individualQuizSettings(): HasOne
    {
        return $this->hasOne(AssignmentIndividualQuizSetting::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(AssignmentParticipant::class);
    }

    public function getTypeAttribute(): string
    {
        if ($this->individualQuizSettings()->exists()) {
            return 'individual_quiz';
        }

        if ($this->groupWorkflowConfigured()) {
            return 'group_upload';
        }

        return 'unconfigured';
    }

    public function groupWorkflowConfigured(): bool
    {
        $hasGroups = $this->relationLoaded('groups')
            ? $this->groups->isNotEmpty()
            : $this->groups()->exists();

        if ($hasGroups) {
            return true;
        }

        return $this->relationLoaded('submissions')
            ? $this->submissions->isNotEmpty()
            : $this->submissions()->exists();
    }
}
