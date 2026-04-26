<?php

namespace App\Repositories;

use App\Models\Comment;
use App\Repositories\Contracts\CommentRepositoryInterface;

class CommentRepository implements CommentRepositoryInterface
{
    public function getByTaskId($taskId)
    {
        return Comment::where('task_id', $taskId)->with('user:id,name')->latest()->get();
    }

    public function create(array $data)
    {
        return Comment::with('user:id,name')->create($data);
    }

    public function delete($id)
    {
        $comment = Comment::findOrFail($id);
        return $comment->delete();
    }
}
