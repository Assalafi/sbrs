<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicantAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('applicant')->check()) {
            return redirect()->route('applicant.login');
        }

        return $next($request);
    }
}
