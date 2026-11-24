<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu baris = satu kali user menjalankan perhitungan untuk satu periode (riwayat).
        // quota, applicant_count, weights, consistency = salinan (snapshot) saat dihitung,
        // supaya riwayat tidak berubah walau data master diubah kemudian.
        Schema::create('calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('period_id')->constrained('periods')->restrictOnDelete();
            $table->string('title');
            $table->unsignedInteger('quota');
            $table->unsignedInteger('applicant_count');
            $table->json('weights')->nullable();       // bobot global tiap sub-kriteria
            $table->json('consistency')->nullable();   // CR tiap matriks pairwise
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calculations');
    }
};