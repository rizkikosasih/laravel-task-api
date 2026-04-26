<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\IndexProjectRequest;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Services\ProjectService;

class ProjectController extends Controller
{
    public function __construct(protected ProjectService $service) {}

    public function index(IndexProjectRequest $request)
    {
        $projects = $this->service->getProjects($request->validated());
        return ProjectResource::collection($projects);
    }

    public function store(StoreProjectRequest $request)
    {
        $project = $this->service->create($request->validated(), $request->user()->id);
        return ProjectResource::make($project);
    }

    public function show($id)
    {
        $project = $this->service->getById($id);

        return ProjectResource::make($project);
    }

    public function update(UpdateProjectRequest $request, $id)
    {
        $project = $this->service->update($id, $request->validated());

        return ProjectResource::make($project);
    }

    public function destroy($id)
    {
        return $this->service->delete($id);
    }
}
