@extends('layouts.app')

@section('title', 'Perbandingan Berpasangan')

@section('content')
<h1 class="h3 mb-1">Perbandingan Berpasangan</h1>
<p class="text-muted">
    Isi setiap grup dengan menjawab pertanyaan "A dibanding B, mana yang lebih penting dan seberapa?"
    (skala 1–9). Grup baru otomatis muncul saat kriteria, sub-kriteria, atau kategori ditambahkan.
</p>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>Grup</th>
                <th class="text-center">Item</th>
                <th class="text-center">Pertanyaan terisi</th>
                <th class="text-center">CR</th>
                <th class="text-end">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($groups as $g)
                <tr @class(['table-primary' => $g['level'] === 'criteria'])>
                    <td>
                        @if ($g['level'] === 'criteria')
                            <i class="bi bi-diagram-3"></i>
                        @elseif ($g['level'] === 'sub_criteria')
                            <i class="bi bi-arrow-return-right ms-3"></i>
                        @else
                            <i class="bi bi-tags ms-3"></i>
                        @endif
                        {{ $g['title'] }}
                    </td>
                    <td class="text-center">{{ $g['items'] }}</td>
                    <td class="text-center">
                        @if ($g['total'] === 0)
                            <span class="text-muted small">tidak perlu (≤ 1 item)</span>
                        @elseif ($g['filled'] === $g['total'])
                            <span class="badge text-bg-success">{{ $g['filled'] }}/{{ $g['total'] }}</span>
                        @else
                            <span class="badge text-bg-warning">{{ $g['filled'] }}/{{ $g['total'] }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if ($g['cr'] === null)
                            <span class="text-muted">–</span>
                        @elseif ($g['cr'] < 0.1)
                            <span class="badge text-bg-success">{{ number_format($g['cr'], 4, ',', '.') }}</span>
                        @else
                            <span class="badge text-bg-danger">{{ number_format($g['cr'], 4, ',', '.') }}</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if ($g['total'] > 0)
                            <a href="{{ route('pairwise.edit', ['level' => $g['level'], 'parent' => $g['parent']]) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil-square"></i> Isi / ubah
                            </a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
