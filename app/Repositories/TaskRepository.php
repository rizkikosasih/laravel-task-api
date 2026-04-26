<?php

namespace App\Repositories;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;

class TaskRepository implements TaskRepositoryInterface
{
    public function paginate(array $filters)
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
            ->with(['project:id,name', 'user:id,name'])
            ->latest()
            ->paginate($filters['per_page'] ?? 10);
    }

    public function find(int $id)
    {
        return Task::with(['project:id,name', 'user:id,name'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return Task::with(['project:id,name', 'user:id,name'])->create($data);
    }

    public function update(int $id, array $data)
    {
        $task = Task::with(['project:id,name', 'user:id,name'])->findOrFail($id);
        $task->update($data);
        return $task;
    }

    public function delete(int $id)
    {
        return Task::findOrFail($id)->delete();
    }
}
