<?php

namespace App\Http\Resources;

use App\Models\Role;
use App\Services\Localization\RbacLocalizationService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Role $role */
        $role = $this->resource;
        $localization = app(RbacLocalizationService::class);

        return [
            'id' => $role->id,
            'name' => $role->name,
            'label' => $localization->getRoleLabel($role),
            'description' => $localization->getRoleDescription($role),
            'translations' => $localization->getRoleTranslations($role),
        ];
    }
}
