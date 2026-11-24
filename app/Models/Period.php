<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Period extends Model
{
    protected $fillable = ['name', 'year', 'quota', 'is_active'];

    protected $casts = [
        'year'      => 'integer',
        'quota'     => 'integer',
        'is_active' => 'boolean',
    ];

    public function applicants(): HasMany
    {
        return $this->hasMany(Applicant::class);
    }

    public function calculations(): HasMany
    {
        return $this->hasMany(Calculation::class);
    }

    /** Periode yang sedang dikelola (null bila belum ada). */
    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /** Jadikan periode ini satu-satunya periode aktif. */
    public function activate(): void
    {
        DB::transaction(function () {
            static::where('id', '!=', $this->id)->update(['is_active' => false]);
            $this->update(['is_active' => true]);
        });
    }
}
