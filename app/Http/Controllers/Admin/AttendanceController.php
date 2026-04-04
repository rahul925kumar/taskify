<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', today()->toDateString());
        $employeeId = $request->get('employee_id');

        $query = Attendance::with('user')->whereDate('date', $date);

        if ($employeeId) {
            $query->where('user_id', $employeeId);
        }

        $attendances = $query->latest('login_at')->paginate(20)->withQueryString();
        $employees = User::where('is_admin', false)->orderBy('name')->get();

        return view('admin.attendance.index', compact('attendances', 'employees', 'date', 'employeeId'));
    }
}
