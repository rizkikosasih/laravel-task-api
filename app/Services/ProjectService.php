<?php

namespace App\Services;

use App\Repositories\Contracts\ProjectRepositoryInterface;

class ProjectService
{
    public function __construct(protected ProjectRepositoryInterface $repo) {}

    public function getAll()
    {
        return $this->repo->all();
    }

    public function getById($id)
    {
        return $this->repo->find($id);
    }

    public function create(array $data, $userId)
    {
        $data['created_by'] = $userId;

        return $this->repo->create($data);
    }

    public function update($id, array $data)
    {
        return $this->repo->update($id, $data);
    }

    public function delete($id)
    {
        return $this->repo->delete($id);
    }
}
