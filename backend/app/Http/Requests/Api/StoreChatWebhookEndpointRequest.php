<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChatWebhookEndpointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'url' => ['required', 'url', 'max:2048'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['required', 'string', Rule::in([
                'message.created',
                'message.updated',
                'message.deleted',
                'message.read',
                'message.device_read',
                'message.delivery.updated',
                'conversation.created',
                'participant.joined',
                'participant.left',
                'participant.blocked',
                'participant.unblocked',
                'attachment.created',
                'participant.access_changed',
            ])],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
