# DSS Penerima Beasiswa (AHP) — Langkah 1: Setup

## 1. Buat project Laravel (di laptop masing-masing / satu orang PIC)
```bash
composer create-project laravel/laravel dss-beasiswa
cd dss-beasiswa
```

## 2. Salin file dari zip ini ke project
| Dari zip | Ke project |
|---|---|
| `app/Models/*.php` | `app/Models/` |
| `database/migrations/*.php` | `database/migrations/` |
| `database/seeders/*.php` | `database/seeders/` (timpa `DatabaseSeeder.php`) |

## 3. Atur database di `.env`
**Pakai MySQL (XAMPP/Laragon):** buat database `dss_beasiswa` di phpMyAdmin, lalu:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dss_beasiswa
DB_USERNAME=root
DB_PASSWORD=
```
**Atau SQLite (tanpa install apa-apa):** biarkan `DB_CONNECTION=sqlite` (default Laravel 11/12).

## 4. Jalankan migration + seeder
```bash
php artisan migrate:fresh --seed
php artisan serve
```
`migrate:fresh --seed` aman diulang kapan saja untuk kembali ke data awal.

## 5. Git repo
```bash
git init
git add .
git commit -m "Setup: migration, model, seeder data artikel"
git branch -M main
git remote add origin https://github.com/<akun>/dss-beasiswa.git
git push -u origin main
```
Lalu undang 5 anggota lain sebagai collaborator di GitHub (Settings → Collaborators).
Anggota lain cukup: `git clone …`, `composer install`, `cp .env.example .env`,
`php artisan key:generate`, `php artisan migrate:fresh --seed`.

## Isi database setelah seeding
| Tabel | Isi |
|---|---|
| criteria | 4 kriteria (C1 Ekonomi, C2 Rumah, C3 Pendidikan, C4 Pekerjaan) |
| sub_criteria | 12 sub-kriteria (type kuantitatif/kualitatif, direction benefit/cost) |
| categories | opsi untuk 6 sub-kriteria kualitatif (urut paling layak dulu) |
| pairwise_comparisons | 6 pasangan kriteria, 18 pasangan sub-kriteria (artikel), pasangan kategori (default) |
| applicants | 5 pendaftar fiktif (Andi–Eko) |
| applicant_values | 60 nilai (5 pendaftar × 12 sub-kriteria) |

## Angka patokan untuk menguji AhpService nanti (langkah 4)
- Kriteria: Ekonomi 0,4405 · Rumah 0,2738 · Pendidikan 0,1845 · Pekerjaan 0,1012 · CR 0,0119
- Rumah tinggal (dari segitiga atas artikel): Luas bangunan 0,3728 · Luas tanah 0,2611 ·
  Kepemilikan 0,1598 · Jumlah orang 0,0959 · Sumber air 0,0659 · Sumber listrik 0,0445 · CR 0,0112
- 2×2: Penghasilan ayah 0,7566 · Pekerjaan ayah 0,7340 · Prestasi akademik 0,7407
