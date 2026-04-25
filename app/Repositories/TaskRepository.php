<?php

namespace App\Repositories;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;

class TaskRepository implements TaskRepositoryInterface
{
    public function all(array $filters)
    {
        return Task::query()
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
            ->latest()
            ->paginate($filters['per_page'] ?? 10);
    }

    public function find(int $id)
    {
        return Task::findOrFail($id);
    }

    public function create(array $data)
    {
        return Task::create($data);
    }

    public function update(int $id, array $data)
    {
        $task = Task::findOrFail($id);
        $task->update($data);
        return $task;
    }

    public function delete(int $id)
    {
        return Task::findOrFail($id)->delete();
    }
}
