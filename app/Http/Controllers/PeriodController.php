<?php

namespace App\Http\Controllers;

use App\Models\Period;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Periode seleksi beasiswa (mis. "Bidikmisi 2019").
 * Hanya satu periode yang aktif; pendaftar baru dan perhitungan selalu memakai periode aktif.
 */
class PeriodController extends Controller
{
    public function index()
    {
        $periods = Period::withCount(['applicants', 'calculations'])->orderByDesc('year')->get();

        return view('periods.index', compact('periods'));
    }

    public function create()
    {
        $period = new Period([
            'year'      => (Period::max('year') ?? (int) date('Y') - 1) + 1,
            'is_active' => ! Period::exists(), // periode pertama otomatis aktif
        ]);

        return view('periods.form', compact('period'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $period = DB::transaction(function () use ($data, $request) {
            $period = Period::create($data + ['is_active' => false]);

            // Aktifkan bila diminta, atau bila belum ada periode aktif sama sekali
            if ($request->boolean('is_active') || ! Period::active()) {
                $period->activate();
            }

            return $period;
        });

        return redirect()->route('periods.index')
            ->with('success', "Periode {$period->name} berhasil ditambahkan.");
    }

    public function edit(Period $period)
    {
        return view('periods.form', compact('period'));
    }

    public function update(Request $request, Period $period)
    {
        $data = $this->validated($request, $period->id);

        DB::transaction(function () use ($period, $data, $request) {
            $period->update($data);

            if ($request->boolean('is_active') && ! $period->is_active) {
                $period->activate();
            }
        });

        return redirect()->route('periods.index')
            ->with('success', "Periode {$period->name} berhasil diperbarui.");
    }

    public function activate(Period $period)
    {
        $period->activate();

        return back()->with('success', "Periode {$period->name} sekarang aktif.");
    }

    public function destroy(Period $period)
    {
        // Sama dengan aturan restrict di database, tetapi dengan pesan yang jelas
        if ($period->applicants()->exists() || $period->calculations()->exists()) {
            return back()->with('warning',
                "Periode {$period->name} tidak dapat dihapus karena masih memiliki pendaftar atau riwayat perhitungan.");
        }

        $period->delete();

        return redirect()->route('periods.index')
            ->with('success', "Periode {$period->name} berhasil dihapus.");
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'year'  => ['required', 'integer', 'between:2000,2100', Rule::unique('periods', 'year')->ignore($ignoreId)],
            'quota' => ['required', 'integer', 'min:1'],
        ], [], [
            'name'  => 'nama periode',
            'year'  => 'tahun',
            'quota' => 'kuota penerima',
        ]);
    }
}
