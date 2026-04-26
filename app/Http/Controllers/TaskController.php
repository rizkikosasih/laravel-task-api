<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Task\IndexTaskRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskStatusRequest;
use App\Http\Resources\TaskResource;
use App\Services\TaskService;

class TaskController extends Controller
{
    public function __construct(protected TaskService $service) {}

    public function index(IndexTaskRequest $request)
    {
        $tasks = $this->service->getTasks($request->validated());

        return ApiResponse::paginated(
            $tasks,
            TaskResource::class,
            'Task list retrieved successfully',
        );
    }

    public function show($id)
    {
        $task = $this->service->getById($id);

        return ApiResponse::success(new TaskResource($task), 'Task retrieved successfully');
    }

    public function store(StoreTaskRequest $request)
    {
        $task = $this->service->create($request->validated());

        return ApiResponse::success(new TaskResource($task), 'Task created successfully', 201);
    }

    public function update(StoreTaskRequest $request, $id)
    {
        $task = $this->service->update($id, $request->validated());

        return ApiResponse::success(new TaskResource($task), 'Task updated successfully');
    }

    public function updateStatus(UpdateTaskStatusRequest $request, $id)
    {
        $status = $request->validated()['status'];

        $task = $this->service->updateStatus($id, $status);

        return ApiResponse::success(new TaskResource($task), 'Task status updated successfully');
    }

    public function destroy($id)
    {
        $this->service->delete($id);

        return ApiResponse::success(null, 'Task deleted successfully');
    }
}
