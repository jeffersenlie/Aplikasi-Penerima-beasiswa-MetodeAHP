<?php

use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CriteriaController;
use App\Http\Controllers\PairwiseController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\SubCriteriaController;
use App\Http\Controllers\WeightController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/criteria');

// Kriteria (C1, C2, ...)
Route::resource('criteria', CriteriaController::class)
    ->except(['show'])
    ->parameters(['criteria' => 'criteria']);

// Sub-kriteria (milik satu kriteria)
Route::get('criteria/{criteria}/sub-criteria/create', [SubCriteriaController::class, 'create'])->name('sub-criteria.create');
Route::post('criteria/{criteria}/sub-criteria', [SubCriteriaController::class, 'store'])->name('sub-criteria.store');
Route::get('sub-criteria/{subCriteria}/edit', [SubCriteriaController::class, 'edit'])->name('sub-criteria.edit');
Route::put('sub-criteria/{subCriteria}', [SubCriteriaController::class, 'update'])->name('sub-criteria.update');
Route::delete('sub-criteria/{subCriteria}', [SubCriteriaController::class, 'destroy'])->name('sub-criteria.destroy');

// Kategori (pilihan jawaban sub-kriteria kualitatif)
Route::post('sub-criteria/{subCriteria}/categories', [CategoryController::class, 'store'])->name('categories.store');
Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

// Perbandingan berpasangan (daftar pertanyaan, bukan matrix)
Route::get('pairwise', [PairwiseController::class, 'index'])->name('pairwise.index');
Route::get('pairwise/{level}/{parent?}', [PairwiseController::class, 'edit'])
    ->whereIn('level', ['criteria', 'sub_criteria', 'category'])->whereNumber('parent')
    ->name('pairwise.edit');
Route::put('pairwise/{level}/{parent?}', [PairwiseController::class, 'update'])
    ->whereIn('level', ['criteria', 'sub_criteria', 'category'])->whereNumber('parent')
    ->name('pairwise.update');

// Ringkasan bobot (kriteria, lokal, global)
Route::get('weights', [WeightController::class, 'index'])->name('weights.index');

// Pendaftar (alternatif) + nilai tiap sub-kriteria
Route::resource('applicants', ApplicantController::class)->except(['show']);

// Hasil: prioritas lokal, skor global, ranking, kuota
Route::get('results', [ResultController::class, 'index'])->name('results.index');