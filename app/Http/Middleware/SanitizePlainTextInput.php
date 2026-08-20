<?php

namespace App\Http\Middleware;

use App\Support\PlainTextSanitizer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizePlainTextInput
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/webhooks/*')) {
            return $next($request);
        }

        $request->merge(PlainTextSanitizer::clean($request->all()));

        return $next($request);
    }
}
