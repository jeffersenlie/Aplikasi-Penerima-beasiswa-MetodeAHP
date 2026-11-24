@extends('layouts.app')

@section('title', $applicant->exists ? 'Ubah Pendaftar' : 'Tambah Pendaftar')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <a href="{{ route('applicants.index') }}" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Kembali</a>
        <h1 class="h3 mt-2 mb-3">{{ $applicant->exists ? 'Ubah Data Pendaftar' : 'Tambah Pendaftar' }}</h1>

        <form method="POST"
              action="{{ $applicant->exists ? route('applicants.update', $applicant) : route('applicants.store') }}">
            @csrf
            @if ($applicant->exists)
                @method('PUT')
            @endif

            <div class="card mb-3">
                <div class="card-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="registration_no">No. pendaftaran</label>
                        <input type="text" id="registration_no" name="registration_no" maxlength="30"
                               class="form-control @error('registration_no') is-invalid @enderror"
                               value="{{ old('registration_no', $applicant->registration_no) }}" required>
                        @error('registration_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold" for="name">Nama pendaftar</label>
                        <input type="text" id="name" name="name" maxlength="100"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $applicant->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            @foreach ($criteriaList as $criteria)
                @continue($criteria->subCriteria->isEmpty())
                <div class="card mb-3">
                    <div class="card-header bg-white fw-semibold">
                        <span class="badge bg-primary">{{ $criteria->code }}</span> {{ $criteria->name }}
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach ($criteria->subCriteria as $sub)
                                @php($field = "values.{$sub->id}")
                                @php($val = old($field, $current[$sub->id] ?? null))
                                <div class="col-md-6">
                                    <label class="form-label" for="v{{ $sub->id }}">
                                        <code>{{ $sub->code }}</code> {{ $sub->name }}
                                    </label>

                                    @if ($sub->isQualitative())
                                        <select id="v{{ $sub->id }}" name="values[{{ $sub->id }}]"
                                                class="form-select @error($field) is-invalid @enderror" required>
                                            <option value="">— pilih —</option>
                                            @foreach ($sub->categories as $cat)
                                                <option value="{{ $cat->id }}" @selected((string) $val === (string) $cat->id)>
                                                    {{ $cat->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if ($sub->categories->isEmpty())
                                            <div class="form-text text-danger">Belum ada kategori. Isi dulu di menu Kriteria.</div>
                                        @endif
                                    @else
                                        <div class="input-group">
                                            <input type="number" step="any" min="0" id="v{{ $sub->id }}"
                                                   name="values[{{ $sub->id }}]"
                                                   class="form-control @error($field) is-invalid @enderror"
                                                   value="{{ $val !== null ? rtrim(rtrim(number_format((float) $val, 4, '.', ''), '0'), '.') : '' }}"
                                                   required>
                                            @if ($sub->unit)
                                                <span class="input-group-text">{{ $sub->unit }}</span>
                                            @endif
                                        </div>
                                        <div class="form-text">
                                            {{ $sub->direction === 'cost' ? 'Makin kecil makin layak.' : 'Makin besar makin layak.' }}
                                            @if ($sub->direction === 'cost') Isi 0 bila tidak ada. @endif
                                        </div>
                                    @endif

                                    @error($field) <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="position-sticky bottom-0 bg-body py-3 border-top">
                <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Simpan</button>
                <a href="{{ route('applicants.index') }}" class="btn btn-light">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
