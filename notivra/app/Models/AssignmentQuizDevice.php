<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'assignment_id',
    'student_id',
    'device_token_hash',
    'first_used_at',
    'last_used_at',
])]
class AssignmentQuizDevice extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'first_used_at' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
