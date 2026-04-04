@extends('layouts.auth')
@section('title', 'Employee Login')
@section('content')
    <h4>Employee Login</h4>
    <p class="subtitle">Enter your email to receive an OTP</p>

    <form method="POST" action="{{ route('employee.request-otp') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus placeholder="your@email.com">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-success btn-lg w-100 mt-2">Send OTP</button>
    </form>

    <div class="text-center mt-4">
        <a href="{{ route('admin.login') }}" class="text-decoration-none">&larr; Login as Admin</a>
    </div>
@endsection
