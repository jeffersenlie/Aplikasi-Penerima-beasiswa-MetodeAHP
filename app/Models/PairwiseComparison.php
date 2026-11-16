<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PairwiseComparison extends Model
{
    protected $fillable = ['level', 'parent_id', 'item_a_id', 'item_b_id', 'value'];

    protected $casts = ['value' => 'float'];

    public function scopeLevel($query, string $level, ?int $parentId = null)
    {
        return $query->where('level', $level)->where('parent_id', $parentId);
    }

    /** Hapus semua perbandingan yang melibatkan satu item (kriteria / sub-kriteria / kategori). */
    public static function purgeItem(string $level, int $id): void
    {
        static::where('level', $level)
            ->where(fn ($q) => $q->where('item_a_id', $id)->orWhere('item_b_id', $id))
            ->delete();
    }

    /** Hapus semua perbandingan anak dari induk tertentu. */
    public static function purgeChildren(string $level, array $parentIds): void
    {
        if (empty($parentIds)) {
            return;
        }

        static::where('level', $level)->whereIn('parent_id', $parentIds)->delete();
    }
}
