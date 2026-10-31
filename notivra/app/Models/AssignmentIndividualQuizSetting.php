<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'assignment_id',
    'duration_minutes',
])]
class AssignmentIndividualQuizSetting extends Model
{
    use HasFactory;

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }
}
