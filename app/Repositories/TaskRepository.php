<?php

namespace App\Repositories;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class TaskRepository implements TaskRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $user = Auth::user();

        return Task::query()
            ->when($user->hasRole('member'), function ($q) use ($user) {
                return $q->where('assigned_to', $user->id);
            })
            ->when(
                $filters['search'] ?? null,
                fn($q, $search) => $q->where('title', 'like', "%{$search}%"),
            )
            ->when(
                $filters['project_id'] ?? null,
                fn($q, $projectId) => $q->where('project_id', $projectId),
            )
            ->when($filters['status'] ?? null, fn($q, $status) => $q->where('status', $status))
            ->when(
                $filters['assigned_to'] ?? null,
                fn($q, $assignedTo) => $q->where('assigned_to', $assignedTo),
            )
            ->with(['project:id,name', 'user:id,name'])
            ->latest()
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    public function find(int $id): Task
    {
        return Task::with(['project:id,name', 'user:id,name'])->findOrFail($id);
    }

    public function create(array $data): Task
    {
        return Task::with(['project:id,name', 'user:id,name'])->create($data);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);

        return $task->fresh(['project:id,name', 'user:id,name']);
    }

    public function delete(Task $task): bool
    {
        return $task->delete();
    }
}
