<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showAdminLoginForm()
    {
        return view('auth.admin-login');
    }

    public function adminLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'is_admin' => true])) {
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        return back()->with('error', 'Invalid credentials or you are not an admin.');
    }

    public function showEmployeeLoginForm()
    {
        return view('auth.employee-login');
    }

    public function employeeRequestOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->where('is_admin', false)->first();

        if (!$user) {
            return back()->with('error', 'No employee found with this email.');
        }

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(config('constants.otp_validity_minutes')),
        ]);

        $adminEmail = config('constants.admin_email');

        try {
            \Illuminate\Support\Facades\Mail::to($adminEmail)->send(
                new \App\Mail\OtpMail($otp, $user->name, $user->email)
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('OTP Mail failed: ' . $e->getMessage());
        }

        return redirect()->route('employee.verify-otp.form', ['email' => $request->email])
            ->with('success', 'OTP has been sent to admin email. Please ask admin for the OTP.');
    }

    public function showVerifyOtpForm(Request $request)
    {
        return view('auth.verify-otp', ['email' => $request->query('email')]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)
            ->where('is_admin', false)
            ->where('otp', $request->otp)
            ->where('otp_expires_at', '>', now())
            ->first();

        if (!$user) {
            return back()->with('error', 'Invalid or expired OTP.');
        }

        $user->update(['otp' => null, 'otp_expires_at' => null]);

        Auth::login($user);
        $request->session()->regenerate();

        \App\Models\Attendance::create([
            'user_id' => $user->id,
            'login_at' => now(),
            'date' => today(),
        ]);

        return redirect()->route('employee.dashboard');
    }

    public function logout(Request $request)
    {
        $user = auth()->user();

        if ($user && !$user->is_admin) {
            $attendance = \App\Models\Attendance::where('user_id', $user->id)
                ->whereDate('date', today())
                ->whereNull('logout_at')
                ->latest()
                ->first();

            if ($attendance) {
                $attendance->update(['logout_at' => now()]);
            }
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
