<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable('slug', 'icon', 'title', 'summary', 'description')]
class Service extends Model
{
    use HasUuids;

    protected $casts = [
        'title' => 'array',
        'summary' => 'array',
        'description' => 'array',
        'features' => 'array',
    ];

    /* public function hires(): HasMany */
    /* { */
    /*     return $this->hasMany(HireRequest::class); */
    /* } */
}
