<?php

namespace App\Http\Controllers;

use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(protected TaskService $service) {}

    public function index(Request $request)
    {
        return $this->service->list($request->all());
    }

    public function show($id)
    {
        return $this->service->getById($id);
    }

    public function store(Request $request)
    {
        return $this->service->create($request->all());
    }

    public function update(Request $request, $id)
    {
        return $this->service->update($id, $request->all());
    }

    public function updateStatus(Request $request, $id)
    {
        return $this->service->updateStatus($id, $request->status);
    }

    public function destroy($id)
    {
        return $this->service->delete($id);
    }
}
