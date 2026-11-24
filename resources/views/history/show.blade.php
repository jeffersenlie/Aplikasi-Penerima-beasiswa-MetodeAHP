@extends('layouts.app')

@section('title', $calculation->title)

@php($fmt = fn ($x) => number_format($x, 4, ',', '.'))

@section('content')
<a href="{{ route('history.index') }}" class="small"><i class="bi bi-arrow-left"></i> Kembali ke riwayat</a>
<h1 class="h3 mt-2 mb-1">{{ $calculation->title }}</h1>
<p class="text-muted">
    Periode {{ $calculation->period?->name ?? '–' }} ·
    dihitung {{ $calculation->created_at->translatedFormat('d M Y H:i') }} ·
    kuota {{ $calculation->quota }} · {{ $calculation->applicant_count }} pendaftar
</p>

@if (! empty($calculation->consistency))
    <p class="small text-muted mb-3">
        <i class="bi bi-check2-circle"></i> Konsistensi saat dihitung:
        @foreach ($calculation->consistency as $c)
            {{ $c['title'] }} CR {{ $c['cr'] !== null ? $fmt($c['cr']) : '–' }}@unless ($loop->last) · @endunless
        @endforeach
    </p>
@endif

<div class="card mb-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th style="width: 5rem">Rank</th>
                <th>Pendaftar</th>
                <th class="text-end">Skor</th>
                <th class="text-center">Status</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($calculation->results as $r)
                <tr class="{{ $r->eligible ? 'table-success' : '' }}">
                    <td class="fw-bold">{{ $r->rank_no }}</td>
                    <td class="fw-semibold">
                        {{ $r->applicant_name }}
                        <span class="text-muted fw-normal small">{{ $r->registration_no }}</span>
                    </td>
                    <td class="text-end font-monospace">{{ $fmt($r->score) }}</td>
                    <td class="text-center">
                        <span class="badge {{ $r->eligible ? 'text-bg-success' : 'text-bg-secondary' }}">
                            {{ $r->eligible ? 'Layak' : 'Tidak layak' }}
                        </span>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<h2 class="h5">Rincian prioritas lokal (saat dihitung)</h2>
<div class="card">
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>Sub-kriteria</th>
                <th class="text-end">Bobot global</th>
                @foreach ($calculation->results as $r)
                    <th class="text-end">{{ $r->applicant_name }}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach ($calculation->weights ?? [] as $w)
                <tr>
                    <td><code class="text-danger">{{ $w['code'] }}</code> {{ $w['name'] }}</td>
                    <td class="text-end">{{ $fmt($w['global']) }}</td>
                    @foreach ($calculation->results as $r)
                        <td class="text-end">{{ $fmt(collect($r->details)->firstWhere('code', $w['code'])['local'] ?? 0) }}</td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection