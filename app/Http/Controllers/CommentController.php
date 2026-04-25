<?php

namespace App\Http\Controllers;

use App\Services\CommentService;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(protected CommentService $service) {}

    public function index($taskId)
    {
        return $this->service->listByTask($taskId);
    }

    public function store(Request $request, $taskId)
    {
        return $this->service->create($taskId, $request->message);
    }

    public function destroy($id)
    {
        return $this->service->delete($id);
    }
}
