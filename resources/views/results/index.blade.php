@extends('layouts.app')

@section('title', 'Hasil')

@php($fmt = fn ($x) => number_format($x, 4, ',', '.'))

@section('content')
<h1 class="h3 mb-1">Hasil Seleksi</h1>
<p class="text-muted">
    Skor = Σ (prioritas lokal pendaftar × bobot global sub-kriteria). Total skor semua pendaftar = 1.
</p>

@if ($result['blocked'])
    <div class="alert alert-danger">
        <h2 class="h6"><i class="bi bi-x-octagon"></i> Hasil belum dapat ditampilkan</h2>
        <p class="mb-2">
            Ada matriks perbandingan yang tidak konsisten (CR ≥ 0,1). Perbaiki dulu penilaian berikut:
        </p>
        <ul class="mb-0">
            @foreach ($result['inconsistent'] as $g)
                <li>
                    <a href="{{ route('pairwise.edit', ['level' => $g['level'], 'parent' => $g['parent']]) }}">{{ $g['title'] }}</a>
                    — CR {{ $fmt($g['cr']) }}
                </li>
            @endforeach
        </ul>
    </div>
@elseif ($result['applicants']->isEmpty())
    <div class="alert alert-info">
        Belum ada pendaftar. Tambahkan di menu <a href="{{ route('applicants.index') }}">Pendaftar</a>.
    </div>
@else
    @if ($result['incomplete']->isNotEmpty())
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            Beberapa perbandingan belum diisi (dianggap sama penting):
            {{ $result['incomplete']->pluck('title')->implode(', ') }}.
        </div>
    @endif

    <form method="GET" action="{{ route('results.index') }}" class="row g-2 align-items-end mb-3">
        <div class="col-auto">
            <label for="quota" class="form-label small mb-1">Kuota penerima</label>
            <input type="number" id="quota" name="quota" class="form-control" style="width: 8rem"
                   min="1" max="{{ $result['applicants']->count() }}" value="{{ $result['quota'] }}">
        </div>
        <div class="col-auto">
            <button class="btn btn-primary"><i class="bi bi-arrow-repeat"></i> Terapkan</button>
        </div>
    </form>

    @php($max = max(collect($result['ranking'])->max('score'), 1e-9))

    <div class="card mb-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th style="width: 5rem">Rank</th>
                    <th>Pendaftar</th>
                    <th style="width: 30%">Skor</th>
                    <th>Kontribusi terbesar</th>
                    <th class="text-center">Status</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($result['ranking'] as $row)
                    <tr class="{{ $row['eligible'] ? 'table-success' : '' }}">
                        <td class="fw-bold">{{ $row['rank'] }}</td>
                        <td class="fw-semibold">{{ $row['applicant']->name }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: .6rem">
                                    <div class="progress-bar {{ $row['eligible'] ? 'bg-success' : 'bg-secondary' }}"
                                         style="width: {{ round($row['score'] / $max * 100, 1) }}%"></div>
                                </div>
                                <span class="font-monospace">{{ $fmt($row['score']) }}</span>
                            </div>
                        </td>
                        <td class="small text-muted">
                            @foreach ($row['top'] as $part)
                                {{ $part['sub']->code }} {{ $part['sub']->name }} ({{ $fmt($part['contribution']) }})@if (! $loop->last), @endif
                            @endforeach
                        </td>
                        <td class="text-center">
                            @if ($row['eligible'])
                                <span class="badge text-bg-success">Layak</span>
                            @else
                                <span class="badge text-bg-secondary">Tidak layak</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot class="table-light">
                <tr>
                    <td colspan="2" class="text-end fw-semibold">Total skor</td>
                    <td class="font-monospace fw-semibold">{{ $fmt(collect($result['ranking'])->sum('score')) }}</td>
                    <td colspan="2"></td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <h2 class="h5">Rincian prioritas lokal</h2>
    <p class="text-muted small">
        Prioritas lokal tiap pendaftar pada setiap sub-kriteria (total per baris = 1).
        Baris terakhir adalah skor akhir.
    </p>

    @php($scoreById = collect($result['ranking'])->keyBy(fn ($r) => $r['applicant']->id))

    <div class="card">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Sub-kriteria</th>
                    <th class="text-end">Bobot global</th>
                    @foreach ($result['applicants'] as $a)
                        <th class="text-end">{{ $a->name }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach ($result['subs'] as $r)
                    <tr>
                        <td><code class="text-danger">{{ $r['sub']->code }}</code> {{ $r['sub']->name }}</td>
                        <td class="text-end">{{ $fmt($r['global']) }}</td>
                        @foreach ($result['applicants'] as $a)
                            <td class="text-end">{{ $fmt($r['local'][$a->id] ?? 0) }}</td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
                <tfoot class="table-light fw-semibold">
                <tr>
                    <td colspan="2" class="text-end">Skor akhir</td>
                    @foreach ($result['applicants'] as $a)
                        <td class="text-end">{{ $fmt($scoreById[$a->id]['score']) }}</td>
                    @endforeach
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endif
@endsection