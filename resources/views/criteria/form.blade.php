@extends('layouts.app')

@section('title', $criteria->exists ? 'Ubah Kriteria' : 'Tambah Kriteria')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <a href="{{ route('criteria.index') }}" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Kembali</a>
        <div class="card mt-2">
            <div class="card-body p-4">
                <h1 class="h4 mb-4">{{ $criteria->exists ? 'Ubah Kriteria' : 'Tambah Kriteria' }}</h1>

                <form method="POST"
                      action="{{ $criteria->exists ? route('criteria.update', $criteria) : route('criteria.store') }}">
                    @csrf
                    @if ($criteria->exists)
                        @method('PUT')
                    @endif

                    <div class="mb-3">
                        <label class="form-label" for="code">Kode</label>
                        <input type="text" id="code" name="code" maxlength="10" placeholder="mis. C5"
                               class="form-control @error('code') is-invalid @enderror"
                               value="{{ old('code', $criteria->code) }}" required>
                        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="name">Nama kriteria</label>
                        <input type="text" id="name" name="name" maxlength="100" placeholder="mis. Ekonomi"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $criteria->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    @unless ($criteria->exists)
                        <div class="alert alert-info small">
                            Setelah kriteria baru ditambahkan, pertanyaan perbandingan berpasangannya
                            akan otomatis muncul di halaman <strong>Perbandingan</strong>.
                        </div>
                    @endunless

                    <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Simpan</button>
                    <a href="{{ route('criteria.index') }}" class="btn btn-light">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
