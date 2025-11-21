<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top soft-shadow">
    <div class="container">
        {{-- <a class="navbar-brand fw-bold text-primary" href="/">{{ config('app.name', 'Absensi Magang') }}</a> --}}
        <a class="navbar-brand fw-bold text-primary" href="/">DEVGENFOUR</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto align-items-lg-center">
                @auth
                    @if(auth()->user()->isAdmin())
                        <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dasbor</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('students.index') }}">Mahasiswa Magang</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('reports.index') }}">Laporan</a></li>
                    @elseif(auth()->user()->isStudent())
                        <li class="nav-item"><a class="nav-link" href="{{ route('attendance.index') }}">Absensi Saya</a></li>
                    @endif
                @endauth
            </ul>
            @auth
                <div class="d-flex align-items-center gap-3 ms-auto flex-wrap justify-content-lg-end">
                    <div class="d-flex align-items-center small text-muted gap-2 bg-light px-3 py-2 rounded-pill soft-shadow-sm">
                        <i class="bi bi-clock-history text-primary"></i>
                        <div class="lh-sm">
                            <div class="fw-semibold text-body">Waktu WIB</div>
                            <div id="nav-clock" class="fw-bold text-body">–</div>
                        </div>
                    </div>
                    <ul class="navbar-nav align-items-center">
                        <li class="nav-item me-2 d-flex align-items-center text-muted">
                            <i class="bi bi-person-circle me-1"></i> {{ auth()->user()->name }}
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="btn btn-outline-secondary btn-sm">Keluar</button>
                            </form>
                        </li>
                    </ul>
                </div>
            @endauth
        </div>
    </div>
</nav>
