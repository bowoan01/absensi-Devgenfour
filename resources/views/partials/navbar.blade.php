<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}">Intern Attendance</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            @auth
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                @if(auth()->user()->isAdmin())
                    <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('students.index') }}">Students</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('reports.index') }}">Reports</a></li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('attendance.index') }}">Attendance</a></li>
                @endif
            </ul>
            <div class="d-flex align-items-center">
                <span class="me-3">{{ auth()->user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="btn btn-outline-danger btn-sm">Logout</button>
                </form>
            </div>
            @endauth
        </div>
    </div>
</nav>
