<?php

namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponse
{
    public static function success($data = null, $message = 'Success', $code = 200, $meta = null)
    {
        return response()->json(
            [
                'success' => true,
                'message' => $message,
                'data' => $data,
                'meta' => $meta,
            ],
            $code,
        );
    }

    public static function paginated(
        LengthAwarePaginator $paginator,
        $resource,
        $message = 'Data retrieved successfully',
    ) {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $resource::collection($paginator),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'has_next_page' => $paginator->hasMorePages(),
            ],
        ]);
    }

    public static function error($message = 'Error', $errors = null, $code = 400)
    {
        return response()->json(
            [
                'success' => false,
                'message' => $message,
                'errors' => $errors,
            ],
            $code,
        );
    }
}
