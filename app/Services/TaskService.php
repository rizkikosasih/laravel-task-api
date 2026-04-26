<?php

namespace App\Services;

use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Enums\TaskStatus;
use App\Exceptions\BusinessException;
use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskService
{
    public function __construct(protected TaskRepositoryInterface $repo) {}

    public function getTasks($filters): LengthAwarePaginator
    {
        return $this->repo->paginate($filters);
    }

    public function getById($id): Task
    {
        return $this->repo->find($id);
    }

    public function create($data): Task
    {
        $data['status'] = $data['status'] ?? 'todo';
        return $this->repo->create($data);
    }

    public function update(Task $task, $data): Task
    {
        return $this->repo->update($task, $data);
    }

    public function updateStatus(Task $task, $status): Task
    {
        $newStatus = TaskStatus::from($status);

        if ($task->status->isDone() && !$newStatus->isDone()) {
            throw new BusinessException(
                message: 'Invalid Task Transition',
                errors: [
                    'status' => 'Done tasks are locked and cannot be reverted to a previous state.',
                ],
            );
        }

        if ($task->status === $newStatus) {
            return $task;
        }

        return $this->repo->update($task, ['status' => $newStatus]);
    }

    public function delete(Task $task): bool
    {
        return $this->repo->delete($task);
    }
}
