<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait SendsApiResponses
{
    protected function success(string $message = '', array $data = []): JsonResponse
    {
        return response()
            ->json([
                'level' => 'success',
                'message' => $message,
                'data' => $data,
            ]);
    }

    protected function error(string $message = '', array $data = []): JsonResponse
    {
        return response()
            ->json([
                'level' => 'error',
                'message' => $message,
                'data' => $data,
            ]);
    }
}
