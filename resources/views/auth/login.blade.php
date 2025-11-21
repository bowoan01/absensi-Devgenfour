@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm card-hover">
            <div class="card-body p-4">
                <h4 class="mb-1 text-center fw-bold">Masuk</h4>
                {{-- <p class="text-muted text-center mb-4">Silakan login untuk mengelola absensi magang.</p> --}}
                <form method="POST" action="{{ url('/login') }}" id="login-form" class="fade-in">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email atau Username</label>
                        <input type="text" name="login" class="form-control" value="{{ old('login') }}" required autofocus placeholder="contoh: admin atau admin@email.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kata Sandi</label>
                        <input type="password" name="password" class="form-control" required placeholder="Masukkan kata sandi Anda">
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">Ingat saya</label>
                    </div>
                    <button class="btn btn-primary w-100 btn-raise" type="submit">Masuk</button>
                    <div class="text-center mt-2"><a href="#" class="text-muted small">Lupa kata sandi?</a></div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
