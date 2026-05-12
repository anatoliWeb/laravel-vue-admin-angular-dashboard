<?php

namespace App\Http\Resources;

use App\Models\Permission;
use App\Services\Localization\RbacLocalizationService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Permission $permission */
        $permission = $this->resource;
        $localization = app(RbacLocalizationService::class);

        return [
            'id' => $permission->id,
            'name' => $permission->name,
            'label' => $localization->getPermissionLabel($permission),
            'description' => $localization->getPermissionDescription($permission),
            'translations' => $localization->getPermissionTranslations($permission),
        ];
    }
}
