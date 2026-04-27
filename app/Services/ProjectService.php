<?php

namespace App\Services;

use App\Models\Project;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ProjectService
{
    public function __construct(protected ProjectRepositoryInterface $repo) {}

    public function getProjects(array $filters): LengthAwarePaginator
    {
        if (Gate::denies('viewAny', Project::class)) {
            throw new AuthorizationException('You do not have access to any projects.');
        }

        return $this->repo->paginate($filters);
    }

    public function getById(Project $project): Project
    {
        if (Gate::denies('view', $project)) {
            throw new AuthorizationException('You are not involved in this project details.');
        }

        return $project->load(['user:id,name'])->loadCount('tasks');
    }

    public function create(array $data): Project
    {
        if (Gate::denies('create', Project::class)) {
            throw new AuthorizationException('Only administrators can initialize new projects.');
        }

        return $this->repo->create($data, Auth::id());
    }

    public function update(Project $project, array $data): Project
    {
        if (Gate::denies('update', $project)) {
            throw new AuthorizationException(
                'Modification of project structure is restricted to administrators.',
            );
        }

        return $this->repo->update($project, $data);
    }

    public function delete(Project $project): bool
    {
        if (Gate::denies('delete', $project)) {
            throw new AuthorizationException(
                'Modification of project structure is restricted to administrators.',
            );
        }

        return $this->repo->delete($project);
    }
}
