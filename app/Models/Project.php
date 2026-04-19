<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'email',
    'phone',
    'institution',
    'level',
    'field',
    'topic',
    'description',
    'output',
    'deadline',
    'address',
    'city',
    'postal_code',
    'willing_dp',
    'budget',
    'status',
    'files',
    'service_id',
    'user_id'
])]
class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory, HasUuids;
}
