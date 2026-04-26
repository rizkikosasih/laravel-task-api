<?php

namespace App\Services;

use App\Models\Project;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProjectService
{
    public function __construct(protected ProjectRepositoryInterface $repo) {}

    public function getProjects(array $filters): LengthAwarePaginator
    {
        return $this->repo->paginate($filters);
    }

    public function getById($id): Project
    {
        return $this->repo->find($id);
    }

    public function create(array $data, $userId): Project
    {
        $data['created_by'] = $userId;

        return $this->repo->create($data);
    }

    public function update(Project $project, array $data): Project
    {
        return $this->repo->update($project, $data);
    }

    public function delete(Project $project): bool
    {
        return $this->repo->delete($project);
    }
}
