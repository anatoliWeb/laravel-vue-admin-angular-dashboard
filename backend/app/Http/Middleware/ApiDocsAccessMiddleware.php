<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ApiDocsAccessMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (app()->environment(['local', 'testing'])) {
            return $next($request);
        }

        if (Gate::allows('viewApiDocs')) {
            return $next($request);
        }

        abort(403);
    }
}

