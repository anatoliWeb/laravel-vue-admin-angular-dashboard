<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\MetaResource;
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
                (new MetaResource($this->metaService->getMeta()))->resolve(),
                dt('notifications.success')
            );
        } catch (Throwable $exception) {
            Log::error('MetaController::index failed', [
                'error' => $exception->getMessage(),
            ]);

            return $this->errorResponse(dt('notifications.error'), null, 500);
        }
    }
}
