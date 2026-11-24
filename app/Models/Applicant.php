<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Applicant extends Model
{
    protected $fillable = ['period_id', 'registration_no', 'name'];

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(ApplicantValue::class);
    }
}
