<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hasil tiap pendaftar pada satu perhitungan. Nama & skor disalin (snapshot),
        // jadi riwayat tidak berubah walau data pendaftar diedit atau dihapus.
        Schema::create('calculation_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calculation_id')->constrained('calculations')->cascadeOnDelete();
            $table->string('applicant_name');
            $table->decimal('score', 10, 6);
            $table->unsignedInteger('rank_no');
            $table->boolean('eligible')->default(false);
            $table->json('details')->nullable();   // prioritas lokal & kontribusi per sub-kriteria
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calculation_results');
    }
};