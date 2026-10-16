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
    'assignment_group_id',
    'student_id',
])]
class AssignmentGroupMember extends Model
{
    use HasFactory;

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

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function verification(): HasOne
    {
        return $this->hasOne(AssignmentVerification::class);
    }

    public function uploadedSubmissions(): HasMany
    {
        return $this->hasMany(
            AssignmentSubmission::class,
            'uploaded_by_member_id'
        );
    }
}
