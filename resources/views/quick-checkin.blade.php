@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="text-center mb-4">
            <h4 class="fw-bold">Pintas Check-in</h4>
            <p class="text-muted mb-0">Masukkan NIM dan password untuk check-in cepat tanpa login.</p>
        </div>
        @if(!empty($result))
            <div class="alert alert-{{ $result['status'] }} alert-dismissible fade show" role="alert">
                <strong>{{ $result['title'] }}</strong>
                @if(!empty($result['details']))
                    <div class="small mb-0">{{ $result['details'] }}</div>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div class="card shadow-sm border-0 card-hover">
            <div class="card-body">
                <form method="POST" action="{{ route('quick-checkin.submit') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">NIM</label>
                        <input type="text" name="nim" class="form-control @error('nim') is-invalid @enderror" value="{{ old('nim') }}" placeholder="Contoh: INT-001" required autofocus>
                        @error('nim')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Password akun Anda" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-raise">Check-in Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
        <p class="text-center text-muted small mt-3 mb-0">Check-out otomatis diset pukul 16:00 WIB hari ini. Admin tetap bisa mengubah data jika diperlukan.</p>
    </div>
</div>
@endsection
