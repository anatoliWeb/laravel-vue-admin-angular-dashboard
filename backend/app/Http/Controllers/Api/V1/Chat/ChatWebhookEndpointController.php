<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\StoreChatWebhookEndpointRequest;
use App\Http\Requests\Api\UpdateChatWebhookEndpointRequest;
use App\Http\Resources\Chat\ChatWebhookEndpointResource;
use App\Models\ChatWebhookEndpoint;
use App\Models\User;
use App\Services\Chat\ExternalChatTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ChatWebhookEndpointController extends BaseController
{
    public function __construct(
        protected ExternalChatTokenService $tokenService,
    ) {
    }

    public function index(): JsonResponse
    {
        $items = ChatWebhookEndpoint::query()
            ->latest('id')
            ->get()
            ->map(fn (ChatWebhookEndpoint $endpoint) => (new ChatWebhookEndpointResource($endpoint))->resolve())
            ->values()
            ->all();

        return $this->successResponse($items);
    }

    public function store(StoreChatWebhookEndpointRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();

        $plainToken = $this->tokenService->generatePlainToken();
        $tokenHash = $this->tokenService->hashToken($plainToken);
        $secret = Str::random(64);

        $endpoint = ChatWebhookEndpoint::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => $validated['name'],
            'url' => $validated['url'],
            'secret' => $secret,
            'events' => array_values(array_unique($validated['events'])),
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'status' => ((bool) ($validated['is_active'] ?? true)) ? 'active' : 'disabled',
            'created_by' => $user->id,
            'metadata' => [
                'token_hash' => $tokenHash,
                'token_hash_algo' => (string) config('chat.external_api.token_hash_algo', 'sha256'),
            ],
        ]);

        $payload = (new ChatWebhookEndpointResource($endpoint))->resolve();
        $payload['plain_token'] = $plainToken;

        return $this->successResponse($payload, 'Webhook endpoint created', 201);
    }

    public function update(UpdateChatWebhookEndpointRequest $request, ChatWebhookEndpoint $endpoint): JsonResponse
    {
        $validated = $request->validated();
        if (array_key_exists('is_active', $validated) && ! array_key_exists('status', $validated)) {
            $validated['status'] = (bool) $validated['is_active'] ? 'active' : 'disabled';
        }

        $endpoint->fill($validated);
        $endpoint->save();

        return $this->successResponse((new ChatWebhookEndpointResource($endpoint->fresh()))->resolve(), 'Webhook endpoint updated');
    }

    public function destroy(ChatWebhookEndpoint $endpoint): JsonResponse
    {
        $endpoint->delete();

        return $this->successResponse([
            'id' => $endpoint->id,
            'deleted' => true,
        ], 'Webhook endpoint deleted');
    }
}

