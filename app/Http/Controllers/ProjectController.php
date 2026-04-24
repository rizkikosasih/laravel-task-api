<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ProjectService;

class ProjectController extends Controller
{
    public function __construct(protected ProjectService $projectService) {}

    public function index()
    {
        return $this->projectService->getAll();
    }

    public function store(Request $request)
    {
        return $this->projectService->create($request->all(), $request->user()->id);
    }

    public function show($id)
    {
        return $this->projectService->getById($id);
    }

    public function update(Request $request, $id)
    {
        return $this->projectService->update($id, $request->all());
    }

    public function destroy($id)
    {
        return $this->projectService->delete($id);
    }
}
