@extends('layouts.app')

@section('title', 'Kriteria')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1">Kriteria &amp; Sub-kriteria</h1>
        <p class="text-muted mb-0">
            {{ $criteriaList->count() }} kriteria ·
            {{ $criteriaList->sum(fn ($c) => $c->subCriteria->count()) }} sub-kriteria
        </p>
    </div>
    <a href="{{ route('criteria.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Tambah Kriteria
    </a>
</div>

@forelse ($criteriaList as $criteria)
    <div class="card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h2 class="h5 mb-0">
                <span class="badge bg-primary me-1">{{ $criteria->code }}</span> {{ $criteria->name }}
            </h2>
            <div class="d-flex gap-2">
                <a href="{{ route('sub-criteria.create', $criteria) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus"></i> Sub-kriteria
                </a>
                <a href="{{ route('criteria.edit', $criteria) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-pencil"></i> Ubah
                </a>
                <form action="{{ route('criteria.destroy', $criteria) }}" method="POST"
                      onsubmit="return confirm('Hapus kriteria {{ $criteria->code }} beserta semua sub-kriteria dan perbandingannya?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Hapus</button>
                </form>
            </div>
        </div>
        <div class="card-body p-0">
            @if ($criteria->subCriteria->isEmpty())
                <p class="text-muted p-3 mb-0">Belum ada sub-kriteria.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                        <tr>
                            <th style="width: 80px">Kode</th>
                            <th>Sub-kriteria</th>
                            <th>Jenis data</th>
                            <th>Penilaian</th>
                            <th style="width: 150px" class="text-end">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($criteria->subCriteria as $sub)
                            <tr>
                                <td><code>{{ $sub->code }}</code></td>
                                <td>{{ $sub->name }}</td>
                                <td>
                                    @if ($sub->isQualitative())
                                        <span class="badge text-bg-warning">Kualitatif</span>
                                    @else
                                        <span class="badge text-bg-info">Kuantitatif</span>
                                        @if ($sub->unit)
                                            <small class="text-muted">({{ $sub->unit }})</small>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if ($sub->isQualitative())
                                        @forelse ($sub->categories as $cat)
                                            <span class="badge rounded-pill text-bg-light border">{{ $cat->order }}. {{ $cat->name }}</span>
                                        @empty
                                            <span class="text-danger small">Belum ada kategori</span>
                                        @endforelse
                                    @elseif ($sub->direction === 'cost')
                                        <i class="bi bi-arrow-down"></i> Makin kecil makin layak <small class="text-muted">(cost)</small>
                                    @else
                                        <i class="bi bi-arrow-up"></i> Makin besar makin layak <small class="text-muted">(benefit)</small>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('sub-criteria.edit', $sub) }}" class="btn btn-sm btn-outline-secondary" title="Ubah">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('sub-criteria.destroy', $sub) }}" method="POST"
                                              onsubmit="return confirm('Hapus sub-kriteria {{ $sub->code }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@empty
    <div class="card"><div class="card-body text-center text-muted py-5">
        Belum ada kriteria. Klik <strong>Tambah Kriteria</strong> untuk mulai.
    </div></div>
@endforelse
@endsection
