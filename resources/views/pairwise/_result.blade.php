@php($fmt = fn ($x) => number_format($x, 4, ',', '.'))
<div class="card mb-4">
    <div class="card-body">
        <h2 class="h6 d-flex justify-content-between align-items-center">
            Hasil bobot (AHP)
            @if (count($result['weights']) > 1)
                @if ($result['consistent'])
                    <span class="badge text-bg-success">Konsisten</span>
                @else
                    <span class="badge text-bg-danger">Tidak konsisten</span>
                @endif
            @endif
        </h2>
        <p class="small text-muted">Dihitung dari jawaban yang <strong>sudah tersimpan</strong>.</p>

        <table class="table table-sm small">
            @foreach ($result['items'] as $i => $item)
                <tr>
                    <td>{{ $item['label'] }}</td>
                    <td class="text-end fw-semibold">{{ $fmt($result['weights'][$i]) }}</td>
                    <td style="width: 35%">
                        <div class="progress" style="height: 6px">
                            <div class="progress-bar" style="width: {{ round($result['weights'][$i] * 100, 1) }}%"></div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </table>

        @if (count($result['weights']) > 1)
            <table class="table table-sm small mb-2">
                <tr><td>Iterasi (ε = 0,0002)</td><td class="text-end">{{ $result['iterations'] }}</td></tr>
                <tr><td>λmax</td><td class="text-end">{{ $fmt($result['lambda_max']) }}</td></tr>
                <tr><td>CI = (λmax − n)/(n − 1)</td><td class="text-end">{{ $fmt($result['ci']) }}</td></tr>
                <tr><td>RI (n = {{ count($result['weights']) }})</td><td class="text-end">{{ number_format($result['ri'], 2, ',', '.') }}</td></tr>
                <tr class="fw-semibold"><td>CR = CI/RI</td><td class="text-end">{{ $fmt($result['cr']) }}</td></tr>
            </table>

            <a class="small" data-bs-toggle="collapse" href="#detail-matrix">Lihat matriks &amp; iterasi</a>
            <div class="collapse mt-2" id="detail-matrix">
                <p class="small text-muted mb-1">Matriks perbandingan (dibentuk otomatis dari jawaban):</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm small text-end">
                        @foreach ($result['matrix'] as $row)
                            <tr>
                                @foreach ($row as $v)
                                    <td>{{ $fmt($v) }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </table>
                </div>
                <p class="small text-muted mb-1">Bobot tiap iterasi:</p>
                <table class="table table-sm small text-end">
                    @foreach ($result['history'] as $k => $w)
                        <tr>
                            <td class="text-start">Iterasi {{ $k + 1 }}</td>
                            @foreach ($w as $v)
                                <td>{{ $fmt($v) }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif
    </div>
</div>
