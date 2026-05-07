<?php

namespace App\Http\Controllers\Api;

use App\Services\MetaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class MetaController extends BaseController
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
            return $this->successResponse(
                $this->metaService->getMeta(),
                'Metadata fetched'
            );
        } catch (Throwable $exception) {
            Log::error('MetaController::index failed', [
                'error' => $exception->getMessage(),
            ]);

            return $this->errorResponse('Failed to load metadata', null, 500);
        }
    }
}
