<?php

namespace App\Services;

use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Enums\TaskStatus;
use Illuminate\Validation\ValidationException;

class TaskService
{
    public function __construct(protected TaskRepositoryInterface $repo) {}

    public function getTasks($filters)
    {
        return $this->repo->paginate($filters);
    }

    public function getById($id)
    {
        return $this->repo->find($id);
    }

    public function create($data)
    {
        $data['status'] = $data['status'] ?? 'todo';
        return $this->repo->create($data);
    }

    public function update($id, $data)
    {
        return $this->repo->update($id, $data);
    }

    public function updateStatus($id, $status)
    {
        // workflow guard
        $task = $this->repo->find($id);

        $newStatus = TaskStatus::from($status);

        if ($task->status->isDone() && !$newStatus->isDone()) {
            throw ValidationException::withMessages([
                'status' => 'Done task cannot be reverted',
            ]);
        }

        if ($task->status === $newStatus) {
            return $task;
        }

        return $this->repo->update($id, ['status' => $newStatus]);
    }

    public function delete($id)
    {
        return $this->repo->delete($id);
    }
}
