<?php

namespace Database\Seeders;

use App\Models\Criteria;
use App\Models\PairwiseComparison;
use App\Models\SubCriteria;
use Illuminate\Database\Seeder;

/**
 * Nilai perbandingan berpasangan awal.
 * - Kriteria & sub-kriteria: dari artikel (Tabel 4–8), segitiga atas matriks.
 * - Kategori: default kelompok (selisih 1 peringkat = 3, 2 = 5, 3 = 7, 4 = 9),
 *   karena artikel tidak memuatnya. Semua nilai bisa diubah lewat aplikasi.
 *
 * Catatan perbandingan dengan artikel (sudah diverifikasi):
 * - Artikel menghitung bobot dengan normalisasi kolom (rata-rata baris), sedangkan
 *   aplikasi memakai eigenvector (pengkuadratan matriks, sesuai materi kuliah).
 *   Karena itu bobot aplikasi sedikit berbeda, mis. Ekonomi 0,4405 vs 0,4389.
 *   Bila Tabel 4 dihitung dengan normalisasi kolom, hasilnya persis sama dengan artikel.
 * - Tabel 6 artikel tidak resiprokal pada 2 pasangan (kemungkinan salah ketik):
 *   Listrik–Jumlah orang 0,3813 (atas) vs 2,8845 (bawah, seharusnya 2,6226) dan
 *   Luas tanah–Jumlah orang 3,1748 (atas) vs 0,3969 (bawah, seharusnya 0,3150).
 *   Seeder memakai segitiga atas, jadi bobot Jumlah orang tinggal 0,0959 (artikel 0,1020).
 *   CR tetap 0,0112 < 0,1 (konsisten).
 */
class PairwiseSeeder extends Seeder
{
    public function run(): void
    {
        $c = Criteria::pluck('id', 'code');
        $s = SubCriteria::pluck('id', 'code');

        // Kriteria (Tabel 4). [A, B, nilai A dibanding B]
        $this->pairs('criteria', null, $c, [
            ['C4', 'C2', 0.3684],   // Pekerjaan vs Rumah
            ['C4', 'C1', 0.2370],   // Pekerjaan vs Ekonomi
            ['C4', 'C3', 0.5507],   // Pekerjaan vs Pendidikan
            ['C2', 'C1', 0.5000],   // Rumah vs Ekonomi
            ['C2', 'C3', 1.8171],   // Rumah vs Pendidikan
            ['C1', 'C3', 1.9574],   // Ekonomi vs Pendidikan
        ]);

        // Sub-kriteria Ekonomi (Tabel 7)
        $this->pairs('sub_criteria', $c['C1'], $s, [
            ['C1.1', 'C1.2', 3.1072],
        ]);

        // Sub-kriteria Rumah tinggal (Tabel 6)
        // Urutan artikel: kepemilikan, listrik, tanah, bangunan, air, jumlah orang
        $this->pairs('sub_criteria', $c['C2'], $s, [
            ['C2.1', 'C2.2', 3.7798],
            ['C2.1', 'C2.3', 0.5000],
            ['C2.1', 'C2.4', 0.3466],
            ['C2.1', 'C2.5', 2.8845],
            ['C2.1', 'C2.6', 2.0000],
            ['C2.2', 'C2.3', 0.1883],
            ['C2.2', 'C2.4', 0.1586],
            ['C2.2', 'C2.5', 0.5848],
            ['C2.2', 'C2.6', 0.3813],
            ['C2.3', 'C2.4', 0.5501],
            ['C2.3', 'C2.5', 3.9149],
            ['C2.3', 'C2.6', 3.1748],
            ['C2.4', 'C2.5', 4.9324],
            ['C2.4', 'C2.6', 3.6342],
            ['C2.5', 'C2.6', 0.6298],
        ]);

        // Sub-kriteria Pendidikan (Tabel 8)
        $this->pairs('sub_criteria', $c['C3'], $s, [
            ['C3.1', 'C3.2', 2.8845],
        ]);

        // Sub-kriteria Pekerjaan orang tua (Tabel 5)
        $this->pairs('sub_criteria', $c['C4'], $s, [
            ['C4.1', 'C4.2', 2.7589],
        ]);

        // Preferensi antar kategori (sub-kriteria kualitatif)
        $qualitative = SubCriteria::where('type', 'kualitatif')->with('categories')->get();
        foreach ($qualitative as $sub) {
            $cats = $sub->categories->values();
            for ($i = 0; $i < $cats->count(); $i++) {
                for ($j = $i + 1; $j < $cats->count(); $j++) {
                    PairwiseComparison::create([
                        'level'     => 'category',
                        'parent_id' => $sub->id,
                        'item_a_id' => $cats[$i]->id,
                        'item_b_id' => $cats[$j]->id,
                        // kategori ke-i lebih layak; makin jauh peringkatnya makin besar nilainya
                        'value'     => min(2 * ($j - $i) + 1, 9),
                    ]);
                }
            }
        }
    }

    private function pairs(string $level, ?int $parentId, $ids, array $rows): void
    {
        foreach ($rows as [$a, $b, $value]) {
            PairwiseComparison::create([
                'level'     => $level,
                'parent_id' => $parentId,
                'item_a_id' => $ids[$a],
                'item_b_id' => $ids[$b],
                'value'     => $value,
            ]);
        }
    }
}
