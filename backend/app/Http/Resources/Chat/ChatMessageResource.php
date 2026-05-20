<?php

namespace App\Http\Resources\Chat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageResource extends JsonResource
{
    protected bool $canViewAdminMetadata = false;

    public function withAdminMetadata(bool $allowed): self
    {
        $this->canViewAdminMetadata = $allowed;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'conversation_id' => $this->conversation_id,
            'sender_id' => $this->sender_id,
            'sender_type' => $this->sender_type,
            'type' => $this->type,
            'body' => $this->body,
            'status' => $this->status,
            'is_imported' => (bool) $this->is_imported,
            'sent_at' => $this->sent_at?->toISOString(),
            'edited_at' => $this->edited_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'attachments_count' => (int) ($this->attachments_count ?? 0),
            'attachments' => ChatAttachmentResource::collection($this->whenLoaded('attachments')),
        ];

        if ($this->canViewAdminMetadata) {
            $data['imported_from_conversation_id'] = $this->imported_from_conversation_id;
            $data['imported_from_message_id'] = $this->imported_from_message_id;
        }

        return $data;
    }
}
