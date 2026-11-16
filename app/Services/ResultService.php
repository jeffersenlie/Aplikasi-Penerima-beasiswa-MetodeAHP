<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\SubCriteria;
use Illuminate\Support\Collection;

class ResultService
{
    public const CR_LIMIT = 0.1;
    public const ZERO_SUBSTITUTE = 0.1;
    public const TIE_TOLERANCE = 1e-9;

    public function __construct(
        private PairwiseService $pairwise,
        private AhpService $ahp,
    ) {
    }

    public function compute(int $quota = 2): array
    {
        $groups = collect($this->pairwise->groups());

        $inconsistent = $groups
            ->filter(fn ($g) => $g['cr'] !== null && $g['cr'] >= self::CR_LIMIT)
            ->values();
        $incomplete = $groups->filter(fn ($g) => $g['filled'] < $g['total'])->values();

        $applicants = Applicant::with('values')->orderBy('id')->get();
        $quota = max(1, min($quota, max($applicants->count(), 1)));

        $result = [
            'quota'        => $quota,
            'blocked'      => $inconsistent->isNotEmpty(),
            'inconsistent' => $inconsistent,
            'incomplete'   => $incomplete,
            'applicants'   => $applicants,
            'subs'         => [],
            'ranking'      => [],
        ];

        if ($result['blocked'] || $applicants->isEmpty()) {
            return $result;
        }

        // Prioritas lokal tiap sub-kriteria + bobot global
        $rows = [];
        foreach ($this->pairwise->hierarchy()['rows'] as $row) {
            foreach ($row['subs'] as $item) {
                $rows[] = [
                    'sub'    => $item['sub'],
                    'global' => $item['global'],
                    'local'  => $this->localPriorities($item['sub'], $applicants),
                ];
            }
        }

        // Skor akhir tiap pendaftar
        $scored = $applicants->map(function ($a) use ($rows) {
            $parts = [];
            $score = 0.0;

            foreach ($rows as $r) {
                $local = $r['local'][$a->id] ?? 0.0;
                $contribution = $r['global'] * $local;
                $score += $contribution;
                $parts[] = ['sub' => $r['sub'], 'local' => $local, 'contribution' => $contribution];
            }

            return [
                'applicant' => $a,
                'score'     => $score,
                'top'       => collect($parts)->sortByDesc('contribution')->take(2)->values()->all(),
            ];
        })->sortByDesc('score')->values();

        // Ranking (skor sama -> peringkat sama) dan status sesuai kuota
        $ranking = [];
        foreach ($scored as $i => $item) {
            $tied = $i > 0 && abs($item['score'] - $scored[$i - 1]['score']) < self::TIE_TOLERANCE;
            $rank = $tied ? $ranking[$i - 1]['rank'] : $i + 1;

            $ranking[] = $item + ['rank' => $rank, 'eligible' => $rank <= $quota];
        }

        $result['subs'] = $rows;
        $result['ranking'] = $ranking;

        return $result;
    }

    // ---------------------------------------------------------------

    /** @return array<int, float> [applicant_id => prioritas lokal], total = 1 */
    private function localPriorities(SubCriteria $sub, Collection $applicants): array
    {
        return $sub->isQualitative()
            ? $this->qualitative($sub, $applicants)
            : $this->quantitative($sub, $applicants);
    }

    private function quantitative(SubCriteria $sub, Collection $applicants): array
    {
        $raw = [];

        foreach ($applicants as $a) {
            $x = (float) ($a->values->firstWhere('sub_criteria_id', $sub->id)?->numeric_value ?? 0);

            $raw[$a->id] = $sub->direction === 'cost'
                ? 1 / ($x > 0 ? $x : self::ZERO_SUBSTITUTE)
                : max($x, 0.0);
        }

        return $this->normalize($raw);
    }

    private function qualitative(SubCriteria $sub, Collection $applicants): array
    {
        $cat = $this->pairwise->calculate('category', $sub->id);
        $position = array_flip($cat['items']->pluck('id')->all());   // category_id => indeks matriks
        $count = count($position);

        if ($count === 0) {
            return $this->normalize(array_fill_keys($applicants->pluck('id')->all(), 1.0));
        }

        // Posisi kategori tiap pendaftar; bila kosong dianggap kategori paling tidak layak (terakhir)
        $pos = [];
        foreach ($applicants as $a) {
            $categoryId = $a->values->firstWhere('sub_criteria_id', $sub->id)?->category_id;
            $pos[$a->id] = $categoryId !== null ? ($position[$categoryId] ?? $count - 1) : $count - 1;
        }

        // Matriks antar pendaftar dari matriks kategori
        $ids = array_keys($pos);
        $A = [];
        foreach ($ids as $i => $idI) {
            foreach ($ids as $j => $idJ) {
                $A[$i][$j] = $cat['matrix'][$pos[$idI]][$pos[$idJ]];
            }
        }

        return array_combine($ids, $this->ahp->calculate($A)['weights']);
    }

    private function normalize(array $raw): array
    {
        $total = array_sum($raw);

        if ($total <= 0) {
            $equal = 1 / max(count($raw), 1);

            return array_map(fn () => $equal, $raw);
        }

        return array_map(fn ($v) => $v / $total, $raw);
    }
}