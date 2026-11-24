<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Calculation extends Model
{
    protected $fillable = ['user_id', 'period_id', 'title', 'quota', 'applicant_count', 'weights', 'consistency'];

    protected $casts = [
        'weights'     => 'array',
        'consistency' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(CalculationResult::class)->orderBy('rank_no')->orderBy('id');
    }
}