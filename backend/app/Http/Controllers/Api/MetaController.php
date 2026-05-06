<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MetaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class MetaController extends Controller
{
    public function __construct(
        protected MetaService $metaService
    ) {
    }

    /**
     * Return frontend metadata.
     *
     * WHY:
     * Frontend needs roles and permissions for forms,
     * buttons and conditional UI rendering.
     */
    public function index(): JsonResponse
    {
        try {
            return response()->json([
                'data' => $this->metaService->getMeta(),
            ]);
        } catch (Throwable $exception) {
            Log::error('MetaController::index failed', [
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to load metadata',
            ], 500);
        }
    }
}
