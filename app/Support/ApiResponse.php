<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function ok(mixed $data, int $status = 200, array $headers = []): JsonResponse
    {
        return response()->json($data, $status, $headers);
    }

    protected function created(mixed $data): JsonResponse
    {
        return response()->json($data, 201);
    }

    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    protected function error(string $message, int $status = 400, ?array $errors = null, ?string $code = null): JsonResponse
    {
        $payload = ['message' => $message];
        if ($code) $payload['code'] = $code;
        if ($errors !== null) $payload['errors'] = $errors;
        return response()->json($payload, $status);
    }
}
