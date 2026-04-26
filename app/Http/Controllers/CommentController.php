<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Services\CommentService;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(protected CommentService $service) {}

    public function index(Request $request, $taskId)
    {
        $perPage = $request->get('per_page', 10);

        $comments = $this->service->getTaskComments($taskId, $perPage);

        return ApiResponse::paginated(
            $comments,
            CommentResource::class,
            'Comment list retrieved successfully',
        );
    }

    public function store(StoreCommentRequest $request, $taskId)
    {
        $comment = $this->service->create($taskId, $request->validated()['message']);

        return ApiResponse::success(
            new CommentResource($comment),
            'Comment created successfully',
            201,
        );
    }

    public function destroy($id)
    {
        $this->service->delete($id);

        return ApiResponse::success(null, 'Comment deleted successfully');
    }
}
