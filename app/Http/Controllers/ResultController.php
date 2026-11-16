<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Services\ResultService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResultController extends Controller
{
    public const DEFAULT_QUOTA = 2;

    public function index(Request $request, ResultService $results)
    {
        $quota = (int) $request->query('quota', self::DEFAULT_QUOTA);

        return view('results.index', ['result' => $results->compute($quota)]);
    }

    /** Simpan hasil perhitungan saat ini sebagai riwayat milik user yang login. */
    public function store(Request $request, ResultService $results)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'quota' => ['required', 'integer', 'min:1'],
        ]);

        $result = $results->compute((int) $data['quota']);

        if ($result['blocked'] || empty($result['ranking'])) {
            return back()->withErrors(['title' => 'Hasil tidak dapat disimpan: matriks tidak konsisten atau belum ada pendaftar.']);
        }

        $calculation = DB::transaction(function () use ($request, $data, $result) {
            $calculation = Calculation::create([
                'user_id'         => $request->user()->id,
                'title'           => $data['title'],
                'quota'           => $result['quota'],
                'applicant_count' => count($result['ranking']),
                'weights'         => collect($result['subs'])->map(fn ($r) => [
                    'code'   => $r['sub']->code,
                    'name'   => $r['sub']->name,
                    'global' => round($r['global'], 6),
                ])->all(),
            ]);

            foreach ($result['ranking'] as $row) {
                $id = $row['applicant']->id;

                $calculation->results()->create([
                    'applicant_name' => $row['applicant']->name,
                    'score'          => $row['score'],
                    'rank_no'        => $row['rank'],
                    'eligible'       => $row['eligible'],
                    'details'        => collect($result['subs'])->map(fn ($r) => [
                        'code'         => $r['sub']->code,
                        'name'         => $r['sub']->name,
                        'global'       => round($r['global'], 6),
                        'local'        => round($r['local'][$id] ?? 0, 6),
                        'contribution' => round($r['global'] * ($r['local'][$id] ?? 0), 6),
                    ])->all(),
                ]);
            }

            return $calculation;
        });

        return redirect()->route('history.show', $calculation)
            ->with('success', 'Hasil perhitungan tersimpan di riwayat.');
    }
}