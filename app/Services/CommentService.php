<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Repositories\Contracts\CommentRepositoryInterface;

class CommentService
{
    public function __construct(protected CommentRepositoryInterface $repo) {}

    public function getTaskComments($taskId)
    {
        return $this->repo->getByTaskId($taskId);
    }

    public function create($taskId, $message)
    {
        return $this->repo->create([
            'task_id' => $taskId,
            'user_id' => Auth::id(),
            'message' => $message,
        ]);
    }

    public function delete($id)
    {
        return $this->repo->delete($id);
    }
}
