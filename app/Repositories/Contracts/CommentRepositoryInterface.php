<?php

namespace App\Repositories\Contracts;

interface CommentRepositoryInterface
{
    public function getByTask($taskId);

    public function create(array $data);

    public function delete($id);
}
