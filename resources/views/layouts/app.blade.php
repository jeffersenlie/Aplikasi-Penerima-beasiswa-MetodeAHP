<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'DSS Beasiswa') · {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f6f8;
        }

        .card {
            border: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
        }

        .table td,
        .table th {
            vertical-align: middle;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand fw-semibold" href="{{ url('/') }}">
                <i class="bi bi-mortarboard"></i> DSS Beasiswa (AHP)
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="nav">
                @auth
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('criteria*', 'sub-criteria*') ? 'active fw-semibold' : '' }}"
                                href="{{ route('criteria.index') }}">Kriteria</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('pairwise*') ? 'active fw-semibold' : '' }}"
                                href="{{ route('pairwise.index') }}">Perbandingan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('weights*') ? 'active fw-semibold' : '' }}"
                                href="{{ route('weights.index') }}">Bobot</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('applicants*') ? 'active fw-semibold' : '' }}"
                                href="{{ route('applicants.index') }}">Pendaftar</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('results*') ? 'active fw-semibold' : '' }}"
                                href="{{ route('results.index') }}">Hasil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('history*') ? 'active fw-semibold' : '' }}"
                                href="{{ route('history.index') }}">Riwayat</a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}" class="d-flex">
                                @csrf
                                <button class="btn btn-link nav-link">Keluar ({{ auth()->user()->name }})</button>
                            </form>
                        </li>
                    </ul>
                @endauth
            </div>
        </div>
    </nav>

    <main class="container pb-5">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('warning'))
            <div class="alert alert-warning alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle"></i> {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>