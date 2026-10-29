<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RemitaSplitApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $provided = trim((string) $request->header('X-API-Key', ''));
        $expected = trim((string) config('services.remita_split_report.api_key', ''));
        if ($expected === '' || $provided === '' || ! hash_equals($expected, $provided)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - Invalid API key.'], 401);
        }
        return $next($request);
    }
}
