<?php

namespace App\Http\Controllers\Api;

use App\Services\StatsService;
use Illuminate\Http\JsonResponse;

class StatsController extends BaseController
{
    /**
     * Stats service instance.
     *
     * Handles business logic for application statistics.
     */
    protected StatsService $statsService;

    /**
     * Create a new controller instance.
     *
     * @param StatsService $statsService
     */
    public function __construct(StatsService $statsService)
    {
        $this->statsService = $statsService;
    }


    /**
     * Display basic application statistics.
     *
     * Returns mocked statistical data used
     * for dashboard representation.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $stats = $this->statsService->getStats();

            return $this->successResponse($stats, 'Stats fetched');

        } catch (\Throwable $e) {

            // IMPORTANT:
            // Never expose internal errors to client
            // but always log them
            \Log::error('Stats fetch failed', [
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse('Failed to fetch stats', null, 500);
        }
    }
}
