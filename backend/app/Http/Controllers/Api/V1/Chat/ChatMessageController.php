<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\SendChatMessageRequest;
use App\Http\Requests\Api\UpdateChatMessageRequest;
use App\Http\Resources\Chat\ChatMessageResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\Chat\ChatConversationQueryService;
use App\Services\Chat\ChatMessageService;
use Illuminate\Http\JsonResponse;

class ChatMessageController extends BaseController
{
    public function __construct(
        protected ChatMessageService $messageService,
        protected ChatConversationQueryService $queryService,
    ) {
    }

    public function store(SendChatMessageRequest $request, Conversation $conversation): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $message = $this->messageService->sendMessage($user, $conversation, $request->validated());

        $payload = (new ChatMessageResource($message))
            ->withAdminMetadata($this->queryService->applyAdminMetadataGate($user, $conversation))
            ->resolve();

        return $this->successResponse($payload, 'Message sent', 201);
    }

    public function update(UpdateChatMessageRequest $request, Message $message): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $updated = $this->messageService->editMessage($user, $message, $request->validated());

        $conversation = $updated->conversation;
        $payload = (new ChatMessageResource($updated))
            ->withAdminMetadata($conversation ? $this->queryService->applyAdminMetadataGate($user, $conversation) : false)
            ->resolve();

        return $this->successResponse($payload, 'Message updated');
    }

    public function destroy(Message $message): JsonResponse
    {
        /** @var User $user */
        $user = request()->user();
        $deleted = $this->messageService->deleteMessage($user, $message);

        return $this->successResponse([
            'id' => $deleted->id,
            'conversation_id' => $deleted->conversation_id,
            'status' => $deleted->status,
            'deleted_at' => $deleted->deleted_at?->toISOString(),
        ], 'Message deleted');
    }
}

