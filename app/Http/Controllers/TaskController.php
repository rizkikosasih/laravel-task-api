<?php

namespace App\Http\Controllers;

use App\Http\Requests\Task\IndexTaskRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskStatusRequest;
use App\Services\TaskService;

class TaskController extends Controller
{
    public function __construct(protected TaskService $service) {}

    public function index(IndexTaskRequest $request)
    {
        return $this->service->list($request->validated());
    }

    public function show($id)
    {
        return $this->service->getById($id);
    }

    public function store(StoreTaskRequest $request)
    {
        return $this->service->create($request->validated());
    }

    public function update(StoreTaskRequest $request, $id)
    {
        return $this->service->update($id, $request->validated());
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
