<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
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
            'label' => $this->resolvePermissionLabel($name),
        ];
    }

    protected function resolvePermissionLabel(string $name): string
    {
        $candidates = [
            'permissions.' . $name,
            'permissions.permission.' . $name,
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

