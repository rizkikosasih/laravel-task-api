<?php

namespace App\Repositories;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;

class TaskRepository implements TaskRepositoryInterface
{
    public function all(array $filters)
    {
        return Task::query()
            ->when(isset($filters['project_id']), function ($q) use ($filters) {
                $q->where('project_id', $filters['project_id']);
            })
            ->when(isset($filters['status']), function ($q) use ($filters) {
                $q->where('status', $filters['status']);
            })
            ->when(isset($filters['assigned_to']), function ($q) use ($filters) {
                $q->where('assigned_to', $filters['assigned_to']);
            })
            ->latest()
            ->paginate(10);
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
