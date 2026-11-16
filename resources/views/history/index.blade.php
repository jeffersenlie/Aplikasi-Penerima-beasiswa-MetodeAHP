@extends('layouts.app')

@section('title', 'Riwayat')

@section('content')
<h1 class="h3 mb-1">Riwayat Perhitungan</h1>
<p class="text-muted">Setiap perhitungan yang kamu simpan dari menu Hasil tercatat di sini dan tidak tertimpa.</p>

@if ($calculations->isEmpty())
    <div class="alert alert-info">
        Belum ada riwayat. Buka menu <a href="{{ route('results.index') }}">Hasil</a>, lalu simpan perhitungan.
    </div>
@else
    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Judul</th>
                    <th>Waktu</th>
                    <th class="text-end">Pendaftar</th>
                    <th class="text-end">Kuota</th>
                    <th>Penerima</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($calculations as $c)
                    <tr>
                        <td class="fw-semibold">{{ $c->title }}</td>
                        <td class="small text-muted">{{ $c->created_at->format('d M Y H:i') }}</td>
                        <td class="text-end">{{ $c->applicant_count }}</td>
                        <td class="text-end">{{ $c->quota }}</td>
                        <td>{{ $c->results->pluck('applicant_name')->implode(', ') }}</td>
                        <td class="text-end">
                            <a href="{{ route('history.show', $c) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection