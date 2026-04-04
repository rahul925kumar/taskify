@extends('layouts.app')
@section('title', 'Edit Employee')
@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">Edit Employee</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="{{ route('admin.employees.index') }}">Employees</a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="{{ route('admin.employees.show', $employee) }}">{{ Str::limit($employee->name, 24) }}</a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">Edit</a></li>
        </ul>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Edit Employee</div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.employees.update', $employee) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                @if($employee->image)
                    <div class="mb-4 d-flex align-items-center gap-3">
                        <div class="avatar avatar-xl">
                            <img src="{{ asset('uploads/employees/' . $employee->image) }}" alt="" class="avatar-img rounded-circle" style="object-fit: cover;">
                        </div>
                        <div>
                            <p class="mb-0 fw-semibold">Current photo</p>
                            <small class="text-muted">Upload a new file below to replace it.</small>
                        </div>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $employee->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $employee->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone', $employee->phone) }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                        <input type="text" name="role" id="role" class="form-control @error('role') is-invalid @enderror"
                               value="{{ old('role', $employee->role) }}" required>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea name="address" id="address" rows="3" class="form-control @error('address') is-invalid @enderror">{{ old('address', $employee->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="image" class="form-label">New profile image</label>
                        <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept="image/jpeg,image/png,image/gif,image/jpg">
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Optional. JPG, PNG or GIF. Max 2 MB.</small>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Employee
                    </button>
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
