@extends('layouts.app')

@section('title', 'Periode')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1">Periode Seleksi</h1>
        <p class="text-muted mb-0">
            Pendaftar baru dan perhitungan selalu memakai periode yang <strong>aktif</strong>.
        </p>
    </div>
    <a href="{{ route('periods.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Tambah Periode
    </a>
</div>

<div class="card">
    @if ($periods->isEmpty())
        <div class="card-body text-center text-muted py-5">
            Belum ada periode. Klik <strong>Tambah Periode</strong> untuk memulai seleksi.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th class="ps-3">Periode</th>
                    <th class="text-center">Tahun</th>
                    <th class="text-end">Kuota</th>
                    <th class="text-end">Pendaftar</th>
                    <th class="text-end">Riwayat</th>
                    <th class="text-center">Status</th>
                    <th class="text-end pe-3">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($periods as $period)
                    <tr @class(['table-primary' => $period->is_active])>
                        <td class="ps-3 fw-semibold">{{ $period->name }}</td>
                        <td class="text-center">{{ $period->year }}</td>
                        <td class="text-end">{{ $period->quota }}</td>
                        <td class="text-end">{{ $period->applicants_count }}</td>
                        <td class="text-end">{{ $period->calculations_count }}</td>
                        <td class="text-center">
                            @if ($period->is_active)
                                <span class="badge text-bg-success"><i class="bi bi-check-circle"></i> Aktif</span>
                            @else
                                <form action="{{ route('periods.activate', $period) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success">Jadikan aktif</button>
                                </form>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('periods.edit', $period) }}" class="btn btn-sm btn-outline-secondary" title="Ubah">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @php($locked = $period->applicants_count > 0 || $period->calculations_count > 0)
                                <form action="{{ route('periods.destroy', $period) }}" method="POST"
                                      onsubmit="return confirm('Hapus periode {{ $period->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" @disabled($locked)
                                            title="{{ $locked ? 'Masih ada pendaftar/riwayat' : 'Hapus' }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
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
@endsection
