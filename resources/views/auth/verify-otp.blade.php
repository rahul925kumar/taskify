@extends('layouts.auth')
@section('title', 'Verify OTP')
@section('content')
    <h4>Verify OTP</h4>
    <p class="subtitle">Enter the OTP sent to admin for <strong>{{ $email }}</strong></p>

    <form method="POST" action="{{ route('employee.verify-otp') }}">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">
        <div class="mb-3">
            <label class="form-label">OTP Code</label>
            <input type="text" name="otp" class="form-control text-center @error('otp') is-invalid @enderror" maxlength="6" required autofocus placeholder="000000" style="font-size: 24px; letter-spacing: 8px; font-weight: 600;">
            @error('otp')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-success btn-lg w-100 mt-2">Verify & Login</button>
    </form>

    <div class="text-center mt-4">
        <a href="{{ route('employee.login') }}" class="text-decoration-none">&larr; Back to Employee Login</a>
    </div>
@endsection
