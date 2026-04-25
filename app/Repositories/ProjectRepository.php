<?php

namespace App\Repositories;

use App\Models\Project;
use App\Repositories\Contracts\ProjectRepositoryInterface;

class ProjectRepository implements ProjectRepositoryInterface
{
    public function all(array $filters)
    {
        return Project::query()
            ->when(
                $filters['search'] ?? null,
                fn($q, $search) => $q->where('name', 'like', "%{$search}%"),
            )
            ->when($filters['created_by'] ?? null, fn($q, $user) => $q->where('created_by', $user))
            ->latest()
            ->paginate($filters['per_page'] ?? 10);
    }

    public function find($id)
    {
        return Project::findOrFail($id);
    }

    public function create(array $data)
    {
        return Project::create($data);
    }

    public function update($id, array $data)
    {
        $project = Project::findOrFail($id);
        $project->update($data);
        return $project;
    }

    public function delete($id)
    {
        return Project::findOrFail($id)->delete();
    }
}
