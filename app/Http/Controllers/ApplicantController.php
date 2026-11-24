<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Criteria;
use App\Models\Period;
use App\Models\SubCriteria;
use Illuminate\Http\Exceptions\HttpResponseException;
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

        // Hanya pendaftar pada periode aktif
        $period = Period::active();
        $applicants = $period
            ? $period->applicants()->with('values.category')->orderBy('registration_no')->get()
            : collect();

        return view('applicants.index', compact('subs', 'applicants', 'period'));
    }

    public function create()
    {
        return $this->form(new Applicant(['period_id' => $this->activePeriod()->id]));
    }

    public function store(Request $request)
    {
        $period = $this->activePeriod();

        $applicant = DB::transaction(function () use ($request, $period) {
            $data = $this->validated($request, $period->id);
            $applicant = Applicant::create([
                'period_id'       => $period->id,
                'registration_no' => $data['registration_no'],
                'name'            => $data['name'],
            ]);
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
            $data = $this->validated($request, $applicant->period_id, $applicant->id);
            $applicant->update([
                'registration_no' => $data['registration_no'],
                'name'            => $data['name'],
            ]);
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
    /** Pendaftar baru selalu masuk ke periode aktif. */
    private function activePeriod(): Period
    {
        $period = Period::active();

        if (! $period) {
            throw new HttpResponseException(
                redirect()->route('periods.index')
                    ->with('warning', 'Belum ada periode aktif. Buat atau aktifkan periode terlebih dahulu.')
            );
        }

        return $period;
    }

    private function validated(Request $request, int $periodId, ?int $ignoreId = null): array
    {
        $rules = [
            'registration_no' => [
                'required', 'string', 'max:30',
                Rule::unique('applicants', 'registration_no')->where('period_id', $periodId)->ignore($ignoreId),
            ],
            'name' => ['required', 'string', 'max:100'],
        ];
        $attributes = ['registration_no' => 'no. pendaftaran', 'name' => 'nama pendaftar'];

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
