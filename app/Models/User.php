<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'address', 'role', 'image',
        'is_admin', 'otp', 'otp_expires_at', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token', 'otp',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'is_admin' => 'boolean',
    ];

    public function setPasswordAttribute($value): void
    {
        if ($value === null) {
            $this->attributes['password'] = null;

            return;
        }

        if (is_string($value) && password_get_info($value)['algo'] !== 0) {
            $this->attributes['password'] = $value;

            return;
        }

        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Generate a new employee login OTP and persist it. Returns the plain OTP for admin handoff.
     */
    public function issueFreshLoginOtp(): string
    {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes((int) config('constants.otp_validity_minutes', 10)),
        ]);

        return $otp;
    }

    /**
     * All users that can be selected as task assignees (employees and admins).
     *
     * @return Collection<int, User>
     */
    public static function assignableForTasks()
    {
        return static::query()->orderByDesc('is_admin')->orderBy('name')->get();
    }

    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function todayAttendance()
    {
        return $this->hasOne(Attendance::class)->whereDate('date', today())->latest();
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function isOnLeaveToday(): bool
    {
        return $this->leaves()
            ->where('status', 'approved')
            ->where('from_date', '<=', today())
            ->where('to_date', '>=', today())
            ->exists();
    }

    public function activeLeave()
    {
        return $this->leaves()
            ->where('status', 'approved')
            ->where('from_date', '<=', today())
            ->where('to_date', '>=', today())
            ->first();
    }

    /**
     * Whether this user has an approved leave overlapping the given date range (inclusive).
     */
    public function hasApprovedLeaveOverlapping(CarbonInterface $from, CarbonInterface $to): bool
    {
        return $this->leaves()
            ->where('status', 'approved')
            ->where('from_date', '<=', $to->toDateString())
            ->where('to_date', '>=', $from->toDateString())
            ->exists();
    }
}
