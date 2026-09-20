<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckTrialMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if ($user) {
            $trial = $user->trial_status;
            if (!$trial['active']) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Your 14-day free trial has expired. Please contact support.',
                        'trial_expired' => true,
                    ], 403);
                }
            }
        }
        return $next($request);
    }
}
