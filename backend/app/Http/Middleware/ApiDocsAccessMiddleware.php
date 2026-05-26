<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ApiDocsAccessMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($this->shouldBypassInLocal()) {
            return $next($request);
        }

        if ($this->isInternalRawDocsDispatch($request)) {
            return $next($request);
        }

        $gate = $this->isRawDocsRoute($request) ? 'viewFullApiDocs' : 'viewApiDocs';

        if (Gate::allows($gate)) {
            return $next($request);
        }

        abort(403);
    }

    private function shouldBypassInLocal(): bool
    {
        $localBypassEnabled = (bool) config('api-docs.local_bypass', false);

        return app()->environment(['local', 'testing']) && $localBypassEnabled;
    }

    private function isRawDocsRoute(Request $request): bool
    {
        $path = '/'.ltrim($request->path(), '/');

        return in_array($path, ['/docs/api', '/docs/api.json'], true);
    }

    private function isInternalRawDocsDispatch(Request $request): bool
    {
        return $this->isRawDocsRoute($request)
            && (bool) $request->attributes->get('api_docs_internal_raw_access', false);
    }
}
