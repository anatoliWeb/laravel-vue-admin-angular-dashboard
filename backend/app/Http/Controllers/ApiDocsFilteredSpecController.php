<?php

namespace App\Http\Controllers;

use App\Services\ApiDocsOpenApiFilterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;

class ApiDocsFilteredSpecController extends Controller
{
    public function __invoke(
        Request $request,
        Router $router,
        ApiDocsOpenApiFilterService $filterService
    ): JsonResponse {
        $baseSpecRequest = Request::create('/docs/api.json', 'GET');
        $baseSpecRequest->setUserResolver($request->getUserResolver());
        $baseSpecRequest->attributes->set('api_docs_internal_raw_access', true);

        $baseSpecResponse = $router->dispatch($baseSpecRequest);
        $decodedSpec = json_decode($baseSpecResponse->getContent(), true);
        $spec = is_array($decodedSpec) ? $decodedSpec : [];

        $filteredSpec = $filterService->filterForUser($spec, $request->user());

        return response()->json($filteredSpec);
    }
}
