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
            $table->unsignedInteger('order')->default(0); // 1 = paling layak
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
