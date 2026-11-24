<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubCriteria extends Model
{
    protected $table = 'sub_criteria';

    protected $fillable = ['criteria_id', 'code', 'name', 'type', 'direction', 'unit', 'order'];

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(Criteria::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class)->orderBy('order');
    }

    /** Nilai semua pendaftar pada sub-kriteria ini. */
    public function applicantValues(): HasMany
    {
        return $this->hasMany(ApplicantValue::class);
    }

    public function isQualitative(): bool
    {
        return $this->type === 'kualitatif';
    }
}
