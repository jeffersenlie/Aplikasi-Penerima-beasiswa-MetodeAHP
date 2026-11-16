@extends('layouts.app')

@section('title', 'Pendaftar')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1">Pendaftar Beasiswa</h1>
        <p class="text-muted mb-0">{{ $applicants->count() }} pendaftar · {{ $subs->count() }} sub-kriteria dinilai</p>
    </div>
    <a href="{{ route('applicants.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus"></i> Tambah Pendaftar
    </a>
</div>

<div class="card">
    @if ($applicants->isEmpty())
        <div class="card-body text-center text-muted py-5">
            Belum ada pendaftar. Klik <strong>Tambah Pendaftar</strong>.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0 small">
                <thead class="table-light">
                <tr>
                    <th class="ps-3">Nama</th>
                    @foreach ($subs as $sub)
                        <th class="text-center text-nowrap" title="{{ $sub->name }}">
                            {{ $sub->code }}<br>
                            <span class="fw-normal text-muted">{{ \Illuminate\Support\Str::limit($sub->name, 14) }}</span>
                        </th>
                    @endforeach
                    <th class="text-end pe-3">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($applicants as $applicant)
                    @php($values = $applicant->values->keyBy('sub_criteria_id'))
                    <tr>
                        <td class="ps-3 fw-semibold text-nowrap">
                            {{ $applicant->name }}
                            @if ($values->count() < $subs->count())
                                <span class="badge text-bg-warning" title="Ada sub-kriteria yang belum diisi">belum lengkap</span>
                            @endif
                        </td>
                        @foreach ($subs as $sub)
                            @php($v = $values->get($sub->id))
                            <td class="text-center">
                                @if (! $v)
                                    <span class="text-danger">–</span>
                                @elseif ($sub->isQualitative())
                                    {{ $v->category?->name ?? '–' }}
                                @else
                                    {{ rtrim(rtrim(number_format($v->numeric_value, 2, ',', '.'), '0'), ',') }}
                                @endif
                            </td>
                        @endforeach
                        <td class="text-end pe-3">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('applicants.edit', $applicant) }}" class="btn btn-sm btn-outline-secondary" title="Ubah">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('applicants.destroy', $applicant) }}" method="POST"
                                      onsubmit="return confirm('Hapus pendaftar {{ $applicant->name }}?')">
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
@endsection
