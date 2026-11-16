@extends('layouts.app')

@section('title', 'Bobot')

@php($fmt = fn ($x) => number_format($x, 4, ',', '.'))

@section('content')
<h1 class="h3 mb-1">Ringkasan Bobot</h1>
<p class="text-muted">
    Bobot global sub-kriteria = bobot kriteria × bobot lokal sub-kriteria.
    Total bobot global = {{ $fmt(collect($rows)->flatMap(fn ($r) => $r['subs'])->sum('global')) }}.
</p>

@php($inconsistent = collect($rows)->filter(fn ($r) => $r['cr'] >= 0.1))
@if (! $top['consistent'] || $inconsistent->isNotEmpty())
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i>
        Ada matriks yang tidak konsisten (CR ≥ 0,1). Perbaiki di menu <a href="{{ route('pairwise.index') }}">Perbandingan</a>.
    </div>
@endif

<div class="card mb-3">
    <div class="card-body py-2 small">
        Matriks kriteria: λmax {{ $fmt($top['lambda_max']) }} · CI {{ $fmt($top['ci']) }} ·
        <strong>CR {{ $fmt($top['cr']) }}</strong>
        <span class="badge {{ $top['consistent'] ? 'text-bg-success' : 'text-bg-danger' }}">
            {{ $top['consistent'] ? 'Konsisten' : 'Tidak konsisten' }}
        </span>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>Kriteria</th>
                <th class="text-end">Bobot</th>
                <th>Sub-kriteria</th>
                <th class="text-end">Bobot lokal</th>
                <th class="text-end">Bobot global</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($rows as $r)
                @forelse ($r['subs'] as $i => $s)
                    <tr>
                        @if ($i === 0)
                            <td rowspan="{{ count($r['subs']) }}" class="fw-semibold">
                                <span class="badge bg-primary">{{ $r['criteria']->code }}</span> {{ $r['criteria']->name }}
                                @if (count($r['subs']) > 2)
                                    <div class="small fw-normal text-muted">CR sub-kriteria {{ $fmt($r['cr']) }}</div>
                                @endif
                            </td>
                            <td rowspan="{{ count($r['subs']) }}" class="text-end fw-semibold">{{ $fmt($r['weight']) }}</td>
                        @endif
                        <td><code>{{ $s['sub']->code }}</code> {{ $s['sub']->name }}</td>
                        <td class="text-end">{{ $fmt($s['local']) }}</td>
                        <td class="text-end fw-semibold">{{ $fmt($s['global']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="fw-semibold"><span class="badge bg-primary">{{ $r['criteria']->code }}</span> {{ $r['criteria']->name }}</td>
                        <td class="text-end fw-semibold">{{ $fmt($r['weight']) }}</td>
                        <td colspan="3" class="text-muted">Belum ada sub-kriteria</td>
                    </tr>
                @endforelse
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
