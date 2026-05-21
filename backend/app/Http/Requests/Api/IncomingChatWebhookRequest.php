<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IncomingChatWebhookRequest extends FormRequest
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
            'event' => ['required', 'string', Rule::in(['message.created'])],
            'conversation_id' => ['required', 'integer', 'exists:conversations,id'],
            'external_provider' => ['required', 'string', 'max:100'],
            'external_message_id' => ['required', 'string', 'max:191'],
            'body' => ['required', 'string', 'max:10000'],
            'type' => ['nullable', Rule::in(['text', 'system'])],
            'sent_at' => ['nullable', 'date'],
            'metadata' => ['nullable', 'array'],
            'idempotency_key' => ['nullable', 'string', 'max:191'],
        ];
    }
}

