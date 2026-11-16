<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('criteria_id')->constrained('criteria')->cascadeOnDelete();
            $table->string('code', 10)->unique();      // C1.1, C1.2, ...
            $table->string('name');
            $table->enum('type', ['kuantitatif', 'kualitatif']);
            // benefit = makin besar makin layak, cost = makin kecil makin layak
            // (hanya dipakai untuk sub-kriteria kuantitatif)
            $table->enum('direction', ['benefit', 'cost'])->default('benefit');
            $table->string('unit')->nullable();        // juta Rp/bulan, m², VA, orang
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_criteria');
    }
};
