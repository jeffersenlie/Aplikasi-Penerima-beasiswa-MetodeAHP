<?php

namespace App\Services;

/**
 * Perhitungan AHP sesuai materi DSS Week 8:
 * 1. Kuadratkan matriks, jumlahkan tiap baris, normalisasi -> bobot (eigenvector).
 * 2. Ulangi pengkuadratan sampai selisih bobot dengan iterasi sebelumnya < epsilon (0,0002).
 * 3. Konsistensi: λmax = rata-rata((A·w)_i / w_i), CI = (λmax − n)/(n − 1), CR = CI/RI.
 */
class AhpService
{
    public const EPSILON = 0.0002;

    public const RI = [1 => 0, 2 => 0, 3 => 0.58, 4 => 0.90, 5 => 1.12, 6 => 1.24,
                       7 => 1.32, 8 => 1.41, 9 => 1.45, 10 => 1.49];

    /**
     * @param  array<int, array<int, float>>  $A  matriks n×n (indeks 0..n-1)
     * @return array{weights: float[], iterations: int, history: array, lambda_max: float, ci: float, ri: float, cr: float, consistent: bool}
     */
    public function calculate(array $A, float $epsilon = self::EPSILON): array
    {
        $n = count($A);

        if ($n === 0) {
            return $this->result([], 0, [], 0, 0, 0, 0);
        }
        if ($n === 1) {
            return $this->result([1.0], 0, [], 1, 0, 0, 0);
        }

        $M = $A;
        $prev = null;
        $history = [];
        $iterations = 0;

        while (true) {
            $iterations++;
            $M = $this->multiply($M, $M);                 // kuadratkan

            $rowSums = array_map('array_sum', $M);        // jumlah tiap baris
            $total = array_sum($rowSums);
            $w = array_map(fn ($r) => $r / $total, $rowSums); // normalisasi

            $history[] = $w;

            if ($prev !== null && $this->maxDiff($w, $prev) < $epsilon) {
                break;
            }
            if ($iterations >= 50) {
                break;                                     // pengaman
            }

            $prev = $w;
            $max = max(array_map('max', $M));             // skala ulang agar angka tidak meledak
            $M = array_map(fn ($row) => array_map(fn ($v) => $v / $max, $row), $M);
        }

        // Uji konsistensi memakai matriks asli A
        $Aw = array_map(
            fn ($row) => array_sum(array_map(fn ($a, $wi) => $a * $wi, $row, $w)),
            $A
        );
        $lambda = array_sum(array_map(fn ($x, $wi) => $x / $wi, $Aw, $w)) / $n;
        $ri = self::RI[$n] ?? 1.49;
        $ci = ($lambda - $n) / ($n - 1);
        $cr = $ri > 0 ? $ci / $ri : 0;

        return $this->result($w, $iterations, $history, $lambda, $ci, $ri, $cr);
    }

    /** Bentuk matriks n×n dari nilai pasangan: $values["i-j"] = nilai item i dibanding item j. */
    public function buildMatrix(array $ids, callable $valueOf): array
    {
        $n = count($ids);
        $A = [];

        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                if ($i === $j) {
                    $A[$i][$j] = 1.0;
                } elseif ($j > $i) {
                    $A[$i][$j] = $valueOf($ids[$i], $ids[$j]) ?? 1.0;   // belum diisi = sama penting
                } else {
                    $A[$i][$j] = 1 / $A[$j][$i];
                }
            }
        }

        return $A;
    }

    private function multiply(array $X, array $Y): array
    {
        $n = count($X);
        $R = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $sum = 0;
                for ($k = 0; $k < $n; $k++) {
                    $sum += $X[$i][$k] * $Y[$k][$j];
                }
                $R[$i][$j] = $sum;
            }
        }

        return $R;
    }

    private function maxDiff(array $a, array $b): float
    {
        return max(array_map(fn ($x, $y) => abs($x - $y), $a, $b));
    }

    private function result(array $w, int $it, array $hist, float $lambda, float $ci, float $ri, float $cr): array
    {
        return [
            'weights'    => $w,
            'iterations' => $it,
            'history'    => $hist,
            'lambda_max' => $lambda,
            'ci'         => $ci,
            'ri'         => $ri,
            'cr'         => $cr,
            'consistent' => $cr < 0.1,
        ];
    }
}
