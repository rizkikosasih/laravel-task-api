<?php

namespace App\Services;

use App\Models\Task;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Contracts\CommentRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

class CommentService
{
    public function __construct(protected CommentRepositoryInterface $repo) {}

    public function getTaskComments(Task $task): LengthAwarePaginator
    {
        if (Gate::denies('view', $task)) {
            throw new AuthorizationException('Access denied to comments for this task.');
        }
        return $this->repo->paginateByTaskId($task);
    }

    public function create(Task $task, string $message): Comment
    {
        if (Gate::denies('view', $task)) {
            throw new AuthorizationException(
                'You cannot comment on a task you are not involved in.',
            );
        }

        return $this->repo->create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'message' => $message,
        ]);
    }

    public function delete(Comment $comment): bool
    {
        if (Gate::denies('delete', $comment)) {
            throw new AuthorizationException('You can only delete your own comments.');
        }

        return $this->repo->delete($comment);
    }
}
