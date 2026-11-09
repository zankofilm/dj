<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController
{
    public function __invoke(): JsonResponse
    {
        try {
            DB::select('SELECT 1');

            return response()->json([
                'status' => 'ok',
                'application' => config('app.name'),
                'database' => 'reachable',
                'timestamp' => now()->toISOString(),
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'status' => 'degraded',
                'database' => 'unavailable',
            ], 503);
        }
    }
}
