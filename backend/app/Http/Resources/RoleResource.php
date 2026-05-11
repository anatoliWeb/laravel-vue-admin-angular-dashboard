<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $name = (string) data_get($this->resource, 'name', '');

        return [
            'id' => data_get($this->resource, 'id'),
            'name' => $name,
            'label' => $this->resolveRoleLabel($name),
        ];
    }

    protected function resolveRoleLabel(string $name): string
    {
        $candidates = [
            'roles.' . $name,
            'roles.role.' . $name,
        ];

        foreach ($candidates as $key) {
            $translated = dt($key);
            if ($translated !== $key) {
                return $translated;
            }
        }

        return $name;
    }
}

