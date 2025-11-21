@php $tz = config('app.timezone'); @endphp
<div class="table-responsive">
<table class="table table-hover align-middle mb-0">
    <thead class="table-light">
        <tr>
            <th>Date</th>
            <th>Check-in</th>
            <th>Check-out</th>
            <th>Status</th>
            <th>Note</th>
        </tr>
    </thead>
    <tbody>
        @forelse($history as $item)
            <tr>
                <td>{{ $item->date->setTimezone($tz)->format('Y-m-d') }}</td>
                <td>{{ optional($item->check_in_at?->setTimezone($tz))->format('H:i') }}</td>
                <td>{{ optional($item->check_out_at?->setTimezone($tz))->format('H:i') }}</td>
                <td><span class="badge bg-{{ $item->status === 'late' ? 'warning' : ($item->status === 'absent' ? 'danger' : 'success') }}">{{ ucfirst($item->status) }}</span></td>
                <td>{{ $item->note }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center py-4">No attendance history yet.</td></tr>
        @endforelse
    </tbody>
</table>
</div>
<div class="p-3">{{ $history->links() }}</div>
