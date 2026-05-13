<?php

namespace App\Http\Controllers\Api;

use App\Events\SystemNotificationEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RealtimeController extends BaseController
{
    /**
     * Development-only realtime smoke-test trigger.
     *
     * WHY:
     * Provides one deterministic endpoint to validate end-to-end websocket
     * wiring (Reverb -> Echo client) without introducing business workflow
     * coupling during foundation phase.
     */
    public function notify(Request $request)
    {
        $payload = $request->validate([
            'type' => ['nullable', 'string', 'max:40'],
            'title' => ['nullable', 'string', 'max:120'],
            'message' => ['nullable', 'string', 'max:300'],
        ]);

        event(new SystemNotificationEvent(
            type: $payload['type'] ?? 'info',
            title: $payload['title'] ?? 'Realtime event',
            message: $payload['message'] ?? 'System notification delivered.',
            createdAt: Carbon::now()->toIso8601String(),
        ));

        return $this->successResponse([
            'dispatched' => true,
        ], 'Realtime notification dispatched');
    }
}

