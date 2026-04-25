<?php

namespace App\Services;

use App\Repositories\Contracts\TaskRepositoryInterface;

class TaskService
{
    public function __construct(protected TaskRepositoryInterface $repo) {}

    public function list($filters)
    {
        return $this->repo->all($filters);
    }

    public function getById($id)
    {
        return $this->repo->find($id);
    }

    public function create($data)
    {
        $data['status'] = $data['status'] ?? 'todo';
        return $this->repo->create($data);
    }

    public function update($id, $data)
    {
        return $this->repo->update($id, $data);
    }

    public function updateStatus($id, $status)
    {
        // workflow guard
        $task = $this->repo->find($id);

        if ($task->status === 'done' && $status !== 'done') {
            throw new \Exception('Done task cannot be reverted');
        }

        return $this->repo->update($id, ['status' => $status]);
    }

    public function delete($id)
    {
        return $this->repo->delete($id);
    }
}
