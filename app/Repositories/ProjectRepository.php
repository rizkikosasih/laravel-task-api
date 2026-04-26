<?php

namespace App\Repositories;

use App\Models\Project;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProjectRepository implements ProjectRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        return Project::query()
            ->when(
                $filters['search'] ?? null,
                fn($q, $search) => $q->where('name', 'like', "%{$search}%"),
            )
            ->when($filters['created_by'] ?? null, fn($q, $user) => $q->where('created_by', $user))
            ->withCount('tasks')
            ->with(['user:id,name'])
            ->latest()
            ->paginate($filters['per_page'] ?? 10);
    }

    public function find($id): Project
    {
        return Project::with(['user:id,name'])
            ->withCount('tasks')
            ->findOrFail($id);
    }

    public function create(array $data): Project
    {
        return Project::with(['user:id,name'])
            ->withCount('tasks')
            ->create($data);
    }

    public function update(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->fresh(['user:id,name'])->loadCount('tasks');
    }

    public function delete(Project $project): bool
    {
        return $project->delete();
    }
}
