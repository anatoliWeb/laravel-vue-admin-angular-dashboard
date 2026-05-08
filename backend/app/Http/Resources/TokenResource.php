<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Token API resource.
 *
 * WHY THIS RESOURCE EXISTS:
 * Token responses are security-sensitive and should expose only a deliberate
 * subset of fields required by clients.
 *
 * WHY NOT RETURN RAW MODELS:
 * Raw token models include internal attributes and implementation details that
 * should not be part of a public API contract.
 *
 * WHAT THIS RESOURCE CONTROLS:
 * It standardizes token payload shape for list/create flows and keeps owner
 * information consistent across endpoints.
 */
class TokenResource extends JsonResource
{
    /**
     * Transform token payload into stable API structure.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => data_get($this->resource, 'id'),
            'name' => data_get($this->resource, 'name'),
            'created_at' => data_get($this->resource, 'created_at'),
            'owner' => [
                'id' => data_get($this->resource, 'owner.id'),
                'name' => data_get($this->resource, 'owner.name'),
            ],
        ];
    }
}
