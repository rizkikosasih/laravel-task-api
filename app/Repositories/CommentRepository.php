<?php

namespace App\Repositories;

use App\Models\Comment;
use App\Repositories\Contracts\CommentRepositoryInterface;

class CommentRepository implements CommentRepositoryInterface
{
    public function getByTask($taskId)
    {
        return Comment::where('task_id', $taskId)->with('user')->latest()->get();
    }

    public function create(array $data)
    {
        return Comment::create($data);
    }

    public function delete($id)
    {
        $comment = Comment::findOrFail($id);
        return $comment->delete();
    }
}
