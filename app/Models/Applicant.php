<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Applicant extends Model
{
    protected $fillable = ['name'];

    public function values(): HasMany
    {
        return $this->hasMany(ApplicantValue::class);
    }
}
