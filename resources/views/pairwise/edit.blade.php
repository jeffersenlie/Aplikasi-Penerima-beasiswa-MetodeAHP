@extends('layouts.app')

@section('title', $title)

@section('content')
<a href="{{ route('pairwise.index') }}" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Semua grup</a>
<h1 class="h3 mt-2 mb-1">{{ $title }}</h1>
<p class="text-muted">{{ $question }} · {{ count($questions) }} pertanyaan</p>

<div class="row g-4">
    <div class="col-lg-8">
        @if (empty($questions))
            <div class="alert alert-info">Butuh minimal 2 item untuk dibandingkan.</div>
        @else
            <form method="POST" action="{{ route('pairwise.update', ['level' => $level, 'parent' => $parent]) }}">
                @csrf
                @method('PUT')

                @foreach ($questions as $k => $q)
                    @php
                        $choice = old("pairs.$k.choice", $q['choice']);
                        $scale  = old("pairs.$k.scale", $q['scale']);
                    @endphp
                    <div class="card mb-3 pair-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Pertanyaan {{ $k + 1 }}</span>
                                @unless ($q['filled'])
                                    <span class="badge text-bg-warning">belum diisi</span>
                                @endunless
                            </div>

                            <input type="hidden" name="pairs[{{ $k }}][a]" value="{{ $q['a']['id'] }}">
                            <input type="hidden" name="pairs[{{ $k }}][b]" value="{{ $q['b']['id'] }}">

                            <div class="btn-group w-100 mb-3" role="group">
                                <input type="radio" class="btn-check choice" name="pairs[{{ $k }}][choice]"
                                       id="p{{ $k }}a" value="a" @checked($choice === 'a')>
                                <label class="btn btn-outline-primary w-50 text-start" for="p{{ $k }}a">
                                    <strong>{{ $q['a']['label'] }}</strong><br><small>lebih penting</small>
                                </label>

                                <input type="radio" class="btn-check choice" name="pairs[{{ $k }}][choice]"
                                       id="p{{ $k }}e" value="equal" @checked($choice === 'equal')>
                                <label class="btn btn-outline-secondary px-3" for="p{{ $k }}e">Sama<br><small>penting</small></label>

                                <input type="radio" class="btn-check choice" name="pairs[{{ $k }}][choice]"
                                       id="p{{ $k }}b" value="b" @checked($choice === 'b')>
                                <label class="btn btn-outline-primary w-50 text-end" for="p{{ $k }}b">
                                    <strong>{{ $q['b']['label'] }}</strong><br><small>lebih penting</small>
                                </label>
                            </div>

                            <div class="row align-items-center g-2 scale-row">
                                <label class="col-sm-4 col-form-label" for="s{{ $k }}">Seberapa lebih penting? (1–9)</label>
                                <div class="col-sm-3">
                                    <input type="number" step="any" min="1" max="9" id="s{{ $k }}"
                                           name="pairs[{{ $k }}][scale]" list="saaty"
                                           class="form-control scale @error("pairs.$k.scale") is-invalid @enderror"
                                           value="{{ $scale }}">
                                    @error("pairs.$k.scale") <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-sm-5">
                                    <input type="range" class="form-range slider" min="1" max="9" step="1" value="{{ round($scale) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <datalist id="saaty">
                    @foreach (range(1, 9) as $n) <option value="{{ $n }}"></option> @endforeach
                </datalist>

                <div class="position-sticky bottom-0 bg-body py-3 border-top">
                    <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Simpan semua jawaban</button>
                    <a href="{{ route('pairwise.index') }}" class="btn btn-light">Batal</a>
                </div>
            </form>
        @endif
    </div>

    <div class="col-lg-4">
        @include('pairwise._result', ['result' => $result])

        <div class="card">
            <div class="card-body">
                <h2 class="h6">Skala Saaty</h2>
                <table class="table table-sm small mb-2">
                    <tr><td class="fw-semibold">1</td><td>Sama penting</td></tr>
                    <tr><td class="fw-semibold">3</td><td>Sedikit lebih penting</td></tr>
                    <tr><td class="fw-semibold">5</td><td>Lebih penting</td></tr>
                    <tr><td class="fw-semibold">7</td><td>Sangat lebih penting</td></tr>
                    <tr><td class="fw-semibold">9</td><td>Mutlak lebih penting</td></tr>
                    <tr><td class="fw-semibold">2,4,6,8</td><td>Nilai di antaranya</td></tr>
                </table>
                <p class="small text-muted mb-0">
                    Nilai desimal diperbolehkan (mis. 4,2172 dari rata-rata geometrik beberapa penilai).
                    Kebalikannya (B dibanding A = 1/nilai) dihitung otomatis.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.pair-card').forEach(card => {
        const radios = card.querySelectorAll('.choice');
        const row    = card.querySelector('.scale-row');
        const input  = card.querySelector('.scale');
        const slider = card.querySelector('.slider');

        function toggle() {
            const equal = card.querySelector('.choice:checked')?.value === 'equal';
            row.classList.toggle('opacity-50', equal);
            input.readOnly = equal;
            slider.disabled = equal;
            if (equal) { input.value = 1; slider.value = 1; }
        }

        slider.addEventListener('input', () => input.value = slider.value);
        input.addEventListener('input', () => slider.value = Math.round(input.value || 1));
        radios.forEach(r => r.addEventListener('change', toggle));
        toggle();
    });
</script>
@endpush
