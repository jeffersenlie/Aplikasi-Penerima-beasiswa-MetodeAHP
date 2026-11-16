@extends('layouts.app')

@section('title', 'Daftar')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card">
            <div class="card-body p-4">
                <h1 class="h4 mb-3">Daftar akun</h1>
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                               class="form-control" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password (min. 6 karakter)</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Ulangi password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="form-control" required>
                    </div>
                    <button class="btn btn-primary w-100">Daftar</button>
                </form>
                <p class="small text-muted mt-3 mb-0">
                    Sudah punya akun? <a href="{{ route('login') }}">Masuk</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection