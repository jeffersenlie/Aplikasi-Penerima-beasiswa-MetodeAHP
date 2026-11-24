<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pilihan jawaban untuk sub-kriteria kualitatif, mis. "Menumpang", "Sewa/kontrak"
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_criteria_id')->constrained('sub_criteria')->cascadeOnDelete();
            $table->string('name');
            // Urutan tampilan saja (1 = ditampilkan pertama). Prioritas kategori
            // TIDAK diambil dari kolom ini, tetapi dihitung dari pairwise_comparisons.
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->unique(['sub_criteria_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
