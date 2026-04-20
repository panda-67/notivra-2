<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'data', 'step', 'status'])]
class HireDraft extends Model
{
    use HasUuids;

    protected $casts = [
        'data' => 'array',
        'step' => 'integer',
    ];
}
