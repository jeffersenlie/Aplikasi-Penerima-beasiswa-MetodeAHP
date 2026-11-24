@extends('layouts.app')

@section('title', $sub->exists ? 'Ubah Sub-kriteria' : 'Tambah Sub-kriteria')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <a href="{{ route('criteria.index') }}" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Kembali</a>

        <div class="card mt-2">
            <div class="card-body p-4">
                <p class="text-muted mb-1">
                    <span class="badge bg-primary">{{ $criteria->code }}</span> {{ $criteria->name }}
                </p>
                <h1 class="h4 mb-4">{{ $sub->exists ? 'Ubah Sub-kriteria' : 'Tambah Sub-kriteria' }}</h1>

                <form method="POST"
                      action="{{ $sub->exists ? route('sub-criteria.update', $sub) : route('sub-criteria.store', $criteria) }}">
                    @csrf
                    @if ($sub->exists)
                        @method('PUT')
                    @endif

                    <div class="row g-3 mb-3">
                        <div class="col-sm-4">
                            <label class="form-label" for="code">Kode</label>
                            <input type="text" id="code" name="code" maxlength="10" placeholder="mis. C1.3"
                                   class="form-control @error('code') is-invalid @enderror"
                                   value="{{ old('code', $sub->code) }}" required>
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-sm-8">
                            <label class="form-label" for="name">Nama sub-kriteria</label>
                            <input type="text" id="name" name="name" maxlength="100"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $sub->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block">Jenis data</label>
                        @php($type = old('type', $sub->type))
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="type" id="type-kuan"
                                   value="kuantitatif" @checked($type === 'kuantitatif')>
                            <label class="form-check-label" for="type-kuan">Kuantitatif <small class="text-muted">(angka: penghasilan, luas, …)</small></label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="type" id="type-kual"
                                   value="kualitatif" @checked($type === 'kualitatif')>
                            <label class="form-check-label" for="type-kual">Kualitatif <small class="text-muted">(pilihan: pekerjaan, prestasi, …)</small></label>
                        </div>
                    </div>

                    <div id="quantitative-fields" class="row g-3 mb-4">
                        <div class="col-sm-7">
                            <label class="form-label" for="direction">Arah penilaian</label>
                            <select id="direction" name="direction" class="form-select @error('direction') is-invalid @enderror">
                                @php($direction = old('direction', $sub->direction))
                                <option value="cost" @selected($direction === 'cost')>Makin kecil makin layak (cost)</option>
                                <option value="benefit" @selected($direction === 'benefit')>Makin besar makin layak (benefit)</option>
                            </select>
                            @error('direction') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-sm-5">
                            <label class="form-label" for="unit">Satuan</label>
                            <input type="text" id="unit" name="unit" maxlength="30" placeholder="mis. juta Rp/bulan"
                                   class="form-control" value="{{ old('unit', $sub->unit) }}">
                        </div>
                    </div>

                    <div id="qualitative-note" class="alert alert-info small">
                        Sub-kriteria kualitatif dinilai dari <strong>pilihan kategori</strong>
                        (mis. Menumpang / Sewa / Milik sendiri).
                        @unless ($sub->exists) Kategori diisi setelah disimpan. @endunless
                    </div>

                    <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Simpan</button>
                    <a href="{{ route('criteria.index') }}" class="btn btn-light">Batal</a>
                </form>
            </div>
        </div>

        @if ($sub->exists && $sub->isQualitative())
            <div class="card mt-4">
                <div class="card-body p-4">
                    <h2 class="h5 mb-1">Kategori</h2>
                    <p class="text-muted small">
                        Urutan hanya untuk tampilan (disarankan 1 = paling layak). Tingkat kelayakan tiap kategori
                        dihitung dari <a href="{{ route('pairwise.edit', ['level' => 'category', 'parent' => $sub->id]) }}">perbandingan berpasangan kategori</a>.
                    </p>

                    <table class="table align-middle">
                        <thead class="table-light">
                        <tr>
                            <th style="width: 90px">Urutan</th>
                            <th>Nama kategori</th>
                            <th style="width: 120px" class="text-end">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($sub->categories as $cat)
                            <tr>
                                <td>
                                    <input type="number" min="1" name="order" form="cat-upd-{{ $cat->id }}"
                                           class="form-control form-control-sm" value="{{ $cat->order }}" required>
                                </td>
                                <td>
                                    <input type="text" name="name" maxlength="100" form="cat-upd-{{ $cat->id }}"
                                           class="form-control form-control-sm" value="{{ $cat->name }}" required>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <form id="cat-upd-{{ $cat->id }}" method="POST" action="{{ route('categories.update', $cat) }}">
                                            @csrf
                                            @method('PUT')
                                            <button class="btn btn-sm btn-outline-primary" title="Simpan"><i class="bi bi-check-lg"></i></button>
                                        </form>
                                        <form method="POST" action="{{ route('categories.destroy', $cat) }}"
                                              onsubmit="return confirm('Hapus kategori {{ $cat->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted">Belum ada kategori.</td></tr>
                        @endforelse
                        </tbody>
                    </table>

                    <form method="POST" action="{{ route('categories.store', $sub) }}" class="d-flex gap-2">
                        @csrf
                        <input type="text" name="name" maxlength="100" class="form-control"
                               placeholder="Nama kategori baru" required>
                        <button class="btn btn-outline-primary text-nowrap"><i class="bi bi-plus"></i> Tambah</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const radios = document.querySelectorAll('input[name="type"]');
        const quant = document.getElementById('quantitative-fields');
        const note = document.getElementById('qualitative-note');

        function toggle() {
            const checked = document.querySelector('input[name="type"]:checked');
            const isQual = checked && checked.value === 'kualitatif';
            quant.classList.toggle('d-none', isQual);
            note.classList.toggle('d-none', !isQual);
        }

        radios.forEach(r => r.addEventListener('change', toggle));
        toggle();
    })();
</script>
@endpush
