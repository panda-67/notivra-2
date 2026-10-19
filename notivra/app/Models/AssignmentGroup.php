<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'assignment_id',
    'group_name',
    'reference_code',
    'status',
    'submitted_at',
])]
class AssignmentGroup extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'upload_bypassed_at' => 'datetime',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(AssignmentGroupMember::class);
    }

    public function submission(): HasOne
    {
        return $this->hasOne(AssignmentSubmission::class);
    }

    public function uploadBypassedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'upload_bypassed_by');
    }
}
