<?php

namespace App\CoreService;

use Illuminate\Http\JsonResponse;

class CoreResponse
{
    public static function ok($data, $message = "Success"): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'errors'  => null
        ], 200);
    }

    public static function fail($exception): JsonResponse
    {
        $statusCode = method_exists($exception, 'getStatusCode') 
            ? $exception->getStatusCode() 
            : 400;

        return response()->json([
            'success' => false,
            'message' => $exception->getMessage(),
            'data'    => null,
            'errors'  => null
        ], $statusCode);
    }

    public static function error($message = "Internal Server Error", $statusCode = 500): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'errors'  => true
        ], $statusCode);
    }
}