<?php

namespace App\Services;

use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Enums\TaskStatus;
use App\Exceptions\BusinessException;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

class TaskService
{
    public function __construct(protected TaskRepositoryInterface $repo) {}

    public function getTasks($filters): LengthAwarePaginator
    {
        if (Gate::denies('viewAny', Task::class)) {
            throw new AuthorizationException('Access denied to task list.');
        }

        return $this->repo->paginate($filters);
    }

    public function getById(Task $task): Task
    {
        if (Gate::denies('view', $task)) {
            throw new AuthorizationException('Access denied to task details.');
        }

        return $task->load(['user:id,name', 'project:id,name']);
    }

    public function create(array $data): Task
    {
        $project = Project::findOrFail($data['project_id']);

        if (Gate::denies('create', [Task::class, $project])) {
            throw new AuthorizationException('Tasks can only be added by the project owner.');
        }

        $data['status'] = isset($data['status'])
            ? TaskStatus::from($data['status'])
            : TaskStatus::TODO;

        return $this->repo->create($data);
    }

    public function update(Task $task, array $data): Task
    {
        if (Gate::denies('update', $task)) {
            throw new AuthorizationException('Only the project owner can update task details.');
        }

        return $this->repo->update($task, $data);
    }

    public function updateStatus(Task $task, string $status): Task
    {
        if (Gate::denies('updateStatus', $task)) {
            throw new AuthorizationException('You are not authorized to update this task status.');
        }

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
        if (Gate::denies('delete', $task)) {
            throw new AuthorizationException('Task removal is restricted to the project owner.');
        }

        return $this->repo->delete($task);
    }
}
