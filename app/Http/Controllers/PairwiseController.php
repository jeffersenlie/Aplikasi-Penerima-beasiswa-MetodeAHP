<?php

namespace App\Http\Controllers;

use App\Services\PairwiseService;
use Illuminate\Http\Request;

class PairwiseController extends Controller
{
    public function __construct(private PairwiseService $pairwise)
    {
    }

    public function index()
    {
        return view('pairwise.index', ['groups' => $this->pairwise->groups()]);
    }

    public function edit(string $level, ?int $parent = null)
    {
        $this->checkParent($level, $parent);

        return view('pairwise.edit', [
            'level'     => $level,
            'parent'    => $parent,
            'title'     => $this->pairwise->title($level, $parent),
            'question'  => $this->pairwise->question($level),
            'questions' => $this->pairwise->questions($level, $parent),
            'result'    => $this->pairwise->calculate($level, $parent),
        ]);
    }

    public function update(Request $request, string $level, ?int $parent = null)
    {
        $this->checkParent($level, $parent);

        $data = $request->validate([
            'pairs'          => ['required', 'array'],
            'pairs.*.a'      => ['required', 'integer'],
            'pairs.*.b'      => ['required', 'integer'],
            'pairs.*.choice' => ['required', 'in:a,equal,b'],
            'pairs.*.scale'  => ['required_unless:pairs.*.choice,equal', 'nullable', 'numeric', 'min:1', 'max:9'],
        ], [
            'pairs.*.scale.min' => 'Nilai skala minimal 1.',
            'pairs.*.scale.max' => 'Nilai skala maksimal 9.',
            'pairs.*.scale.required_unless' => 'Nilai skala wajib diisi bila salah satu lebih penting.',
        ]);

        $this->pairwise->save($level, $parent, $data['pairs']);

        $cr = $this->pairwise->calculate($level, $parent)['cr'];
        $redirect = redirect()->route('pairwise.edit', ['level' => $level, 'parent' => $parent]);

        // Validasi penuh (menolak CR ≥ 0,1) dibuat di langkah 7; sekarang cukup peringatan
        return $cr < 0.1
            ? $redirect->with('success', 'Tersimpan. Penilaian konsisten (CR = ' . number_format($cr, 4, ',', '.') . ').')
            : $redirect->with('warning', 'Tersimpan, tetapi penilaian TIDAK konsisten (CR = '
                . number_format($cr, 4, ',', '.') . ' ≥ 0,1). Periksa kembali jawaban Anda.');
    }

    private function checkParent(string $level, ?int $parent): void
    {
        abort_if($level !== 'criteria' && $parent === null, 404);
    }
}
