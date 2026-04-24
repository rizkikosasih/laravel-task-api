<?php

namespace App\Services;

use App\Repositories\Contracts\ProjectRepositoryInterface;

class ProjectService
{
    public function __construct(protected ProjectRepositoryInterface $projectRepository) {}

    public function getAll()
    {
        return $this->projectRepository->all();
    }

    public function getById($id)
    {
        return $this->projectRepository->find($id);
    }

    public function create(array $data, $userId)
    {
        $data['created_by'] = $userId;

        return $this->projectRepository->create($data);
    }

    public function update($id, array $data)
    {
        return $this->projectRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->projectRepository->delete($id);
    }
}
