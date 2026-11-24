<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu baris = satu periode seleksi beasiswa (Bidikmisi dibuka per tahun).
        // Pendaftar dan perhitungan selalu terikat ke satu periode,
        // sehingga pendaftar tahun berbeda tidak saling bersaing.
        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // mis. "Bidikmisi 2019"
            $table->unsignedSmallInteger('year')->unique(); // tahun seleksi / angkatan
            $table->unsignedInteger('quota');              // jumlah penerima beasiswa
            $table->boolean('is_active')->default(false);  // periode yang sedang dikelola (hanya satu)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periods');
    }
};
