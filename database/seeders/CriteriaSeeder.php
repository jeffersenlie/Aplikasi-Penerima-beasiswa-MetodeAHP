<?php

namespace Database\Seeders;

use App\Models\Criteria;
use Illuminate\Database\Seeder;

/**
 * Kriteria, sub-kriteria, dan kategori sesuai proposal
 * (Iflakhah & Hafiyusholeh, 2021).
 */
class CriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $pekerjaan = ['Tidak bekerja / Buruh', 'Petani / Pedagang kecil', 'Karyawan swasta', 'PNS'];
        $prestasi  = ['Nasional', 'Provinsi', 'Kota', 'Sekolah', 'Tidak ada'];

        // [kode, nama, [sub-kriteria: kode, nama, tipe, arah, satuan, kategori(urut paling layak dulu)]]
        $data = [
            ['C1', 'Ekonomi', [
                ['C1.1', 'Penghasilan ayah', 'kuantitatif', 'cost', 'juta Rp/bulan', []],
                ['C1.2', 'Penghasilan ibu', 'kuantitatif', 'cost', 'juta Rp/bulan', []],
            ]],
            ['C2', 'Rumah tinggal keluarga', [
                ['C2.1', 'Kepemilikan rumah', 'kualitatif', null, null, ['Menumpang', 'Sewa/kontrak', 'Milik sendiri']],
                ['C2.2', 'Sumber listrik', 'kuantitatif', 'cost', 'VA', []],
                ['C2.3', 'Luas tanah', 'kuantitatif', 'cost', 'm²', []],
                ['C2.4', 'Luas bangunan', 'kuantitatif', 'cost', 'm²', []],
                ['C2.5', 'Sumber air', 'kualitatif', null, null, ['Sungai/mata air', 'Sumur', 'PDAM']],
                ['C2.6', 'Jumlah orang tinggal', 'kuantitatif', 'benefit', 'orang', []],
            ]],
            ['C3', 'Pendidikan', [
                ['C3.1', 'Prestasi akademik', 'kualitatif', null, null, $prestasi],
                ['C3.2', 'Prestasi non-akademik', 'kualitatif', null, null, $prestasi],
            ]],
            ['C4', 'Pekerjaan orang tua', [
                ['C4.1', 'Pekerjaan ayah', 'kualitatif', null, null, $pekerjaan],
                ['C4.2', 'Pekerjaan ibu', 'kualitatif', null, null, $pekerjaan],
            ]],
        ];

        foreach ($data as $i => [$code, $name, $subs]) {
            $criteria = Criteria::create(['code' => $code, 'name' => $name, 'order' => $i + 1]);

            foreach ($subs as $j => [$sCode, $sName, $type, $direction, $unit, $categories]) {
                $sub = $criteria->subCriteria()->create([
                    'code'      => $sCode,
                    'name'      => $sName,
                    'type'      => $type,
                    'direction' => $direction,
                    'unit'      => $unit,
                    'order'     => $j + 1,
                ]);

                foreach ($categories as $k => $catName) {
                    $sub->categories()->create(['name' => $catName, 'order' => $k + 1]);
                }
            }
        }
    }
}
