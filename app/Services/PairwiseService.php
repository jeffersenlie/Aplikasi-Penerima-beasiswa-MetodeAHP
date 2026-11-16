<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Criteria;
use App\Models\PairwiseComparison;
use App\Models\SubCriteria;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Mengubah data tabel pairwise_comparisons menjadi daftar pertanyaan
 * "A vs B" (untuk form) dan sebaliknya menyimpan jawaban form.
 *
 * level 'criteria'     : antar kriteria            (parent = null)
 * level 'sub_criteria' : antar sub-kriteria         (parent = id kriteria)
 * level 'category'     : antar kategori kualitatif  (parent = id sub-kriteria)
 */
class PairwiseService
{
    public const LEVELS = ['criteria', 'sub_criteria', 'category'];

    /** Item yang dibandingkan, urut sesuai kolom `order`. */
    public function items(string $level, ?int $parentId): Collection
    {
        return match ($level) {
            'criteria' => Criteria::orderBy('order')->get()
                ->map(fn ($c) => ['id' => $c->id, 'label' => "{$c->code} {$c->name}"]),
            'sub_criteria' => SubCriteria::where('criteria_id', $parentId)->orderBy('order')->get()
                ->map(fn ($s) => ['id' => $s->id, 'label' => "{$s->code} {$s->name}"]),
            'category' => Category::where('sub_criteria_id', $parentId)->orderBy('order')->get()
                ->map(fn ($c) => ['id' => $c->id, 'label' => $c->name]),
        };
    }

    /** Judul grup, mis. "Sub-kriteria C2 Rumah tinggal keluarga". */
    public function title(string $level, ?int $parentId): string
    {
        return match ($level) {
            'criteria'     => 'Antar kriteria',
            'sub_criteria' => 'Sub-kriteria ' . optional(Criteria::find($parentId), fn ($c) => "{$c->code} {$c->name}"),
            'category'     => 'Kategori ' . optional(SubCriteria::find($parentId), fn ($s) => "{$s->code} {$s->name}"),
        };
    }

    /** Pertanyaan yang dipakai dalam penilaian: "mana yang lebih penting / lebih layak?" */
    public function question(string $level): string
    {
        return $level === 'category'
            ? 'Kategori mana yang lebih layak menerima beasiswa?'
            : 'Mana yang lebih penting dalam menentukan penerima beasiswa?';
    }

    /**
     * Daftar pertanyaan untuk n item: n(n-1)/2 pasangan.
     * Tiap pertanyaan: a, b, choice (a|equal|b), scale (1–9), filled.
     */
    public function questions(string $level, ?int $parentId): array
    {
        $items  = $this->items($level, $parentId)->values();
        $stored = $this->stored($level, $parentId);
        $result = [];

        for ($i = 0; $i < $items->count(); $i++) {
            for ($j = $i + 1; $j < $items->count(); $j++) {
                $a = $items[$i];
                $b = $items[$j];
                $value = $this->valueOf($stored, $a['id'], $b['id']); // nilai a dibanding b

                $result[] = [
                    'a'      => $a,
                    'b'      => $b,
                    'filled' => $value !== null,
                    'choice' => $value === null || abs($value - 1) < 1e-9 ? 'equal' : ($value > 1 ? 'a' : 'b'),
                    'scale'  => $value === null ? 1 : round($value >= 1 ? $value : 1 / $value, 4),
                ];
            }
        }

        return $result;
    }

    /** Simpan jawaban form. $answers[k] = [a, b, choice, scale]. */
    public function save(string $level, ?int $parentId, array $answers): void
    {
        DB::transaction(function () use ($level, $parentId, $answers) {
            foreach ($answers as $ans) {
                $a = (int) $ans['a'];
                $b = (int) $ans['b'];
                $value = match ($ans['choice']) {
                    'a'     => (float) $ans['scale'],
                    'b'     => 1 / (float) $ans['scale'],
                    default => 1.0,
                };

                // Simpan sebagai "a dibanding b"; hapus versi terbalik bila ada
                PairwiseComparison::where('level', $level)
                    ->where('item_a_id', $b)->where('item_b_id', $a)->delete();

                PairwiseComparison::updateOrCreate(
                    ['level' => $level, 'item_a_id' => $a, 'item_b_id' => $b],
                    ['parent_id' => $parentId, 'value' => round($value, 4)]
                );
            }
        });
    }

    /** Semua grup perbandingan yang ada (untuk halaman daftar). */
    public function groups(): array
    {
        $groups = [['level' => 'criteria', 'parent' => null]];

        foreach (Criteria::orderBy('order')->get() as $c) {
            $groups[] = ['level' => 'sub_criteria', 'parent' => $c->id];
        }
        foreach (SubCriteria::where('type', 'kualitatif')->orderBy('code')->get() as $s) {
            $groups[] = ['level' => 'category', 'parent' => $s->id];
        }

        return array_map(function ($g) {
            $questions = $this->questions($g['level'], $g['parent']);

            return $g + [
                'title'  => $this->title($g['level'], $g['parent']),
                'items'  => $this->items($g['level'], $g['parent'])->count(),
                'total'  => count($questions),
                'filled' => collect($questions)->where('filled', true)->count(),
                'cr'     => count($questions) > 0 ? $this->calculate($g['level'], $g['parent'])['cr'] : null,
            ];
        }, $groups);
    }

    /**
     * Hitung bobot satu grup dengan AHP.
     * Hasil: items, matrix (n×n), dan hasil AhpService (weights, λmax, CI, CR, ...).
     */
    public function calculate(string $level, ?int $parentId): array
    {
        $items  = $this->items($level, $parentId)->values();
        $stored = $this->stored($level, $parentId);
        $ahp    = app(AhpService::class);

        $matrix = $ahp->buildMatrix(
            $items->pluck('id')->all(),
            fn ($a, $b) => $this->valueOf($stored, $a, $b)
        );

        return ['items' => $items, 'matrix' => $matrix] + $ahp->calculate($matrix);
    }

    /**
     * Bobot seluruh hierarki:
     * bobot kriteria, bobot lokal sub-kriteria, dan bobot global (= kriteria × lokal).
     */
    public function hierarchy(): array
    {
        $top = $this->calculate('criteria', null);
        $criteriaWeights = array_combine($top['items']->pluck('id')->all(), $top['weights']) ?: [];
        $rows = [];

        foreach (Criteria::with('subCriteria')->orderBy('order')->get() as $c) {
            $sub = $this->calculate('sub_criteria', $c->id);
            $local = array_combine($sub['items']->pluck('id')->all(), $sub['weights']) ?: [];
            $cw = $criteriaWeights[$c->id] ?? 0;

            $rows[] = [
                'criteria' => $c,
                'weight'   => $cw,
                'cr'       => $sub['cr'],
                'subs'     => $c->subCriteria->map(fn ($s) => [
                    'sub'    => $s,
                    'local'  => $local[$s->id] ?? 0,
                    'global' => $cw * ($local[$s->id] ?? 0),
                ])->all(),
            ];
        }

        return ['top' => $top, 'rows' => $rows];
    }

    // ---------------------------------------------------------------

    private function stored(string $level, ?int $parentId): Collection
    {
        return PairwiseComparison::where('level', $level)
            ->where('parent_id', $parentId)
            ->get()
            ->keyBy(fn ($p) => "{$p->item_a_id}-{$p->item_b_id}");
    }

    /** Nilai "a dibanding b", membaca baris a-b atau kebalikannya b-a. */
    private function valueOf(Collection $stored, int $a, int $b): ?float
    {
        if ($row = $stored->get("$a-$b")) {
            return (float) $row->value;
        }
        if ($row = $stored->get("$b-$a")) {
            return 1 / (float) $row->value;
        }

        return null;
    }
}
