<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\Calculation;
use App\Models\Category;
use App\Models\Period;
use App\Models\SubCriteria;
use App\Models\User;
use App\Services\PairwiseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Alur utama DSS Beasiswa (AHP) memakai data seeder (artikel Iflakhah & Hafiyusholeh, 2021).
 */
class DssFlowTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function admin(): User
    {
        return User::where('email', 'admin@dss.test')->firstOrFail();
    }

    /** Isi nilai semua sub-kriteria: kategori pertama untuk kualitatif, angka untuk kuantitatif. */
    private function values(float $number = 1): array
    {
        return SubCriteria::with('categories')->get()->mapWithKeys(fn ($s) => [
            $s->id => $s->isQualitative() ? $s->categories->first()->id : $number,
        ])->all();
    }

    public function test_all_main_pages_open(): void
    {
        $this->actingAs($this->admin());

        foreach (['/criteria', '/pairwise', '/weights', '/periods', '/periods/create',
                  '/applicants', '/applicants/create', '/results', '/history'] as $uri) {
            $this->get($uri)->assertOk();
        }
    }

    public function test_criteria_weights_and_consistency_match_article(): void
    {
        $top = app(PairwiseService::class)->calculate('criteria', null);

        // λmax dan CI pada artikel: 4,0323 dan 0,0108 (Tabel 4)
        $this->assertEqualsWithDelta(4.0323, $top['lambda_max'], 0.0005);
        $this->assertEqualsWithDelta(0.0108, $top['ci'], 0.0005);
        $this->assertLessThan(0.1, $top['cr']);

        foreach (app(PairwiseService::class)->groups() as $group) {
            $this->assertLessThan(0.1, $group['cr'] ?? 0, "CR {$group['title']} harus < 0,1");
        }
    }

    public function test_ranking_of_seeded_applicants(): void
    {
        $this->actingAs($this->admin())
            ->get('/results')
            ->assertOk()
            ->assertSeeInOrder(['Dewi', 'Citra', 'Budi', 'Andi', 'Eko']);
    }

    public function test_period_rules(): void
    {
        $this->actingAs($this->admin());

        // tahun dobel ditolak dengan pesan Bahasa Indonesia
        $this->post('/periods', ['name' => 'X', 'year' => 2019, 'quota' => 1])
            ->assertSessionHasErrors(['year' => 'Tahun sudah digunakan.']);

        // periode baru yang diaktifkan menonaktifkan periode lama
        $this->post('/periods', ['name' => 'Bidikmisi 2020', 'year' => 2020, 'quota' => 3, 'is_active' => 1])
            ->assertRedirect('/periods');
        $this->assertSame('Bidikmisi 2020', Period::active()->name);
        $this->assertSame(1, Period::where('is_active', true)->count());

        // periode yang masih punya pendaftar tidak bisa dihapus
        $p2019 = Period::where('year', 2019)->first();
        $this->delete("/periods/{$p2019->id}")->assertSessionHas('warning');
        $this->assertModelExists($p2019);

        // periode kosong bisa dihapus
        $p2020 = Period::where('year', 2020)->first();
        $p2019->activate();
        $this->delete("/periods/{$p2020->id}")->assertRedirect('/periods');
        $this->assertModelMissing($p2020);
    }

    public function test_applicant_crud_and_registration_number_unique_per_period(): void
    {
        $this->actingAs($this->admin());

        // no. pendaftaran dobel di periode yang sama ditolak
        $this->post('/applicants', ['registration_no' => 'BM2019-001', 'name' => 'Dobel', 'values' => $this->values()])
            ->assertSessionHasErrors('registration_no');

        // nilai kosong ditolak
        $this->post('/applicants', ['registration_no' => 'BM2019-099', 'name' => 'Kosong', 'values' => []])
            ->assertSessionHasErrors();

        // tambah pendaftar baru (alternatif dinamis)
        $this->post('/applicants', ['registration_no' => 'BM2019-006', 'name' => 'Fajar', 'values' => $this->values(2)])
            ->assertRedirect('/applicants');
        $fajar = Applicant::where('name', 'Fajar')->firstOrFail();
        $this->assertSame(Period::active()->id, $fajar->period_id);
        $this->assertSame(SubCriteria::count(), $fajar->values()->count());

        // ubah
        $this->put("/applicants/{$fajar->id}", ['registration_no' => 'BM2019-006', 'name' => 'Fajar R', 'values' => $this->values(3)])
            ->assertRedirect('/applicants');
        $this->assertSame('Fajar R', $fajar->fresh()->name);

        // hapus (nilai ikut terhapus)
        $this->delete("/applicants/{$fajar->id}")->assertRedirect('/applicants');
        $this->assertModelMissing($fajar);
        $this->assertDatabaseMissing('applicant_values', ['applicant_id' => $fajar->id]);

        // no. pendaftaran yang sama boleh dipakai di periode lain
        Period::create(['name' => 'Bidikmisi 2020', 'year' => 2020, 'quota' => 2])->activate();
        $this->post('/applicants', ['registration_no' => 'BM2019-001', 'name' => 'Gina', 'values' => $this->values()])
            ->assertRedirect('/applicants');
    }

    public function test_save_result_to_history_keeps_snapshot(): void
    {
        $this->actingAs($this->admin());

        $this->post('/results', ['title' => 'Seleksi 1', 'quota' => 2])->assertRedirect();
        $calc = Calculation::with('results')->firstOrFail();

        $this->assertSame(Period::active()->id, $calc->period_id);
        $this->assertCount(5, $calc->results);
        $this->assertNotEmpty($calc->consistency);
        $this->assertSame(2, $calc->results->where('eligible', true)->count());

        // pendaftar dihapus -> riwayat tetap utuh, applicant_id jadi null
        $first = $calc->results->first();
        Applicant::find($first->applicant_id)->delete();
        $first->refresh();
        $this->assertNull($first->applicant_id);
        $this->assertSame('Dewi', $first->applicant_name);

        $this->get("/history/{$calc->id}")->assertOk()->assertSee('Bidikmisi 2019');
    }

    public function test_duplicate_category_is_rejected(): void
    {
        $this->actingAs($this->admin());
        $sub = SubCriteria::where('code', 'C2.1')->firstOrFail();

        $this->post("/sub-criteria/{$sub->id}/categories", ['name' => 'Menumpang'])
            ->assertSessionHasErrors(['name' => 'Nama kategori sudah digunakan.']);
        $this->assertSame(3, Category::where('sub_criteria_id', $sub->id)->count());
    }

    public function test_results_warn_when_applicant_values_incomplete(): void
    {
        $this->actingAs($this->admin());
        $this->get('/results')->assertDontSee('belum lengkap nilainya');

        // sub-kriteria baru -> semua pendaftar lama belum punya nilainya
        $c5 = \App\Models\Criteria::create(['code' => 'C5', 'name' => 'Tanggungan', 'order' => 5]);
        $c5->subCriteria()->create(['code' => 'C5.1', 'name' => 'Jumlah tanggungan', 'type' => 'kuantitatif', 'direction' => 'benefit']);
        $this->get('/results')->assertOk()->assertSee('5 pendaftar belum lengkap nilainya');
    }

    public function test_changing_sub_criteria_type_cleans_old_data(): void
    {
        $this->actingAs($this->admin());
        $sub = SubCriteria::where('code', 'C2.1')->firstOrFail();   // kualitatif, 3 kategori

        $this->put("/sub-criteria/{$sub->id}", [
            'code' => 'C2.1', 'name' => 'Kepemilikan rumah', 'type' => 'kuantitatif', 'direction' => 'cost', 'unit' => 'skor',
        ])->assertSessionHas('warning');

        $this->assertSame(0, Category::where('sub_criteria_id', $sub->id)->count());
        $this->assertDatabaseMissing('applicant_values', ['sub_criteria_id' => $sub->id]);
        $this->assertDatabaseMissing('pairwise_comparisons', ['level' => 'category', 'parent_id' => $sub->id]);
        $this->get('/results')->assertSee('5 pendaftar belum lengkap nilainya');
    }

    public function test_pairwise_rejects_items_from_other_group(): void
    {
        $this->actingAs($this->admin());
        $c1 = SubCriteria::where('code', 'C1.1')->first();
        $c2 = SubCriteria::where('code', 'C2.1')->first();   // milik kriteria lain

        $this->put("/pairwise/sub_criteria/{$c1->criteria_id}", ['pairs' => [
            ['a' => $c1->id, 'b' => $c2->id, 'choice' => 'a', 'scale' => 3],
        ]])->assertStatus(422);

        $this->get('/pairwise/category/9999')->assertNotFound();
        $this->get("/pairwise/category/{$c1->id}")->assertNotFound();   // C1.1 kuantitatif
    }

    public function test_history_is_private_per_user(): void
    {
        $this->actingAs($this->admin())->post('/results', ['title' => 'Milik admin', 'quota' => 2]);
        $calc = Calculation::firstOrFail();

        $other = User::factory()->create();
        $this->actingAs($other)->get("/history/{$calc->id}")->assertForbidden();
    }
}
