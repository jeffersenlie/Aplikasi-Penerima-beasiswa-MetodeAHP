<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicantValue extends Model
{
    protected $fillable = ['applicant_id', 'sub_criteria_id', 'numeric_value', 'category_id'];

    protected $casts = ['numeric_value' => 'float'];

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function subCriteria(): BelongsTo
    {
        return $this->belongsTo(SubCriteria::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
