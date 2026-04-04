@extends('layouts.auth')
@section('title', 'Admin Login')
@section('content')
    <h4>Admin Login</h4>
    <p class="subtitle">Sign in with your admin credentials</p>

    <form method="POST" action="{{ route('admin.login.submit') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus placeholder="admin@example.com">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required placeholder="••••••••">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-primary btn-lg w-100 mt-2">Sign In</button>
    </form>

    <div class="text-center mt-4">
        <a href="{{ route('employee.login') }}" class="text-decoration-none">Login as Employee &rarr;</a>
    </div>
@endsection
