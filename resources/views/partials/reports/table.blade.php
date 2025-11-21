@php $tz = config('app.timezone'); @endphp
<table class="table table-striped align-middle mb-0">
    <thead class="table-light sticky-top">
        <tr>
            <th>Student</th>
            <th>Date</th>
            <th>Check-in</th>
            <th>Check-out</th>
            <th>Status</th>
            <th>Note</th>
        </tr>
    </thead>
    <tbody id="reportTableBody">
        @forelse($attendance as $item)
            <tr>
                <td>{{ $item->student?->full_name }}</td>
                <td>{{ $item->date->setTimezone($tz)->format('Y-m-d') }}</td>
                <td>{{ optional($item->check_in_at?->setTimezone($tz))->format('H:i') }}</td>
                <td>{{ optional($item->check_out_at?->setTimezone($tz))->format('H:i') }}</td>
                <td><span class="badge bg-{{ $item->status === 'late' ? 'warning' : ($item->status === 'absent' ? 'danger' : 'success') }}">{{ ucfirst($item->status) }}</span></td>
                <td>{{ $item->note }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center py-4">No records.</td></tr>
        @endforelse
    </tbody>
</table>
<div class="p-3">
    {{ $attendance->links() }}
</div>
