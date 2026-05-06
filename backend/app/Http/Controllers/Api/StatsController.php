<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Services\StatsService;

class StatsController extends Controller
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
    public function index()
    {
        try {
            $stats = $this->statsService->getStats();

            return response()->json($stats);

        } catch (\Throwable $e) {

            // IMPORTANT:
            // Never expose internal errors to client
            // but always log them
            \Log::error('Stats fetch failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to fetch stats'
            ], 500);
        }
    }
}
