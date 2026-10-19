<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'student_number',
    'name',
    'program_study',
    'institution',
    'status',
])]
class Student extends Model
{
    use HasFactory;

    public function groupMemberships(): HasMany
    {
        return $this->hasMany(AssignmentGroupMember::class);
    }
}
