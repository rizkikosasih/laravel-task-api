<?php

namespace App\Repositories\Contracts;

use App\Models\Comment;
use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CommentRepositoryInterface
{
    public function paginateByTaskId(Task $task): LengthAwarePaginator;
    public function create(array $data): Comment;
    public function delete(Comment $comment): bool;
}
