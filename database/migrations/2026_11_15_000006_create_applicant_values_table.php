<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nilai pendaftar per sub-kriteria:
        // kuantitatif -> numeric_value, kualitatif -> category_id
        Schema::create('applicant_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->foreignId('sub_criteria_id')->constrained('sub_criteria')->cascadeOnDelete();
            $table->decimal('numeric_value', 15, 4)->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->timestamps();

            $table->unique(['applicant_id', 'sub_criteria_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicant_values');
    }
};
