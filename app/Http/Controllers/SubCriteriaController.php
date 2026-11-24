<?php

namespace App\Http\Controllers;

use App\Models\Criteria;
use App\Models\PairwiseComparison;
use App\Models\SubCriteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SubCriteriaController extends Controller
{
    public function create(Criteria $criteria)
    {
        $sub = new SubCriteria(['type' => 'kuantitatif', 'direction' => 'benefit']);

        return view('sub_criteria.form', compact('criteria', 'sub'));
    }

    public function store(Request $request, Criteria $criteria)
    {
        $data = $this->validated($request);
        $data['order'] = ($criteria->subCriteria()->max('order') ?? 0) + 1;

        $sub = $criteria->subCriteria()->create($data);

        if ($sub->isQualitative()) {
            return redirect()->route('sub-criteria.edit', $sub)
                ->with('success', "Sub-kriteria {$sub->code} ditambahkan. Sekarang isi pilihan kategorinya.");
        }

        return redirect()->route('criteria.index')
            ->with('success', "Sub-kriteria {$sub->code} berhasil ditambahkan.");
    }

    public function edit(SubCriteria $subCriteria)
    {
        $subCriteria->load('categories');

        return view('sub_criteria.form', [
            'criteria' => $subCriteria->criteria,
            'sub'      => $subCriteria,
        ]);
    }

    public function update(Request $request, SubCriteria $subCriteria)
    {
        $data = $this->validated($request, $subCriteria->id);
        $typeChanged = $data['type'] !== $subCriteria->type;

        DB::transaction(function () use ($subCriteria, $data, $typeChanged) {
            if ($typeChanged) {
                // Nilai pendaftar lama tidak cocok dengan jenis baru (angka vs kategori) -> hapus,
                // pendaftar akan tampil "belum lengkap" dan perlu diisi ulang.
                $subCriteria->applicantValues()->delete();

                // Kuantitatif tidak memakai kategori: hapus kategori beserta perbandingannya
                if ($data['type'] === 'kuantitatif') {
                    PairwiseComparison::purgeChildren('category', [$subCriteria->id]);
                    $subCriteria->categories()->delete();
                }
            }

            $subCriteria->update($data);
        });

        $redirect = redirect()->route($subCriteria->isQualitative() && $typeChanged ? 'sub-criteria.edit' : 'criteria.index',
            $subCriteria->isQualitative() && $typeChanged ? $subCriteria : []);

        return $typeChanged
            ? $redirect->with('warning', "Jenis data {$subCriteria->code} diubah. Nilai pendaftar untuk sub-kriteria ini dihapus; "
                . 'isi ulang di menu Pendaftar' . ($subCriteria->isQualitative() ? ' setelah kategori ditambahkan.' : '.'))
            : $redirect->with('success', "Sub-kriteria {$subCriteria->code} berhasil diperbarui.");
    }

    public function destroy(SubCriteria $subCriteria)
    {
        DB::transaction(function () use ($subCriteria) {
            PairwiseComparison::purgeItem('sub_criteria', $subCriteria->id);
            PairwiseComparison::purgeChildren('category', [$subCriteria->id]);

            $subCriteria->delete();
        });

        return redirect()->route('criteria.index')
            ->with('success', "Sub-kriteria {$subCriteria->code} berhasil dihapus.");
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'code'      => ['required', 'string', 'max:10', Rule::unique('sub_criteria', 'code')->ignore($ignoreId)],
            'name'      => ['required', 'string', 'max:100'],
            'type'      => ['required', Rule::in(['kuantitatif', 'kualitatif'])],
            'direction' => ['required_if:type,kuantitatif', 'nullable', Rule::in(['benefit', 'cost'])],
            'unit'      => ['nullable', 'string', 'max:30'],
        ], [], [
            'code'      => 'kode',
            'name'      => 'nama sub-kriteria',
            'type'      => 'jenis data',
            'direction' => 'arah penilaian',
            'unit'      => 'satuan',
        ]);

        // Sub-kriteria kualitatif dinilai lewat pairwise antar-kategori, tidak memakai arah & satuan
        if ($data['type'] === 'kualitatif') {
            $data['direction'] = null;
            $data['unit'] = null;
        }

        return $data;
    }
}
