<?php

namespace Database\Seeders;

use App\Models\Applicant;
use App\Models\Category;
use App\Models\Period;
use App\Models\SubCriteria;
use Illuminate\Database\Seeder;

/**
 * 5 data pendaftar fiktif dari proposal (Bagian 2).
 * Penghasilan dalam juta Rp/bulan; nilai 0 tetap disimpan 0
 * (diganti 0,1 juta hanya saat perhitungan 1/x).
 */
class ApplicantSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Andi' => [
                'C1.1' => 4.0, 'C1.2' => 0,
                'C2.1' => 'Milik sendiri', 'C2.2' => 1300, 'C2.3' => 120, 'C2.4' => 90,
                'C2.5' => 'PDAM', 'C2.6' => 4,
                'C3.1' => 'Kota', 'C3.2' => 'Provinsi',
                'C4.1' => 'Karyawan swasta', 'C4.2' => 'Tidak bekerja / Buruh', // ibu rumah tangga
            ],
            'Budi' => [
                'C1.1' => 1.5, 'C1.2' => 0.5,
                'C2.1' => 'Menumpang', 'C2.2' => 450, 'C2.3' => 60, 'C2.4' => 36,
                'C2.5' => 'Sumur', 'C2.6' => 6,
                'C3.1' => 'Sekolah', 'C3.2' => 'Tidak ada',
                'C4.1' => 'Tidak bekerja / Buruh', 'C4.2' => 'Tidak bekerja / Buruh', // buruh harian, buruh cuci
            ],
            'Citra' => [
                'C1.1' => 2.0, 'C1.2' => 1.0,
                'C2.1' => 'Sewa/kontrak', 'C2.2' => 900, 'C2.3' => 72, 'C2.4' => 45,
                'C2.5' => 'Sumur', 'C2.6' => 5,
                'C3.1' => 'Nasional', 'C3.2' => 'Nasional',
                'C4.1' => 'Petani / Pedagang kecil', 'C4.2' => 'Petani / Pedagang kecil',
            ],
            'Dewi' => [
                'C1.1' => 1.0, 'C1.2' => 0,
                'C2.1' => 'Milik sendiri', 'C2.2' => 450, 'C2.3' => 100, 'C2.4' => 30,
                'C2.5' => 'Sungai/mata air', 'C2.6' => 7,
                'C3.1' => 'Provinsi', 'C3.2' => 'Kota',
                'C4.1' => 'Petani / Pedagang kecil', 'C4.2' => 'Tidak bekerja / Buruh',
            ],
            'Eko' => [
                'C1.1' => 6.0, 'C1.2' => 3.0,
                'C2.1' => 'Milik sendiri', 'C2.2' => 2200, 'C2.3' => 200, 'C2.4' => 150,
                'C2.5' => 'PDAM', 'C2.6' => 3,
                'C3.1' => 'Provinsi', 'C3.2' => 'Kota',
                'C4.1' => 'PNS', 'C4.2' => 'Karyawan swasta',
            ],
        ];

        $subs = SubCriteria::all()->keyBy('code');
        $period = Period::where('year', 2019)->firstOrFail();
        $no = 0;

        foreach ($data as $name => $values) {
            $applicant = Applicant::create([
                'period_id'       => $period->id,
                'registration_no' => sprintf('BM%d-%03d', $period->year, ++$no), // BM2019-001
                'name'            => $name,
            ]);

            foreach ($values as $code => $value) {
                $sub = $subs[$code];

                if ($sub->isQualitative()) {
                    $category = Category::where('sub_criteria_id', $sub->id)
                        ->where('name', $value)
                        ->firstOrFail();
                    $applicant->values()->create([
                        'sub_criteria_id' => $sub->id,
                        'category_id'     => $category->id,
                    ]);
                } else {
                    $applicant->values()->create([
                        'sub_criteria_id' => $sub->id,
                        'numeric_value'   => $value,
                    ]);
                }
            }
        }
    }
}
