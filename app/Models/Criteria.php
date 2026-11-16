<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Criteria extends Model
{
    protected $table = 'criteria';

    protected $fillable = ['code', 'name', 'order'];

    public function subCriteria(): HasMany
    {
        return $this->hasMany(SubCriteria::class)->orderBy('order');
    }
}
