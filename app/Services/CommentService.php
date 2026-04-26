<?php

namespace App\Services;

use App\Models\Task;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Contracts\CommentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CommentService
{
    public function __construct(protected CommentRepositoryInterface $repo) {}

    public function getTaskComments(Task $task): LengthAwarePaginator
    {
        return $this->repo->paginateByTaskId($task);
    }

    public function create(Task $task, string $message): Comment
    {
        return $this->repo->create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'message' => $message,
        ]);
    }

    public function delete(Comment $comment): bool
    {
        return $this->repo->delete($comment);
    }
}
