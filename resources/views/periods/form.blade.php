@extends('layouts.app')

@section('title', $period->exists ? 'Ubah Periode' : 'Tambah Periode')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <a href="{{ route('periods.index') }}" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Kembali</a>
        <div class="card mt-2">
            <div class="card-body p-4">
                <h1 class="h4 mb-4">{{ $period->exists ? 'Ubah Periode' : 'Tambah Periode' }}</h1>

                <form method="POST"
                      action="{{ $period->exists ? route('periods.update', $period) : route('periods.store') }}">
                    @csrf
                    @if ($period->exists)
                        @method('PUT')
                    @endif

                    <div class="mb-3">
                        <label class="form-label" for="name">Nama periode</label>
                        <input type="text" id="name" name="name" maxlength="100" placeholder="mis. Bidikmisi {{ $period->year }}"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $period->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label" for="year">Tahun</label>
                            <input type="number" id="year" name="year" min="2000" max="2100"
                                   class="form-control @error('year') is-invalid @enderror"
                                   value="{{ old('year', $period->year) }}" required>
                            @error('year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="quota">Kuota penerima</label>
                            <input type="number" id="quota" name="quota" min="1"
                                   class="form-control @error('quota') is-invalid @enderror"
                                   value="{{ old('quota', $period->quota) }}" required>
                            @error('quota') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    @if ($period->is_active && $period->exists)
                        <div class="alert alert-success small"><i class="bi bi-check-circle"></i> Periode ini sedang aktif.</div>
                    @else
                        <div class="form-check mb-4">
                            <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input"
                                   @checked(old('is_active', $period->is_active))>
                            <label class="form-check-label" for="is_active">
                                Jadikan periode aktif <span class="text-muted small">(periode aktif lain otomatis dinonaktifkan)</span>
                            </label>
                        </div>
                    @endif

                    <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Simpan</button>
                    <a href="{{ route('periods.index') }}" class="btn btn-light">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
