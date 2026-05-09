<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * User API resource.
 *
 * WHY THIS RESOURCE EXISTS:
 * API consumers (Vue admin, Angular dashboard, mobile clients) need a stable
 * and explicit contract that does not depend on internal model/service shape.
 *
 * WHY NOT RETURN RAW ELOQUENT:
 * Returning model instances directly can accidentally leak internal fields
 * and couples clients to backend implementation details.
 *
 * WHAT THIS RESOURCE CONTROLS:
 * It explicitly defines which user fields are exposed and keeps output
 * frontend-friendly and version-safe.
 */
class UserResource extends JsonResource
{
    /**
     * Transform a user payload into a stable API structure.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => data_get($this->resource, 'id'),
            'name' => data_get($this->resource, 'name'),
            'email' => data_get($this->resource, 'email'),
            'roles' => array_values(data_get($this->resource, 'roles', [])),
            'permissions' => array_values(data_get($this->resource, 'permissions', [])),
            'denied_permissions' => array_values(data_get($this->resource, 'denied_permissions', [])),
            'created_at' => data_get($this->resource, 'created_at'),
        ];
    }
}
