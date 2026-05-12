<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Task;
use App\Models\Project;

class TaskPolicy
{
    /**
     * Admin boleh lihat list task
     * Member boleh lihat list task jika dia terlibat di dalamnya
     * */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view task');
    }

    /**
     * Admin boleh lihat task detail
     * Member boleh lihat task jika dia terlibat di dalamnya
     */
    public function view(User $user, Task $task): bool
    {
        if ($user->hasPermissionTo('view project')) {
            return true;
        }

        return $user->id === $task->assigned_to;
    }

    /**
     * Hanya pemilik project
     */
    public function create(User $user, Project $project): bool
    {
        return $user->id === $project->created_by;
    }

    /**
     * Hanya pemilik project
     */
    public function update(User $user, Task $task): bool
    {
        return $user->id === $task->project->created_by;
    }

    /**
     * Pemilik project dan user yang ditugaskan
     */
    public function updateStatus(User $user, Task $task): bool
    {
        if ($user->id === $task->project->created_by) {
            return true;
        }

        return $user->id === $task->assigned_to;
    }

    /**
     * Hanya pemilik project
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->id === $task->project->created_by;
    }
}
