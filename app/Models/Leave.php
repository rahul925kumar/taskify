<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'delegated_to', 'from_date', 'to_date', 'type', 'reason', 'status', 'admin_remarks',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function delegate()
    {
        return $this->belongsTo(User::class, 'delegated_to');
    }

    public function isActive(): bool
    {
        return $this->status === 'approved'
            && $this->from_date->lte(today())
            && $this->to_date->gte(today());
    }

    public function totalDays(): int
    {
        return $this->from_date->diffInDays($this->to_date) + 1;
    }
}
