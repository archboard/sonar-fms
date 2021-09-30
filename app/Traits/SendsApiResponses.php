<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait SendsApiResponses
{
    protected function success(string $message = ''): JsonResponse
    {
        return response()
            ->json([
                'level' => 'success',
                'message' => $message
            ]);
    }

    protected function error(string $message = ''): JsonResponse
    {
        return response()
            ->json([
                'level' => 'error',
                'message' => $message
            ]);
    }
}
