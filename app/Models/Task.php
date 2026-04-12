<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'description', 'project_id', 'assigned_to', 'originally_assigned_to', 'created_by',
        'start_date', 'due_date', 'status', 'priority', 'type', 'category', 'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function originalAssignee()
    {
        return $this->belongsTo(User::class, 'originally_assigned_to')->withTrashed();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class)->whereNull('parent_id')->latest();
    }

    public function allComments()
    {
        return $this->hasMany(TaskComment::class)->latest();
    }

    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function histories()
    {
        return $this->hasMany(TaskHistory::class)->latest();
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && ! in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Full calendar days elapsed since the task was created (0 = created today).
     */
    public function daysSinceCreation(): int
    {
        $created = $this->created_at->copy()->startOfDay();
        $today = now()->startOfDay();

        return max(0, (int) $created->diffInDays($today));
    }
}
