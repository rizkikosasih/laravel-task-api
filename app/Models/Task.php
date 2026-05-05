<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'status',
        'assigned_to',
        'due_date',
    ];

    protected $casts = [
        'status' => TaskStatus::class,
    ];

    /* Relations */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /* Scopes */
    public function scopeStatus($query, $status)
    {
        return $query->when($status, fn($q) => $q->where('status', $status));
    }

    public function scopeProject($query, $projectId)
    {
        return $query->when($projectId, fn($q) => $q->where('project_id', $projectId));
    }

    public function isDone(): bool
    {
        return $this->status === 'done';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isTodo(): bool
    {
        return $this->status === 'todo';
    }
}
