<?php

namespace App\Http\Controllers;

use App\Http\Requests\Task\IndexTaskRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskStatusRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;

class TaskController extends Controller
{
    public function __construct(protected TaskService $service) {}

    public function index(IndexTaskRequest $request)
    {
        $tasks = $this->service->getTasks($request->validated());

        return TaskResource::collection($tasks);
    }

    public function show($id)
    {
        $task = $this->service->getById($id);

        return TaskResource::make($task);
    }

    public function store(StoreTaskRequest $request)
    {
        $task = $this->service->create($request->validated());

        return TaskResource::make($task);
    }

    public function update(StoreTaskRequest $request, $id)
    {
        $task = $this->service->update($id, $request->validated());

        return TaskResource::make($task);
    }

    public function updateStatus(UpdateTaskStatusRequest $request, $id)
    {
        $status = $request->validated()['status'];

        $task = $this->service->updateStatus($id, $status);

        return TaskResource::make($task);
    }

    public function destroy($id)
    {
        return $this->service->delete($id);
    }
}
