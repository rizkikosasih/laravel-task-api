<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use Illuminate\Http\Request;
use App\Services\ProjectService;

class ProjectController extends Controller
{
    public function __construct(protected ProjectService $service) {}

    public function index(Request $request)
    {
        return $this->service->list($request->only(['search', 'created_by']));
    }

    public function store(StoreProjectRequest $request)
    {
        return $this->service->create($request->validate(), $request->user()->id);
    }

    public function show($id)
    {
        return $this->service->getById($id);
    }

    public function update(UpdateProjectRequest $request, $id)
    {
        return $this->service->update($id, $request->validate());
    }

    public function destroy($id)
    {
        return $this->service->delete($id);
    }
}
