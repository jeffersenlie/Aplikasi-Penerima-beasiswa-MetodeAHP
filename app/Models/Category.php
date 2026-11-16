<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    protected $fillable = ['sub_criteria_id', 'name', 'order'];

    public function subCriteria(): BelongsTo
    {
        return $this->belongsTo(SubCriteria::class);
    }
}
