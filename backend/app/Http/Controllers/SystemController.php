<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class SystemController extends Controller
{
    public function status(): JsonResponse
    {
        return response()->json([
            'name' => config('app.name'),
            'status' => 'ok',
        ]);
    }

    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'app' => config('app.name'),
            'version' => config('app.version'),
            'environment' => app()->environment(),
        ]);
    }
}
