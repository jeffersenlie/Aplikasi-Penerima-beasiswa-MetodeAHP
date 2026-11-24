<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Alternatif = pendaftar beasiswa pada satu periode.
        Schema::create('applicants', function (Blueprint $table) {
            $table->id();
            // restrict: periode yang masih punya pendaftar tidak boleh dihapus
            $table->foreignId('period_id')->constrained('periods')->restrictOnDelete();
            $table->string('registration_no', 30);   // no. pendaftaran / NIM, pembeda pendaftar bernama sama
            $table->string('name');
            $table->timestamps();

            // no. pendaftaran unik di dalam satu periode
            $table->unique(['period_id', 'registration_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicants');
    }
};
