<table class="table table-hover align-middle mb-0">
    <thead class="table-light sticky-top">
        <tr>
            <th>Name</th>
            <th>ID Code</th>
            <th>Department</th>
            <th>Status</th>
            <th>Start</th>
            <th>End</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($students as $student)
            <tr>
                <td>{{ $student->full_name }}</td>
                <td>{{ $student->student_id_code }}</td>
                <td>{{ $student->department }}</td>
                <td><span class="badge bg-{{ $student->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($student->status) }}</span></td>
                <td>{{ $student->start_date->format('Y-m-d') }}</td>
                <td>{{ optional($student->end_date)->format('Y-m-d') }}</td>
                <td class="text-end">
                    <a href="{{ route('reports.show', $student) }}" class="btn btn-sm btn-outline-primary">View Report</a>
                    <button class="btn btn-sm btn-secondary edit-student" data-id="{{ $student->id }}">Edit</button>
                    <button class="btn btn-sm btn-outline-warning toggle-status" data-id="{{ $student->id }}">{{ $student->status === 'active' ? 'Deactivate' : 'Activate' }}</button>
                    <button class="btn btn-sm btn-outline-danger delete-student" data-id="{{ $student->id }}">Delete</button>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center py-4">No students found.</td></tr>
        @endforelse
    </tbody>
</table>
<div class="p-3">
    {{ $students->links() }}
</div>
