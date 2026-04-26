<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Services\CommentService;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(protected CommentService $service) {}

    public function index($taskId)
    {
        $comments = $this->service->getTaskComments($taskId);

        return CommentResource::collection($comments);
    }

    public function store(StoreCommentRequest $request, $taskId)
    {
        $comment = $this->service->create($taskId, $request->validated()['message']);

        return CommentResource::make($comment);
    }

    public function destroy($id)
    {
        return $this->service->delete($id);
    }
}
