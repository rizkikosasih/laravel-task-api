<?php

namespace App\Repositories\Contracts;

interface CommentRepositoryInterface
{
    public function getByTaskId($taskId);

    public function create(array $data);

    public function delete($id);
}
