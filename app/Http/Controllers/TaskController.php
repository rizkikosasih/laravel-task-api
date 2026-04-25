<?php

namespace App\Http\Controllers;

use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskStatusRequest;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(protected TaskService $service) {}

    public function index(Request $request)
    {
        return $this->service->list(
            $request->only(['search', 'project_id', 'assigned_to', 'status', 'per_page']),
        );
    }

    public function show($id)
    {
        return $this->service->getById($id);
    }

    public function store(StoreTaskRequest $request)
    {
        return $this->service->create($request->validate());
    }

    public function update(StoreTaskRequest $request, $id)
    {
        return $this->service->update($id, $request->validate());
    }

    public function updateStatus(UpdateTaskStatusRequest $request, $id)
    {
        $status = $request->validated()['status'];

        $task = $this->service->updateStatus($id, $status);

        return response()->json($task);
    }

    public function destroy($id)
    {
        return $this->service->delete($id);
    }
}
