<?php

namespace App\Repositories;

use App\Models\Comment;
use App\Models\Task;
use App\Repositories\Contracts\CommentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CommentRepository implements CommentRepositoryInterface
{
    public function paginateByTaskId(Task $task, $perPage = 5): LengthAwarePaginator
    {
        return $task
            ->comments()
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): Comment
    {
        return Comment::with('user:id,name')->create($data);
    }

    public function delete(Comment $comment): bool
    {
        return $comment->delete();
    }
}
