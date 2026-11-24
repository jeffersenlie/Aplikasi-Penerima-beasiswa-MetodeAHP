<?php

namespace Database\Seeders;

use App\Models\Period;
use Illuminate\Database\Seeder;

/**
 * Periode awal. Artikel (Iflakhah & Hafiyusholeh, 2021) membahas seleksi
 * Bidikmisi mahasiswa Saintek UINSA angkatan 2019. Kuota 2 dipakai untuk
 * 5 data pendaftar contoh; nilainya bisa diubah lewat aplikasi.
 */
class PeriodSeeder extends Seeder
{
    public function run(): void
    {
        Period::create([
            'name'      => 'Bidikmisi 2019',
            'year'      => 2019,
            'quota'     => 2,
            'is_active' => true,
        ]);
    }
}
