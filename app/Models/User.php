<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
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
