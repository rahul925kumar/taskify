@extends('layouts.app')
@section('title', 'Apply Leave')
@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">Apply Leave</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('employee.dashboard') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="{{ route('employee.leaves.index') }}">Leaves</a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">Apply</a></li>
        </ul>
    </div>

    <div class="card">
        <div class="card-header"><h4 class="card-title">Leave Application</h4></div>
        <div class="card-body">
            <form method="POST" action="{{ route('employee.leaves.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Leave Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                            <option value="casual" {{ old('type') == 'casual' ? 'selected' : '' }}>Casual Leave</option>
                            <option value="sick" {{ old('type') == 'sick' ? 'selected' : '' }}>Sick Leave</option>
                            <option value="earned" {{ old('type') == 'earned' ? 'selected' : '' }}>Earned Leave</option>
                            <option value="emergency" {{ old('type') == 'emergency' ? 'selected' : '' }}>Emergency Leave</option>
                            <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">From Date <span class="text-danger">*</span></label>
                        <input type="date" name="from_date" class="form-control @error('from_date') is-invalid @enderror" value="{{ old('from_date') }}" min="{{ date('Y-m-d') }}" required>
                        @error('from_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">To Date <span class="text-danger">*</span></label>
                        <input type="date" name="to_date" class="form-control @error('to_date') is-invalid @enderror" value="{{ old('to_date') }}" min="{{ date('Y-m-d') }}" required>
                        @error('to_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Reason <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control @error('reason') is-invalid @enderror" rows="4" required placeholder="Please provide a reason for your leave...">{{ old('reason') }}</textarea>
                    @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Submit Leave Request</button>
                    <a href="{{ route('employee.leaves.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
