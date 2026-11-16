<?php

namespace App\Http\Controllers;

use App\Models\Criteria;
use App\Models\PairwiseComparison;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CriteriaController extends Controller
{
    public function index()
    {
        $criteriaList = Criteria::with('subCriteria.categories')->orderBy('order')->get();

        return view('criteria.index', compact('criteriaList'));
    }

    public function create()
    {
        return view('criteria.form', ['criteria' => new Criteria()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['order'] = (Criteria::max('order') ?? 0) + 1;

        Criteria::create($data);

        return redirect()->route('criteria.index')
            ->with('success', "Kriteria {$data['code']} berhasil ditambahkan.");
    }

    public function edit(Criteria $criteria)
    {
        return view('criteria.form', compact('criteria'));
    }

    public function update(Request $request, Criteria $criteria)
    {
        $criteria->update($this->validated($request, $criteria->id));

        return redirect()->route('criteria.index')
            ->with('success', "Kriteria {$criteria->code} berhasil diperbarui.");
    }

    public function destroy(Criteria $criteria)
    {
        DB::transaction(function () use ($criteria) {
            $subIds = $criteria->subCriteria()->pluck('id')->all();

            // Bersihkan perbandingan berpasangan yang terkait
            PairwiseComparison::purgeItem('criteria', $criteria->id);
            PairwiseComparison::purgeChildren('sub_criteria', [$criteria->id]);
            PairwiseComparison::purgeChildren('category', $subIds);

            // Sub-kriteria, kategori, dan nilai pendaftar ikut terhapus (cascade)
            $criteria->delete();
        });

        return redirect()->route('criteria.index')
            ->with('success', "Kriteria {$criteria->code} beserta sub-kriterianya berhasil dihapus.");
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:10', Rule::unique('criteria', 'code')->ignore($ignoreId)],
            'name' => ['required', 'string', 'max:100'],
        ], [], [
            'code' => 'kode',
            'name' => 'nama kriteria',
        ]);
    }
}
