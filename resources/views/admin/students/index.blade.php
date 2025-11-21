@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Intern Students</h4>
    <button class="btn btn-primary" id="addStudentBtn">Add Student</button>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <form id="filterForm" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search name or ID" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="department" class="form-select">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" @selected(request('department') === $dept)> {{ $dept }} </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" @selected(request('status')==='active')>Active</option>
                    <option value="inactive" @selected(request('status')==='inactive')>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 text-end">
                <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
            </div>
        </form>
        <div id="studentsTable">
            @include('partials.students.table', ['students' => $students])
        </div>
    </div>
</div>

<div class="modal fade" id="studentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Student</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="studentForm">
            @csrf
            <input type="hidden" id="student_id">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Student ID Code</label>
                    <input type="text" name="student_id_code" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Department</label>
                    <input type="text" name="department" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="saveStudent">Save</button>
      </div>
    </div>
  </div>
</div>
@endsection
