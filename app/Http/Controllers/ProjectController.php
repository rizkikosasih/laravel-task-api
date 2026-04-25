<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ProjectService;

class ProjectController extends Controller
{
    public function __construct(protected ProjectService $service) {}

    public function index()
    {
        return $this->service->getAll();
    }

    public function store(Request $request)
    {
        return $this->service->create($request->all(), $request->user()->id);
    }

    public function show($id)
    {
        return $this->service->getById($id);
    }

    public function update(Request $request, $id)
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id)
    {
        return $this->service->delete($id);
    }
}
