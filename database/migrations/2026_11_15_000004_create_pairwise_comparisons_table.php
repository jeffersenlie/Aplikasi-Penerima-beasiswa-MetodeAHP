<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu baris = satu pertanyaan "A dibanding B".
        // value > 1 : A lebih penting/disukai, value < 1 : B lebih penting, value = 1 : sama.
        // Kebalikannya (B vs A = 1/value) dihitung saat membentuk matrix, tidak disimpan.
        Schema::create('pairwise_comparisons', function (Blueprint $table) {
            $table->id();
            $table->enum('level', ['criteria', 'sub_criteria', 'category']);
            // criteria: null | sub_criteria: id kriteria induk | category: id sub-kriteria
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('item_a_id');
            $table->unsignedBigInteger('item_b_id');
            $table->decimal('value', 10, 4);           // decimal: nilai artikel berupa rata-rata geometrik
            $table->timestamps();

            $table->unique(['level', 'item_a_id', 'item_b_id']);
            $table->index(['level', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pairwise_comparisons');
    }
};
