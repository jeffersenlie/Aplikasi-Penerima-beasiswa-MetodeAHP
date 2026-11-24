<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalculationResult extends Model
{
    protected $fillable = [
        'calculation_id', 'applicant_id', 'registration_no', 'applicant_name',
        'score', 'rank_no', 'eligible', 'details',
    ];

    protected $casts = [
        'score'    => 'float',
        'eligible' => 'boolean',
        'details'  => 'array',
    ];

    public function calculation(): BelongsTo
    {
        return $this->belongsTo(Calculation::class);
    }

    /** Null bila pendaftar sudah dihapus; gunakan registration_no/applicant_name (snapshot). */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }
}