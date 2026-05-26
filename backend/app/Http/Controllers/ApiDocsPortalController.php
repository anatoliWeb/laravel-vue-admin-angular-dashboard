<?php

namespace App\Http\Controllers;

use App\Services\ApiDocsPermissionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ApiDocsPortalController extends Controller
{
    public function __invoke(Request $request, ApiDocsPermissionService $permissionService): View
    {
        $user = $request->user();
        $allGroups = $permissionService->groups();
        $hasFullAccess = $permissionService->userHasFullDocsAccess($user);

        $visibleGroups = [];
        $hasPermissionScopedVisibility = false;
        foreach ($allGroups as $groupKey => $group) {
            if ($permissionService->userCanSeeGroup($user, $groupKey)) {
                $visibleGroups[$groupKey] = $group;

                if (
                    count((array) ($group['permissions_any'] ?? [])) > 0
                    || count((array) ($group['permissions_all'] ?? [])) > 0
                ) {
                    $hasPermissionScopedVisibility = true;
                }
            }
        }

        if (! $hasFullAccess && ! $hasPermissionScopedVisibility) {
            $visibleGroups = [];
        }

        return view('docs.api-portal', [
            'visibleGroups' => $visibleGroups,
            'hasFullAccess' => $hasFullAccess,
            'docsUiUrl' => '/docs/api',
            'docsJsonUrl' => '/docs/api.json',
        ]);
    }
}
