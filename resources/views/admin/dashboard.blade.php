@extends('layouts.app')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Today's Check-ins</p>
                        <h3 class="mb-0">{{ $checkIns }}</h3>
                    </div>
                    <i class="bi bi-check2-circle text-success fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Missing Check-ins</p>
                        <h3 class="mb-0">{{ $missing }}</h3>
                    </div>
                    <i class="bi bi-exclamation-triangle text-warning fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Late Arrivals</p>
                        <h3 class="mb-0">{{ $late }}</h3>
                    </div>
                    <i class="bi bi-clock-history text-danger fs-1"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Recent Check-ins</h5>
        <div>
            <a href="{{ route('students.index') }}" class="btn btn-sm btn-outline-primary">Roster</a>
            <a href="{{ route('reports.index') }}" class="btn btn-sm btn-primary">Reports</a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Student</th>
                        <th>Check-in</th>
                        <th>Status</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent as $item)
                        <tr>
                            <td>{{ $item->student?->full_name }}</td>
                            <td>{{ optional($item->check_in_at)->format('Y-m-d H:i') }}</td>
                            <td><span class="badge bg-{{ $item->status === 'late' ? 'warning' : 'success' }} text-dark">{{ ucfirst($item->status) }}</span></td>
                            <td>{{ $item->note }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-4">No check-ins yet today.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
