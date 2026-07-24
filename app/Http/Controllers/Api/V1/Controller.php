<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\JsonResponse;

abstract class Controller extends BaseController
{
    /**
     * Success response
     */
    protected function successResponse(mixed $data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'error' => null,
            'meta' => [],
            'message' => $message,
        ], $code);
    }

    /**
     * Error response
     */
    protected function errorResponse(string $message, string $code = 'ERROR', array $details = [], int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'data' => null,
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details,
            ],
            'meta' => [],
        ], $statusCode);
    }

    /**
     * Validation error response
     */
    protected function validationErrorResponse(array $errors): JsonResponse
    {
        return $this->errorResponse('Validation failed', 'VALIDATION_ERROR', $errors, 422);
    }

    /**
     * Not found response
     */
    protected function notFoundResponse(string $resource = 'Resource'): JsonResponse
    {
        return $this->errorResponse("{$resource} not found", 'NOT_FOUND', [], 404);
    }

    /**
     * Unauthorized response
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, 'UNAUTHORIZED', [], 401);
    }

    /**
     * Forbidden response
     */
    protected function forbiddenResponse(string $message = 'Forbidden'): JsonResponse
    {
        return $this->errorResponse($message, 'FORBIDDEN', [], 403);
    }
}
