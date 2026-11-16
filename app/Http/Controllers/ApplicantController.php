<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Criteria;
use App\Models\SubCriteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ApplicantController extends Controller
{
    public function index()
    {
        $subs = SubCriteria::with('criteria')
            ->join('criteria', 'criteria.id', '=', 'sub_criteria.criteria_id')
            ->orderBy('criteria.order')->orderBy('sub_criteria.order')
            ->select('sub_criteria.*')
            ->get();

        $applicants = Applicant::with('values.category')->orderBy('name')->get();

        return view('applicants.index', compact('subs', 'applicants'));
    }

    public function create()
    {
        return $this->form(new Applicant());
    }

    public function store(Request $request)
    {
        $applicant = DB::transaction(function () use ($request) {
            $data = $this->validated($request);
            $applicant = Applicant::create(['name' => $data['name']]);
            $this->saveValues($applicant, $data['values']);

            return $applicant;
        });

        return redirect()->route('applicants.index')
            ->with('success', "Pendaftar {$applicant->name} berhasil ditambahkan.");
    }

    public function edit(Applicant $applicant)
    {
        return $this->form($applicant->load('values'));
    }

    public function update(Request $request, Applicant $applicant)
    {
        DB::transaction(function () use ($request, $applicant) {
            $data = $this->validated($request);
            $applicant->update(['name' => $data['name']]);
            $this->saveValues($applicant, $data['values']);
        });

        return redirect()->route('applicants.index')
            ->with('success', "Data {$applicant->name} berhasil diperbarui.");
    }

    public function destroy(Applicant $applicant)
    {
        $applicant->delete(); // nilai ikut terhapus (cascade)

        return redirect()->route('applicants.index')
            ->with('success', "Pendaftar {$applicant->name} berhasil dihapus.");
    }

    // ---------------------------------------------------------------

    private function form(Applicant $applicant)
    {
        $criteriaList = Criteria::with('subCriteria.categories')->orderBy('order')->get();

        // nilai lama: [sub_criteria_id => numeric_value / category_id]
        $current = $applicant->exists
            ? $applicant->values->mapWithKeys(fn ($v) => [
                $v->sub_criteria_id => $v->category_id ?? $v->numeric_value,
            ])->all()
            : [];

        return view('applicants.form', compact('applicant', 'criteriaList', 'current'));
    }

    /** Aturan validasi dibentuk dari sub-kriteria yang ada di database (tidak hard-coded). */
    private function validated(Request $request): array
    {
        $rules = ['name' => ['required', 'string', 'max:100']];
        $attributes = ['name' => 'nama pendaftar'];

        foreach (SubCriteria::all() as $sub) {
            $key = "values.{$sub->id}";
            $attributes[$key] = $sub->name;

            $rules[$key] = $sub->isQualitative()
                ? ['required', 'integer', Rule::exists('categories', 'id')->where('sub_criteria_id', $sub->id)]
                : ['required', 'numeric', 'min:0'];
        }

        return $request->validate($rules, [], $attributes);
    }

    private function saveValues(Applicant $applicant, array $values): void
    {
        $subs = SubCriteria::whereIn('id', array_keys($values))->get()->keyBy('id');

        foreach ($values as $subId => $value) {
            $sub = $subs[$subId] ?? null;
            if (! $sub) {
                continue;
            }

            $applicant->values()->updateOrCreate(
                ['sub_criteria_id' => $sub->id],
                $sub->isQualitative()
                    ? ['category_id' => (int) $value, 'numeric_value' => null]
                    : ['numeric_value' => (float) $value, 'category_id' => null]
            );
        }
    }
}
