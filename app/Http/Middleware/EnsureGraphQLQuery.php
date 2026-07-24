<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnsureGraphQLQuery
{
    public function handle(Request $request, Closure $next): mixed
    {
        $query = $request->input('query');
        $queryId = $request->input('queryId');

        if ($query === null && $queryId === null) {
            return new JsonResponse([
                'message' => 'GraphQL request must include either a "query" or "queryId" parameter.',
            ], 400);
        }

        return $next($request);
    }
}

